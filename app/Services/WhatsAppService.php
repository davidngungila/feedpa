<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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

            $body = $response->json() ?? [];

            if ($response->successful() && ($body['success'] ?? true) !== false) {
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

            if ($statusCode >= 200 && $statusCode < 300 && ($body['success'] ?? true) !== false) {
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
        $uploadUrl = rtrim($this->wasenderBaseUrl, '/') . '/upload';
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

            if (is_string($file) && file_exists($file)) {
                $fileContent = file_get_contents($file);
                if (!$detectedType && function_exists('mime_content_type')) {
                    $detectedType = mime_content_type($file);
                }
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                $fileContent = file_get_contents($file->getRealPath());
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
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->timeout(120)->post($uploadUrl, [
                'base64'   => 'data:' . $finalContentType . ';base64,' . base64_encode($fileContent),
                'mimetype' => $finalContentType,
            ]);

            $body = $response->json() ?? [];

            if ($response->successful() && ($body['success'] ?? false) === true) {
                return [
                    'success' => true,
                    'data'    => $body['data'] ?? null,
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
                'message' => $body['message'] ?? $body['error'] ?? $response->body(),
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
        return Cache::remember('whatsapp.groups.list', now()->addMinutes(2), function () {
            $result = $this->request('GET', '/groups');

            if (($result['success'] ?? false) && is_array($result['data'])) {
                return $result['data'];
            }

            return [];
        });
    }

    public function isRateLimited(array $result): bool
    {
        if (($result['status'] ?? null) === 429) {
            return true;
        }

        return stripos((string) ($result['message'] ?? ''), 'too many requests') !== false;
    }

    public function getGroupMetadata(string $jid): array
    {
        $result = $this->getGroupMetadataRaw($jid);

        if (($result['success'] ?? false) && is_array($result['data'])) {
            return $result['data'];
        }

        return [];
    }

    public function getGroupMetadataRaw(string $jid): array
    {
        $key = 'whatsapp.group.meta.' . md5($jid);

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $result = $this->request('GET', '/groups/' . rawurlencode($jid) . '/metadata');

        if ($result['success'] ?? false) {
            Cache::put($key, $result, now()->addMinutes(10));
        }

        return $result;
    }

    public function getGroupParticipants(string $jid): array
    {
        $key = 'whatsapp.group.participants.' . md5($jid);

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $result = $this->request('GET', '/groups/' . rawurlencode($jid) . '/participants');

        if (($result['success'] ?? false) && is_array($result['data'])) {
            Cache::put($key, $result['data'], now()->addMinutes(10));
            return $result['data'];
        }

        return [];
    }

    public function getGroupPicture(string $jid): ?string
    {
        return Cache::remember('whatsapp.group_picture.' . md5($jid), now()->addMinutes(10), function () use ($jid) {
            $result = $this->request('GET', '/groups/' . rawurlencode($jid) . '/picture');

            if (($result['success'] ?? false) && !empty($result['data']['imgUrl'])) {
                return $result['data']['imgUrl'];
            }

            return null;
        });
    }

    public function getContactsRaw(int $page = 1, int $limit = 100, bool $paginated = false): array
    {
        $key = 'whatsapp.contacts.' . $paginated . '.' . $page . '.' . $limit;

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $query = http_build_query([
            'paginated' => $paginated ? 'true' : 'false',
            'page'      => $page,
            'limit'     => $limit,
        ]);

        $result = $this->request('GET', '/contacts?' . $query);

        if ($result['success'] ?? false) {
            Cache::put($key, $result, now()->addMinutes(2));
        }

        return $result;
    }

    public function getContacts(int $page = 1, int $limit = 100, bool $paginated = false): array
    {
        $result = $this->getContactsRaw($page, $limit, $paginated);

        if (!($result['success'] ?? false) || !is_array($result['data'] ?? null)) {
            return [];
        }

        if (isset($result['data']['items']) && is_array($result['data']['items'])) {
            return $result['data']['items'];
        }

        return $result['data'];
    }

    protected function normalizePhone(string $contactId): string
    {
        $phone = preg_replace('/@.*$/', '', trim($contactId));

        return preg_replace('/\D/', '', $phone);
    }

    public function getContactInfoRaw(string $contactId): array
    {
        $phone = $this->normalizePhone($contactId);

        if (!$phone) {
            return ['success' => false, 'message' => 'Invalid contact identifier.'];
        }

        $key = 'whatsapp.contact.info.' . $phone;

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $result = $this->request('GET', '/contacts/' . rawurlencode($phone));

        if ($result['success'] ?? false) {
            Cache::put($key, $result, now()->addMinutes(10));
        }

        return $result;
    }

    public function getContactInfo(string $contactId): ?array
    {
        $result = $this->getContactInfoRaw($contactId);

        if (($result['success'] ?? false) && is_array($result['data'])) {
            return $result['data'];
        }

        return null;
    }

    public function getContactPicture(string $contactId): ?string
    {
        $phone = $this->normalizePhone($contactId);

        if (!$phone) {
            return null;
        }

        return Cache::remember('whatsapp.contact.picture.' . $phone, now()->addMinutes(10), function () use ($phone) {
            $result = $this->request('GET', '/contacts/' . rawurlencode($phone) . '/picture');

            if (($result['success'] ?? false) && !empty($result['data']['imgUrl'])) {
                return $result['data']['imgUrl'];
            }

            return null;
        });
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

    public function deleteMessage(int|string $msgId): array
    {
        $result = $this->request('DELETE', '/messages/' . rawurlencode((string) $msgId));

        if (!($result['success'] ?? false)) {
            $message = $result['message'] ?? 'Failed to delete the message.';
            if (is_string($message) && str_contains($message, '<')) {
                $message = 'Message not found or no longer deletable (code ' . ($result['status'] ?? 'unknown') . ').';
            }
            $result['message'] = $message;
        }

        return $result;
    }

    // =========================================================================
    // WASENDER RICH MESSAGE TYPES (POST /api/send-message)
    // =========================================================================

    public function sendMedia(string $to, string $type, string $url, ?string $caption = null, array $options = []): array
    {
        $type = strtolower($type);
        if (!in_array($type, ['image', 'video', 'audio', 'sticker', 'document'], true)) {
            throw new InvalidArgumentException("Unsupported media type: {$type}");
        }

        $payload = array_merge([
            'to'           => $to,
            $type . 'Url'  => $url,
        ], $options);

        if ($caption !== null && $caption !== '') {
            $payload['text'] = $caption;
        }

        return $this->sendWasenderRequest($payload);
    }

    public function sendContactCard(string $to, string $name, string $phone, array $options = []): array
    {
        $payload = array_merge([
            'to'      => $to,
            'contact' => [
                'name'  => $name,
                'phone' => $phone,
            ],
        ], $options);

        return $this->sendWasenderRequest($payload);
    }

    public function sendLocation(string $to, float $latitude, float $longitude, ?string $name = null, ?string $address = null, ?string $caption = null): array
    {
        $location = [
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ];

        if ($name !== null && $name !== '') {
            $location['name'] = $name;
        }

        if ($address !== null && $address !== '') {
            $location['address'] = $address;
        }

        $payload = [
            'to'       => $to,
            'location' => $location,
        ];

        if ($caption !== null && $caption !== '') {
            $payload['text'] = $caption;
        }

        return $this->sendWasenderRequest($payload);
    }

    public function sendPoll(string $to, string $question, array $options, bool $multiSelect = false): array
    {
        return $this->sendWasenderRequest([
            'to'   => $to,
            'poll' => [
                'question'    => $question,
                'options'     => array_values(array_filter(array_map('trim', $options))),
                'multiSelect' => $multiSelect,
            ],
        ]);
    }

    public function sendQuoted(string $to, int $replyTo, ?string $text = null, array $media = []): array
    {
        $payload = [
            'to'      => $to,
            'replyTo' => $replyTo,
        ];

        if ($text !== null && $text !== '') {
            $payload['text'] = $text;
        }

        foreach (['imageUrl', 'videoUrl', 'documentUrl', 'audioUrl', 'stickerUrl'] as $key) {
            if (!empty($media[$key])) {
                $payload[$key] = $media[$key];
            }
        }

        if (!empty($media['fileName'])) {
            $payload['fileName'] = $media['fileName'];
        }

        return $this->sendWasenderRequest($payload);
    }

    public function sendViewOnce(string $to, string $type, string $url): array
    {
        $type = strtolower($type);
        if (!in_array($type, ['image', 'video', 'audio'], true)) {
            throw new InvalidArgumentException("Unsupported view-once media type: {$type}");
        }

        return $this->sendWasenderRequest([
            'to'          => $to,
            $type . 'Url' => $url,
            'viewOnce'    => true,
        ]);
    }

    // =========================================================================
    // WASENDER MESSAGE MANAGEMENT
    // =========================================================================

    public function editMessage(int $msgId, string $text): array
    {
        $result = $this->request('PUT', '/messages/' . $msgId, ['text' => $text]);

        if (!($result['success'] ?? false)) {
            $message = $result['message'] ?? 'Failed to edit the message.';
            if (is_string($message) && str_contains($message, '<')) {
                $message = 'Message not found or no longer editable (code ' . ($result['status'] ?? 'unknown') . ').';
            }
            $result['message'] = $message;
        }

        return $result;
    }

    public function resendMessage(int $msgId): array
    {
        return $this->request('POST', '/messages/' . $msgId . '/resend');
    }

    public function getMessageInfo(int $msgId): array
    {
        return $this->request('GET', '/messages/' . $msgId . '/info');
    }

    public function markMessageRead(array $key): array
    {
        return $this->request('POST', '/messages/read', ['key' => $key]);
    }

    public function decryptMedia(array $data): array
    {
        return $this->request('POST', '/decrypt-media', ['data' => $data]);
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