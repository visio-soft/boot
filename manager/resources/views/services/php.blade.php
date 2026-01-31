@extends('layouts.app')

@section('content')
<header>
    <h1>Edit php.ini</h1>
    <div>
        <a href="{{ route('services.index') }}" class="btn btn-secondary">Back</a>
    </div>
</header>

<div class="input-group" style="max-width: 100%;">
    <div style="margin-bottom: 1rem; font-weight: 600; color: #86868b;">
        Editing: {{ $path }}
    </div>
    
    <form action="{{ route('services.save-php') }}" method="POST">
        @csrf
        <textarea name="content" style="width: 100%; height: 60vh; font-family: monospace; padding: 1rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: #f5f5f7; font-size: 0.9rem; resize: vertical;" spellcheck="false">{{ $content }}</textarea>
        
        <div style="margin-top: 1rem; display: flex; justify-content: flex-end; gap: 1rem;">
            <button type="submit" class="btn" onclick="return confirm('This will restart PHP-FPM service. Continue?')">Save & Restart PHP</button>
        </div>
    </form>
</div>
@endsection
