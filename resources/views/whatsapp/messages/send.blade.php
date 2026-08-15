@extends('layouts.app')

@section('title', 'Send WhatsApp Messages')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fab fa-whatsapp text-green-500"></i> Send Messages
            </h2>
            <p class="text-xs text-primary-500 mt-1">Send text, media and document messages via WhatsApp</p>
        </div>
        <a href="{{ route('whatsapp.sessions.index') }}" class="px-4 py-2 rounded-xl bg-green-600 hover:bg-green-500 text-white text-xs font-bold transition-all">
            <i class="fas fa-plug mr-1"></i> Check Sessions
        </a>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    @if(session('error'))
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Compose Message Card -->
        <div class="card p-6 space-y-6 lg:col-span-2">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-pen-square"></i> Compose Message
            </h3>

            <form id="sendMessageForm" method="POST" action="{{ route('whatsapp.messages.send.post') }}">
                @csrf
                <div class="space-y-4">
                    <!-- Message Type -->
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Message Type</label>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                            @foreach(['text' => 'Text', 'image' => 'Image', 'document' => 'Document', 'video' => 'Video', 'audio' => 'Audio'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="message_type" value="{{ $value }}" class="hidden peer message-type" {{ $loop->first ? 'checked' : '' }}>
                                <div class="px-3 py-2 rounded-xl border border-primary-100 dark:border-primary-800 text-center text-xs font-bold text-primary-700 dark:text-primary-300 peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-500 transition-all">
                                    <i class="fab fa-{{ $value === 'text' ? 'whatsapp' : ($value === 'image' ? 'image' : ($value === 'document' ? 'file-pdf' : ($value === 'video' ? 'video' : 'music'))) }} block text-base mb-1"></i>
                                    {{ $label }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Recipient -->
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Recipient</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-3">
                            @foreach(['phone' => 'Phone Number', 'contact' => 'Saved Contact', 'group' => 'Group'] as $value => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="recipient_type" value="{{ $value }}" class="hidden peer recipient-type" {{ $loop->first ? 'checked' : '' }}>
                                <div class="px-3 py-2 rounded-xl border border-primary-100 dark:border-primary-800 text-center text-xs font-bold text-primary-700 dark:text-primary-300 peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600 transition-all">
                                    {{ $label }}
                                </div>
                            </label>
                            @endforeach
                        </div>

                        <!-- Phone -->
                        <div id="recipientPhone" class="mb-2">
                            <input type="text" name="phone" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="255655123456">
                        </div>

                        <!-- Contact Select -->
                        <div id="recipientContact" class="hidden mb-2">
                            <select name="contact_id" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Select Contact --</option>
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Group Select -->
                        <div id="recipientGroup" class="hidden">
                            <select name="group_id" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Select Group --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Text Message -->
                    <div id="textField">
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Message Text</label>
                        <textarea name="text" rows="6" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Type your message here..."></textarea>
                    </div>

                    <!-- Media Fields -->
                    <div id="mediaFields" class="hidden space-y-4">
                        <div>
                            <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Media URL</label>
                            <input type="url" name="media_url" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://example.com/file.jpg">
                        </div>
                        <div id="fileNameField" class="hidden">
                            <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">File Name</label>
                            <input type="text" name="file_name" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="document.pdf">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Caption</label>
                            <input type="text" name="caption" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Optional caption">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-green-900/20">
                        <i class="fab fa-whatsapp me-2"></i> Send Message
                    </button>
                </div>
            </form>
        </div>

        <!-- Templates & Info -->
        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
                    <i class="fas fa-layer-group"></i> Message Templates
                </h3>
                @if($templates->isEmpty())
                    <p class="text-xs text-primary-500">No templates available.</p>
                @else
                    <div class="space-y-3">
                        @foreach($templates as $template)
                            <button type="button" class="template-btn w-full text-left p-3 rounded-xl border border-primary-100 dark:border-primary-800 hover:border-green-400 transition-all" data-content="{{ $template->content }}">
                                <p class="text-xs font-bold text-primary-900 dark:text-white">{{ $template->name }}</p>
                                <p class="text-[10px] text-primary-500 mt-1 line-clamp-2">{{ Str::limit($template->content, 80) }}</p>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card p-6 bg-blue-50/60 dark:bg-blue-900/10 border-blue-100 dark:border-blue-800">
                <h3 class="text-xs font-black uppercase tracking-widest text-blue-600 flex items-center gap-2 mb-2">
                    <i class="fas fa-info-circle"></i> Tip
                </h3>
                <p class="text-[11px] text-blue-700 dark:text-blue-300 leading-relaxed">
                    Phone numbers must include the country code without the "+" sign (e.g. 255655123456). For media messages, provide a publicly accessible URL or upload files under Media & Files first.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Send Results Modal -->
<div id="sendResultsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60" onclick="closeSendResultsModal()"></div>
    <div class="relative card w-full max-w-2xl max-h-[85vh] overflow-y-auto p-6 animate-fade-in">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-paper-plane"></i> Send Results
            </h3>
            <button type="button" onclick="closeSendResultsModal()" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-dark-border hover:bg-red-100 hover:text-red-600 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="sendResultsSummary" class="mb-4"></div>
        <div id="sendResultsBody" class="space-y-3"></div>
        <div class="mt-6 flex justify-end">
            <button type="button" onclick="closeSendResultsModal()" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messageTypes = document.querySelectorAll('.message-type');
        const textField = document.getElementById('textField');
        const mediaFields = document.getElementById('mediaFields');
        const fileNameField = document.getElementById('fileNameField');

        function updateMessageFields() {
            const selected = document.querySelector('.message-type:checked');
            const type = selected ? selected.value : 'text';

            textField.classList.toggle('hidden', type !== 'text');
            mediaFields.classList.toggle('hidden', type === 'text');
            fileNameField.classList.toggle('hidden', type !== 'document');
        }

        messageTypes.forEach(input => input.addEventListener('change', updateMessageFields));
        updateMessageFields();

        const recipientTypes = document.querySelectorAll('.recipient-type');
        const phoneDiv = document.getElementById('recipientPhone');
        const contactDiv = document.getElementById('recipientContact');
        const groupDiv = document.getElementById('recipientGroup');

        recipientTypes.forEach(input => input.addEventListener('change', function () {
            const type = this.value;
            phoneDiv.classList.toggle('hidden', type !== 'phone');
            contactDiv.classList.toggle('hidden', type !== 'contact');
            groupDiv.classList.toggle('hidden', type !== 'group');
        }));

        document.querySelectorAll('.template-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const typeInput = document.querySelector('.message-type[value="text"]');
                if (typeInput) typeInput.checked = true;
                updateMessageFields();

                const textarea = document.querySelector('textarea[name="text"]');
                if (textarea) textarea.value = this.dataset.content;
            });
        });

        const form = document.getElementById('sendMessageForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;

        function openSendResultsModal(results) {
            const body = document.getElementById('sendResultsBody');
            const summary = document.getElementById('sendResultsSummary');
            const items = Array.isArray(results) ? results : [];

            const okCount = items.filter(r => r.success === true).length;
            const failCount = items.length - okCount;

            summary.innerHTML = okCount > 0
                ? '<div class="p-3 rounded-xl bg-green-50/60 dark:bg-green-900/10 border border-green-200 dark:border-green-800 text-xs font-bold text-green-700 dark:text-green-300">' +
                  '<i class="fas fa-check-circle mr-1"></i> ' + okCount + ' sent successfully' +
                  (failCount > 0 ? ', ' + failCount + ' failed' : '') + '</div>'
                : '<div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs font-bold text-red-700 dark:text-red-300">' +
                  '<i class="fas fa-exclamation-circle mr-1"></i> ' + failCount + ' failed</div>';

            body.innerHTML = items.length
                ? items.map(r => {
                    const ok = r.success === true;
                    const who = r.phone
                        ? '<i class="fas fa-phone mr-1"></i>' + r.phone
                        : (r.contact ? '<i class="fas fa-user mr-1"></i>' + r.contact
                            : (r.group ? '<i class="fas fa-users mr-1"></i>' + r.group : '<i class="fas fa-user mr-1"></i>Unknown'));
                    const sub = r.data && (r.data.msgId || r.data.jid || r.data.status)
                        ? '<span class="font-mono">msgId: ' + (r.data.msgId ?? '-') + ' | status: ' + (r.data.status ?? '-') + '</span>'
                        : '';
                    return '<div class="flex items-start justify-between gap-3 p-3 rounded-xl border ' +
                        (ok ? 'border-green-200 dark:border-green-800 bg-green-50/60 dark:bg-green-900/10' : 'border-red-200 dark:border-red-800 bg-red-50/60 dark:bg-red-900/10') + '">' +
                        '<div class="min-w-0">' +
                            '<p class="text-xs font-bold text-primary-900 dark:text-white truncate">' + who + '</p>' +
                            (sub ? '<p class="text-[10px] text-primary-500 mt-0.5">' + sub + '</p>' : '') +
                            '<p class="text-[10px] ' + (ok ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400') + ' font-bold mt-1">' + (r.message || (ok ? 'Sent successfully' : 'Failed')) + '</p>' +
                        '</div>' +
                        '<span class="badge shrink-0 ' + (ok ? 'badge-green' : 'badge-red') + '">' + (ok ? 'Success' : 'Failed') + '</span>' +
                    '</div>';
                }).join('')
                : '<p class="text-xs text-primary-500 text-center py-4">No results returned.</p>';

            document.getElementById('sendResultsModal').classList.remove('hidden');
        }

        function closeSendResultsModal() {
            document.getElementById('sendResultsModal').classList.add('hidden');
        }

        window.openSendResultsModal = openSendResultsModal;
        window.closeSendResultsModal = closeSendResultsModal;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: new FormData(form),
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (result.data.success) {
                    openSendResultsModal(result.data.results);
                } else {
                    openSendResultsModal([{ success: false, message: result.data.message || 'Request failed. Please try again.' }]);
                }
            })
            .catch(function () {
                openSendResultsModal([{ success: false, message: 'Network error. Please check your connection and try again.' }]);
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            });
        });
    });
</script>
@endpush
