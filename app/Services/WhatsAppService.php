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
        $this->wasenderBaseUrl = SystemSetting::get('whatsapp_base_url') ?? config('services.whatsapp.wasender_base_url', 'https://www.wasenderapi.com/api');
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

    public function hasSessionApiKey(): bool
    {
        return !empty($this->wasenderSessionApiKey);
    }

    public function hasPersonalAccessToken(): bool
    {
        return !empty($this->wasenderPersonalAccessToken);
    }

    protected function request(string $method, string $path, array $data = [], bool $usePersonalToken = false): array
    {
        $token = $usePersonalToken ? $this->wasenderPersonalAccessToken : $this->wasenderSessionApiKey;

        if (!$token) {
            $label = $usePersonalToken ? 'Personal access token' : 'Session API key';
            Log::error("WhatsApp WasenderAPI {$method} {$path}: {$label} not configured");
            return [
                'success' => false,
                'message' => "{$label} is not set. Please configure it in WhatsApp settings.",
            ];
        }

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(30);

            $url = $this->wasenderBaseUrl . $path;

            $response = match (strtoupper($method)) {
                'GET'    => $http->get($url),
                'POST'   => $http->post($url, $data),
                'PUT'    => $http->put($url, $data),
                'DELETE' => $http->delete($url),
                default  => $http->get($url),
            };

            $body = $response->json() ?? [];

            if ($response->successful() && ($body['success'] ?? false) === true) {
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? null,
                    'message' => $body['message'] ?? 'Request completed successfully',
                    'status'  => $response->status(),
                    'response' => $body,
                ];
            }

            Log::error('WhatsApp WasenderAPI request failed', [
                'method' => $method,
                'path'   => $path,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $body['message'] ?? $body['error'] ?? $response->body(),
                'status'  => $response->status(),
                'response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error("WhatsApp WasenderAPI {$method} {$path} exception", ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function sendWasenderRequest(array $payload): array
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
        $url = rtrim($this->wasenderBaseUrl, '/api') . '/api/send-message';
        $apiKey = $this->getWasenderApiKey();

        if (!$apiKey) {
            Log::error('WhatsApp WasenderAPI sendDocument: No session API key configured');
            return [
                'success' => false,
                'message' => 'Session API Key is not set. Please configure it in WhatsApp settings.',
            ];
        }

        try {
            $client = new \GuzzleHttp\Client();
            
            $payload = [
                'to' => $to,
                'text' => $caption ?? '',
                'documentUrl' => $documentUrl,
            ];

            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 120,
            ]);

            $body = json_decode((string) $response->getBody(), true);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300 && (isset($body['success']) || isset($body['status']) || $statusCode === 200)) {
                return [
                    'success' => true,
                    'data' => $body['data'] ?? $body,
                    'message' => $body['message'] ?? 'Document sent successfully',
                ];
            }

            Log::error('WhatsApp WasenderAPI sendDocument failed', [
                'status' => $statusCode,
                'body' => (string) $response->getBody(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => $body['message'] ?? (string) $response->getBody() ?? 'Unknown error',
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = 'Request failed: ' . $e->getMessage();
            if ($e->hasResponse()) {
                $errorMessage .= "\nResponse: " . $e->getResponse()->getBody();
            }
            Log::error('WhatsApp WasenderAPI sendDocument exception: ' . $errorMessage);
            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp WasenderAPI sendDocument exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
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

            $finalContentType = $detectedType ?: 'application/pdf';

            // Use cURL for more control over the multipart request
            $ch = curl_init();
            $cfile = new \CURLFile($file, $fileName, $finalContentType);
            
            $postData = [
                'file' => $cfile
            ];

            curl_setopt($ch, CURLOPT_URL, $uploadUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: multipart/form-data'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error('WhatsApp WasenderAPI uploadFile cURL error: ' . $error);
                return [
                    'success' => false,
                    'message' => 'cURL error: ' . $error,
                ];
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                $body = json_decode($response, true);
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? $body,
                    'message' => $body['message'] ?? 'File uploaded successfully',
                ];
            }

            Log::error('WhatsApp WasenderAPI uploadFile failed', [
                'status'       => $httpCode,
                'body'         => $response,
                'content_type' => $finalContentType,
            ]);

            return [
                'success' => false,
                'status'  => $httpCode,
                'message' => 'Failed to upload file: ' . $response,
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

    // =========================================================================
    // LIVE GROUPS (captured from Wasender API)
    // =========================================================================

    public function getGroups(): array
    {
        $result = $this->request('GET', '/groups');

        if (($result['success'] ?? false) && is_array($result['data'])) {
            return $result['data'];
        }

        return [];
    }

    public function getGroupMetadata(string $jid): array
    {
        $result = $this->request('GET', '/groups/' . rawurlencode($jid) . '/metadata');

        if (($result['success'] ?? false) && is_array($result['data'])) {
            return $result['data'];
        }

        return [];
    }

    public function getGroupMetadataRaw(string $jid): array
    {
        return $this->request('GET', '/groups/' . rawurlencode($jid) . '/metadata');
    }

    public function getGroupParticipants(string $jid): array
    {
        $result = $this->request('GET', '/groups/' . rawurlencode($jid) . '/participants');

        if (($result['success'] ?? false) && is_array($result['data'])) {
            return $result['data'];
        }

        return [];
    }

    public function getGroupPicture(string $jid): ?string
    {
        $result = $this->request('GET', '/groups/' . rawurlencode($jid) . '/picture');

        if (($result['success'] ?? false) && !empty($result['data']['imgUrl'])) {
            return $result['data']['imgUrl'];
        }

        return null;
    }

    public function addGroupParticipants(string $jid, array $participants): array
    {
        return $this->request('POST', '/groups/' . rawurlencode($jid) . '/participants/add', [
            'participants' => $participants,
        ]);
    }

    public function removeGroupParticipants(string $jid, array $participants): array
    {
        return $this->request('DELETE', '/groups/' . rawurlencode($jid) . '/participants/remove', [
            'participants' => $participants,
        ]);
    }

    public function getMessageLogs(int|string $session, int $page = 1, int $perPage = 20): array
    {
        $result = $this->request('GET', '/whatsapp-sessions/' . rawurlencode((string) $session) . '/message-logs?page=' . $page . '&per_page=' . $perPage, [], true);

        if (($result['success'] ?? false) && is_array($result['data'])) {
            return [
                'success' => true,
                'data'    => $result['data'],
                'message' => $result['message'] ?? 'Message logs retrieved.',
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Failed to fetch message logs.',
        ];
    }

    public function getSessionLogs(int|string $session, int $page = 1, int $perPage = 15): array
    {
        $result = $this->request('GET', '/whatsapp-sessions/' . rawurlencode((string) $session) . '/session-logs?page=' . $page . '&per_page=' . $perPage, [], true);

        if (($result['success'] ?? false) && is_array($result['data'])) {
            return [
                'success' => true,
                'data'    => $result['data'],
                'message' => $result['message'] ?? 'Session logs retrieved.',
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Failed to fetch session logs.',
        ];
    }

    // =========================================================================
    // LIVE SESSIONS (captured from Wasender API using Personal Access Token)
    // =========================================================================

    public function getSessionDetails(int $id): array
    {
        $result = $this->request('GET', '/whatsapp-sessions/' . $id, [], true);

        if (($result['success'] ?? false) && is_array($result['data'])) {
            return $result['data'];
        }

        return [];
    }

    public function createSession(array $data = []): array
    {
        return $this->request('POST', '/whatsapp-sessions', $data, true);
    }

    public function updateSession(int $id, array $data = []): array
    {
        return $this->request('PUT', '/whatsapp-sessions/' . $id, $data, true);
    }

    public function connectSession(int $id): array
    {
        return $this->request('POST', '/whatsapp-sessions/' . $id . '/connect', [], true);
    }

    public function disconnectSession(int $id): array
    {
        return $this->request('POST', '/whatsapp-sessions/' . $id . '/disconnect', [], true);
    }

    public function restartSession(int $id): array
    {
        return $this->request('POST', '/whatsapp-sessions/' . $id . '/restart', [], true);
    }

    public function deleteSession(int $id): array
    {
        return $this->request('DELETE', '/whatsapp-sessions/' . $id, [], true);
    }

    public function getSessionQr(int $id): array
    {
        return $this->request('GET', '/whatsapp-sessions/' . $id . '/qrcode', [], true);
    }

    public function getSessionStatus(int $id): array
    {
        return $this->request('GET', '/whatsapp-sessions/' . $id, [], true);
    }
}