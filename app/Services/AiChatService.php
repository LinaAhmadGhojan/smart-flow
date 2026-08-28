<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    public function enabled(): bool
    {
        return (bool) config('chat.ai_enabled') && (string) config('chat.api_key') !== '';
    }

    /**
     * @param  list<array{id: int, name: string, name_ar: ?string, brand: ?string, price: ?string}>  $products
     * @return array<string, mixed>|null
     */
    public function reply(string $message, bool $ar, array $products = []): ?array
    {
        if (!$this->enabled()) {
            return null;
        }

        try {
            $text = match (config('chat.provider')) {
                'gemini' => $this->askGemini($message, $ar, $products),
                default => $this->askGroq($message, $ar, $products),
            };

            if ($text === null || trim($text) === '') {
                return null;
            }

            return $this->formatReply(trim($text), $ar);
        } catch (\Throwable $e) {
            Log::warning('Chat AI failed: ' . $e->getMessage());

            return null;
        }
    }

    /** @param list<array{id: int, name: string, name_ar: ?string, brand: ?string, price: ?string}> $products */
    private function askGroq(string $message, bool $ar, array $products): ?string
    {
        $response = Http::withToken((string) config('chat.api_key'))
            ->timeout((int) config('chat.timeout'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('chat.model'),
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt($ar, $products)],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.35,
                'max_tokens' => (int) config('chat.max_tokens'),
            ]);

        if (!$response->successful()) {
            Log::warning('Groq chat error', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return $response->json('choices.0.message.content');
    }

    /** @param list<array{id: int, name: string, name_ar: ?string, brand: ?string, price: ?string}> $products */
    private function askGemini(string $message, bool $ar, array $products): ?string
    {
        $model = config('chat.gemini_model');
        $key = (string) config('chat.api_key');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

        $response = Http::timeout((int) config('chat.timeout'))
            ->post($url, [
                'systemInstruction' => ['parts' => [['text' => $this->systemPrompt($ar, $products)]]],
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $message]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.35,
                    'maxOutputTokens' => (int) config('chat.max_tokens'),
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Gemini chat error', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    /** @param list<array{id: int, name: string, name_ar: ?string, brand: ?string, price: ?string}> $products */
    private function systemPrompt(bool $ar, array $products): string
    {
        $company = $this->companyInfo();
        $phone = $company['contact']['phone'] ?? '+971 56 256 6232';
        $whatsapp = $company['contact']['whatsapp'] ?? '971562566232';
        $email = $company['contact']['email'] ?? 'info@smartflow.ae';
        $hoursAr = $company['workingHours']['ar'] ?? 'الاثنين–السبت 9–6';
        $hoursEn = $company['workingHours']['en'] ?? 'Mon–Sat 9 AM–6 PM';
        $aboutAr = $company['about']['ar'] ?? ($company['descriptionAr'] ?? '');
        $aboutEn = $company['about']['en'] ?? ($company['description'] ?? '');

        $productBlock = '';
        if ($products !== []) {
            $lines = [];
            foreach ($products as $p) {
                $label = trim($p['name_ar'] ?: $p['name']);
                if (!empty($p['brand'])) {
                    $label = $p['brand'] . ' — ' . $label;
                }
                $price = $p['price'] ?? '';
                $lines[] = "- #{$p['id']}: {$label}" . ($price !== '' ? " ({$price})" : '');
            }
            $productBlock = "\n\nProducts that may match the user's question (only mention if relevant, use exact prices shown):\n" . implode("\n", $lines);
        }

        $lang = $ar ? 'Arabic' : 'English';

        return <<<PROMPT
You are the customer assistant for Smart Flow (سمارت فلو / التدفق الذكي), a UAE company specializing in smart home automation, CCTV, security alarms, gate motors, networking, and project site studies.

Reply in {$lang} unless the user clearly writes in another language.

Company facts (use only these — do not invent prices, phone numbers, or services):
- WhatsApp: {$whatsapp} | Phone display: {$phone}
- Email: {$email}
- Hours (AR): {$hoursAr} | Hours (EN): {$hoursEn}
- About (AR): {$aboutAr}
- About (EN): {$aboutEn}

Website pages you can recommend:
- /project-study — smart home / villa project study form
- /gate-machine-study — external gate motor sizing study
- /products — product catalog

Rules:
- Be helpful, concise (max 120 words), friendly, professional.
- For support/contact questions, give WhatsApp as the fastest channel plus email and hours.
- For pricing, explain it depends on scope and suggest project study or WhatsApp — never guess amounts unless listed in products below.
- If unsure, suggest WhatsApp or project study form.
- Do not mention you are an AI model.
- Plain text only, no markdown headers.
{$productBlock}
PROMPT;
    }

    /** @return array<string, mixed> */
    private function formatReply(string $text, bool $ar): array
    {
        $company = $this->companyInfo();
        $whatsapp = $company['contact']['whatsapp'] ?? '971562566232';
        $actions = [
            [
                'label' => $ar ? 'واتساب' : 'WhatsApp',
                'href' => $this->whatsappHref($whatsapp),
            ],
        ];

        $lower = mb_strtolower($text);
        if ($this->mentions($lower, ['دراس', 'مشروع', 'project study', 'villa'])) {
            $actions[] = ['label' => $ar ? 'دراسة مشروع' : 'Project study', 'href' => '/project-study'];
        }
        if ($this->mentions($lower, ['بواب', 'باب', 'gate', 'motor', 'ماكين'])) {
            $actions[] = ['label' => $ar ? 'دراسة ماكينة باب' : 'Gate study', 'href' => '/gate-machine-study'];
        }
        if ($this->mentions($lower, ['منتج', 'product', 'catalog', 'عرض'])) {
            $actions[] = ['label' => $ar ? 'المنتجات' : 'Products', 'href' => '/products'];
        }

        return [
            'reply' => str_replace("\r\n", "\n", $text),
            'actions' => array_values($actions),
            'ai' => true,
        ];
    }

    /** @param list<string> $needles */
    private function mentions(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, mb_strtolower($needle)) !== false) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, mixed> */
    private function companyInfo(): array
    {
        $path = public_path('company-info.json');
        if (!File::exists($path)) {
            return [];
        }
        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : [];
    }

    private function whatsappHref(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number) ?: '971562566232';
        $msg = urlencode('مرحباً، أتواصل معكم من موقع SmartFlow');

        return "https://wa.me/{$digits}?text={$msg}";
    }
}
