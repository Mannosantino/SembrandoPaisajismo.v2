<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'error' => 'Método no permitido. Usa POST para enviar el carrito.',
        'ayuda' => 'Si ves este mensaje desde el navegador, la API está funcionando. Usa fetch/AJAX para probar el pago.'
    ]);
    exit;
}

// Tu Access Token de Mercado Pago
$access_token = 'APP_USR-2212742190752581-061323-f6479799a22ca668014d8894ae831473-1373829935';

// Recibe los datos del carrito desde JS
$data = json_decode(file_get_contents('php://input'), true);

$items = [];
foreach ($data['carrito'] as $item) {
    $items[] = [
        "title" => $item['nombre'],
        "quantity" => $item['cantidad'],
        "unit_price" => $item['precio'],
        "currency_id" => "ARS"
    ];
}

$preference_data = [
    "items" => $items
];

// Inicializa cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/checkout/preferences");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $access_token"
]);

$result = curl_exec($ch);
curl_close($ch);

$response = json_decode($result, true);

if (isset($response['init_point'])) {
    echo json_encode(['init_point' => $response['init_point']]);
} else {
    echo json_encode([
        'error' => 'No se pudo crear la preferencia',
        'detalle' => $response,
        'raw' => $result
    ]);
}