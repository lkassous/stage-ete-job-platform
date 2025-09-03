<?php

namespace App\Filament\Widgets;

use App\Models\CandidateApplication;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ApplicationsChart extends ChartWidget
{
    protected static ?string $heading = 'Candidatures par mois';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::model(CandidateApplication::class)
            ->between(
                start: now()->subMonths(12),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Candidatures',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date->format('M Y')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
