<?php 
header('Content-Type: application/json'); 
 
// Read JSON input from frontend 
$data = json_decode(file_get_contents('php://input'), true); 
if (!$data) { 
    echo json_encode(['success' => false, 'message' => 'Invalid request payload']); 
    exit; 
} 
 
$phone = $data['phone']; 
$amount = $data['amount']; 
$packageId = $data['package_id']; 
 
// Format phone number to 2547XXXXXXXX 
$phone = preg_replace('/^(?:254|\+254|0)?([17][0-9]{8})$/', '254$1', $phone); 
 
// Safaricom Daraja Sandbox / Production Credentials 
$consumerKey = 'YOUR_DARAJA_CONSUMER_KEY'; 
$consumerSecret = 'YOUR_DARAJA_CONSUMER_SECRET'; 
$businessShortCode = '174379'; // Sandbox test shortcode 
$passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'; // 
Sandbox passkey 
 
// 1. Generate Access Token 
$credentials = base64_encode($consumerKey . ':' . $consumerSecret); 
$url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'; 
 
$curl = curl_init($url); 
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]); 
$response = curl_exec($curl); 
curl_close($curl); 
 
$result = json_decode($response); 
if (!isset($result->access_token)) { 
    echo json_encode(['success' => false, 'message' => 'Failed to connect to M-Pesa API']); 
    exit; 
} 
$accessToken = $result->access_token; 
 
// 2. Initiate STK Push 
$timestamp = date('YmdHis'); 
$password = base64_encode($businessShortCode . $passkey . $timestamp); 
$stkUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'; 
 
$stkPayload = [ 
    'BusinessShortCode' => $businessShortCode, 
    'Password' => $password, 
    'Timestamp' => $timestamp, 
    'TransactionType' => 'CustomerPayBillOnline', 
    'Amount' => $amount, 
    'PartyA' => $phone, 
    'PartyB' => $businessShortCode, 
    'PhoneNumber' => $phone, 
    'CallBackURL' => 'https://your-domain.com/hotspot/callback.php', // Use Ngrok URL for 
testing 
    'AccountReference' => 'KibandaskiWiFi', 
    'TransactionDesc' => 'WiFi Hotspot Payment' 
]; 
 
$curl = curl_init($stkUrl); 
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($curl, CURLOPT_HTTPHEADER, [ 
    'Authorization: Bearer ' . $accessToken, 
    'Content-Type: application/json' 
]); 
curl_setopt($curl, CURLOPT_POST, true); 
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($stkPayload)); 
$stkResponse = curl_exec($curl); 
curl_close($curl); 
$stkResult = json_decode($stkResponse); 
if (isset($stkResult->ResponseCode) && $stkResult->ResponseCode == '0') { 
// Save pending transaction in MySQL 
$pdo = new PDO ('mysql:host=localhost;dbname=hotspot_db', 'root', ''); 
$stmt = $pdo->prepare("INSERT INTO transactions (phone, amount, package_id, 
checkout_request_id, status) VALUES (?, ?, ?, ?, 'Pending')"); 
$stmt->execute([$phone, $amount, $packageId, $stkResult->CheckoutRequestID]); 
echo json_encode(['success' => true, 'message' => 'STK Push sent successfully']); 
} else { 
echo json_encode(['success' => false, 'message' => $stkResult->errorMessage ?? 'STK Push 
failed']); 
} 
?>