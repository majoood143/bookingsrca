<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Builder\Block;

class GalleryBlock
{
    public static function make(): Block
    {
        return Block::make('gallery')
            ->label(__('cms_blocks.gallery.label'))
            ->icon('heroicon-o-photo')
            ->schema([
                FileUpload::make('images')
                    ->label(__('cms_blocks.gallery.images'))
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->disk('public')
                    ->directory('cms/blocks'),
            ]);
    }
}
