@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-0 focus:ring-0 rounded-md shadow-[4px_4px_6px_0px_rgba(0,0,0,0.1)]']) !!}>
