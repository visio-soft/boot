@extends('layouts.app')

@section('content')
<header class="flex items-center justify-between mb-12">
    <div>
        <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">Services</h1>
        <p class="text-apple-grey mt-2">Monitor system services and view logs.</p>
    </div>
    <a href="{{ route('services.php') }}" class="btn-secondary text-sm px-5 py-2.5">Edit php.ini</a>
</header>

<!-- System Services -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-12">
    @foreach($status as $key => $s)
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold">{{ $s['label'] }}</h3>
            @if($s['active'])
                <span class="text-xs text-green-600 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Active
                </span>
            @else
                <span class="text-xs text-red-500 font-medium">Stopped</span>
            @endif
        </div>
        <p class="text-xs text-apple-grey mb-4 font-mono">{{ $key }}</p>
        <div class="flex gap-2">
            <form action="{{ route('services.restart') }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="service" value="{{ $key }}">
                <button type="submit" class="btn-secondary text-xs w-full py-2">Restart</button>
            </form>
            @php 
                $logKey = match($key) {
                    'nginx' => 'nginx',
                    'php8.4-fpm' => 'php',
                    'redis-server' => 'redis',
                    'postgresql' => 'postgres',
                    default => null
                };
            @endphp
            @if($logKey)
            <a href="{{ route('services.logs', ['type' => $logKey]) }}" class="btn-secondary text-xs px-4 py-2">Logs</a>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
