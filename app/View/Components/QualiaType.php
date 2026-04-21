<?php

namespace App\View\Components;

use App\Database\Criteria;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class QualiaType extends Component
{
    public array $options;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $id,
        public string $value,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->options = Criteria::table('view_qualia')
            ->select('idTypeInstance', 'type')
            ->distinct()
            ->whereNotNull('type')
            ->orderBy('type')
            ->chunkResult('idTypeInstance', 'type');

        return view('components.qualia-type');
    }
}
