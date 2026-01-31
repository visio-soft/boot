@extends('layouts.app')

@section('content')
<header>
    <h1>Logs: {{ $title }}</h1>
    <div style="display: flex; gap: 1rem;">
        <input type="text" id="searchInput" placeholder="Search logs..." 
               style="padding: 0.5rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 0.9rem; width: 300px;">
        <a href="{{ route('services.index') }}" class="btn btn-secondary">Back</a>
    </div>
</header>

<div class="card" style="display: flex; flex-direction: column; height: 75vh; padding: 0; overflow: hidden;">
    <div style="padding: 0.8rem 1rem; border-bottom: 1px solid var(--border-color); background: #f9f9f9; font-size: 0.8rem; color: #86868b; display: flex; justify-content: space-between;">
        <span>Source: {{ $logPath }}</span>
        <span id="statusIndicator">Live</span>
    </div>
    
    <div id="logContainer" style="flex: 1; overflow-y: auto; padding: 1rem; background: #1e1e1e; color: #d4d4d4; font-family: 'Menlo', 'Monaco', 'Courier New', monospace; font-size: 0.85rem; line-height: 1.5;">
        <!-- Logs injected here -->
    </div>
</div>

<script>
    const logContainer = document.getElementById('logContainer');
    const searchInput = document.getElementById('searchInput');
    let allLines = [];
    let isAutoScroll = true;

    // Detect if user scrolled up
    logContainer.addEventListener('scroll', () => {
        const threshold = 50;
        const position = logContainer.scrollTop + logContainer.offsetHeight;
        const height = logContainer.scrollHeight;
        isAutoScroll = position > height - threshold;
    });

    async function fetchLogs() {
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('json', '1');
            
            const res = await fetch(url);
            const data = await res.json();
            
            if (data.content) {
                // Split lines
                const lines = data.content.split('\n');
                // Only update if changed (naive check, or just replace)
                // For simplicity and search sync, we'll store lines and render
                if (JSON.stringify(lines) !== JSON.stringify(allLines)) {
                    allLines = lines;
                    renderLogs();
                }
            }
        } catch (e) {
            console.error("Fetch error", e);
        }
    }

    function renderLogs() {
        const filter = searchInput.value.toLowerCase();
        logContainer.innerHTML = ''; // efficient enough for < 1000 lines
        
        allLines.forEach(line => {
            if (!line) return;
            if (filter && !line.toLowerCase().includes(filter)) return;

            const div = document.createElement('div');
            div.style.marginBottom = '2px';
            div.style.whiteSpace = 'pre-wrap';
            div.style.wordBreak = 'break-all';

            // Highlighting
            let html = escapeHtml(line);
            
            // Highlight Levels
            html = html.replace(/(\.ERROR)/g, '<span style="color: #ff5f56; font-weight:bold;">$1</span>');
            html = html.replace(/(\.WARNING)/g, '<span style="color: #ffbd2e; font-weight:bold;">$1</span>');
            html = html.replace(/(\.INFO)/g, '<span style="color: #27c93f; font-weight:bold;">$1</span>');

            // Linkify File Paths
            // Pattern: /var/www/projects/foo/bar.php:123
            // Needs to be careful not to break HTML attributes if we add more
            const fileRegex = /(\/var\/www\/projects\/[a-zA-Z0-9_\-\/]+\.php):(\d+)/g;
            html = html.replace(fileRegex, '<a href="vscode://file$1:$2" style="color: #4daafc; text-decoration: underline; cursor: pointer;">$1:$2</a>');

            div.innerHTML = html;
            logContainer.appendChild(div);
        });

        if (isAutoScroll) {
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Search Listener
    searchInput.addEventListener('input', () => {
        renderLogs();
    });

    // Initial Load & Polling
    fetchLogs();
    setInterval(fetchLogs, 2000); // Poll every 2s

</script>
@endsection
