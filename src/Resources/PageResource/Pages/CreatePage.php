<?php

namespace Z3d0X\FilamentFabricator\Resources\PageResource\Pages;

use Z3d0X\FilamentFabricator\Resources\PageResource\Pages\Concerns\HasPreviewModal;
use Filament\Resources\Pages\CreateRecord;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use Z3d0X\FilamentFabricator\Resources\PageResource;

class CreatePage extends CreateRecord
{
    use HasPreviewModal;

    protected static string $resource = PageResource::class;

    public static function getResource(): string
    {
        return config('filament-fabricator.page-resource') ?? static::$resource;
    }

    protected function getActions(): array
    {
        return [
            PreviewAction::make(),
        ];
    }

    /**
     * @deprecated Use `mountAction()` instead.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function mountFormComponentAction(string $component, string $name, array $arguments = []): mixed
    {
        // Handle the add action directly for the blocks component
        if ($name === 'add' && str_contains($component, 'blocks')) {
            return $this->addBlock($arguments);
        }

        return $this->mountAction($name, $arguments, [
            'schemaComponent' => str_replace('data.', '', $component),
        ]);
    }

    protected function addBlock(array $arguments): void
    {
        $blockType = $arguments['block'] ?? null;
        if (!$blockType) {
            return;
        }

        // Get current blocks data
        $currentBlocks = $this->data['blocks'] ?? [];

        // Generate a new UUID for the block
        $newUuid = \Illuminate\Support\Str::uuid()->toString();

        // Add the new block
        $currentBlocks[$newUuid] = [
            'type' => $blockType,
            'data' => [],
        ];

        // Update the data
        $this->data['blocks'] = $currentBlocks;

        // Trigger form state update
        $this->dispatch('$refresh');
    }
}
