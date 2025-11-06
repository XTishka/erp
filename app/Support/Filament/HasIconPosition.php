<?php

namespace Filament\Support\Concerns;

if (! trait_exists(HasIconPosition::class)) {
    trait HasIconPosition
    {
        // Filament v3 already provides icon positioning via the HasIcon trait.
        // This shim satisfies packages that expect the separate trait introduced in v4.
    }
}

if (! trait_exists(HasIconSize::class)) {
    trait HasIconSize
    {
        // Filament v3's HasIcon trait already exposes iconSize() helpers.
        // This shim keeps v4 plugins happy without redefining methods.
    }
}
