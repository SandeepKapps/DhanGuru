<?php
// CORS Headers for API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle CORS Preflight OPTIONS Request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$apiKey = 'gw_2d95805008dfdc532b71f80f5cf2c66a20e9de366d5f61e9f3c2a70afc138194';

// Extract target endpoint
$endpoint = isset($_GET['path']) ? $_GET['path'] : '';
if (!$endpoint) {
    $uri = $_SERVER['REQUEST_URI'];
    $cleanUri = strtok($uri, '?');
    $endpoint = preg_replace('#^/(silkpay|payin|api)(\.php)?#', '', $cleanUri);
}
if (!$endpoint || $endpoint === '/') {
    $endpoint = '/merchant/api/payin/create';
}
if ($endpoint[0] !== '/') {
    $endpoint = '/' . $endpoint;
}

$targetUrl = 'https://api.silkpay.live' . $endpoint;

$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

// Forward auth headers
$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : ('Bearer ' . $apiKey);
$headers = [
    'Content-Type: application/json',
    'Authorization: ' . $authHeader,
    'Accept: application/json'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Forward request body for POST/PUT
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $rawInput = file_get_contents('php://input');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $rawInput);
}

// Disable SSL verification issues on shared hosting
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false || empty($response)) {
    http_response_code(502);
    echo json_encode(['code' => -1, 'msg' => 'cURL Error: ' . ($curlErr ?: 'Empty response from SilkPay gateway')]);
    exit;
}

http_response_code($httpCode ?: 200);
echo $response;
?>
