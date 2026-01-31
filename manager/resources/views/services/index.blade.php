@extends('layouts.app')

@section('content')
<header>
    <h1>Services & Logs</h1>
    <div>
        <a href="{{ route('services.php') }}" class="btn btn-secondary">Edit php.ini</a>
    </div>
</header>

<div class="grid">
    @foreach($status as $key => $s)
    <div class="card">
        <div class="card-header">
            <div>
                <div class="site-name">{{ $s['label'] }}</div>
                <div style="font-size: 0.8rem; color: #86868b;">{{ $key }}</div>
            </div>
            <div>
                @if($s['active'])
                    <span class="badge" style="background: rgba(52, 199, 89, 0.1); color: #34c759;">Active</span>
                @else
                    <span class="badge" style="background: rgba(255, 59, 48, 0.1); color: #ff3b30;">Stopped</span>
                @endif
            </div>
        </div>
        
        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 0.5rem;">
                <form action="{{ route('services.restart') }}" method="POST">
                    @csrf
                    <input type="hidden" name="service" value="{{ $key }}">
                    <button type="submit" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Restart</button>
                </form>
            </div>
            
            @if(in_array($key, ['nginx', 'php8.4-fpm', 'redis-server', 'postgresql']))
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
                <a href="{{ route('services.logs', ['type' => $logKey]) }}" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Logs</a>
                @endif
            @endif
        </div>
    </div>
    @endforeach
</div>

<h2 style="margin-top: 3rem; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">Project Logs</h2>
<div class="grid">
    @foreach($projects as $proj)
    <div class="card">
        <div class="card-header" style="margin-bottom: 0;">
            <div class="site-name">{{ $proj }}</div>
            <a href="{{ route('services.logs', ['type' => 'project', 'project' => $proj]) }}" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">View Log</a>
        </div>
    </div>
    @endforeach
</div>
@endsection
