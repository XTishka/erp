@php
    $tags = $getState();
@endphp

@if($tags && $tags->count())
    <div class="flex flex-wrap gap-1">
        @foreach ($tags as $tag)
            <span
                class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                style="color: {{ $tag->color }}; border-color: {{ $tag->color }}; background-color: {{ $tag->color }}1A;"
            >
                {{ $tag->name }}
            </span>
        @endforeach
    </div>
@endif
