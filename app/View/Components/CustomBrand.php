<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomBrand extends Component
{
    public string $logo;
    public string $title;
    /**
     * Create a new component instance.
     */
    public function __construct(string $logo, string $title)
    {
        $this->logo = $logo;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.custom-brand');
    }
}
