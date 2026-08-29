<?php

namespace App\Services;

use App\Models\ExtraService;
use App\Models\TicketType;
use App\Models\TimeSlot;
use Exception;
use Illuminate\Support\Collection;

/**
 * The single place that locks and validates seat/ticket capacity at the
 * moment of sale. Every sales channel — the online booking wizard, the
 * kiosk, and the POS counter — MUST go through this instead of
 * re-implementing lockForUpdate()/isAvailable() checks, or the double-sell
 * race condition this exists to prevent reopens on whichever channel skips it.
 *
 * Callers MUST invoke lockAndPrice() from inside their own DB::transaction():
 * the row locks taken here are only held for the life of that transaction.
 */
class SeatAllocationService
{
    /**
     * @param int $timeSlotId
     * @param int $eventId
     * @param array<int,int> $ticketQuantities [ticketTypeId => quantity]
     * @param array<int,array<int,int>> $extraServiceCounts [ticketTypeId => [serviceId => count]]
     * @param bool $enforceCapacity when false, locks and prices without rejecting on availability (admin override)
     * @param array<string,mixed> $extraTimeSlotConstraints extra equality where() clauses applied to the
     *        TimeSlot lookup (e.g. ['date' => $selectedDate]) — a mismatch makes the slot resolve to "not
     *        found" the same way it would if the id didn't exist, for callers that need that guard.
     * @param callable|null $afterSlotLocked receives the locked TimeSlot immediately after it's fetched,
     *        before the capacity check — for channel-specific policy (cutoff windows, booking_channel
     *        visibility) that has nothing to do with the lock itself. Throw from here to abort.
     * @return array{
     *     timeSlot: TimeSlot,
     *     ticketTypes: Collection<int,TicketType>,
     *     extraServices: Collection<int,ExtraService>,
     *     ticketPrice: float,
     *     servicesPrice: float,
     * }
     *
     * @throws Exception when the slot/ticket types/services can't be found or don't have enough capacity
     */
    public function lockAndPrice(
        int $timeSlotId,
        int $eventId,
        array $ticketQuantities,
        array $extraServiceCounts = [],
        bool $enforceCapacity = true,
        array $extraTimeSlotConstraints = [],
        ?callable $afterSlotLocked = null,
    ): array {
        $timeSlot = TimeSlot::where('event_id', $eventId)
            ->where($extraTimeSlotConstraints)
            ->lockForUpdate()
            ->find($timeSlotId);

        if (!$timeSlot) {
            throw new Exception(__('Time slot is no longer available.'));
        }

        if ($afterSlotLocked) {
            $afterSlotLocked($timeSlot);
        }

        $totalQty = array_sum($ticketQuantities);

        if ($enforceCapacity && !$timeSlot->isAvailable($totalQty)) {
            throw new Exception(__('Time slot is not available for the requested quantity.'));
        }

        $ticketPrice = 0;
        $servicesPrice = 0;
        $lockedTicketTypes = collect();
        $lockedExtraServices = collect();

        foreach ($ticketQuantities as $typeId => $qty) {
            if ($qty <= 0) {
                continue;
            }

            $ticketType = TicketType::where('id', $typeId)->lockForUpdate()->first();

            if (!$ticketType) {
                throw new Exception(__('Ticket type not found.'));
            }

            if ($enforceCapacity && !$ticketType->isAvailable($qty)) {
                throw new Exception(__('Ticket type :name is not available.', [
                    'name' => $ticketType->getTranslation('name', app()->getLocale()),
                ]));
            }

            $ticketPrice += $ticketType->price * $qty;
            $lockedTicketTypes->put($typeId, $ticketType);

            if (!empty($extraServiceCounts[$typeId])) {
                $services = ExtraService::whereIn('id', array_keys($extraServiceCounts[$typeId]))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($extraServiceCounts[$typeId] as $serviceId => $count) {
                    if ($count <= 0) {
                        continue;
                    }

                    $service = $services[$serviceId] ?? null;

                    if ($enforceCapacity && (!$service || !$service->isAvailable($count))) {
                        throw new Exception(__('Service :name is not available.', [
                            'name' => $service?->getTranslation('name', app()->getLocale()) ?? '',
                        ]));
                    }

                    $servicesPrice += ($service->price ?? 0) * $count;
                    $lockedExtraServices->put($serviceId, $service);
                }
            }
        }

        return [
            'timeSlot' => $timeSlot,
            'ticketTypes' => $lockedTicketTypes,
            'extraServices' => $lockedExtraServices,
            'ticketPrice' => $ticketPrice,
            'servicesPrice' => $servicesPrice,
        ];
    }
}
