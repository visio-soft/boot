@extends('layouts.app')

@section('content')
<header class="mb-12">
    <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">Software</h1>
    <p class="text-apple-grey mt-2">Install development tools with one click.</p>
</header>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($software as $tool)
    <div class="card">
        <div class="flex items-start gap-4 mb-6">
            <span class="text-3xl">{{ $tool['icon'] }}</span>
            <div>
                <h3 class="text-lg font-semibold">{{ $tool['name'] }}</h3>
                <p class="text-sm text-apple-grey">{{ $tool['description'] }}</p>
            </div>
        </div>

        <div class="flex items-center justify-between">
            @if($tool['installed'])
                <span class="text-xs text-green-600 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-green-500 rounded-full"></span> Installed
                </span>
                @if($tool['url'])
                    <a href="{{ $tool['url'] }}" target="_blank" class="btn-secondary text-xs px-4 py-2">Open</a>
                @endif
            @else
                <span class="text-xs text-apple-grey">Not installed</span>
                <form action="{{ route('software.install') }}" method="POST">
                    @csrf
                    <input type="hidden" name="software" value="{{ $tool['key'] }}">
                    <button type="submit" class="btn text-xs px-4 py-2">Install</button>
                </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
