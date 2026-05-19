<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntentParserService
{
    public function parse(string $input, float $lat, float $lng): array
    {
        $start = now();

        $prompt = "Parse this home service request from Karachi, Pakistan: \"{$input}\". "
            .'Call extract_service_intent with the extracted data. '
            .'Key mappings — Roman Urdu: mujhe/chahiye=need, kal=tomorrow, subah=morning, abhi=now. '
            .'Urdu script services: پلمبر=Plumber, الیکٹریشن/بجلی=Electrician, AC/ایسی=AC Technician, '
            .'صفائی=Cleaning Service, استاد=Tutor, بڑھئی=Carpenter. '
            .'Urgency clues: ابھی/emergency/fori=emergency, آج/today=high, kal/tomorrow=normal.';

        $payload = [
            'system_instruction' => [
                'parts' => [[
                    'text' => 'You are a multilingual home service intent parser for Karachi, Pakistan. '
                        .'Urdu/Arabic script service type mappings: پلمبر=Plumber, بجلی/الیکٹریشن=Electrician, '
                        .'AC/ایسی/ائیر کنڈیشنر=AC Technician, صفائی=Cleaning Service, استاد/ٹیوٹر=Tutor, '
                        .'بڑھئی=Carpenter, رنگ ساز=Painter, ڈرائیور=Driver, مکینک=Mechanic, بیوٹیشن=Beautician. '
                        .'For urgency: ابھی/فوری=emergency, آج=high, kal/کل=normal, default=normal.',
                ]],
            ],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $prompt]]],
            ],
            'tools' => [
                ['function_declarations' => [$this->buildFunctionDeclaration()]],
            ],
            'tool_config' => [
                'function_calling_config' => [
                    'mode' => 'ANY',
                    'allowed_function_names' => ['extract_service_intent'],
                ],
            ],
            'generationConfig' => ['temperature' => 0.1],
        ];

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post(config('services.gemini.endpoint').'?key='.config('services.gemini.key'), $payload);

        if (! $response->successful()) {
            Log::error('Gemini function calling API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'clarification_question' => 'Could you please rephrase your request?',
                'proceed' => false,
                'duration_ms' => (int) abs($start->diffInMilliseconds(now())),
            ];
        }

        // Primary path: function call args (Gemini tool use)
        $functionCall = $response->json('candidates.0.content.parts.0.functionCall');
        if ($functionCall && isset($functionCall['args'])) {
            $data = $functionCall['args'];
        } else {
            // Fallback: text parsing for edge cases
            $text = $response->json('candidates.0.content.parts.0.text');
            $text = preg_replace('/```json|```/', '', $text ?? '');
            $data = json_decode(trim($text), true) ?? [];
        }

        if (empty($data)) {
            Log::error('Gemini response unparseable', ['raw' => $response->body()]);

            return [
                'clarification_question' => 'Could you please rephrase your request?',
                'proceed' => false,
                'duration_ms' => (int) abs($start->diffInMilliseconds(now())),
            ];
        }

        if (($data['confidence'] ?? 1) < 0.75) {
            return [
                'clarification_question' => $data['clarification_question'] ?? 'Could you please provide more details?',
                'proceed' => false,
                'duration_ms' => (int) abs($start->diffInMilliseconds(now())),
            ];
        }

        return array_merge($data, [
            'complexity' => $this->classifyComplexity($data),
            'proceed' => true,
            'duration_ms' => (int) abs($start->diffInMilliseconds(now())),
        ]);
    }

    public function detectLanguage(string $input): string
    {
        $urduPattern = '/[\x{0600}-\x{06FF}]/u';
        $hasUrdu = preg_match($urduPattern, $input);
        $hasEnglish = preg_match('/[a-zA-Z]/', $input);

        if ($hasUrdu && $hasEnglish) {
            return 'mixed';
        }
        if ($hasUrdu) {
            return 'urdu';
        }
        $romanUrduWords = ['mujhe', 'chahiye', 'karo', 'karna', 'kal', 'subah', 'aaj', 'ghar'];
        foreach ($romanUrduWords as $word) {
            if (stripos($input, $word) !== false) {
                return 'roman_urdu';
            }
        }

        return 'english';
    }

    public function classifyComplexity(array $intent): string
    {
        $severity = $intent['issue_severity'] ?? 'low';
        $serviceType = strtolower($intent['service_type'] ?? '');

        $complexKeywords = ['industrial', 'commercial', 'rewiring', 'main line', 'solar', 'hvac'];
        $intermediateKeywords = ['repair', 'installation', 'inverter', 'cctv', 'drainage'];

        foreach ($complexKeywords as $keyword) {
            if (str_contains($serviceType, $keyword)) {
                return 'complex';
            }
        }

        if ($severity === 'high') {
            return 'complex';
        }

        foreach ($intermediateKeywords as $keyword) {
            if (str_contains($serviceType, $keyword)) {
                return 'intermediate';
            }
        }

        if ($severity === 'medium') {
            return 'intermediate';
        }

        return 'basic';
    }

    private function buildFunctionDeclaration(): array
    {
        return [
            'name' => 'extract_service_intent',
            'description' => 'Extracts structured service request intent from multilingual (Urdu, Roman Urdu, English, mixed) user input for Karachi, Pakistan home services.',
            'parameters' => [
                'type' => 'OBJECT',
                'properties' => [
                    'service_type' => [
                        'type' => 'STRING',
                        'enum' => ['AC Technician', 'Plumber', 'Electrician', 'Cleaning Service', 'Tutor', 'Carpenter', 'Painter', 'Driver', 'Mechanic', 'Beautician'],
                        'description' => 'Type of service needed. Map Urdu/Roman Urdu to English: پلمبر/plumber→Plumber, بجلی/bijli→Electrician, AC/ایسی→AC Technician, صفائی→Cleaning Service',
                    ],
                    'location' => [
                        'type' => 'STRING',
                        'description' => 'Neighborhood or area in Karachi, e.g. Gulshan-e-Iqbal, DHA, Clifton, PECHS',
                    ],
                    'urgency' => [
                        'type' => 'STRING',
                        'enum' => ['low', 'normal', 'high', 'emergency'],
                        'description' => 'How urgently the service is needed',
                    ],
                    'preferred_time' => [
                        'type' => 'STRING',
                        'description' => 'Human-readable time preference, e.g. tomorrow morning, today evening, right now',
                    ],
                    'budget_sensitivity' => [
                        'type' => 'STRING',
                        'enum' => ['normal', 'high'],
                        'description' => 'Whether the user is price-sensitive',
                    ],
                    'issue_severity' => [
                        'type' => 'STRING',
                        'enum' => ['low', 'medium', 'high'],
                        'description' => 'Severity of the problem described',
                    ],
                    'detected_lang' => [
                        'type' => 'STRING',
                        'enum' => ['urdu', 'roman_urdu', 'english', 'mixed'],
                        'description' => 'Language detected in the input',
                    ],
                    'confidence' => [
                        'type' => 'NUMBER',
                        'description' => 'Parsing confidence from 0.0 to 1.0',
                    ],
                    'clarification_question' => [
                        'type' => 'STRING',
                        'description' => 'Question to ask user only when confidence is below 0.75',
                    ],
                ],
                'required' => ['service_type', 'urgency', 'detected_lang', 'confidence'],
            ],
        ];
    }
}
