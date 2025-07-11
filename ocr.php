<?php
// 画像ファイル受け取り
if (!isset($_FILES['image'])) {
    echo json_encode(['error' => 'No image uploaded']);
    exit;
}
$image = file_get_contents($_FILES['image']['tmp_name']);
$api_key = ''; // セキュアに管理してください

// APIリクエスト用JSON
$json = json_encode([
    'requests' => [[
        'image' => ['content' => base64_encode($image)],
        'features' => [['type' => 'TEXT_DETECTION', 'maxResults' => 1]]
    ]]
]);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://vision.googleapis.com/v1/images:annotate?key=" . $api_key);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
$response = curl_exec($curl);
curl_close($curl);

$result = json_decode($response, true);
$text = $result['responses'][0]['textAnnotations'][0]['description'] ?? '';
echo json_encode(['text' => $text]);
?>
