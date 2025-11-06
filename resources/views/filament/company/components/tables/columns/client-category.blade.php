@php
    /** @var \App\Models\Common\ClientCategory|null $category */
    $category = $getState();
@endphp

@if($category)
    <span
        class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
        style="color: {{ $category->color }}; border-color: {{ $category->color }}; background-color: {{ $category->color }}1A;"
    >
        {{ $category->name }}
    </span>
@endif
