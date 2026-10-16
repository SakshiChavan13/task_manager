
<?php

$apiKey = getenv('GEMINI_API_KEY');

if (!$apiKey) {
    $envFile = __DIR__ . '/../.env';

    if (is_file($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^GEMINI_API_KEY\s*=\s*(.*)$/', $line, $matches)) {
                $apiKey = trim($matches[1], " \t\n\r\0\x0B\"'");
                break;
            }
        }
    }
}

if (!$apiKey) {
    throw new RuntimeException('Gemini API key is not configured.');
}

return [
    'api_key' => $apiKey,
    'model' => 'gemini-3.5-flash',
];
