<?php
header('Content-Type: application/json');

$assetsDir = __DIR__ . '/assets/berita';
$response = [
    'time' => date('Y-m-d H:i:s'),
    'php_user' => get_current_user(),
    'assets_berita_exists' => is_dir($assetsDir),
    'assets_berita_writable' => is_writable($assetsDir),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? null,
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? null,
    'recent_files' => []
];

if (is_dir($assetsDir)) {
    $files = array_diff(scandir($assetsDir), ['.', '..']);
    $fileList = [];
    foreach ($files as $file) {
        $path = $assetsDir . '/' . $file;
        if (is_file($path)) {
            $fileList[] = [
                'name' => $file,
                'time' => date('Y-m-d H:i:s', filemtime($path)),
                'size' => filesize($path)
            ];
        }
    }
    usort($fileList, function($a, $b) { return strtotime($b['time']) - strtotime($a['time']); });
    $response['recent_files'] = array_slice($fileList, 0, 10);
}

echo json_encode($response, JSON_PRETTY_PRINT);
