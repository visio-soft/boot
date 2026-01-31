@extends('layouts.app')

@section('content')
<header>
    <h1>Software Center</h1>
    <div>
        <span class="badge badge-other">Tools</span>
    </div>
</header>

<div class="grid">
    @foreach($software as $tool)
        <div class="card">
            <div class="card-header">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div style="font-size: 2rem;">{{ $tool['icon'] }}</div>
                    <div>
                        <div class="site-name">{{ $tool['name'] }}</div>
                        <div class="site-meta">{{ $tool['description'] }}</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                @if($tool['installed'])
                    <span class="badge" style="background: rgba(52, 199, 89, 0.1); color: #34c759;">
                         Installed
                    </span>
                    @if($tool['url'])
                    <a href="{{ $tool['url'] }}" class="btn btn-secondary" style="text-decoration: none; padding: 0.5rem 1rem; font-size: 0.8rem;">Open</a>
                    @endif
                @else
                    <span class="badge" style="background: rgba(142, 142, 147, 0.1); color: #8e8e93;">
                         Not Installed
                    </span>
                    <form action="{{ route('software.install') }}" method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="software" value="{{ $tool['key'] }}">
                        <button type="submit" class="btn" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Install</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
