<?php

namespace Trinavo\LivewirePageBuilder\Http\Livewire;

use Trinavo\LivewirePageBuilder\Services\PageBuilderService;
use Livewire\Component;
use Livewire\Attributes\On;
use Trinavo\LivewirePageBuilder\Support\RowBlock;

class PageEditor extends Component
{
    public $rows = [];
    public $availableBlocks = [];
    public ?string $selectedRowId = null;
    public ?string $selectedBlockId = null;
    public $selectedBlock = null;
    public $selectedRow = null;
    public bool $showBlockModal = false;
    public string $blockFilter = '';
    public ?string $modalRowId = null;

    public function mount()
    {
        $this->availableBlocks = app(PageBuilderService::class)->getAvailableBlocks();
    }

    public function addRow()
    {
        $rowId = uniqid();
        $rowClass = RowBlock::class;
        $this->rows[$rowId] = [
            'type' => 'row',
            'class' => $rowClass,
            'properties' => app(PageBuilderService::class)->getBlockPropertiesArray($rowClass),
            'propertyValues' => [
                'mobile_columns' => 12,
                'tablet_columns' => 12,
                'desktop_columns' => 12,
            ],
            'blocks' => [],
        ];
        $this->dispatch('rowAdded', $rowId);
    }


    #[On('selectBlock')]
    public function selectBlock($rowId, $blockId)
    {
        $this->selectedRowId = $rowId;
        $this->selectedBlockId = $blockId;
        if ($blockId) {
            $this->selectedBlock = $this->rows[$rowId]['blocks'][$blockId] ?? null;
        } else {
            $this->selectedBlock = $this->rows[$rowId] ?? null;
        }
    }


    #[On('updateBlockProperty')]
    public function updateBlockProperty($rowId, $blockId, $property, $value)
    {
        $this->rows[$rowId]['blocks'][$blockId]['propertyValues'][$property] = $value;
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
            'properties' => app(PageBuilderService::class)->getBlockPropertiesArray($blockClass),
            'propertyValues' => [
                'mobile_columns' => 12,
                'tablet_columns' => 12,
                'desktop_columns' => 12,
            ],
        ];
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
        if (!$this->blockFilter) {
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
        return view('page-builder::page-editor')
            ->layout('page-builder::layouts.app');
    }
}
