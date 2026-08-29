<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Grid;

class StatsBlock
{
    public static function make(): Block
    {
        return Block::make('stats')
            ->label(__('cms_blocks.stats.label'))
            ->icon('heroicon-o-chart-bar')
            ->schema([
                Repeater::make('items')
                    ->label(__('cms_blocks.stats.items'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('number')->label(__('cms_blocks.stats.number'))->required()->maxLength(50),
                            TextInput::make('label.en')->label(__('cms_blocks.stats.label_en'))->required()->maxLength(100),
                            TextInput::make('label.ar')->label(__('cms_blocks.stats.label_ar'))->required()->maxLength(100)->extraInputAttributes(['dir' => 'rtl']),
                        ]),
                        TextInput::make('icon')->label(__('cms_blocks.stats.icon'))->helperText(__('cms_blocks.stats.icon_helper')),
                    ])
                    ->collapsible()
                    ->defaultItems(1),
            ]);
    }
}
