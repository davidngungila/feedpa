@extends('layouts.app')

@section('title', 'Manage Groups')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-users text-primary-500"></i> Manage Groups
            </h2>
            <p class="text-xs text-primary-500 mt-1">Organize contacts into broadcast groups</p>
        </div>
        <a href="{{ route('whatsapp.groups.create') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
            <i class="fas fa-plus mr-1"></i> Create Group
        </a>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($groups as $group)
            <div class="card p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center">
                        <i class="fas fa-users text-xl text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('whatsapp.groups.edit', $group->id) }}" class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Edit">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                        <form action="{{ route('whatsapp.groups.destroy', $group->id) }}" method="POST" data-ajax-delete onsubmit="return confirm('Are you sure you want to delete this group?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Delete">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <h3 class="text-sm font-bold text-primary-900 dark:text-white">{{ $group->name }}</h3>
                <p class="text-[11px] text-primary-500 mt-1 line-clamp-2">{{ $group->description ?? 'No description' }}</p>
                <div class="mt-4 pt-4 border-t border-primary-100 dark:border-primary-800 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-primary-500">
                        <i class="fas fa-user mr-1"></i> {{ $group->contacts_count }} members
                    </span>
                    @if($group->group_id)
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                            <i class="fab fa-whatsapp mr-1"></i> Linked
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="card p-16 text-center col-span-full">
                <i class="fas fa-users text-4xl text-primary-300 mb-3 block"></i>
                <p class="text-sm font-bold text-primary-500">No groups found</p>
                <p class="text-xs text-primary-400 mt-1">Create your first group to organize contacts.</p>
            </div>
        @endforelse
    </div>

    @if($groups->hasPages())
        <div class="p-6">
            {{ $groups->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-ajax-delete]').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this group?')) return;

                fetch(form.action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        form.closest('.card').remove();
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete group.');
                    }
                })
                .catch(error => alert('Error: ' + error.message));
            });
        });
    });
</script>
@endpush
@endsection
