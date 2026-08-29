<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Grid;

class FeaturesBlock
{
    public static function make(): Block
    {
        return Block::make('features')
            ->label(__('cms_blocks.features.label'))
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                Repeater::make('items')
                    ->label(__('cms_blocks.features.items'))
                    ->schema([
                        TextInput::make('icon')->label(__('cms_blocks.features.icon'))->helperText(__('cms_blocks.features.icon_helper')),
                        Grid::make(2)->schema([
                            TextInput::make('title.en')->label(__('cms_blocks.features.title_en'))->required()->maxLength(255),
                            TextInput::make('title.ar')->label(__('cms_blocks.features.title_ar'))->required()->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                        ]),
                        Grid::make(2)->schema([
                            Textarea::make('description.en')->label(__('cms_blocks.features.description_en'))->rows(2),
                            Textarea::make('description.ar')->label(__('cms_blocks.features.description_ar'))->rows(2)->extraInputAttributes(['dir' => 'rtl']),
                        ]),
                    ])
                    ->collapsible()
                    ->defaultItems(1),
            ]);
    }
}
