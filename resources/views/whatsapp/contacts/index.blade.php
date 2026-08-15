@extends('layouts.app')

@section('title', 'Manage Contacts')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-address-book text-primary-500"></i> Manage Contacts
            </h2>
            <p class="text-xs text-primary-500 mt-1">Store and manage WhatsApp contacts</p>
        </div>
        <div class="flex gap-2">
            <button type="button" id="openImportBtn" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-file-import mr-1"></i> Import
            </button>
            <a href="{{ route('whatsapp.contacts.export') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-download mr-1"></i> Export
            </a>
            <a href="{{ route('whatsapp.contacts.create') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-plus mr-1"></i> Add Contact
            </a>
        </div>
    </div>

    <!-- Import CSV Form (hidden by default) -->
    <div id="importForm" class="hidden card p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
            <i class="fas fa-file-import"></i> Import Contacts (CSV)
        </h3>
        <form method="POST" action="{{ route('whatsapp.contacts.import') }}" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-3 items-end">
            @csrf
            <div class="flex-1 w-full">
                <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">CSV File *</label>
                <input type="file" name="file" accept=".csv,.txt" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <p class="text-[10px] text-primary-400 mt-1">CSV header must include: name, phone, email, company, notes</p>
            </div>
            <button type="submit" class="px-6 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all whitespace-nowrap">
                <i class="fas fa-upload mr-1"></i> Import
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Contacts Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Groups</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 flex items-center justify-center">
                                        <i class="fab fa-whatsapp text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $contact->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $contact->phone }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $contact->email ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $contact->company ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($contact->groups as $group)
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">{{ $group->name }}</span>
                                    @empty
                                        <span class="text-[10px] text-primary-400">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('whatsapp.contacts.edit', $contact->id) }}" class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('whatsapp.contacts.destroy', $contact->id) }}" method="POST" data-ajax-delete onsubmit="return confirm('Are you sure you want to delete this contact?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <i class="fas fa-address-book text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No contacts found</p>
                                <p class="text-xs text-primary-400 mt-1">Add your first contact to start sending messages.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contacts->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $contacts->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const importForm = document.getElementById('importForm');
        const openBtn = document.getElementById('openImportBtn');

        if (openBtn) {
            openBtn.addEventListener('click', function () {
                importForm.classList.toggle('hidden');
                if (!importForm.classList.contains('hidden')) {
                    openBtn.innerHTML = '<i class="fas fa-times mr-1"></i> Close';
                } else {
                    openBtn.innerHTML = '<i class="fas fa-file-import mr-1"></i> Import';
                }
            });
        }

        document.querySelectorAll('form[data-ajax-delete]').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this contact?')) return;

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
                        form.closest('tr').remove();
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete contact.');
                    }
                })
                .catch(error => alert('Error: ' + error.message));
            });
        });
    });
</script>
@endpush
