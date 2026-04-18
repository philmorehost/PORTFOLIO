<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'];
    $title = $_POST['title'] ?? '';

    $prompt = "
    Perform a deep-scan analysis of $url.
    Context: $title

    Return a JSON object:
    {
      \"content\": \"2-3 paragraph markdown power pitch\",
      \"metaTitle\": \"SEO title\",
      \"metaDescription\": \"SEO description\",
      \"keywords\": [\"array\", \"of\", \"strings\"],
      \"techStack\": [{\"name\": \"React\"}],
      \"waMessage\": \"WhatsApp text\",
      \"performance_scores\": {\"security\": 98, \"ui_ux\": 95, \"scalability\": 90},
      \"code_snippet\": \"Code snippet or system architecture note\"
    }
    ";

    $result = call_ai_service($pdo, $prompt);

    if (isset($result['error'])) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['error' => $result['error']]);
    } else {
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    exit;
}
