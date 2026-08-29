<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder\Block;

class EmbedBlock
{
    public static function make(): Block
    {
        return Block::make('embed')
            ->label(__('cms_blocks.embed.label'))
            ->icon('heroicon-o-play-circle')
            ->schema([
                TextInput::make('url')
                    ->label(__('cms_blocks.embed.url'))
                    ->helperText(__('cms_blocks.embed.url_helper'))
                    ->url()
                    ->required()
                    ->maxLength(500),
            ]);
    }
}
