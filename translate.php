<?php
$data = json_decode(file_get_contents('php://input'), true);
$text = $data['text'] ?? '';
if ($text === '') {
    echo json_encode(['error' => 'No text provided']);
    exit;
}
$api_key = 'YOUR_DEEPL_API_KEY'; // セキュアに管理してください
$url = 'https://api-free.deepl.com/v2/translate';

$params = [
    'auth_key' => $api_key,
    'text' => $text,
    'source_lang' => 'DE',
    'target_lang' => 'JA'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$translation = $result['translations'][0]['text'] ?? '';
echo json_encode(['translation' => $translation]);
?>
