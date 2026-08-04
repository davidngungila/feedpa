<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;
use InvalidArgumentException;

class WhatsAppService
{
    protected string $wasenderBaseUrl;
    protected ?string $wasenderSessionApiKey;
    protected ?string $wasenderPersonalAccessToken;

    public function __construct()
    {
        $this->wasenderBaseUrl = config('services.whatsapp.wasender_base_url', 'https://www.wasenderapi.com/api');
        $this->wasenderSessionApiKey = SystemSetting::get('whatsapp_session_api_key') ?? config('services.whatsapp.session_api_key');
        $this->wasenderPersonalAccessToken = SystemSetting::get('whatsapp_personal_access_token') ?? config('services.whatsapp.personal_access_token');
    }

    // =========================================================================
    // WASENDER API METHODS (using wasenderapi.com)
    // =========================================================================

    protected function getWasenderApiKey(): ?string
    {
        return $this->wasenderSessionApiKey;
    }

    protected function sendWasenderRequest(array $payload): array
    {
        $apiKey = $this->getWasenderApiKey();

        if (!$apiKey) {
            Log::error('WhatsApp WasenderAPI: No session API key configured');
            return [
                'success' => false,
                'error' => 'No API key configured',
                'message' => 'WhatsApp session API key is not set. Please configure it in settings.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(30)->post($this->wasenderBaseUrl . '/send-message', $payload);

            $body = $response->json();

            if ($response->successful() && (isset($body['success']) || isset($body['status']) || $response->ok())) {
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? $body,
                    'message' => $body['message'] ?? 'Message sent successfully',
                ];
            }

            Log::error('WhatsApp WasenderAPI request failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error'   => 'API request failed',
                'status'  => $response->status(),
                'message' => $body['message'] ?? $response->body() ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI exception', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error'   => 'Exception occurred',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function sendText(string $to, string $text, array $options = []): array
    {
        $payload = array_merge([
            'to'   => $to,
            'text' => $text,
        ], $options);

        return $this->sendWasenderRequest($payload);
    }

    public function sendImage(string $to, string $imageUrl, ?string $caption = null, array $options = []): array
    {
        $payload = array_merge([
            'to'       => $to,
            'imageUrl' => $imageUrl,
        ], $options);

        if ($caption) {
            $payload['text'] = $caption;
        }

        return $this->sendWasenderRequest($payload);
    }

    public function sendDocument(string $to, string $documentUrl, string $fileName, ?string $caption = null, array $options = []): array
    {
        $payload = array_merge([
            'to'          => $to,
            'documentUrl' => $documentUrl,
            'fileName'    => $fileName,
        ], $options);

        if ($caption) {
            $payload['text'] = $caption;
        }

        return $this->sendWasenderRequest($payload);
    }

    // =========================================================================
    // WASENDER MEDIA UTILITY METHODS
    // =========================================================================

    public function uploadFile($file, ?string $contentType = null): array
    {
        $uploadUrl = rtrim($this->wasenderBaseUrl, '/api') . '/api/upload';
        $apiKey = $this->getWasenderApiKey();

        if (!$apiKey) {
            Log::error('WhatsApp WasenderAPI uploadFile: No session API key configured');
            return [
                'success' => false,
                'message' => 'Session API Key is not set. Please configure it in WhatsApp settings.',
            ];
        }

        try {
            $fileContent = null;
            $detectedType = $contentType;
            $fileName = 'file';

            if (is_string($file) && file_exists($file)) {
                $fileContent = file_get_contents($file);
                $fileName = basename($file);
                if (!$detectedType && function_exists('mime_content_type')) {
                    $detectedType = mime_content_type($file);
                }
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                $fileContent = file_get_contents($file->getRealPath());
                $fileName = $file->getClientOriginalName();
                if (!$detectedType) {
                    $detectedType = $file->getMimeType();
                }
            } elseif (is_resource($file)) {
                $fileContent = stream_get_contents($file);
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid file input. Provide a file path, UploadedFile, or resource.',
                ];
            }

            if ($fileContent === false || $fileContent === '') {
                return [
                    'success' => false,
                    'message' => 'Could not read file or file is empty.',
                ];
            }

            $finalContentType = $detectedType ?: 'application/octet-stream';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(120)
              ->attach('file', $fileContent, $fileName, ['Content-Type' => $finalContentType])
              ->post($uploadUrl);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? $body,
                    'message' => $body['message'] ?? 'File uploaded successfully',
                ];
            }

            Log::error('WhatsApp WasenderAPI uploadFile failed', [
                'status'       => $response->status(),
                'body'         => $response->body(),
                'content_type' => $finalContentType,
            ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'message' => $response->json('message') ?? $response->body() ?? 'Failed to upload file',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI uploadFile exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // =========================================================================
    // SESSION MANAGEMENT
    // =========================================================================

    public function getSessions(): array
    {
        if (!$this->wasenderPersonalAccessToken) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->wasenderPersonalAccessToken,
            ])->get($this->wasenderBaseUrl . '/whatsapp-sessions');

            if ($response->successful()) {
                return $response->json('data', []);
            }

            return [];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI getSessions exception: ' . $e->getMessage());
            return [];
        }
    }

    public function getSessionInfo(): ?array
    {
        $apiKey = $this->getWasenderApiKey();
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->get($this->wasenderBaseUrl . '/user');

            if ($response->successful()) {
                return $response->json('data', null);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI getSessionInfo exception: ' . $e->getMessage());
            return null;
        }
    }
}