<?php

namespace Z3d0X\FilamentFabricator\Resources\PageResource\Pages;

use Z3d0X\FilamentFabricator\Resources\PageResource\Pages\Concerns\HasPreviewModal;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Pboivin\FilamentPeek\Pages\Actions\PreviewAction;
use Z3d0X\FilamentFabricator\Facades\FilamentFabricator;
use Z3d0X\FilamentFabricator\Models\Contracts\Page as PageContract;
use Z3d0X\FilamentFabricator\Resources\PageResource;

class EditPage extends EditRecord
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

            ViewAction::make()
                ->visible(config('filament-fabricator.enable-view-page')),

            DeleteAction::make(),

            Action::make('visit')
                ->label(__('filament-fabricator::page-resource.actions.visit'))
                ->url(function () {
                    /** @var PageContract $page */
                    $page = $this->getRecord();

                    return FilamentFabricator::getPageUrlFromId($page->id);
                })
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->openUrlInNewTab()
                ->color('success')
                ->visible(config('filament-fabricator.routing.enabled')),

            Action::make('save')
                ->action('save')
                ->label(__('filament-fabricator::page-resource.actions.save')),
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
