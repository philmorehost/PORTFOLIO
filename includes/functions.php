<?php

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function call_gemini_api($prompt) {
    $api_key = GEMINI_API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $api_key;

    $data = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ],
        "generationConfig" => [
            "response_mime_type" => "application/json"
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) return ['error' => $error];

    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return json_decode($result['candidates'][0]['content']['parts'][0]['text'], true);
    }

    if (isset($result['error'])) {
        return ['error' => $result['error']['message'] ?? 'Gemini API Error'];
    }

    return ['error' => 'Invalid API response', 'raw' => $response];
}

function capture_screenshot_psi($target_url) {
    $api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
    $psi_url = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=" . urlencode($target_url) . "&screenshot=true";
    if ($api_key) $psi_url .= "&key=" . $api_key;

    $ch = curl_init($psi_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if (isset($result['lighthouseResult']['audits']['final-screenshot']['details']['data'])) {
        return $result['lighthouseResult']['audits']['final-screenshot']['details']['data'];
    }

    return null;
}

function save_local_image($image_data, $slug) {
    if (strpos($image_data, 'data:image') === 0) {
        $data = explode(',', $image_data);
        $content = base64_decode($data[1]);
    } else {
        // Assume it's a URL or already decoded?
        // For PSI it's a data URL.
        return $image_data;
    }

    $filename = $slug . '-' . time() . '.jpg';
    $path = __DIR__ . '/../assets/uploads/' . $filename;

    if (file_put_contents($path, $content)) {
        return '/assets/uploads/' . $filename;
    }

    return $image_data;
}

function get_setting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function update_setting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}
