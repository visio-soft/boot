@extends('layouts.app')

@section('content')
<header class="flex items-center justify-between mb-12">
    <div>
        <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">{{ $title }}</h1>
        <p class="text-apple-grey mt-2 font-mono text-sm">{{ $logPath }}</p>
    </div>
    <div class="flex items-center gap-4">
        <input type="text" id="search" placeholder="Search logs..." class="input-field w-64">
        <a href="{{ route('services.index') }}" class="btn-secondary text-sm px-5 py-2.5">Back</a>
    </div>
</header>

<div class="card p-0 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-2 bg-[#f5f5f7] border-b border-apple-border">
        <span class="text-xs text-apple-grey font-medium">Live Log Stream</span>
        <span class="flex items-center gap-1.5 text-xs text-green-600">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Connected
        </span>
    </div>
    <div id="logContainer" class="h-[65vh] overflow-y-auto p-4 bg-[#1e1e1e] font-mono text-sm text-gray-300 leading-relaxed"></div>
</div>

<script>
const container = document.getElementById('logContainer');
const searchInput = document.getElementById('search');
let logs = [];
let autoScroll = true;

container.addEventListener('scroll', () => {
    const pos = container.scrollTop + container.offsetHeight;
    autoScroll = pos > container.scrollHeight - 50;
});

async function fetchLogs() {
    try {
        const url = new URL(window.location.href);
        url.searchParams.set('json', '1');
        const res = await fetch(url);
        const data = await res.json();
        if (data.content) {
            logs = data.content.split('\n');
            render();
        }
    } catch (e) {}
}

function render() {
    const filter = searchInput.value.toLowerCase();
    container.innerHTML = logs
        .filter(line => line && (!filter || line.toLowerCase().includes(filter)))
        .map(line => {
            let html = escape(line);
            html = html.replace(/(\.ERROR)/g, '<span class="text-red-400">$1</span>');
            html = html.replace(/(\.WARNING)/g, '<span class="text-yellow-400">$1</span>');
            html = html.replace(/(\.INFO)/g, '<span class="text-green-400">$1</span>');
            return `<div class="mb-0.5">${html}</div>`;
        }).join('');
    if (autoScroll) container.scrollTop = container.scrollHeight;
}

function escape(text) {
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

searchInput.addEventListener('input', render);
fetchLogs();
setInterval(fetchLogs, 2000);
</script>
@endsection
