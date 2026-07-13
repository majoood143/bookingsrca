<?php

namespace App\Filament\Resources\BookingSettingResource\Pages;

use App\Filament\Resources\BookingSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookingSetting extends CreateRecord
{
    protected static string $resource = BookingSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['value'] = $data['value_text'] ?? '';
        unset($data['value_text'], $data['value_boolean'], $data['value_richtext'], $data['value_file'], $data['value_color']);

        return $data;
    }
}
