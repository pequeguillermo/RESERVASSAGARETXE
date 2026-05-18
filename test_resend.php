<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'from' => 'Reservas <reservas@sagaretxe.net>',
    'to' => ['admin@sagaretxe.com'],
    'subject' => 'Test API Key desde el Local',
    'html' => 'API Key works!'
]));
$headers = [
    'Authorization: Bearer re_8u7KofTo_EqZEvFsqDSCTqKp6Z5fC9stj',
    'Content-Type: application/json'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
echo "Response: " . $result . "\n";
curl_close($ch);
