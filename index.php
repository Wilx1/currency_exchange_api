<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include_once 'config/Database.php';
// require_once '/config/Database.php';
require_once 'src/controllers/CountryController.php';
// include_once 'src/Models/Country.php';

// Connect to DB
$db = new Database();
$conn = $db->connect();

// Initialize controller
$controller = new CountryController($conn);

// Get request details
$method = $_SERVER['REQUEST_METHOD'];
// $uri = trim($_SERVER['REQUEST_URI'], '/');
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uriParts = explode('/', $uri);

// Optional: handle OPTIONS preflight
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simple Router
if ($uri === 'countries' && $method === 'GET') {
    $controller->getAllCountries();
}
elseif ($uri === 'countries/image' && $method === 'GET') {
    $file = __DIR__ . '/cache/summary.png';
    if (file_exists($file)) {
        header('Content-Type: image/png');
        readfile($file);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Summary image not found"], JSON_UNESCAPED_UNICODE);
    }
}
elseif ($uri == 'countries/refresh' && $method == 'POST') {
    $controller->refreshCountries();
}
elseif (preg_match('/^countries\/([^\/]+)$/', $uri, $matches)) {
    $countryName = urldecode($matches[1]);
    if ($method === 'GET') {
        $controller->getCountryByName($countryName);
    } elseif ($method === 'DELETE') {
        $controller->deleteCountry($countryName);
    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"], JSON_UNESCAPED_UNICODE);
    }
}
elseif ($uri === 'status' && $method === 'GET') {
    $controller->getStatus();
}
else {
    http_response_code(404);
    echo json_encode(["error" => "Route not found"], JSON_UNESCAPED_UNICODE);
}
