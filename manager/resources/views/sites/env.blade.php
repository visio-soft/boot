@extends('layouts.app')

@section('content')
<header class="flex items-center justify-between mb-12">
    <div>
        <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">.env</h1>
        <p class="text-apple-grey mt-2 font-mono text-sm">{{ $path }}</p>
    </div>
    <a href="{{ route('sites.index') }}" class="btn-secondary text-sm px-5 py-2.5">Back</a>
</header>

<div class="card">
    <form action="{{ route('sites.save-env', $site) }}" method="POST">
        @csrf
        <textarea name="content" class="w-full h-[60vh] font-mono text-sm p-4 bg-[#f5f5f7] rounded-apple-sm border-none outline-none resize-none" spellcheck="false">{{ $content }}</textarea>
        <div class="flex justify-end mt-6">
            <button type="submit" class="btn">Save Changes</button>
        </div>
    </form>
</div>
@endsection
