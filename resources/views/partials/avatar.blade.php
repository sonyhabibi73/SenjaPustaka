@props(['user', 'class' => ''])

@if ($user->avatar)
    <span class="avatar {{ $class }}"><img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"></span>
@else
    <span class="avatar {{ $class }}">{{ $user->initials() }}</span>
@endif
