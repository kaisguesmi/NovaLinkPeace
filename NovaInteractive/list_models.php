<?php
$apiKey = 'AIzaSyCFHh7ybtTgPwlRz7JU4TTFnEiLcAtxACA';
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Curl Error: ' . curl_error($ch) . "\n";
} else {
    $models = json_decode($result, true);
    if (isset($models['models'])) {
        foreach ($models['models'] as $model) {
            if (strpos($model['name'], 'gemini-1.5') !== false) {
                echo "Found supported model: " . $model['name'] . "\n";
                echo "Methods: " . implode(", ", $model['supportedGenerationMethods']) . "\n\n";
            }
        }
    } else {
        echo "No models found or error structure:\n" . substr($result, 0, 500); 
    }
}

curl_close($ch);
?>
