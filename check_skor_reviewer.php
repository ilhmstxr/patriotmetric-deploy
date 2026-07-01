<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$penugasan = \App\Models\Penugasan::where('status', 'SUBMITTED')->first();
if (!$penugasan) {
    echo "No SUBMITTED penugasan found\n";
    exit;
}

$reviewerId = $penugasan->reviewer_id ?? \App\Models\User::where('role', 'REVIEWER')->first()->id;

try {
    $service = app(\App\Services\PenugasanService::class);
    $result = $service->getDetailReviewTasks($reviewerId, $penugasan->id);
    
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
