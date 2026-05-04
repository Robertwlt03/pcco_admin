<?php

namespace App\Service;

use App\Enum\Status;
use App\Entity\Project;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

readonly class DashboardChartService
{
    private const string COLOR_PCCO_GREEN = '#57A632';
    private const string BG_TOOLTIP = 'rgba(0, 0, 0, 0.8)';
    private const array CHART_COLORS = ['#444444', '#3498db', '#f1c40f', '#57A632'];

    public function __construct(
        private ChartBuilderInterface $chartBuilder
    ) {}

    /**
     * Central configuration
     */
    private function configureChart(string $type, array $labels, array $datasets, array $options = []): Chart
    {
        $chart = $this->chartBuilder->createChart($type);

        foreach ($datasets as &$dataset) {
            $dataset['borderColor'] = 'var(--card-border)';
            $dataset['borderWidth'] = 1;
        }

        $chart->setData([
            'labels' => $labels,
            'datasets' => $datasets,
        ]);

        $chart->setOptions(array_merge([
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'backgroundColor' => self::BG_TOOLTIP,
                    'padding' => 10,
                    'cornerRadius' => 8
                ]
            ],
        ], $options));

        return $chart;
    }

    /**
     * Create a doughnut chart showing the status distribution of the projects.
     */
    public function createStatusChart(array $projects): Chart
    {
        $data = $this->aggregateStatusData($projects);

        return $this->configureChart(
            type: Chart::TYPE_DOUGHNUT,
            labels: array_keys($data),
            datasets: [[
                'data' => array_values($data),
                'backgroundColor' => self::CHART_COLORS,
            ]],
            options: ['cutout' => '80%']
        );
    }

    /**
     * Create the bar chart for the budget overview by customer.
     */
    public function createBudgetChart(array $customers): Chart
    {
        $labels = [];
        $values = [];

        foreach ($customers as $customer) {
            $labels[] = $customer->getName();
            $values[] = array_reduce(
                $customer->getProjects()->toArray(),
                fn($sum, Project $p) => $sum + (float)$p->getBudget(), 0
            );
        }

        return $this->configureChart(
            type: Chart::TYPE_BAR,
            labels: $labels,
            datasets: [[
                'label' => 'Budget in €',
                'data' => $values,
                'backgroundColor' => self::COLOR_PCCO_GREEN,
                'borderRadius' => 6,
            ]],
            options: ['scales' => ['y' => ['beginAtZero' => true]]]
        );
    }

    /**
     * Calculates the percentage of progress for each project.
     */
    public function calculateProjectProgress(array $projects): array
    {
        return array_map(function (Project $project) {
            $tasks = $project->getTasks();
            $total = count($tasks);
            $done = count(array_filter($tasks->toArray(),
                fn($t) => $t->getStatus() === Status::Done->value || $t->getStatus() === Status::Done
            ));

            return [
                'name' => $project->getTitle(),
                'percentage' => $total > 0 ? round(($done / $total) * 100) : 0,
            ];
        }, $projects);
    }

    /**
     * A method for counting the frequency of statuses.
     */
    private function aggregateStatusData(array $projects): array
    {
        $counts = array_fill_keys(array_column(Status::cases(), 'value'), 0);

        foreach ($projects as $p) {
            $val = $p->getStatus();
            if (isset($counts[$val])) $counts[$val]++;
        }

        return $counts;
    }
}
