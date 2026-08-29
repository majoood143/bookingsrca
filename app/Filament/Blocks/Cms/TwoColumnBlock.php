<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Grid;

class TwoColumnBlock
{
    public static function make(): Block
    {
        return Block::make('two_column')
            ->label(__('cms_blocks.two_column.label'))
            ->icon('heroicon-o-view-columns')
            ->schema([
                Select::make('columns')
                    ->label(__('cms_blocks.two_column.columns'))
                    ->options(['1' => '1', '2' => '2', '3' => '3'])
                    ->default('2')
                    ->native(false)
                    ->required(),
                Grid::make(2)->schema([
                    RichEditor::make('column_1.en')->label(__('cms_blocks.two_column.column_1_en')),
                    RichEditor::make('column_1.ar')->label(__('cms_blocks.two_column.column_1_ar'))->extraInputAttributes(['dir' => 'rtl']),
                ]),
                Grid::make(2)->schema([
                    RichEditor::make('column_2.en')->label(__('cms_blocks.two_column.column_2_en')),
                    RichEditor::make('column_2.ar')->label(__('cms_blocks.two_column.column_2_ar'))->extraInputAttributes(['dir' => 'rtl']),
                ]),
                Grid::make(2)->schema([
                    RichEditor::make('column_3.en')->label(__('cms_blocks.two_column.column_3_en')),
                    RichEditor::make('column_3.ar')->label(__('cms_blocks.two_column.column_3_ar'))->extraInputAttributes(['dir' => 'rtl']),
                ]),
            ]);
    }
}
