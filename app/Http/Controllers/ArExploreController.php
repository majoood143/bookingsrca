<?php

namespace App\Http\Controllers;

use App\Models\ArDestination;
use App\Models\ArItem;
use App\Models\ArZone;
use App\Models\BookingSetting;
use Illuminate\View\View;

class ArExploreController extends Controller
{
    public function index(): View
    {
        $destinations = ArDestination::published()->with('media')->orderBy('sort_order')->orderBy('id')->get();

        return view('ar.index', ['destinations' => $destinations, 'siteName' => $this->siteName()]);
    }

    public function destination(ArDestination $destination): View
    {
        abort_unless($destination->is_published, 404);

        $zones = $destination->zones()
            ->with(['media', 'items' => fn ($q) => $q->published()->with('media')->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        return view('ar.destination', ['destination' => $destination, 'zones' => $zones, 'siteName' => $this->siteName()]);
    }

    public function item(ArDestination $destination, ArItem $item): View
    {
        abort_unless($destination->is_published && $item->is_published, 404);

        return view('ar.item', ['destination' => $destination, 'item' => $item->load(['media', 'zone.media']), 'siteName' => $this->siteName()]);
    }

    public function scan(ArDestination $destination, ArZone $zone): View
    {
        abort_unless($destination->is_published && $zone->isScannable(), 404);

        return view('ar.scan', [
            'destination' => $destination,
            'zone' => $zone,
            'cards' => $zone->scannerCards(),
            'siteName' => $this->siteName(),
        ]);
    }

    private function siteName(): string
    {
        return BookingSetting::get('site_name_'.app()->getLocale(), config('app.name', 'Bookings'));
    }
}
