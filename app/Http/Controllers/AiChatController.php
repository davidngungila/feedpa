<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\Payout;
use App\Models\SystemSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    private array $imageGenerationTriggers = [
        'generate image', 'create image', 'draw', 'make a picture', 'picture of',
        'image of', 'photo of', 'illustration of', 'generate a picture', 'generate a photo',
        'picha', 'unda picha', 'chora', 'generate artwork', 'create artwork',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        $sessions = $user
            ? AiChatSession::where('user_id', $user->id)
                ->withCount('messages')
                ->latest()
                ->get()
            : collect();

        $activeSessionId = $request->get('session');
        if ($activeSessionId) {
            $activeSession = $user
                ? AiChatSession::where('id', $activeSessionId)->where('user_id', $user->id)->first()
                : null;
            if (!$activeSession) {
                $activeSessionId = null;
            }
        }

        if (!$activeSessionId && $sessions->isNotEmpty()) {
            $activeSessionId = $sessions->first()->id;
        }

        $chatHistory = $user && $activeSessionId
            ? AiChatMessage::where('user_id', $user->id)
                ->where('chat_session_id', $activeSessionId)
                ->oldest()
                ->get()
            : collect();

        return view('ai-chat.index', compact('chatHistory', 'sessions', 'activeSessionId'));
    }

    public function newSession()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        $session = AiChatSession::create([
            'user_id' => $user->id,
            'title' => 'New Chat',
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
        ]);
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable',
            'session_id' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $apiKey = SystemSetting::get('openrouter_api_key');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'OpenRouter API key not configured. Please set it in AI Settings.',
            ], 400);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        try {
            // Resolve the session for this message (create one if needed)
            $session = null;
            if (!empty($validated['session_id'])) {
                $session = AiChatSession::where('id', $validated['session_id'])
                    ->where('user_id', $user->id)
                    ->first();
            }
            if (!$session) {
                $session = AiChatSession::create([
                    'user_id' => $user->id,
                    'title' => Str::limit($request->message, 50),
                ]);
            }

            $history = $validated['history'] ?? [];
            if (is_string($history)) {
                $decodedHistory = json_decode($history, true);
                $history = is_array($decodedHistory) ? $decodedHistory : [];
            }

            $imageFile = $request->file('image');
            $imagePath = null;

            // Save uploaded image if present
            if ($imageFile) {
                $imagePath = $imageFile->store('ai-chat-images', 'public');
            }
            $messages = [];

            // System prompt with rich platform + user data context
            $systemContext = $this->buildSystemContext($user);

            $messages[] = [
                'role' => 'system',
                'content' => $this->buildSystemPrompt($user, $systemContext),
            ];

            if (is_array($history)) {
                foreach ($history as $item) {
                    $role = $item['role'] ?? 'user';
                    // Map any invalid roles to valid ones
                    if (!in_array($role, ['system', 'user', 'assistant'])) {
                        $role = $role === 'model' ? 'assistant' : 'user';
                    }
                    $messages[] = [
                        'role' => $role,
                        'content' => $item['text'] ?? ''
                    ];
                }
            }

            $userMessage = [
                'role' => 'user',
                'content' => $request->message,
            ];

            $model = 'meta-llama/llama-3.3-70b-instruct';
            if ($imageFile) {
                $mimeType = $imageFile->getMimeType() ?: 'image/jpeg';
                $base64Image = base64_encode(file_get_contents($imageFile->getRealPath()));

                $userMessage['content'] = [
                    [
                        'type' => 'text',
                        'text' => $request->message,
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$base64Image}",
                        ],
                    ],
                ];

                $model = 'meta-llama/llama-4-scout';
            }

            $messages[] = $userMessage;

            // Check if user wants to generate an image
            if ($this->isImageGenerationRequest($request->message)) {
                return $this->generateImage($apiKey, $request->message, $user, $session, $imagePath);
            }

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'HTTP-Referer' => config('app.url'),
                    'X-OpenRouter-Title' => config('app.name'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 2048,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $aiResponse = $result['choices'][0]['message']['content'] ?? null;

                if ($aiResponse) {
                    // Save user's message to DB
                    AiChatMessage::create([
                        'user_id' => $user->id,
                        'chat_session_id' => $session->id,
                        'role' => 'user',
                        'content' => $request->message,
                        'image_path' => $imagePath,
                    ]);

                    // Save assistant's response to DB
                    AiChatMessage::create([
                        'user_id' => $user->id,
                        'chat_session_id' => $session->id,
                        'role' => 'assistant',
                        'content' => $aiResponse,
                        'image_path' => null,
                    ]);

                    if (empty($session->title) || $session->title === 'New Chat') {
                        $session->update(['title' => Str::limit($request->message, 50)]);
                    }

                    return response()->json([
                        'success' => true,
                        'response' => $aiResponse,
                        'session_id' => $session->id,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'AI Error: No response text received from OpenRouter.',
                    ], 500);
                }
            } else {
                Log::error('OpenRouter API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI Error: OpenRouter API failed (status ' . $response->status() . '): ' . $response->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('AI Chat exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function isImageGenerationRequest(string $message): bool
    {
        $lower = strtolower($message);
        foreach ($this->imageGenerationTriggers as $trigger) {
            if (str_contains($lower, $trigger)) {
                return true;
            }
        }
        return false;
    }

    protected function generateImage(string $apiKey, string $prompt, $user, $session, ?string $imagePath)
    {
        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'HTTP-Referer' => config('app.url'),
                    'X-OpenRouter-Title' => config('app.name'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://openrouter.ai/api/v1/images', [
                    'model' => 'bytedance-seed/seedream-4.5',
                    'prompt' => $prompt,
                    'n' => 1,
                    'resolution' => '1K',
                ]);

            Log::info('OpenRouter Image API response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $imageData = $result['data'][0]['b64_json'] ?? null;

                if ($imageData) {
                    $imageFileName = 'ai-generated-' . time() . '.png';
                    $imageStoragePath = 'ai-chat-images/' . $imageFileName;
                    $fullPath = storage_path('app/public/' . $imageStoragePath);

                    $dir = dirname($fullPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    file_put_contents($fullPath, base64_decode($imageData));

                    // Save user message
                    AiChatMessage::create([
                        'user_id' => $user->id,
                        'chat_session_id' => $session->id,
                        'role' => 'user',
                        'content' => $prompt,
                        'image_path' => $imagePath,
                    ]);

                    // Save assistant response with generated image
                    AiChatMessage::create([
                        'user_id' => $user->id,
                        'chat_session_id' => $session->id,
                        'role' => 'assistant',
                        'content' => "Here is the image I generated for you: **{$prompt}**",
                        'image_path' => $imageStoragePath,
                    ]);

                    if (empty($session->title) || $session->title === 'New Chat') {
                        $session->update(['title' => Str::limit($prompt, 50)]);
                    }

                    return response()->json([
                        'success' => true,
                        'response' => "Here is the image I generated for you based on: \"{$prompt}\"",
                        'image_url' => '/storage/' . $imageStoragePath,
                        'session_id' => $session->id,
                    ]);
                }
            }

            Log::error('OpenRouter Image API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $errorBody = json_decode($response->body(), true);
            $errorMessage = $errorBody['error']['message'] ?? 'Image generation failed. Please try again.';

            return response()->json([
                'success' => false,
                'message' => 'Image generation error: ' . $errorMessage,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Image generation exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Image generation error: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function buildSystemContext($user): string
    {
        $context = "## Platform Overview\n";
        $context .= "Feedtan Digital Payment System (pay.feedtancmg.org) is a payment platform operated by FEEDTAN GROUP. ";
        $context .= "It enables businesses to collect payments online (via ClickPesa) and to initiate, approve and disburse payouts (bank transfers & mobile money). ";
        $context .= "Key modules: Payments/Transactions, Payouts (initiation -> OTP verify -> approval -> OTP request -> authorization -> processing -> completed), Bills, Beneficiaries, Reports, Notifications, and system settings.\n";

        $context .= "\n## Current User\n";
        if ($user) {
            $context .= "- Name: {$user->name}\n";
            $context .= "- Email: {$user->email}\n";
            $context .= "- Phone: {$user->phone}\n";
            $context .= "- Position: {$user->position}\n";
            $context .= "- Role: " . ($user->is_admin ? 'Administrator' : 'Staff member') . "\n";
            $context .= "- Can create payouts: " . ($user->can_create_payouts ? 'Yes' : 'No') . "\n";
        }

        try {
            $context .= "\n## Account Balances\n";
            $balances = \App\Models\AccountBalance::all();
            if ($balances->count() > 0) {
                foreach ($balances as $balance) {
                    $context .= "- {$balance->currency}: {$balance->balance} (synced {$balance->synced_at})\n";
                }
            } else {
                $context .= "- No balance records synced yet.\n";
            }
        } catch (\Throwable $e) {
            $context .= "- Unavailable.\n";
        }

        $context .= "\n## Summary Statistics\n";
        try {
            $txCount = \App\Models\Transaction::count();
            $txTotal = \App\Models\Transaction::where('status', 'SUCCESS')->sum('amount');
            $txSuccess = \App\Models\Transaction::where('status', 'SUCCESS')->count();
            $txPending = \App\Models\Transaction::whereIn('status', ['PENDING', 'PENDING_OTP'])->count();

            $payoutCount = \App\Models\Payout::count();
            $payoutTotal = \App\Models\Payout::where('status', 'SUCCESS')->sum('amount');
            $payoutSuccess = \App\Models\Payout::where('status', 'SUCCESS')->count();
            $payoutPending = \App\Models\Payout::whereNotIn('status', ['SUCCESS', 'FAILED', 'REJECTED'])->count();

            $context .= "- Total transactions: {$txCount}\n";
            $context .= "- Successful transactions: {$txSuccess}\n";
            $context .= "- Pending transactions: {$txPending}\n";
            $context .= "- Total collected (SUCCESS): {$txTotal}\n";
            $context .= "- Total payouts: {$payoutCount}\n";
            $context .= "- Successful payouts: {$payoutSuccess}\n";
            $context .= "- Payouts in progress: {$payoutPending}\n";
            $context .= "- Total paid out (SUCCESS): {$payoutTotal}\n";
        } catch (\Throwable $e) {
            $context .= "- Unavailable.\n";
        }

        try {
            $recentTransactions = \App\Models\Transaction::latest()->take(10)->get();
            $context .= "\n## Recent Transactions (Last 10)\n";
            if ($recentTransactions->count() > 0) {
                foreach ($recentTransactions as $t) {
                    $context .= "- ID: {$t->id}, Ref: {$t->order_reference}, Status: {$t->status}, Amount: {$t->amount} {$t->currency}, Type: {$t->type}, Payer: {$t->payer_name}, Date: {$t->created_at}\n";
                }
            } else {
                $context .= "- None yet.\n";
            }
        } catch (\Throwable $e) {
            $context .= "- Unavailable.\n";
        }

        try {
            $recentPayouts = \App\Models\Payout::latest()->take(10)->get();
            $context .= "\n## Recent Payouts (Last 10)\n";
            if ($recentPayouts->count() > 0) {
                foreach ($recentPayouts as $p) {
                    $context .= "- ID: {$p->id}, Ref: {$p->order_reference}, Status: {$p->status}, Stage: {$p->workflow_stage}, Amount: {$p->amount} {$p->currency}, Type: {$p->payout_type}, Recipient: {$p->recipient_name}, Date: {$p->created_at}\n";
                }
            } else {
                $context .= "- None yet.\n";
            }
        } catch (\Throwable $e) {
            $context .= "- Unavailable.\n";
        }

        try {
            $beneficiaries = \App\Models\Beneficiary::where('user_id', $user->id)->where('is_active', true)->get();
            $context .= "\n## Active Beneficiaries\n";
            if ($beneficiaries->count() > 0) {
                foreach ($beneficiaries as $b) {
                    $context .= "- ID: {$b->id}, Name: {$b->name}, Type: {$b->type}, Phone: {$b->phone}, Bank: {$b->bank_name}, Account: {$b->account_number}\n";
                }
            } else {
                $context .= "- None yet.\n";
            }
        } catch (\Throwable $e) {
            $context .= "- Unavailable.\n";
        }

        try {
            $bills = \App\Models\BillPayNumber::latest()->take(10)->get();
            $context .= "\n## Recent Bills\n";
            if ($bills->count() > 0) {
                foreach ($bills as $b) {
                    $context .= "- Bill: {$b->bill_pay_number}, Amount: {$b->bill_amount} {$b->bill_currency}, Status: {$b->bill_status}, Customer: {$b->customer_name}, Type: {$b->bill_type}, Date: {$b->created_at}\n";
                }
            } else {
                $context .= "- None yet.\n";
            }
        } catch (\Throwable $e) {
            $context .= "- Unavailable.\n";
        }

        return $context;
    }

    protected function buildSystemPrompt($user, string $systemContext): string
    {
        $name = $user?->name ?? 'user';

        return <<<PROMPT
You are FEEDTAN AI, the smart assistant for the Feedtan Digital Payment System (pay.feedtancmg.org), operated by FEEDTAN GROUP.

You help $name with payments, transactions, payouts, bills, beneficiaries, and general questions about the platform.

## Your capabilities
- Answer questions about the user's recent payments/transactions, payouts, beneficiaries, bills and account balances using the system data below.
- Explain the payout workflow: Payout initiation by an authorized officer -> initiation OTP verification -> approval by an approver -> payment OTP request -> payment authorization -> processing -> completed (SUCCESS) or FAILED.
- Explain transaction statuses: PENDING (awaiting confirmation), SUCCESS (payment confirmed), FAILED (payment failed).
- Explain platform features: collecting payments via ClickPesa gateway, initiating bank transfer & mobile money payouts, bill payments, beneficiary management, reporting, notifications (SMS, WhatsApp, email), two-factor authentication, and payout WhatsApp notifications to recipients.
- Generate images when requested. When the user asks you to generate, create, draw, or make an image/picture/photo/illustration, respond with the request and the system will automatically generate the image.
- If you don't know or data is unavailable, say so honestly and offer the next step.

## Image Generation
When a user asks you to generate an image, you should respond normally acknowledging the request. The system will detect the image generation intent and call the image API automatically. Just respond as if you are fulfilling the request.

## Guidelines
- Answer in a clear, structured, helpful way. Use short paragraphs, bullet points and bold for emphasis where useful.
- Base your answers on the system data provided below. Do NOT invent amounts, statuses, or records that are not present.
- Reference specific order references, amounts and statuses when relevant.
- If the user asks about an order reference that is not in the recent list, tell them the list only shows the most recent 10 records and suggest viewing the full report in the dashboard.
- Always stay in character as the Feedtan platform assistant.

## Current System Data
$systemContext
PROMPT;
    }
}
