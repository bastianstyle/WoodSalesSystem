@props(['type' => '', 'alt' => ''])
@php
    $woodType = strtolower(trim($type));
    $image = match (true) {
        str_contains($woodType, 'jati') => 'jati.svg',
        str_contains($woodType, 'merbau') => 'merbau.svg',
        str_contains($woodType, 'kamper') => 'kamper.svg',
        str_contains($woodType, 'mahoni'), str_contains($woodType, 'mahogany') => 'mahoni.svg',
        str_contains($woodType, 'pinus'), str_contains($woodType, 'pine') => 'pinus.svg',
        str_contains($woodType, 'bengkirai'), str_contains($woodType, 'bangkirai') => 'bengkirai.svg',
        str_contains($woodType, 'meranti') => 'meranti.svg',
        str_contains($woodType, 'sengon') => 'sengon.svg',
        str_contains($woodType, 'sonokeling'), str_contains($woodType, 'rosewood') => 'sonokeling.svg',
        str_contains($woodType, 'ulin'), str_contains($woodType, 'belian') => 'ulin.svg',
        str_contains($woodType, 'nyatoh') => 'nyatoh.svg',
        default => 'campuran.svg',
    };
@endphp
<img src="{{ asset('images/woods/'.$image) }}" alt="{{ $alt }}" {{ $attributes->merge(['loading' => 'lazy']) }}>
