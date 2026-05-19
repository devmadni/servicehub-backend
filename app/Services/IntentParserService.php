<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IntentParserService
{
    public function parse(string $input, float $lat, float $lng): array
    {
        $start = now();
        $prompt = $this->buildPrompt($input);

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post(config('services.gemini.endpoint').'?key='.config('services.gemini.key'), [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 512],
            ]);

        $text = $response->json('candidates.0.content.parts.0.text');
        $text = preg_replace('/```json|```/', '', $text ?? '');
        $data = json_decode(trim($text), true) ?? [];

        if (($data['confidence'] ?? 1) < 0.75) {
            return [
                'clarification_question' => $data['clarification_question'] ?? 'Could you please provide more details?',
                'proceed' => false,
                'duration_ms' => now()->diffInMilliseconds($start),
            ];
        }

        return array_merge($data, [
            'complexity' => $this->classifyComplexity($data),
            'proceed' => true,
            'duration_ms' => now()->diffInMilliseconds($start),
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
        // Heuristic: common Roman Urdu words
        $romanUrduWords = ['mujhe', 'chahiye', 'karo', 'karna', 'kal', 'subah', 'aaj', 'ghar'];
        foreach ($romanUrduWords as $word) {
            if (stripos($input, $word) !== false) {
                return 'roman_urdu';
            }
        }

        return 'english';
    }

    public function buildPrompt(string $input): string
    {
        return <<<PROMPT
You are a multilingual service request parser for Karachi, Pakistan. Parse the following service request and return a JSON object only (no markdown).

Input: "{$input}"

Extract these fields:
- service_type: the type of service needed (e.g. "AC Technician", "Plumber", "Electrician")
- location: the area/neighborhood in Karachi
- urgency: one of [low, normal, high, emergency]
- preferred_time: human-readable time preference
- budget_sensitivity: one of [normal, high]
- issue_severity: one of [low, medium, high]
- detected_lang: one of [urdu, roman_urdu, english, mixed]
- confidence: float 0.0-1.0 representing parsing confidence
- clarification_question: only if confidence < 0.75, a question to ask the user

Examples:
- "Mujhe kal subah Gulshan mein AC technician chahiye" → service_type: AC Technician, location: Gulshan-e-Iqbal, urgency: normal
- "Emergency plumber needed in DHA now" → service_type: Plumber, urgency: emergency, location: DHA

Return ONLY valid JSON, no explanation.
PROMPT;
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
}
