@extends('layouts.app')

@section('content')
<header class="mb-12">
    <h1 class="text-4xl font-semibold tracking-tight text-[#1d1d1f]">Databases</h1>
    <p class="text-apple-grey mt-2">Manage your PostgreSQL databases.</p>
</header>

<!-- Create Database -->
<div class="card mb-12">
    <h2 class="text-lg font-semibold mb-6">New Database</h2>
    <form action="{{ route('databases.store') }}" method="POST" class="flex gap-4">
        @csrf
        <input type="text" name="name" placeholder="database_name" class="input-field flex-1" required>
        <button type="submit" class="btn">Create</button>
    </form>
</div>

<!-- Database List -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($databases as $db)
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">{{ $db->datname }}</h3>
                <p class="text-sm text-apple-grey">{{ number_format($db->size / 1024 / 1024, 2) }} MB</p>
            </div>
            <a href="tableplus:///?driver=PostgreSQL&host=127.0.0.1&port=5432&database={{ $db->datname }}&user={{ $db->datname }}&password=secret" 
               class="btn-secondary text-xs px-4 py-2">
                Open in TablePlus
            </a>
        </div>
    </div>
    @endforeach
</div>
@endsection
