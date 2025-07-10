<?php
$token = 'PASTE_YOUR_ACCESS_TOKEN_HERE'; // palitan ng token mo

$data = array(
    'type' => 'note',
    'title' => 'Router Load Reminder',
    'body' => 'Mag-load na sa router ngayon!'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.pushbullet.com/v2/pushes');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Access-Token: ' . $token,
    'Content-Type: application/json'
));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "Push sent.\n";
