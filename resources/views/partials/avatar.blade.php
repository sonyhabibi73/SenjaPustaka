@props(['user', 'class' => ''])

@if ($user->avatar)
    <span class="avatar {{ $class }}"><img src="{{ '/storage/'.ltrim($user->avatar, '/') }}" alt="{{ $user->name }}"></span>
@else
    <span class="avatar {{ $class }}">{{ $user->initials() }}</span>
@endif
