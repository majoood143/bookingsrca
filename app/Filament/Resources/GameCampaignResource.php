<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameCampaignResource\Pages\CampaignLeaderboard;
use App\Filament\Resources\GameCampaignResource\Pages\CreateGameCampaign;
use App\Filament\Resources\GameCampaignResource\Pages\EditGameCampaign;
use App\Filament\Resources\GameCampaignResource\Pages\ListGameCampaigns;
use App\Filament\Resources\GameCampaignResource\RelationManagers\AttemptsRelationManager;
use App\Filament\Resources\GameCampaignResource\RelationManagers\AwardsRelationManager;
use App\Models\BookingSetting;
use App\Models\GameAttempt;
use App\Models\GameCampaign;
use App\Services\Games\GameAwardService;
use App\Services\Games\GameException;
use App\Support\QrCode;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Games → Campaigns: everything about a game run is set here (period, rules, prizes, branding,
 * texts), so each client runs and brands their own challenges without a developer.
 */
class GameCampaignResource extends Resource
{
    protected static ?string $model = GameCampaign::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-trophy';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'slug';

    public static function canAccess(): bool
    {
        return parent::canAccess() && (bool) BookingSetting::get('module_games_enabled', true);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('games.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('games.campaign.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('games.campaign.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('campaign')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Tab::make(__('games.campaign.tabs.details'))
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name.en')
                                    ->label(__('games.campaign.fields.name_en'))
                                    ->required()
                                    ->maxLength(120)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, Get $get, Set $set, string $operation) {
                                        if ($operation === 'create' && blank($get('slug'))) {
                                            $set('slug', Str::slug((string) $state));
                                        }
                                    }),

                                TextInput::make('name.ar')
                                    ->label(__('games.campaign.fields.name_ar'))
                                    ->required()
                                    ->maxLength(120)
                                    ->extraInputAttributes(['dir' => 'rtl']),

                                TextInput::make('slug')
                                    ->label(__('games.campaign.fields.slug'))
                                    ->helperText(__('games.campaign.fields.slug_helper'))
                                    ->required()
                                    ->maxLength(80)
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true)
                                    ->prefix('/play/')
                                    ->extraInputAttributes(['dir' => 'ltr']),

                                Select::make('game_type')
                                    ->label(__('games.campaign.fields.game_type'))
                                    ->options(['penalty' => __('games.campaign.game_types.penalty')])
                                    ->default(GameCampaign::TYPE_PENALTY)
                                    ->required()
                                    ->native(false),

