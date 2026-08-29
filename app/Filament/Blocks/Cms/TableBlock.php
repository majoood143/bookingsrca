<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Grid;

class TableBlock
{
    public static function make(): Block
    {
        return Block::make('table')
            ->label(__('cms_blocks.table.label'))
            ->icon('heroicon-o-table-cells')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('title.en')->label(__('cms_blocks.table.title_en'))->maxLength(255),
                    TextInput::make('title.ar')->label(__('cms_blocks.table.title_ar'))->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                ]),
                Toggle::make('has_header')
                    ->label(__('cms_blocks.table.has_header'))
                    ->default(true),
                Repeater::make('rows')
                    ->label(__('cms_blocks.table.rows'))
                    ->schema([
                        Repeater::make('cells')
                            ->label(__('cms_blocks.table.cells'))
                            ->simple(
                                TextInput::make('value')->label(__('cms_blocks.table.cell_value'))
                            )
                            ->defaultItems(2),
                    ])
                    ->collapsible()
                    ->defaultItems(1),
            ]);
    }
}
