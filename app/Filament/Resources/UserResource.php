<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\UserPrefix;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUserActivities;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('user.navigation.group');
    }

    public static function getModelLabel(): string
    {
        return __('user.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('user.navigation.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('user.sections.account'))
                    ->schema([
                        Select::make('prefix')
                            ->label(__('user.fields.prefix'))
                            ->options(UserPrefix::toArray())
                            ->native(false),

                        TextInput::make('name')
                            ->label(__('user.fields.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('user.fields.email'))
                            ->email()
                            ->required()
                            ->maxLength(255),

                        DateTimePicker::make('email_verified_at')
                            ->label(__('user.fields.email_verified_at')),

                        Toggle::make('is_active')
                            ->label(__('user.fields.is_active'))
                            ->default(true),

                        TextInput::make('password')
                            ->label(__('user.fields.password'))
                            ->password()
                            ->required()
                            ->maxLength(255),

                        Select::make('roles')
                            ->label(__('user.fields.roles'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make(__('user.sections.personal'))
                    ->schema([
                        DatePicker::make('date_of_birth')
                            ->label(__('user.fields.date_of_birth'))
                            ->maxDate(now()),

                        Select::make('gender')
                            ->label(__('user.fields.gender'))
                            ->options(Gender::toArray())
                            ->native(false),

                        Select::make('marital_status')
                            ->label(__('user.fields.marital_status'))
                            ->options(MaritalStatus::toArray())
                            ->native(false),

                        Select::make('blood_group')
                            ->label(__('user.fields.blood_group'))
                            ->options(BloodGroup::toArray())
                            ->native(false),

                        TextInput::make('mobile_number')
                            ->label(__('user.fields.mobile_number'))
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('guardian_name')
                            ->label(__('user.fields.guardian_name'))
                            ->maxLength(255),

                        TextInput::make('nationality')
                            ->label(__('user.fields.nationality'))
                            ->maxLength(255),

                        TextInput::make('national_id_number')
                            ->label(__('user.fields.national_id_number'))
                            ->maxLength(50),

                        Textarea::make('address')
                            ->label(__('user.fields.address'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('user.sections.bank'))
                    ->schema([
                        TextInput::make('bank_account_holder_name')
                            ->label(__('user.fields.bank_account_holder_name'))
                            ->maxLength(255),

                        TextInput::make('bank_account_number')
                            ->label(__('user.fields.bank_account_number'))
                            ->maxLength(50),

                        TextInput::make('bank_name')
                            ->label(__('user.fields.bank_name'))
                            ->maxLength(255),

                        TextInput::make('bank_identifier_code')
                            ->label(__('user.fields.bank_identifier_code'))
                            ->maxLength(20),

                        TextInput::make('bank_branch')
                            ->label(__('user.fields.bank_branch'))
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('user.columns.name'))
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('user.columns.email'))
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label(__('user.columns.role'))
                    ->badge()
                    ->searchable(),

                TextColumn::make('mobile_number')
                    ->label(__('user.columns.mobile_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('gender')
                    ->label(__('user.columns.gender'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email_verified_at')
                    ->label(__('user.columns.email_verified_at'))
                    ->dateTime()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label(__('user.columns.is_active'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('user.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


                TextColumn::make('updated_at')
                    ->label(__('user.columns.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])
                    ->label('More actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListUsers::route('/'),
            'create'     => CreateUser::route('/create'),
            'view'       => ViewUser::route('/{record}'),
            'edit'       => EditUser::route('/{record}/edit'),
            'activities' => ListUserActivities::route('/{record}/activities'),
        ];
    }
}
