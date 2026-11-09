<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config/Database.php';
require_once 'data/PelangganRepository.php';
require_once 'services/PelangganService.php';

$db = (new Database())->getConnection();
$repo = new PelangganRepository($db);
$service = new PelangganService($repo);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true) ?? [];
$id = $_GET['id'] ?? ($input['id'] ?? null);

switch ($method) {
    case 'GET':
        $search = $_GET['search'] ?? '';
        $res = $service->getCustomers($search);
        http_response_code($res['code']);
        echo json_encode($res);
        break;

    case 'POST':
        $res = $service->createCustomer($input);
        http_response_code($res['code']);
        echo json_encode($res);
        break;

    case 'PUT':
        $res = $service->updateCustomer($id, $input);
        http_response_code($res['code']);
        echo json_encode($res);
        break;

    case 'DELETE':
        $res = $service->deleteCustomer($id);
        http_response_code($res['code']);
        echo json_encode($res);
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method tidak diizinkan."]);
        break;
}
