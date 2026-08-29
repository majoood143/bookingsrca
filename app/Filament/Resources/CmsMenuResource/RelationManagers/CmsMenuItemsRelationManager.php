<?php

namespace App\Filament\Resources\CmsMenuResource\RelationManagers;

use App\Models\CmsCategory;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\CmsPost;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CmsMenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'allItems';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('cms_menu.relation.items');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('label.en')
                    ->label(__('cms_menu.fields.label_en'))
                    ->required()
                    ->maxLength(255),

                TextInput::make('label.ar')
                    ->label(__('cms_menu.fields.label_ar'))
                    ->required()
                    ->maxLength(255)
                    ->extraInputAttributes(['dir' => 'rtl']),
            ]),

            Select::make('link_type')
                ->label(__('cms_menu.fields.link_type'))
                ->options([
                    'custom' => __('cms_menu.link_types.custom'),
                    'page' => __('cms_menu.link_types.page'),
                    'post' => __('cms_menu.link_types.post'),
                    'category' => __('cms_menu.link_types.category'),
                ])
                ->default('custom')
                ->native(false)
                ->live()
                ->dehydrated(false)
                ->afterStateHydrated(function ($set, $record) {
                    if (!$record) {
                        return;
                    }

                    $set('link_type', match ($record->linkable_type) {
                        CmsPage::class => 'page',
                        CmsPost::class => 'post',
                        CmsCategory::class => 'category',
                        default => 'custom',
                    });
                }),

            TextInput::make('url')
                ->label(__('cms_menu.fields.url'))
                ->maxLength(255)
                ->visible(fn (Get $get) => $get('link_type') === 'custom'),

            Select::make('linkable_id')
                ->label(__('cms_menu.fields.linkable'))
                ->options(fn (Get $get) => match ($get('link_type')) {
                    'page' => CmsPage::query()->get()->mapWithKeys(fn ($p) => [$p->id => $p->getTranslation('title', app()->getLocale())]),
                    'post' => CmsPost::query()->get()->mapWithKeys(fn ($p) => [$p->id => $p->getTranslation('title', app()->getLocale())]),
                    'category' => CmsCategory::query()->get()->mapWithKeys(fn ($c) => [$c->id => $c->getTranslation('name', app()->getLocale())]),
                    default => [],
                })
                ->searchable()
                ->native(false)
                ->visible(fn (Get $get) => in_array($get('link_type'), ['page', 'post', 'category'])),

            Grid::make(3)->schema([
                TextInput::make('icon')
                    ->label(__('cms_menu.fields.icon'))
                    ->helperText(__('cms_menu.fields.icon_helper')),

                Select::make('parent_id')
                    ->label(__('cms_menu.fields.parent'))
                    ->relationship('parent', 'label', fn ($query) => $query->where('menu_id', $this->getOwnerRecord()->getKey()))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('label', app()->getLocale()))
                    ->searchable()
                    ->native(false),

                Toggle::make('is_active')
                    ->label(__('cms_menu.fields.is_active'))
                    ->default(true),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')
                    ->label(__('cms_menu.columns.label'))
                    ->getStateUsing(fn ($record) => $record->getTranslation('label', app()->getLocale()))
                    ->weight('bold'),

                TextColumn::make('resolved_url')
                    ->label(__('cms_menu.columns.url'))
                    ->getStateUsing(fn (CmsMenuItem $record) => $record->resolveUrl()),

                IconColumn::make('is_active')
                    ->label(__('cms_menu.columns.is_active'))
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label(__('cms_menu.columns.sort_order'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->mutateLinkData($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data): array => $this->mutateLinkData($data)),
                DeleteAction::make(),
            ]);
    }

    private function mutateLinkData(array $data): array
    {
        $data['menu_id'] = $this->getOwnerRecord()->getKey();

        $linkableClass = match ($data['link_type'] ?? 'custom') {
            'page' => CmsPage::class,
            'post' => CmsPost::class,
            'category' => CmsCategory::class,
            default => null,
        };

        if ($linkableClass) {
            $data['linkable_type'] = $linkableClass;
            $data['url'] = null;
        } else {
            $data['linkable_type'] = null;
            $data['linkable_id'] = null;
        }

        unset($data['link_type']);

        return $data;
    }
}
