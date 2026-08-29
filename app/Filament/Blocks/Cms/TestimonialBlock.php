<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Grid;

class TestimonialBlock
{
    public static function make(): Block
    {
        return Block::make('testimonial')
            ->label(__('cms_blocks.testimonial.label'))
            ->icon('heroicon-o-chat-bubble-left-right')
            ->schema([
                Grid::make(2)->schema([
                    Textarea::make('quote.en')->label(__('cms_blocks.testimonial.quote_en'))->required()->rows(3),
                    Textarea::make('quote.ar')->label(__('cms_blocks.testimonial.quote_ar'))->required()->rows(3)->extraInputAttributes(['dir' => 'rtl']),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('attribution.en')->label(__('cms_blocks.testimonial.attribution_en'))->maxLength(255),
                    TextInput::make('attribution.ar')->label(__('cms_blocks.testimonial.attribution_ar'))->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                ]),
                FileUpload::make('avatar')
                    ->label(__('cms_blocks.testimonial.avatar'))
                    ->image()
                    ->disk('public')
                    ->directory('cms/blocks'),
            ]);
    }
}
