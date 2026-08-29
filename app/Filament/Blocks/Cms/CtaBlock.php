<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Grid;

class CtaBlock
{
    public static function make(): Block
    {
        return Block::make('cta')
            ->label(__('cms_blocks.cta.label'))
            ->icon('heroicon-o-megaphone')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('heading.en')->label(__('cms_blocks.cta.heading_en'))->required()->maxLength(255),
                    TextInput::make('heading.ar')->label(__('cms_blocks.cta.heading_ar'))->required()->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('button_text.en')->label(__('cms_blocks.cta.button_text_en'))->required()->maxLength(100),
                    TextInput::make('button_text.ar')->label(__('cms_blocks.cta.button_text_ar'))->required()->maxLength(100)->extraInputAttributes(['dir' => 'rtl']),
                    TextInput::make('button_url')->label(__('cms_blocks.cta.button_url'))->url()->required()->maxLength(255),
                ]),
                Select::make('style')
                    ->label(__('cms_blocks.cta.style'))
                    ->options([
                        'primary' => __('cms_blocks.cta.style_primary'),
                        'secondary' => __('cms_blocks.cta.style_secondary'),
                        'outline' => __('cms_blocks.cta.style_outline'),
                    ])
                    ->default('primary')
                    ->native(false),
            ]);
    }
}
