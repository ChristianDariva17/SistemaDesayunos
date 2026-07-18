<?php

namespace App\View\Components;

use Illuminate\View\Component;

class StatsCard extends Component
{
    public $title;

    public $value;

    public $subtitle;

    public $icon;

    public $color;

    public $route;

    public function __construct($title, $value, $subtitle, $icon, $color = 'blue', $route = null)
    {
        $this->title = $title;
        $this->value = $value;
        $this->subtitle = $subtitle;
        $this->icon = $icon;
        $this->color = $color;
        $this->route = $route;
    }

    public function render()
    {
        return view('components.stats-card');
    }
}
