<?php

namespace App\Filament\Widgets;

use App\Models\Penugasan;
use Filament\Widgets\ChartWidget;

class PenugasanChartWidget extends ChartWidget
{
    protected ?string $heading = 'Distribusi Status Penugasan';
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = null;

    public ?string $tahunPeriode = null;

    protected function getData(): array
    {
        $tahunList = Penugasan::select('tahun_periode')
            ->distinct()
            ->orderByDesc('tahun_periode')
            ->pluck('tahun_periode');

        if (! $this->tahunPeriode && $tahunList->isNotEmpty()) {
            $this->tahunPeriode = (string) $tahunList->first();
        }

        $query = Penugasan::query();
        if ($this->tahunPeriode) {
            $query->where('tahun_periode', $this->tahunPeriode);
        }

        $statuses = ['UNVERIFIED', 'ACTIVE', 'IN_PROGRESS', 'SUBMITTED', 'GRADED', 'PUBLISHED'];
        $counts   = [];
        foreach ($statuses as $status) {
            $counts[] = (clone $query)->where('status', $status)->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Jumlah Penugasan',
                    'data'            => $counts,
                    'backgroundColor' => ['#ef4444', '#94a3b8', '#3b82f6', '#f59e0b', '#10b981', '#10b981'],
                ],
            ],
            'labels' => ['Unverified', 'Active', 'In Progress', 'Submitted', 'Graded', 'Published'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
