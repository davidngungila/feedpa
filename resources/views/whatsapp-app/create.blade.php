@extends('layouts.app')
@section('title', 'New WhatsApp Message')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('whatsapp-app.dashboard') }}" class="w-8 h-8 rounded-lg bg-primary-50 border border-primary-100 flex items-center justify-center hover:bg-primary-100"><i class="fas fa-arrow-left text-primary-600 text-xs"></i></a>
        <div>
            <h1 class="text-lg font-bold text-primary-900 dark:text-white">Send WhatsApp Message</h1>
            <p class="text-xs text-primary-500">Gateway Device • Recipient • Message • Optional Attachment (PDF/DOC/JPG etc)</p>
        </div>
    </div>

    @if($errors->any())
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <ul class="text-xs text-red-700 list-disc ml-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('whatsapp-app.store') }}" enctype="multipart/form-data" class="card p-6 space-y-5">
        @csrf
        <div>
            <label class="block text-[11px] font-bold tracking-widest text-primary-500 mb-1">GATEWAY DEVICE *</label>
            <select name="device_id" required class="w-full px-3 py-2.5 rounded-xl border border-primary-200 bg-white text-sm focus:ring-2 focus:ring-green-500 outline-none">
                <option value="">— Select device —</option>
                @foreach($allDevices as $d)
                    @php $online = $d->isOnline(); @endphp
                    <option value="{{ $d->id }}" {{ old('device_id')==$d->id ? 'selected' : '' }}>
                        {{ $d->device_code }} — {{ $d->name }} ({{ $d->location?->name ?? 'No loc' }}) {{ $online ? '🟢 ONLINE' : '🔴 OFFLINE' }} • {{ $d->battery_level ?? '—' }}% • Pending: {{ $d->whatsappMessages()->whereIn('status',['PENDING','QUEUED'])->count() }}
                    </option>
                @endforeach
            </select>
            <p class="text-[10px] text-primary-400 mt-1">Only ONLINE devices deliver instantly; OFFLINE will be QUEUED and delivered when phone syncs via WorkManager.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-[11px] font-bold tracking-widest text-primary-500 mb-1">RECIPIENT *</label>
                <input type="text" name="recipient_phone" value="{{ old('recipient_phone') }}" placeholder="+255712345678" required class="w-full px-3 py-2.5 rounded-xl border border-primary-200 text-sm font-mono focus:ring-2 focus:ring-green-500 outline-none">
                <p class="text-[10px] text-primary-400 mt-1">WhatsApp number with country code. Will be normalized to +255...</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold tracking-widest text-primary-500 mb-1">TEMPLATE (optional)</label>
                <select name="template_id" class="w-full px-3 py-2.5 rounded-xl border border-primary-200 bg-white text-sm">
                    <option value="">— No template —</option>
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}" data-content="{{ $t->content }}">{{ $t->name }} ({{ $t->code }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold tracking-widest text-primary-500 mb-1">MESSAGE</label>
            <textarea name="message" id="message" rows="5" placeholder="Dear John, Please find your invoice attached." class="w-full px-3 py-2.5 rounded-xl border border-primary-200 text-sm focus:ring-2 focus:ring-green-500 outline-none">{{ old('message') }}</textarea>
            <p class="text-[10px] text-primary-400 mt-1">You can send message without attachment, or attachment-only where appropriate. WhatsApp will show this as caption for documents/images.</p>
        </div>

        <div>
            <label class="block text-[11px] font-bold tracking-widest text-primary-500 mb-1">ATTACHMENT (optional)</label>
            <input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt,.csv,.zip" class="w-full px-3 py-2.5 rounded-xl border border-primary-200 bg-white text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-green-50 file:text-green-700 file:font-bold">
            <p class="text-[10px] text-primary-400 mt-1">Supported: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG and other WhatsApp-supported files. Max 25 MB. Stored private, downloaded via authenticated FileProvider.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 py-3 rounded-xl bg-green-600 hover:bg-green-500 text-white text-sm font-bold shadow-lg flex items-center justify-center gap-2"><i class="fas fa-paper-plane"></i> SEND TO PHONE</button>
            <a href="{{ route('whatsapp-app.outbox') }}" class="px-6 py-3 rounded-xl border border-primary-200 text-sm font-bold hover:bg-primary-50">Cancel</a>
        </div>

        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
            <p class="font-bold">Phone-Assisted Flow (no API bypass)</p>
            <p class="mt-1">Phone will show <b>OPEN WHATSAPP</b> → launches WhatsApp via <code class="bg-white px-1 rounded">ACTION_SEND</code> with <code class="bg-white px-1 rounded">EXTRA_TEXT</code> + <code class="bg-white px-1 rounded">EXTRA_STREAM</code> + <code class="bg-white px-1 rounded">FLAG_GRANT_READ_URI_PERMISSION</code> → User taps <b>Send</b> → Phone marks <code class="bg-white px-1 rounded">SENT</code> (or keep pending).</p>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelector('select[name=template_id]')?.addEventListener('change', function(){
  const opt=this.options[this.selectedIndex];
  const content=opt?.dataset?.content;
  if(content) document.getElementById('message').value=content;
});
</script>
@endpush
@endsection
