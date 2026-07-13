<?php

namespace App\Filament\Resources\BookingSettingResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use App\Filament\Resources\BookingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\BookingSettingResource\Pages\ListBookingSettingActivities;
use App\Models\BookingSetting;

class EditBookingSetting extends EditRecord
{
    protected static string $resource = BookingSettingResource::class;

    public function getSubNavigation(): array
    {
        return $this->generateNavigationItems([
            self::class,
            ListBookingSettingActivities::class,
        ]);
    }

    public function getSubNavigationParameters(): array
    {
        return ['record' => $this->getRecord()];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * The form uses a separate, uniquely-named field per setting type (value_boolean,
     * value_richtext, value_file, value_color, value_text) instead of all sharing the
     * 'value' state path — sharing one path let Filament's field-specific hydration
     * (e.g. FileUpload wrapping the state in an array) leak into unrelated field types
     * and corrupt each other's state. Seed each one here from the actual 'value' column.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['value_boolean'] = filter_var($data['value'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['value_richtext'] = $data['value'] ?? '';
        $data['value_file'] = $data['value'] ?? null;
        $data['value_color'] = $data['value'] ?? null;
        $data['value_text'] = $data['value'] ?? '';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['value'] = match ($this->record->type) {
            'boolean' => $data['value_boolean'] ?? 'false',
            'richtext' => $data['value_richtext'] ?? '',
            'file' => $data['value_file'] ?? '',
            'color' => $data['value_color'] ?? '',
            default => $data['value_text'] ?? '',
        };

        unset($data['value_boolean'], $data['value_richtext'], $data['value_file'], $data['value_color'], $data['value_text']);

        return $data;
    }

    protected function afterSave(): void
    {
        BookingSetting::clearCache();

        Notification::make()
            ->success()
            ->title(__('booking_setting.notifications.updated'))
            ->body(__('booking_setting.notifications.updated_body'))
            ->send();
    }
}
