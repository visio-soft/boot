@extends('layouts.app')

@section('content')
<header>
    <h1>Projects</h1>
    <div>
        <span class="badge badge-other">PHP 8.4</span>
    </div>
</header>

<div class="input-group">
    <div style="margin-bottom: 0.5rem; font-weight: 600;">Create New Project</div>
    
    <form action="{{ route('sites.store') }}" method="POST" id="createForm">
        @csrf
        <div style="display: flex; gap: 1rem; flex-direction: column;">
            
            <!-- Repo URL Input -->
            <div>
                <label for="repo">GitHub Repository (SSH Recommended)</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" name="repo" id="repo" placeholder="git@github.com:username/repo.git" style="flex:1;">
                    <button type="button" class="btn btn-secondary" onclick="checkGit()">Check Access</button>
                </div>
                <div id="gitMessage" style="margin-top: 5px; font-size: 0.85rem; display: none;"></div>
            </div>

            <!-- Project Name -->
            <div>
                <label for="name">Project Name (Folder Name)</label>
                <input type="text" name="name" id="name" placeholder="my-app" required>
            </div>

            <!-- Options -->
            <div class="checkbox-wrapper">
                <input type="checkbox" name="horizon" id="horizon" value="1">
                <label for="horizon" style="margin:0; cursor: pointer;">Install & Configure Laravel Horizon</label>
            </div>

            <button type="submit" class="btn">Create Project</button>
        </div>
    </form>
</div>

<div class="grid">
    @foreach($sites as $site)
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="site-name">{{ $site['name'] }}</div>
                    <a href="{{ $site['url'] }}" target="_blank" class="site-url">{{ $site['url'] }} ↗</a>
                </div>
                <div>
                   <span class="badge {{ $site['type'] === 'Laravel' ? 'badge-laravel' : 'badge-other' }}">
                       {{ $site['type'] }} {{ $site['version'] }}
                   </span>
                </div>
            </div>
            
            <div class="site-meta">
                <span>Path: {{ $site['path'] }}</span>
            </div>

            @if($site['type'] === 'Laravel')
            <div style="margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 1rem; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 500;">Horizon Status</span>
                @if($site['horizon'] === 'running')
                    <span class="badge" style="background: rgba(52, 199, 89, 0.1); color: #34c759;">
                         <span class="status-dot"></span> Active
                    </span>
                @else
                     <span class="badge" style="background: rgba(142, 142, 147, 0.1); color: #8e8e93;">
                         Inactive
                    </span>
                @endif
            </div>
            @endif
        </div>
    @endforeach
</div>

<script>
async function checkGit() {
    const repo = document.getElementById('repo').value;
    const msg = document.getElementById('gitMessage');
    
    if (!repo) {
        msg.textContent = 'Please enter a repository URL.';
        msg.style.color = '#ff3b30';
        msg.style.display = 'block';
        return;
    }

    msg.textContent = 'Checking access...';
    msg.style.color = '#86868b';
    msg.style.display = 'block';

    try {
        const response = await fetch(`{{ route('sites.check-git') }}?repo=${encodeURIComponent(repo)}`);
        const data = await response.json();
        
        if (data.status === 'ok') {
            msg.textContent = '✅ Access Granted';
            msg.style.color = '#34c759';
            
            // Auto-fill name if empty
            const nameInput = document.getElementById('name');
            if (!nameInput.value) {
                // git@github.com:visio-soft/qpass.git -> qpass
                const parts = repo.split('/');
                const last = parts[parts.length - 1];
                const clean = last.replace('.git', '');
                nameInput.value = clean;
            }
        } else {
            msg.innerHTML = `❌ ${data.message}<br>Suggested Action: ${data.key_guide}<br><strong>Public Key:</strong><br><textarea readonly style="width:100%; height:80px; font-size:10px; margin-top:5px;">${data.public_key}</textarea>`;
            msg.style.color = '#ff3b30';
        }
    } catch (e) {
        msg.textContent = '❌ Error checking access.';
        msg.style.color = '#ff3b30';
    }
}
</script>
@endsection
