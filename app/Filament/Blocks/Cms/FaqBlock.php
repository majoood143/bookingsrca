<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;
use Filament\Schemas\Components\Grid;

class FaqBlock
{
    public static function make(): Block
    {
        return Block::make('faq')
            ->label(__('cms_blocks.faq.label'))
            ->icon('heroicon-o-question-mark-circle')
            ->schema([
                Repeater::make('items')
                    ->label(__('cms_blocks.faq.items'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('question.en')->label(__('cms_blocks.faq.question_en'))->required()->maxLength(255),
                            TextInput::make('question.ar')->label(__('cms_blocks.faq.question_ar'))->required()->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                        ]),
                        Grid::make(2)->schema([
                            Textarea::make('answer.en')->label(__('cms_blocks.faq.answer_en'))->required()->rows(3),
                            Textarea::make('answer.ar')->label(__('cms_blocks.faq.answer_ar'))->required()->rows(3)->extraInputAttributes(['dir' => 'rtl']),
                        ]),
                    ])
                    ->collapsible()
                    ->defaultItems(1),
            ]);
    }
}
