<?php
require_once 'config.php';
require_login();

$allowed_endpoints = ['students', 'companies', 'placement-summary'];
$endpoint = $_GET['endpoint'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (!in_array($endpoint, $allowed_endpoints, true)) {
    http_response_code(400);
    die('Invalid report endpoint');
}

$url = API_BASE . '/reports/' . $endpoint . '?format=' . urlencode($format);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $_SESSION['token']]);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// cURL handle is automatically closed on variable scope exit in PHP 8.0+

if ($httpCode !== 200) {
    http_response_code($httpCode);
    die('Could not generate report');
}

$body = substr($response, $headerSize);
$ext = ['csv' => 'csv', 'excel' => 'xlsx', 'pdf' => 'pdf'][$format] ?? 'csv';
$mime = ['csv' => 'text/csv', 'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'pdf' => 'application/pdf'][$format];

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $endpoint . '_report.' . $ext . '"');
echo $body;
