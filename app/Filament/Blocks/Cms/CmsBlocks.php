<?php

namespace App\Filament\Blocks\Cms;

class CmsBlocks
{
    /**
     * The full set of Builder blocks shared by CmsPageResource and CmsPostResource.
     *
     * @return array<int, \Filament\Forms\Components\Builder\Block>
     */
    public static function all(): array
    {
        return [
            HeroBlock::make(),
            RichTextBlock::make(),
            FeaturesBlock::make(),
            CtaBlock::make(),
            GalleryBlock::make(),
            TestimonialBlock::make(),
            FaqBlock::make(),
            EmbedBlock::make(),
            StatsBlock::make(),
            TwoColumnBlock::make(),
            TableBlock::make(),
        ];
    }
}
