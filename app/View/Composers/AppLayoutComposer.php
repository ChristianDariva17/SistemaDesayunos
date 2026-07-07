<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Services\NavigationStatsService;
use Illuminate\View\View;

final class AppLayoutComposer
{
    public function __construct(
        private readonly NavigationStatsService $navigationStats,
    ) {}

    public function compose(View $view): void
    {
        $stats = $this->navigationStats->getLayoutStats();

        $view->with('navigationStats', $stats);
        $view->with('stockBajo', $stats['stockBajo']);
        $view->with('pedidosPendientes', $stats['pedidosPendientes']);
    }
}
