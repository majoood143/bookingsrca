<section class="cms-block px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto grid max-w-(--cms-w,64rem) grid-cols-2 gap-4 sm:grid-cols-3">
        @foreach ($data['images'] ?? [] as $image)
            <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
                <img src="{{ asset('storage/'.$image) }}" alt="" loading="lazy" class="h-full w-full object-cover">
            </div>
        @endforeach
    </div>
</section>
