<?php

namespace App\Filament\Resources\TimeSlotResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\TimeSlotResource;
use App\Models\Event;
use App\Models\TimeSlot;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ListTimeSlots extends ListRecords
{
    protected static string $resource = TimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [

            ExportAction::make()
                ->label(__('time_slot.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn () => 'time-slots-' . now()->format('Y-m-d-His')),
                ]),

            Action::make('generate_slots')
                ->label(__('time_slot.actions.generate_slots'))
                ->icon('heroicon-o-bolt')
                ->color('gray')
                ->schema([
                    Select::make('event_id')
                        ->label(__('time_slot.fields.event'))
                        ->options(fn() => Event::query()
                            ->get()
                            ->mapWithKeys(fn($event) => [$event->id => $event->getTranslation('title', 'en')]))
                        ->searchable()
                        ->required()
                        ->native(false),

                    TimePicker::make('start_time')
                        ->label(__('time_slot.fields.start_time'))
                        ->required()
                        ->seconds(false)
                        ->native(false)
                        ->displayFormat('H:i'),

                    TimePicker::make('end_time')
                        ->label(__('time_slot.fields.end_time'))
                        ->required()
                        ->seconds(false)
                        ->native(false)
                        ->displayFormat('H:i')
                        ->after('start_time'),

                    TextInput::make('max_attendees')
                        ->label(__('time_slot.fields.max_attendees'))
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(10000)
                        ->default(50),

                    Toggle::make('is_active')
                        ->label(__('time_slot.fields.is_active'))
                        ->default(true),
                ])
                ->modalHeading(__('time_slot.actions.generate_slots'))
                ->modalDescription(__('time_slot.modals.generate_slots_description'))
                ->action(function (array $data): void {
                    $event = Event::find($data['event_id']);

                    $created = 0;
                    $skipped = 0;

                    foreach ($event->getAvailableDates() as $date) {
                        $slot = TimeSlot::firstOrCreate(
                            [
                                'event_id' => $event->id,
                                'date' => $date,
                                'start_time' => $data['start_time'],
                                'end_time' => $data['end_time'],
                            ],
                            [
                                'max_attendees' => $data['max_attendees'],
                                'is_active' => $data['is_active'],
                            ],
                        );

                        $slot->wasRecentlyCreated ? $created++ : $skipped++;
                    }

                    Notification::make()
                        ->success()
                        ->title(__('time_slot.notifications.slots_generated'))
                        ->body(__('time_slot.notifications.slots_generated_body', [
                            'created' => $created,
                            'skipped' => $skipped,
                        ]))
                        ->send();
                }),

            CreateAction::make()
                ->label(__('time_slot.actions.new'))
                ->icon('heroicon-o-plus'),
            
        ];
    }
}
