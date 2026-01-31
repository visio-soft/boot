@extends('layouts.app')

@section('content')
<header>
    <h1>Databases</h1>
    <div>
        <span style="font-size: 0.9rem; color: var(--text-muted);">PostgreSQL</span>
    </div>
</header>

<form action="{{ route('databases.store') }}" method="POST" class="input-group">
    @csrf
    <input type="text" name="name" placeholder="New Database Name" required>
    <button type="submit" class="btn btn-primary">Create Database</button>
</form>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Database Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($databases as $db)
                <tr>
                    <td>
                        <span style="font-weight: 500; font-size: 1rem;">{{ $db->datname }}</span>
                    </td>
                    <td>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #34d399; padding: 0.2rem 0.5rem;">Active</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                             <!-- pgsql://user:pass@host:port/dbname -->
                             <!-- We assume user/pass is always same as configured: dbname/secret or manager/secret? -->
                             <!-- For 'manager' db it's manager/secret. For others created via Manager, it's dbname/secret. -->
                             <!-- Construct URL: pgsql://{{ $db->datname }}:secret@127.0.0.1:5432/{{ $db->datname }}?name={{ $db->datname }} -->
                             <a href="postgres://{{ $db->datname }}:secret@127.0.0.1:5432/{{ $db->datname }}?name={{ $db->datname }}&statusColor=0071e3" class="btn btn-secondary" style="text-decoration: none; padding: 0.4rem 0.8rem; font-size: 0.75rem;">
                                Open in TablePlus
                             </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
