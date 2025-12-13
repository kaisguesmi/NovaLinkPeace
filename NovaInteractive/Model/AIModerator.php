<?php
class AIModerator {
    // Keywords for basic heuristic analysis
    private $keywords = [
        'Violence' => ['kill', 'die', 'murder', 'hurt', 'fight', 'attack', 'blood', 'gun', 'knife', 'tuer', 'mort', 'frapper'],
        'Sexual Harassment' => ['sex', 'naked', 'body', 'touch', 'kiss', 'rape', 'nude', 'sexe', 'corps', 'nu'],
        'Harassment' => ['idiot', 'stupid', 'ugly', 'hate', 'loser', 'fat', 'bitch', 'con', 'nul', 'moche', 'haine'],
        'Spam' => ['buy', 'sell', 'discount', 'click', 'link', 'money', 'offer', 'prix', 'offre', 'clique'],
        'Fraud' => ['scam', 'bank', 'transfer', 'account', 'password', 'login', 'banque', 'compte']
    ];

    /**
     * Analyzes content against a reclamation category.
     * Returns an array with 'score' (0-100) and 'analysis' (string).
     */
    public function analyze($content, $category) {
        // Always compute a heuristic baseline first (content-only)
        $baseline = $this->heuristicAnalyze($content, $category);

        if ($this->google_api_key) return $this->analyzeWithGemini($content, $category, $baseline);

        return $baseline;
    }

    private function heuristicAnalyze($content, $category) {
        // Content-first: scan all keyword lists, not just the provided category
        $score = 0;
        $matches = [];
        $seen = [];

        foreach ($this->keywords as $catName => $words) {
            foreach ($words as $word) {
                if (preg_match_all('/\b' . preg_quote($word, '/') . '\b/i', $content, $found)) {
                    $count = count($found[0]);
                    // Slightly lower weight for general harassment words to avoid over-inflation
                    $weight = ($catName === 'Harassment') ? 10 : 20;
                    $score += $weight * $count;

                    $matchStr = "$word (x$count)";
                    if (!isset($seen[$matchStr])) {
                        $matches[] = $matchStr;
                        $seen[$matchStr] = true;
                    }
                }
            }
        }

        if ($score > 100) $score = 100;

        if ($score > 0) {
            $analysis = "Detected keywords: " . implode(', ', $matches) . ". Risk level: " . ($score >= 50 ? "HIGH" : "MODERATE") . ".";
        } else {
            $analysis = "No obvious harmful keywords detected. Need human review.";
        }

        return [
            'score' => $score,
            'analysis' => $analysis
        ];
    }

    // --- Google Gemini Integration ---
    private $google_api_key = 'AIzaSyCFHh7ybtTgPwlRz7JU4TTFnEiLcAtxACA';

    private function analyzeWithGemini($content, $category, $baseline = null) {
        if(empty($this->google_api_key)) return ['score' => 0, 'analysis' => 'API Key missing'];

        if ($baseline === null) {
            $baseline = $this->heuristicAnalyze($content, $category);
        }

        // Use Gemini Flash latest on v1beta endpoint (available per model list)
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . $this->google_api_key;

        // Content-first prompt: ignore provided category, just judge the text itself
        $prompt = "Analyze the following text for safety/toxicity (violence, harassment, sexual, spam, fraud, hate, etc.). 
               Do NOT rely on any external category; base the score ONLY on the text content. 
               Return ONLY JSON: {\"score\": number, \"analysis\": \"string\"}.
               Text: \"$content\"";

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        // Fix for local XAMPP SSL issues
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['score' => $baseline['score'], 'analysis' => 'Gemini request failed: ' . $curlError . ' | Heuristic: ' . $baseline['analysis']];
        }

        $response = json_decode($result, true);

        // If Gemini refused to answer for safety reasons, surface that explicitly
        if (isset($response['candidates'][0]['finishReason']) && $response['candidates'][0]['finishReason'] === 'SAFETY') {
            return ['score' => $baseline['score'], 'analysis' => 'Gemini blocked response due to safety filters | Heuristic: ' . $baseline['analysis']];
        }
        if (isset($response['promptFeedback']['blockReason']) && $response['promptFeedback']['blockReason'] === 'SAFETY') {
            return ['score' => $baseline['score'], 'analysis' => 'Gemini blocked prompt due to safety filters | Heuristic: ' . $baseline['analysis']];
        }

        $textResponse = null;
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            $textResponse = $response['candidates'][0]['content']['parts'][0]['text'];
        }

        if ($textResponse) {
            $parsed = $this->parseGeminiJson($textResponse);
            if ($parsed) {
                // If Gemini score seems too low, keep heuristic score but keep Gemini analysis
                if ($baseline && isset($baseline['score']) && $baseline['score'] > ($parsed['score'] ?? 0)) {
                    return [
                        'score' => $baseline['score'],
                        'analysis' => 'Gemini: ' . ($parsed['analysis'] ?? 'n/a') . ' | Heuristic override: ' . $baseline['analysis']
                    ];
                }
                return $parsed;
            }
        }

        // Fallback: try heuristic analysis so we never return an empty analysis
        return [
            'score' => $baseline['score'],
            'analysis' => 'Gemini Analysis Failed or Invalid JSON. Fallback heuristic: ' . $baseline['analysis']
        ];
    }

    // Attempts to extract a clean JSON object from Gemini responses, even if wrapped in prose/markdown
    private function parseGeminiJson($text) {
        // Clean markdown code fences
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $text = $matches[1];
        } elseif (preg_match('/```\s*(.*?)\s*```/s', $text, $matches)) {
            $text = $matches[1];
        }

        $text = trim($text);

        // If the text already looks like JSON, try decoding directly
        $decoded = json_decode($text, true);
        if ($decoded && isset($decoded['score']) && isset($decoded['analysis'])) {
            return $decoded;
        }

        // Try to extract the first JSON object present in the text
        if (preg_match('/\{.*?\}/s', $text, $objMatch)) {
            $decoded = json_decode($objMatch[0], true);
            if ($decoded && isset($decoded['score']) && isset($decoded['analysis'])) {
                return $decoded;
            }
        }

        // Last resort: strip everything before first { and after last }
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $maybeJson = substr($text, $start, $end - $start + 1);
            $decoded = json_decode($maybeJson, true);
            if ($decoded && isset($decoded['score']) && isset($decoded['analysis'])) {
                return $decoded;
            }
        }

        return null;
    }
}
?>
