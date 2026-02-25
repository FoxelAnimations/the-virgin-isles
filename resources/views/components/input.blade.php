@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-zinc-700 bg-zinc-800 text-white focus:border-accent focus:ring-accent rounded-sm shadow-sm']) !!}>
