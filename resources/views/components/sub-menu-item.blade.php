<!-- COLLAPSE - INFORMATION -->
@if ($permission)
    <a class="f-14 text-lightest {{ $active ? 'active' : '' }}" href="{{ $link }}" title="{{ $text }}">{{ $text }}</a>
@endif
