<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Trinavo\LivewirePageBuilder\Services\PageBuilderService;

class PageEditor extends Component
{
    public $rows = [];

    public $availableBlocks = [];

    public bool $showBlockModal = false;

    public string $blockFilter = '';

    public ?string $modalRowId = null;

    public $blockProperties = [];

    public function mount()
    {
        $this->availableBlocks = app(PageBuilderService::class)->getAvailableBlocks();
    }

    public function addRow()
    {
        $rowId = uniqid();
        $this->rows[$rowId] = [];
    }

    #[On('addBlockToRow')]
    public function addBlockToRow($rowId, $blockAlias)
    {
        // Find the class name for this alias
        $blockClass = null;
        foreach ($this->availableBlocks as $block) {
            if ($block['alias'] === $blockAlias) {
                $blockClass = $block['class'];
                break;
            }
        }

        $this->rows[$rowId]['blocks'][uniqid()] = [
            'type' => 'block',
            'class' => $blockClass,
            'alias' => $blockAlias,
        ];

        $this->dispatch('blockAdded', $rowId, $blockAlias)->to('row-block');
    }

    #[On('openBlockModal')]
    public function openBlockModal($rowId)
    {
        $this->showBlockModal = true;
        $this->blockFilter = '';
        $this->modalRowId = $rowId;
    }

    public function closeBlockModal()
    {
        $this->showBlockModal = false;
        $this->modalRowId = null;
    }

    public function getFilteredBlocksProperty()
    {
        if (! $this->blockFilter) {
            return $this->availableBlocks;
        }
        $filter = strtolower($this->blockFilter);

        return array_values(array_filter($this->availableBlocks, function ($block) use ($filter) {
            return str_contains(strtolower($block['label']), $filter);
        }));
    }

    public function addBlockToModalRow($blockAlias)
    {
        if ($this->modalRowId) {
            $this->addBlockToRow($this->modalRowId, $blockAlias);
            $this->closeBlockModal();
        }
    }

    public function render()
    {
        return view('page-builder::page-editor', [
            'selectedRowId' => $this->selectedRowId ?? null,
            'selectedBlockId' => $this->selectedBlockId ?? null,
        ])->layout('page-builder::layouts.app');
    }
}