                                Select::make('status')
                                    ->label(__('games.campaign.fields.status'))
                                    ->options(self::statusOptions())
                                    ->default(GameCampaign::STATUS_DRAFT)
                                    ->required()
                                    ->native(false),
                            ]),

                            Section::make(__('games.campaign.sections.period'))
                                ->description(__('games.campaign.sections.period_desc', ['timezone' => config('app.timezone')]))
                                ->columns(2)
                                ->schema([
                                    DateTimePicker::make('starts_at')
                                        ->label(__('games.campaign.fields.starts_at'))
                                        ->required()
                                        ->seconds(false)
                                        ->native(false),

                                    DateTimePicker::make('ends_at')
                                        ->label(__('games.campaign.fields.ends_at'))
                                        ->required()
                                        ->seconds(false)
                                        ->native(false)
                                        ->after('starts_at'),
                                ]),

                            Grid::make(2)->schema([
                                Textarea::make('description.en')
                                    ->label(__('games.campaign.fields.description_en'))
                                    ->rows(3)
                                    ->maxLength(500),

                                Textarea::make('description.ar')
                                    ->label(__('games.campaign.fields.description_ar'))
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                            ]),
                        ]),

                    Tab::make(__('games.campaign.tabs.rules'))
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Section::make(__('games.campaign.sections.attempts'))
                                ->columns(3)
                                ->schema([
                                    TextInput::make('shots_per_attempt')
                                        ->label(__('games.campaign.fields.shots_per_attempt'))
                                        ->integer()
                                        ->minValue(1)
                                        ->maxValue(10)
                                        ->default(5)
                                        ->required(),

                                    TextInput::make('attempts_per_day')
                                        ->label(__('games.campaign.fields.attempts_per_day'))
                                        ->helperText(__('games.campaign.fields.attempts_per_day_helper'))
                                        ->integer()
                                        ->minValue(1)
                                        ->maxValue(1000)
                                        ->default(3)
                                        ->nullable(),

                                    TextInput::make('attempts_total')
                                        ->label(__('games.campaign.fields.attempts_total'))
                                        ->helperText(__('games.campaign.fields.attempts_total_helper'))
                                        ->integer()
                                        ->minValue(1)
                                        ->maxValue(10000)
                                        ->nullable(),
                                ]),

                            Section::make(__('games.campaign.sections.difficulty'))
                                ->columns(2)
                                ->schema([
                                    TextInput::make('keeper_skill')
                                        ->label(__('games.campaign.fields.keeper_skill'))
                                        ->helperText(__('games.campaign.fields.keeper_skill_helper'))
                                        ->integer()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->default(50)
                                        ->required(),

                                    TextInput::make('risk_threshold')
                                        ->label(__('games.campaign.fields.risk_threshold'))
                                        ->helperText(__('games.campaign.fields.risk_threshold_helper'))
                                        ->integer()
                                        ->minValue(1)
                                        ->maxValue(100)
                                        ->default(60)
                                        ->required(),
                                ]),
                        ]),

                    Tab::make(__('games.campaign.tabs.prizes'))
                        ->icon('heroicon-o-gift')
                        ->schema([
                            Repeater::make('prizes')
                                ->label(__('games.campaign.prize.label'))
                                ->helperText(__('games.campaign.sections.prizes_desc'))
                                ->relationship()
                                ->orderColumn(false)
                                ->addActionLabel(__('games.campaign.prize.add'))
                                ->maxItems(10)
                                ->defaultItems(0)
                                ->columns(3)
                                ->itemLabel(fn (array $state): ?string => isset($state['rank']) ? '#' . $state['rank'] : null)
                                ->schema([
                                    Select::make('rank')
                                        ->label(__('games.campaign.prize.rank'))
                                        ->options(collect(range(1, 10))->mapWithKeys(fn (int $r) => [$r => '#' . $r])->all())
                                        ->required()
                                        ->distinct()
                                        ->native(false),

                                    TextInput::make('title.en')
                                        ->label(__('games.campaign.prize.title_en'))
                                        ->maxLength(120),

                                    TextInput::make('title.ar')
                                        ->label(__('games.campaign.prize.title_ar'))
                                        ->maxLength(120)
                                        ->extraInputAttributes(['dir' => 'rtl']),

                                    Select::make('discount_type')
                                        ->label(__('games.campaign.prize.discount_type'))
                                        ->options([
                                            'percentage' => __('promo.type_percentage'),
                                            'fixed' => __('promo.type_fixed'),
                                        ])
                                        ->default('percentage')
                                        ->required()
                                        ->live()
                                        ->native(false),

                                    TextInput::make('discount_value')
                                        ->label(__('games.campaign.prize.discount_value'))
                                        ->numeric()
                                        ->required()
                                        ->minValue(0.001)
                                        ->maxValue(fn (Get $get): ?float => $get('discount_type') === 'percentage' ? 100 : null)
                                        ->suffix(fn (Get $get): string => $get('discount_type') === 'percentage' ? '%' : BookingSetting::currencySymbol()),

                                    TextInput::make('max_discount_amount')
                                        ->label(__('games.campaign.prize.max_discount_amount'))
                                        ->helperText(__('games.campaign.prize.max_discount_amount_helper'))
                                        ->numeric()
                                        ->minValue(0.001)
                                        ->nullable()
                                        ->suffix(BookingSetting::currencySymbol())
                                        ->visible(fn (Get $get): bool => $get('discount_type') === 'percentage'),

                                    Select::make('event_id')
                                        ->label(__('games.campaign.prize.event'))
                                        ->helperText(__('games.campaign.prize.event_helper'))
                                        ->relationship('event', 'title')
                                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('title', app()->getLocale()))
                                        ->searchable(['title->en', 'title->ar'])
                                        ->preload()
                                        ->nullable()
                                        ->native(false),

                                    TextInput::make('valid_days')
                                        ->label(__('games.campaign.prize.valid_days'))
                                        ->helperText(__('games.campaign.prize.valid_helper'))
                                        ->integer()
                                        ->minValue(1)
                                        ->maxValue(3650)
                                        ->nullable(),

                                    DateTimePicker::make('valid_until')
                                        ->label(__('games.campaign.prize.valid_until'))
                                        ->seconds(false)
                                        ->native(false)
                                        ->nullable(),
                                ]),
                        ]),

                    Tab::make(__('games.campaign.tabs.branding'))
                        ->icon('heroicon-o-swatch')
                        ->schema([
                            Section::make(__('games.campaign.sections.colors'))
                                ->description(__('games.campaign.sections.colors_desc'))
                                ->columns(3)
                                ->schema([
                                    ColorPicker::make('primary_color')->label(__('games.campaign.fields.primary_color'))
                                        ->placeholder(BookingSetting::get('primary_color', '#05602b')),
                                    ColorPicker::make('secondary_color')->label(__('games.campaign.fields.secondary_color'))
                                        ->placeholder(BookingSetting::get('secondary_color', '#0da74c')),
                                    ColorPicker::make('accent_color')->label(__('games.campaign.fields.accent_color'))
                                        ->placeholder(GameCampaign::DEFAULT_ACCENT),
                                    ColorPicker::make('kit_color')->label(__('games.campaign.fields.kit_color')),
                                    ColorPicker::make('keeper_kit_color')->label(__('games.campaign.fields.keeper_kit_color'))
                                        ->placeholder(GameCampaign::DEFAULT_KEEPER_KIT),
                                ]),

                            Section::make(__('games.campaign.sections.images'))
                                ->description(__('games.campaign.sections.images_desc'))
                                ->columns(3)
                                ->schema([
                                    FileUpload::make('logo_path')
                                        ->label(__('games.campaign.fields.logo'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('games')
                                        ->visibility('public')
                                        ->maxSize(2048),

                                    FileUpload::make('sponsor_logo_path')
                                        ->label(__('games.campaign.fields.sponsor_logo'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('games')
                                        ->visibility('public')
                                        ->maxSize(2048),

                                    FileUpload::make('cover_image_path')
                                        ->label(__('games.campaign.fields.cover_image'))
                                        ->helperText(__('games.campaign.fields.cover_image_helper'))
                                        ->image()
                                        ->disk('public')
                                        ->directory('games')
                                        ->visibility('public')
                                        ->maxSize(4096),
                                ]),
                        ]),

                    Tab::make(__('games.campaign.tabs.content'))
                        ->icon('heroicon-o-language')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('headline.en')->label(__('games.campaign.fields.headline_en'))->maxLength(60),
                                TextInput::make('headline.ar')->label(__('games.campaign.fields.headline_ar'))->maxLength(60)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                TextInput::make('tagline.en')->label(__('games.campaign.fields.tagline_en'))->maxLength(200),
                                TextInput::make('tagline.ar')->label(__('games.campaign.fields.tagline_ar'))->maxLength(200)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                TextInput::make('sponsor_name.en')->label(__('games.campaign.fields.sponsor_name_en'))->maxLength(80),
                                TextInput::make('sponsor_name.ar')->label(__('games.campaign.fields.sponsor_name_ar'))->maxLength(80)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                TextInput::make('board_text.en')->label(__('games.campaign.fields.board_text_en'))
                                    ->helperText(__('games.campaign.fields.board_text_helper'))->maxLength(60),
                                TextInput::make('board_text.ar')->label(__('games.campaign.fields.board_text_ar'))->maxLength(60)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                TextInput::make('prize_text.en')->label(__('games.campaign.fields.prize_text_en'))
                                    ->helperText(__('games.campaign.fields.prize_text_helper'))->maxLength(120),
                                TextInput::make('prize_text.ar')->label(__('games.campaign.fields.prize_text_ar'))->maxLength(120)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                Textarea::make('share_text.en')->label(__('games.campaign.fields.share_text_en'))
                                    ->helperText(__('games.campaign.fields.share_text_helper'))->rows(2)->maxLength(300),
                                Textarea::make('share_text.ar')->label(__('games.campaign.fields.share_text_ar'))->rows(2)->maxLength(300)
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                RichEditor::make('terms.en')->label(__('games.campaign.fields.terms_en'))
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
                                RichEditor::make('terms.ar')->label(__('games.campaign.fields.terms_ar'))
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link']),
                            ]),
                        ]),

                    Tab::make(__('games.campaign.tabs.leaderboard'))
                        ->icon('heroicon-o-list-bullet')
                        ->schema([
                            Grid::make(3)->schema([
                                Toggle::make('leaderboard_public')
                                    ->label(__('games.campaign.fields.leaderboard_public'))
                                    ->helperText(__('games.campaign.fields.leaderboard_public_helper'))
                                    ->default(true),

                                TextInput::make('leaderboard_size')
                                    ->label(__('games.campaign.fields.leaderboard_size'))
                                    ->integer()
                                    ->minValue(3)
                                    ->maxValue(100)
                                    ->default(10)
                                    ->required(),

                                DateTimePicker::make('leaderboard_freeze_at')
                                    ->label(__('games.campaign.fields.leaderboard_freeze_at'))
                                    ->helperText(__('games.campaign.fields.leaderboard_freeze_at_helper'))
                                    ->seconds(false)
                                    ->native(false)
                                    ->nullable(),
                            ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount([
                    'attempts',
                    'attempts as flagged_count' => fn (Builder $q) => $q->where('review_status', GameAttempt::REVIEW_FLAGGED),
                ])
                ->addSelect(['players_count' => GameAttempt::query()
                    ->selectRaw('count(distinct game_player_id)')
                    ->whereColumn('game_campaign_id', 'game_campaigns.id')]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('games.campaign.columns.name'))
                    ->getStateUsing(fn (GameCampaign $record) => $record->translated('name'))
                    ->description(fn (GameCampaign $record) => '/play/' . $record->slug)
                    ->searchable(['name->en', 'name->ar', 'slug']),

                TextColumn::make('phase')
                    ->label(__('games.campaign.columns.phase'))
                    ->getStateUsing(fn (GameCampaign $record) => $record->phase())
                    ->formatStateUsing(fn (string $state) => __('games.campaign.phases.' . $state))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        GameCampaign::PHASE_LIVE => 'success',
                        GameCampaign::PHASE_UPCOMING => 'info',
                        GameCampaign::PHASE_ENDED => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('starts_at')
                    ->label(__('games.campaign.columns.period'))
                    ->formatStateUsing(fn (GameCampaign $record) => $record->starts_at->format('d M Y H:i') . ' → ' . $record->ends_at->format('d M Y H:i'))
                    ->sortable(),

                TextColumn::make('players_count')
                    ->label(__('games.campaign.columns.players'))
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('attempts_count')
                    ->label(__('games.campaign.columns.attempts'))
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('flagged_count')
                    ->label(__('games.campaign.columns.flagged'))
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'danger' : 'gray')
                    ->alignCenter(),

                IconColumn::make('awarded_at')
                    ->label(__('games.campaign.columns.awarded'))
                    ->getStateUsing(fn (GameCampaign $record) => $record->isAwarded())
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('games.campaign.fields.status'))
                    ->options(self::statusOptions()),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    Action::make('leaderboard')
                        ->label(__('games.campaign.actions.leaderboard'))
                        ->icon('heroicon-o-list-bullet')
                        ->url(fn (GameCampaign $record) => self::getUrl('leaderboard', ['record' => $record])),
                    Action::make('openGame')
                        ->label(__('games.campaign.actions.open_game'))
                        ->icon('heroicon-o-play')
                        ->url(fn (GameCampaign $record) => $record->playUrl())
                        ->openUrlInNewTab(),
                    self::shareAction(),
                ]),
            ])
            ->defaultSort('starts_at', 'desc');
    }

    /** Links (game, public board, stadium screen) and a printable QR code. */
    public static function shareAction(): Action
    {
        return Action::make('share')
            ->label(__('games.campaign.actions.share'))
            ->icon('heroicon-o-qr-code')
            ->color('gray')
            ->modalHeading(fn (GameCampaign $record) => __('games.campaign.actions.share_heading', ['name' => $record->translated('name')]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('games.public.close'))
            ->modalContent(function (GameCampaign $record) {
                $qr = QrCode::dataUri($record->playUrl(), 480);
                $row = fn (string $label, string $url, ?string $help = null) => '<div style="margin-bottom:14px"><div style="font-weight:600;font-size:.85rem">' . e($label) . '</div>'
                    . '<a href="' . e($url) . '" target="_blank" rel="noopener" dir="ltr" style="word-break:break-all;color:rgb(var(--primary-600))">' . e($url) . '</a>'
                    . ($help ? '<div style="font-size:.78rem;opacity:.7;margin-top:2px">' . e($help) . '</div>' : '') . '</div>';

                return new HtmlString(
                    $row(__('games.campaign.actions.game_link'), $record->playUrl())
                    . $row(__('games.campaign.actions.open_leaderboard'), $record->leaderboardUrl())
                    . $row(__('games.campaign.actions.display_link'), $record->leaderboardUrl(true), __('games.campaign.actions.display_link_helper'))
                    . '<div style="text-align:center;margin-top:8px"><img src="' . $qr . '" alt="QR" style="width:240px;height:240px;margin:0 auto;background:#fff;border-radius:12px">'
                    . '<div style="font-size:.78rem;opacity:.7;margin:6px 0">' . e(__('games.campaign.actions.qr_helper')) . '</div>'
                    . '<a href="' . $qr . '" download="' . e($record->slug) . '-qr.png" style="font-weight:600;color:rgb(var(--primary-600))">' . e(__('games.campaign.actions.download_qr')) . '</a></div>'
                );
            });
    }

    /** Close the campaign and send the prize vouchers. */
    public static function finalizeAction(): Action
    {
        return Action::make('finalize')
            ->label(__('games.campaign.actions.finalize'))
            ->icon('heroicon-o-trophy')
            ->color('success')
            ->visible(fn (GameCampaign $record) => $record->hasEnded() && ! $record->isAwarded())
            ->requiresConfirmation()
            ->modalDescription(__('games.campaign.actions.finalize_desc'))
            ->action(function (GameCampaign $record, GameAwardService $awards) {
                try {
                    $created = $awards->finalize($record, auth()->user());
                } catch (GameException $e) {
                    Notification::make()->danger()->title(__('games.campaign.actions.cannot_finalize'))->body($e->getMessage())->send();

                    return;
                }

                $failed = collect($created)->filter(fn ($award) => $award->notified_at === null)->count();

                Notification::make()
                    ->{$failed ? 'warning' : 'success'}()
                    ->title(__('games.campaign.actions.finalized', ['count' => count($created)]))
                    ->body($failed ? __('games.campaign.actions.finalized_failed') : null)
                    ->send();
            });
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            GameCampaign::STATUS_DRAFT => __('games.campaign.statuses.draft'),
            GameCampaign::STATUS_PUBLISHED => __('games.campaign.statuses.published'),
            GameCampaign::STATUS_ARCHIVED => __('games.campaign.statuses.archived'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            AwardsRelationManager::class,
            AttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGameCampaigns::route('/'),
            'create' => CreateGameCampaign::route('/create'),
            'edit' => EditGameCampaign::route('/{record}/edit'),
            'leaderboard' => CampaignLeaderboard::route('/{record}/leaderboard'),
        ];
    }
}
