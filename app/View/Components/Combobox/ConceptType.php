<?php

namespace App\View\Components\Combobox;

use App\Database\Criteria;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConceptType extends Component
{
    public array $options;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $id,
        public string $value,
        public string $label = '',
        public string $placeholder = ''
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->options = Criteria::table('view_concept')
            ->select('type', 'tiName')
            ->distinct()
            ->whereNotNull('type')
            ->orderBy('tiName')
            ->chunkResult('type', 'tiName');

        return view('components.combobox.concept-type');
    }
}
