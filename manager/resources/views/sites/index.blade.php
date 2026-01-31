@extends('layouts.app')

@section('content')
<header class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">Projects</h1>
        <p class="text-apple-grey mt-2">Manage your local development projects.</p>
    </div>
    <button onclick="document.getElementById('createForm').classList.toggle('hidden')" class="btn">
        <span class="mr-1">+</span> New Project
    </button>
</header>

<!-- Create New Project (Hidden by default) -->
<div id="createForm" class="card mb-12 hidden">
    <h2 class="text-lg font-semibold mb-6">New Project</h2>
    <form action="{{ route('sites.store') }}" method="POST" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm text-apple-grey mb-2">GitHub Repository</label>
            <input type="text" name="repo" id="repo" placeholder="git@github.com:user/repo.git" class="input-field" autocomplete="off">
            <div id="gitMessage" class="mt-2 text-xs hidden"></div>
        </div>
        <div>
            <label class="block text-sm text-apple-grey mb-2">Project Name</label>
            <input type="text" name="name" id="name" placeholder="my-project" class="input-field" required>
        </div>
        <div class="flex items-center gap-3">
            <input type="checkbox" name="horizon" id="horizon" value="1" class="w-4 h-4 rounded">
            <label for="horizon" class="text-sm">Install Laravel Horizon</label>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="btn">Create Project</button>
        </div>
    </form>
</div>

<!-- Project List -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($sites as $site)
    <div class="card flex flex-col h-full">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold">{{ $site['name'] }}</h3>
                <a href="{{ $site['url'] }}" target="_blank" class="text-sm text-apple-blue hover:underline">{{ $site['url'] }}</a>
            </div>
            <span class="badge {{ $site['type'] === 'Laravel' ? 'badge-laravel' : 'badge-other' }}">{{ $site['type'] }}</span>
        </div>

        <div class="text-sm text-apple-grey space-y-1 mb-6 flex-1">
            <p>Database: <span class="text-[#1d1d1f]">{{ $site['db_name'] }}</span></p>
            <p>Version: <span class="text-[#1d1d1f]">{{ $site['version'] ?? 'N/A' }}</span></p>
        </div>

        @if($site['type'] === 'Laravel')
        <div class="flex items-center justify-between py-3 border-t border-apple-border">
            <span class="text-sm text-apple-grey">Horizon</span>
            @if($site['horizon'] === 'running')
                <div class="flex items-center gap-3">
                    <a href="{{ $site['url'] }}/horizon" target="_blank" class="text-xs text-apple-blue hover:underline">Open</a>
                    <span class="flex items-center gap-1.5 text-xs text-green-600 font-medium">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Running
                    </span>
                </div>
            @else
                <span class="text-xs text-apple-grey">Inactive</span>
            @endif
        </div>
        @endif

        <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-apple-border">
            <!-- Logs -->
            <a href="{{ route('services.logs', ['type' => 'project', 'project' => $site['name']]) }}" class="btn-secondary text-[11px] h-8 flex items-center justify-center font-medium" title="View Logs">
                Logs
            </a>

            <!-- Env -->
            @if($site['env_exists'])
            <a href="{{ route('sites.env', $site['name']) }}" class="btn-secondary text-[11px] h-8 flex items-center justify-center font-medium" title="Edit .env">
                .env
            </a>
            @else
            <span class="btn-secondary opacity-50 cursor-not-allowed text-[11px] h-8 flex items-center justify-center font-medium">.env</span>
            @endif

            <!-- Delete -->
            <form action="{{ route('sites.destroy', $site['name']) }}" method="POST" class="h-8" onsubmit="return confirm('Delete this project?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary w-full h-full text-[11px] flex items-center justify-center font-medium hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete">
                    Delete
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<!-- Services Section -->
<div class="mt-16 pt-8 border-t border-apple-border">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-[#1d1d1f]">System Services</h2>
            <p class="text-apple-grey mt-1 text-sm">Monitor and manage core infrastructure.</p>
        </div>
        <a href="{{ route('services.php') }}" class="btn-secondary text-xs px-4 py-2">Edit php.ini</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        @foreach($servicesStatus as $key => $s)
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
                <form action="{{ route('sites.restart-service') }}" method="POST" class="flex-1">
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
                <a href="{{ route('services.logs', ['type' => $logKey]) }}" class="btn-secondary text-xs px-4 py-2" title="View Logs">Logs</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
let timeout;
const repoInput = document.getElementById('repo');
const msg = document.getElementById('gitMessage');

repoInput.addEventListener('input', () => {
    clearTimeout(timeout);
    msg.textContent = '';
    msg.className = 'mt-2 text-xs hidden';
    timeout = setTimeout(checkGit, 600);
});

async function checkGit() {
    const repo = repoInput.value;
    if (!repo) return;

    msg.textContent = 'Checking...';
    msg.className = 'mt-2 text-xs text-apple-grey block';

    try {
        const res = await fetch(`{{ route('sites.check-git') }}?repo=${encodeURIComponent(repo)}`);
        const data = await res.json();

        if (data.status === 'ok') {
            msg.textContent = '✓ Access verified';
            msg.className = 'mt-2 text-xs text-green-600 block';
            if (!document.getElementById('name').value) {
                const name = repo.split('/').pop().replace('.git', '');
                document.getElementById('name').value = name;
            }
        } else {
            msg.innerHTML = `✕ ${data.message}<br><textarea readonly class="w-full h-16 mt-2 p-2 text-[10px] bg-gray-100 rounded">${data.public_key || ''}</textarea>`;
            msg.className = 'mt-2 text-xs text-red-500 block';
        }
    } catch (e) {
        msg.textContent = '✕ Check failed';
        msg.className = 'mt-2 text-xs text-red-500 block';
    }
}
</script>
@endsection
