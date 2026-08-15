@extends('layouts.app')

@section('title', 'All Contacts')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-address-book text-primary-500"></i> All Contacts
            </h2>
            <p class="text-xs text-primary-500 mt-1">Contacts synced with the WhatsApp session</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ request()->fullUrlWithQuery([]) }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
        </div>
    </div>

    @if($error)
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}
            </p>
        </div>
    @endif

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Search & Filters -->
    <div class="card p-4">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-primary-300 text-xs"></i>
                <input type="text" id="contactSearch" placeholder="Search by name, phone or display name..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select id="contactTypeFilter" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="all">All types</option>
                <option value="business">Business (verified)</option>
                <option value="personal">Personal</option>
            </select>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Phone / JID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Display Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($contacts as $contact)
                        @php
                            $contactId = $contact['id'] ?? $contact['jid'] ?? '';
                            $isBusiness = !empty($contact['verifiedName']);
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer contact-row"
                            data-contact="{{ $contactId }}"
                            data-type="{{ $isBusiness ? 'business' : 'personal' }}"
                            data-search="{{ strtolower(trim(($contact['name'] ?? '') . ' ' . ($contact['notify'] ?? '') . ' ' . ($contact['verifiedName'] ?? '') . ' ' . $contactId)) }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 flex items-center justify-center overflow-hidden">
                                        @if(!empty($contact['imgUrl']))
                                            <img src="{{ $contact['imgUrl'] }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="fab fa-whatsapp text-green-600 dark:text-green-400"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-primary-900 dark:text-white truncate">{{ $contact['name'] ?? 'Unknown' }}</p>
                                        @if($isBusiness)
                                            <p class="text-[10px] text-primary-400">
                                                <i class="fas fa-check-circle mr-0.5 text-blue-500"></i> {{ $contact['verifiedName'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $contactId }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $contact['notify'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 italic max-w-xs line-clamp-1">{{ $contact['status'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end">
                                    <button type="button" class="view-contact-btn px-3 py-1.5 rounded-lg bg-primary-100 dark:bg-primary-900/20 text-primary-600 dark:text-primary-300 hover:bg-primary-600 hover:text-white transition-all text-[10px] font-bold" data-contact="{{ $contactId }}">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <i class="fas fa-address-book text-4xl text-primary-300 mb-3 block"></i>
                                @if($error)
                                    <p class="text-sm font-bold text-primary-500">Could not load contacts</p>
                                    <p class="text-xs text-primary-400 mt-1">Check the error message above.</p>
                                @else
                                    <p class="text-sm font-bold text-primary-500">No contacts synced</p>
                                    <p class="text-xs text-primary-400 mt-1">Contacts synced with the WhatsApp session will appear here.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(!$error)
            <div class="px-6 py-4 border-t border-primary-100 dark:border-dark-border flex items-center justify-between">
                <p class="text-[10px] text-primary-400 font-bold uppercase tracking-wider"><span id="contactCount">{{ count($contacts) }}</span> contact{{ count($contacts) === 1 ? '' : 's' }}</p>
            </div>
        @endif
    </div>
</div>

<!-- Contact Details Modal -->
<div id="contactModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="card w-full max-w-md p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
            <h3 class="text-sm font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-user-circle"></i> Contact Details
            </h3>
            <button type="button" id="closeContactModal" class="p-2 rounded-lg bg-gray-100 dark:bg-primary-900/20 text-primary-500 hover:text-red-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="contactModalContent" class="space-y-4">
            <div class="flex items-center justify-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 flex items-center justify-center overflow-hidden">
                    <i class="fab fa-whatsapp text-4xl text-green-600 dark:text-green-400"></i>
                </div>
            </div>
            <div class="text-center">
                <p id="contactModalName" class="text-lg font-black text-primary-900 dark:text-white">—</p>
                <p id="contactModalVerified" class="text-[11px] text-blue-500 hidden mt-0.5"></p>
            </div>
            <div class="space-y-3 text-xs border-t border-primary-100 dark:border-primary-800 pt-4">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Phone / JID</p>
                    <p id="contactModalJid" class="text-primary-700 dark:text-primary-300 font-mono break-all">—</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">LID</p>
                    <p id="contactModalLid" class="text-primary-700 dark:text-primary-300 font-mono break-all">—</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Display Name</p>
                    <p id="contactModalNotify" class="text-primary-700 dark:text-primary-300">—</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Type</p>
                    <p id="contactModalType" class="text-primary-700 dark:text-primary-300">—</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Status</p>
                    <p id="contactModalStatus" class="text-primary-700 dark:text-primary-300 italic">—</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('contactSearch');
        const typeFilter = document.getElementById('contactTypeFilter');
        const rows = Array.from(document.querySelectorAll('.contact-row'));
        const countEl = document.getElementById('contactCount');

        function applyFilters() {
            const query = (searchInput.value || '').toLowerCase().trim();
            const type = typeFilter.value;
            let visible = 0;

            rows.forEach(function (row) {
                const matchesQuery = !query || row.dataset.search.includes(query);
                const matchesType = type === 'all' || row.dataset.type === type;
                const show = matchesQuery && matchesType;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (countEl) countEl.textContent = visible;
        }

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (typeFilter) typeFilter.addEventListener('change', applyFilters);

        const modal = document.getElementById('contactModal');
        const modalContent = document.getElementById('contactModalContent');
        const closeBtn = document.getElementById('closeContactModal');
        const baseUrl = '{{ url('whatsapp/contacts') }}/';

        function openContact(contactId) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modalContent.innerHTML = '<div class="text-center py-10"><i class="fas fa-spinner fa-spin text-2xl text-primary-400"></i><p class="text-xs text-primary-500 mt-3">Loading contact...</p></div>';

            fetch(baseUrl + encodeURIComponent(contactId), {
                headers: { 'Accept': 'application/json' },
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) throw new Error(data.message || 'Failed to load contact details.');

                const c = data.data;
                const avatar = c.picture || c.imgUrl;
                const isBusiness = !!(c.verifiedName);

                modalContent.innerHTML =
                    '<div class="flex items-center justify-center">' +
                        (avatar
                            ? '<img src="' + avatar + '" alt="" class="w-24 h-24 rounded-full object-cover">'
                            : '<div class="w-24 h-24 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 flex items-center justify-center"><i class="fab fa-whatsapp text-4xl text-green-600 dark:text-green-400"></i></div>') +
                    '</div>' +
                    '<div class="text-center">' +
                        '<p class="text-lg font-black text-primary-900 dark:text-white break-all">' + (c.name || 'Unknown') + '</p>' +
                        (isBusiness ? '<p class="text-[11px] text-blue-500 mt-0.5"><i class="fas fa-check-circle mr-0.5"></i>' + (c.verifiedName || 'Verified Business') + '</p>' : '') +
                    '</div>' +
                    '<div class="space-y-3 text-xs border-t border-primary-100 dark:border-primary-800 pt-4">' +
                        '<div><p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Phone / JID</p><p class="text-primary-700 dark:text-primary-300 font-mono break-all">' + (c.id || '—') + '</p></div>' +
                        '<div><p class="text-[10px] text-gray-400 uppercase font-bold mb-1">LID</p><p class="text-primary-700 dark:text-primary-300 font-mono break-all">' + (c.lid || '—') + '</p></div>' +
                        '<div><p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Display Name</p><p class="text-primary-700 dark:text-primary-300">' + (c.notify || '—') + '</p></div>' +
                        '<div><p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Type</p><p class="text-primary-700 dark:text-primary-300">' + (isBusiness ? 'Business (verified)' : 'Personal') + '</p></div>' +
                        '<div><p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Status</p><p class="text-primary-700 dark:text-primary-300 italic">' + (c.status || '—') + '</p></div>' +
                    '</div>';
            })
            .catch(function (err) {
                modalContent.innerHTML = '<div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs font-bold text-red-700 dark:text-red-300"><i class="fas fa-exclamation-circle mr-1"></i>' + (err.message || 'Failed to load contact.') + '</div>';
            });
        }

        document.querySelectorAll('.contact-row').forEach(function (row) {
            row.addEventListener('click', function (e) {
                if (e.target.closest('.view-contact-btn')) return;
                openContact(row.dataset.contact);
            });
        });

        document.querySelectorAll('.view-contact-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                openContact(btn.dataset.contact);
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });
</script>
@endpush
