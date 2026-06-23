<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assessment = \App\Models\Assessment::where('status', 'SUBMITTED')->first();
if (!$assessment) {
    echo "No SUBMITTED assessment found\n";
    exit;
}

try {
    $controller = app(\App\Http\Controllers\AssessmentController::class);
    $response = $controller->getAdminAssessmentDetail($assessment->id);
    $result = json_decode($response->getContent(), true)['data'];
    
    $out = [];
    foreach ($result['rubrik'] as $cat) {
        $catScore = 0;
        foreach ($cat['pertanyaan'] as $q) {
            $jp = $q['jawaban_peserta'];
            $sys = $jp ? ($jp['skor_sistem'] ?? 0) : 0;
            $catScore += $sys;
        }
        $out[] = [
            'kategori' => $cat['kategori'],
            'total_sys_score' => $catScore,
            'bobot' => $cat['bobot_persentase']
        ];
    }
    
    echo json_encode($out, JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
