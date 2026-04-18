<?php

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
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

function log_api_call($pdo, $provider, $endpoint, $status, $response_time) {
    try {
        $stmt = $pdo->prepare("INSERT INTO api_logs (provider, endpoint, status, response_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$provider, $endpoint, $status, $response_time]);
    } catch (Exception $e) {}
}

function get_api_settings($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM api_settings LIMIT 1");
        $res = $stmt->fetch();
        return $res ?: ['provider' => 'manual', 'deepseek_key' => '', 'gemini_key' => '', 'psi_key' => '', 'deepseek_base_url' => ''];
    } catch (Exception $e) {
        return ['provider' => 'manual', 'deepseek_key' => '', 'gemini_key' => '', 'psi_key' => '', 'deepseek_base_url' => ''];
    }
}

function get_admin_profile($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM admin_profile LIMIT 1");
        $res = $stmt->fetch();
        return $res ?: ['full_name' => 'Cyber Architect', 'bio' => 'Security Specialist', 'whatsapp_number' => '', 'email' => ''];
    } catch (Exception $e) {
        return ['full_name' => 'Cyber Architect', 'bio' => 'Security Specialist', 'whatsapp_number' => '', 'email' => ''];
    }
}

function call_ai_service($pdo, $prompt) {
    $api = get_api_settings($pdo);
    $provider = $api['provider'] ?? 'manual';

    if ($provider === 'manual') {
        return ['error' => 'Manual mode active'];
    }

    $start_time = microtime(true);

    if ($provider === 'deepseek') {
        $api_key = $api['deepseek_key'];
        $base_url = $api['deepseek_base_url'] ?: 'https://api.deepseek.com';
        $endpoint = "/chat/completions";

        $data = [
            "model" => "deepseek-chat",
            "messages" => [
                ["role" => "system", "content" => "You are a specialized developer portfolio assistant. Always return valid JSON."],
                ["role" => "user", "content" => $prompt]
            ],
            "response_format" => ["type" => "json_object"]
        ];

        $ch = curl_init($base_url . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $duration = (microtime(true) - $start_time) * 1000;

        if ($http_code !== 200) {
            log_api_call($pdo, 'deepseek', $endpoint, 'fail', $duration);
            return ['error' => 'DeepSeek API error: ' . $response];
        }

        log_api_call($pdo, 'deepseek', $endpoint, 'success', $duration);
        $result = json_decode($response, true);
        return json_decode($result['choices'][0]['message']['content'], true);

    } else {
        // Default to Gemini
        $api_key = $api['gemini_key'];
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
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $duration = (microtime(true) - $start_time) * 1000;

        if ($http_code !== 200) {
            log_api_call($pdo, 'gemini', 'generateContent', 'fail', $duration);
            return ['error' => 'Gemini API Error: ' . $response];
        }

        log_api_call($pdo, 'gemini', 'generateContent', 'success', $duration);
        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return json_decode($result['candidates'][0]['content']['parts'][0]['text'], true);
        }
        return ['error' => 'Invalid Gemini response'];
    }
}

function capture_screenshot_psi($pdo, $target_url) {
    $api = get_api_settings($pdo);
    $api_key = $api['psi_key'] ?? '';
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
        return $image_data;
    }

    $filename = $slug . '-' . time() . '.jpg';
    $path = __DIR__ . '/../assets/uploads/' . $filename;

    if (file_put_contents($path, $content)) {
        return '/assets/uploads/' . $filename;
    }

    return $image_data;
}
