<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'];
    $title = $_POST['title'] ?? '';

    $prompt = "
    Perform a deep-scan and analysis of the provided URL to extract its core value proposition, technical architecture, and visual identity.
    Target URL: $url
    Title Context: $title

    The response must be a JSON object containing:
    1. content: A 'Power Pitch' following Problem -> Solution -> Result, Markdown format, 2-3 paragraphs.
    2. metaTitle: SEO title (max 60 chars).
    3. metaDescription: SEO description (max 160 chars).
    4. keywords: Array of 5-8 strings.
    5. techStack: Array of objects with name (e.g. React).
    6. waMessage: Professional WhatsApp message.
    ";

    $result = call_gemini_api($prompt);

    // Ensure we return a proper JSON response even on error for the frontend to handle
    if (isset($result['error'])) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['error' => $result['error']]);
    } else {
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    exit;
}
