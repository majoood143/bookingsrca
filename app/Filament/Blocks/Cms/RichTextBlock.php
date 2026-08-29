<?php

namespace App\Filament\Blocks\Cms;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Builder\Block;

class RichTextBlock
{
    public static function make(): Block
    {
        return Block::make('rich_text')
            ->label(__('cms_blocks.rich_text.label'))
            ->icon('heroicon-o-document-text')
            ->schema([
                RichEditor::make('content.en')
                    ->label(__('cms_blocks.rich_text.content_en'))
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('cms/blocks'),
                RichEditor::make('content.ar')
                    ->label(__('cms_blocks.rich_text.content_ar'))
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('cms/blocks')
                    ->extraInputAttributes(['dir' => 'rtl']),
            ]);
    }
}
