<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Grid;

class HeroBlock
{
    public static function make(): Block
    {
        return Block::make('hero')
            ->label(__('cms_blocks.hero.label'))
            ->icon('heroicon-o-photo')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('heading.en')->label(__('cms_blocks.hero.heading_en'))->required()->maxLength(255),
                    TextInput::make('heading.ar')->label(__('cms_blocks.hero.heading_ar'))->required()->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('subheading.en')->label(__('cms_blocks.hero.subheading_en'))->maxLength(255),
                    TextInput::make('subheading.ar')->label(__('cms_blocks.hero.subheading_ar'))->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                ]),
                FileUpload::make('background_image')
                    ->label(__('cms_blocks.hero.background_image'))
                    ->image()
                    ->disk('public')
                    ->directory('cms/blocks'),
                Grid::make(2)->schema([
                    TextInput::make('cta_text.en')->label(__('cms_blocks.hero.cta_text_en'))->maxLength(100),
                    TextInput::make('cta_text.ar')->label(__('cms_blocks.hero.cta_text_ar'))->maxLength(100)->extraInputAttributes(['dir' => 'rtl']),
                ]),
                TextInput::make('cta_url')->label(__('cms_blocks.hero.cta_url'))->url()->maxLength(255),
            ]);
    }
}
