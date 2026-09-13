@extends('layouts.app')

@section('title', 'All Contacts')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in" x-data="contactDrawer()" @keydown.escape.window="closeDrawer()">
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
                <input type="text" id="contactSearch" x-model="search" placeholder="Search by name, phone or display name..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select id="contactTypeFilter" x-model="type" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="all">All types</option>
                <option value="business">Business (verified)</option>
                <option value="personal">Personal</option>
            </select>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Display Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($contacts as $contact)
                        <tr @click="openDrawer(@js($contact))" x-show="matchesFilter(contact)" class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 flex items-center justify-center overflow-hidden shrink-0">
                                        @if(!empty($contact['imgUrl']))
                                            <img src="{{ $contact['imgUrl'] }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="fab fa-whatsapp text-green-600 dark:text-green-400"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-primary-900 dark:text-white truncate">{{ $contact['name'] ?? ($contact['notify'] ?? 'Unknown') }}</p>
                                        @if(!empty($contact['verifiedName']))
                                            <p class="text-[10px] text-primary-400">
                                                <i class="fas fa-check-circle mr-0.5 text-blue-500"></i> {{ $contact['verifiedName'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $contact['notify'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 italic max-w-xs line-clamp-1">{{ $contact['status'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click.stop="openMessageDrawer(@js($contact))" class="px-3 py-1.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 hover:bg-green-600 hover:text-white transition-all text-[10px] font-bold">
                                        <i class="fab fa-whatsapp mr-1"></i> Message
                                    </button>
                                    <button type="button" @click.stop="openDrawer(@js($contact))" class="px-3 py-1.5 rounded-lg bg-primary-100 dark:bg-primary-900/20 text-primary-600 dark:text-primary-300 hover:bg-primary-600 hover:text-white transition-all text-[10px] font-bold">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
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
                    @if(count($contacts) > 0)
                        <tr x-show="filtered().length === 0" x-cloak>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <i class="fas fa-filter text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No contacts match your filters</p>
                                <p class="text-xs text-primary-400 mt-1">Try adjusting the search or type filter.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if(!$error)
            <div class="px-6 py-4 border-t border-primary-100 dark:border-dark-border flex items-center justify-between">
                <p class="text-[10px] text-primary-400 font-bold uppercase tracking-wider"><span x-text="filtered().length + ' contact' + (filtered().length === 1 ? '' : 's')">{{ count($contacts) }} contacts</span></p>
            </div>
        @endif
    </div>

    <!-- Right Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 pt-0 pb-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/60 dark:bg-dark-900/60">
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white flex items-center gap-2"><i class="fa-brands fa-whatsapp text-green-600"></i> Contact Details</h3>
                    <p class="text-[11px] text-primary-500 truncate" x-text="selected ? (selected.name || selected.notify || selected.jid || selected.id || '') : ''"></p>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border flex items-center justify-center hover:bg-primary-50"><i class="fas fa-times text-primary-600"></i></button>
            </div>

            <div x-show="loading" class="p-8 text-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-primary-500 mt-3">Loading details...</p>
            </div>

            <div x-show="!loading && selected" class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5" style="display:none;" x-cloak>
                <!-- Avatar + identity -->
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 flex items-center justify-center overflow-hidden shrink-0">
                        <template x-if="selected?.imgUrl || selected?.img_url">
                            <img :src="selected?.imgUrl || selected?.img_url" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!(selected?.imgUrl || selected?.img_url)">
                            <i class="fa-brands fa-whatsapp text-2xl text-green-600 dark:text-green-400"></i>
                        </template>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-lg font-black text-primary-900 dark:text-white truncate" x-text="selected?.name || selected?.notify || 'Unknown'"></p>
                        <template x-if="selected?.verifiedName">
                            <p class="text-[11px] text-blue-500"><i class="fas fa-check-circle mr-0.5"></i><span x-text="selected.verifiedName"></span></p>
                        </template>
                    </div>
                </div>

                <!-- Contact information -->
                <div class="p-4 rounded-xl bg-primary-50 border border-primary-100 dark:bg-dark-800 dark:border-dark-border">
                    <p class="text-[10px] font-bold tracking-widest text-primary-500 mb-2">CONTACT INFORMATION</p>
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between gap-2 border-b border-primary-100 dark:border-dark-border pb-2">
                            <span class="text-primary-500 shrink-0">Phone / JID</span>
                            <span class="flex items-center gap-2 min-w-0 pl-3">
                                <span class="font-mono font-bold break-all text-right" x-text="selected?.id || selected?.jid || '—'"></span>
                                <button @click="copyJid(selected?.jid || selected?.id)" class="w-6 h-6 rounded border bg-white dark:bg-dark-900 flex items-center justify-center shrink-0"><i class="fa-regular fa-copy text-[10px]"></i></button>
                            </span>
                        </div>
                        <div class="flex justify-between gap-2 border-b border-primary-100 dark:border-dark-border pb-2">
                            <span class="text-primary-500 shrink-0">LID</span>
                            <span class="font-mono font-bold break-all text-right" x-text="selected?.lid || '—'"></span>
                        </div>
                        <div class="flex justify-between gap-2 border-b border-primary-100 dark:border-dark-border pb-2">
                            <span class="text-primary-500 shrink-0">Display Name</span>
                            <span class="font-bold break-all text-right" x-text="selected?.notify || '—'"></span>
                        </div>
                        <div class="flex justify-between gap-2 border-b border-primary-100 dark:border-dark-border pb-2">
                            <span class="text-primary-500 shrink-0">Type</span>
                            <span class="font-bold inline-flex items-center gap-1" :class="selected?.verifiedName ? 'text-blue-600' : 'text-primary-900 dark:text-white'">
                                <template x-if="selected?.verifiedName"><i class="fas fa-check-circle"></i></template>
                                <span x-text="selected?.verifiedName ? 'Business (verified)' : 'Personal'"></span>
                            </span>
                        </div>
                        <div class="flex justify-between gap-2">
                            <span class="text-primary-500 shrink-0">Status</span>
                            <span class="font-bold italic break-all text-right" x-text="selected?.status || '—'"></span>
                        </div>
                    </div>
                </div>

                <!-- Close -->
                <button @click="closeDrawer()" class="w-full px-3 py-2 rounded-lg bg-gray-900 dark:bg-white dark:text-gray-900 text-white text-xs font-bold"><i class="fas fa-times mr-1"></i> Close</button>
            </div>
        </div>
    </div>

    <!-- Send Message Drawer -->
    <div x-show="msgOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeMessageDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="msgOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-5 pt-0 pb-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/60 dark:bg-dark-900/60">
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white flex items-center gap-2"><i class="fab fa-whatsapp text-green-600"></i> Send Message</h3>
                    <p class="text-[11px] text-primary-500 truncate" x-text="msgTo ? (msgTo.name || msgTo.notify || msgTo.id || '') : ''"></p>
                </div>
                <button @click="closeMessageDrawer()" class="w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border flex items-center justify-center hover:bg-primary-50"><i class="fas fa-times text-primary-600"></i></button>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5">
                <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800">
                    <p class="text-[10px] font-bold tracking-widest text-green-600 uppercase mb-2">RECIPIENT</p>
                    <div class="text-xs space-y-2">
                        <div class="flex justify-between gap-2 border-b border-green-200 dark:border-green-800 pb-2"><span class="text-green-700 dark:text-green-300 shrink-0">Name</span><span class="font-bold text-green-900 dark:text-green-100 break-all text-right" x-text="msgTo?.name || msgTo?.notify || '—'"></span></div>
                        <div class="flex justify-between gap-2"><span class="text-green-700 dark:text-green-300 shrink-0">Phone / JID</span><span class="font-mono font-bold break-all text-right" x-text="msgTo?.id || msgTo?.jid || '—'"></span></div>
                    </div>
                </div>

                <form @submit.prevent="sendMessage($event)">
                    @csrf
                    <input type="hidden" name="message_type" value="text">
                    <input type="hidden" name="recipient_type" value="phone">
                    <input type="hidden" name="phone" :value="msgTo?.id || msgTo?.jid || ''">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Message Text</label>
                            <textarea name="text" rows="6" x-ref="msgText" class="w-full bg-primary-50 dark:bg-dark-800 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Type your message..."></textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Caption (optional)</label>
                            <input type="text" name="caption" class="w-full bg-primary-50 dark:bg-dark-800 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Optional caption">
                        </div>
                    </div>

                    <div x-show="msgSending || msgSent || msgError" x-cloak class="mt-4 p-3 rounded-xl border" :class="msgSending ? 'border-primary-100 bg-primary-50' : msgSent ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'">
                        <p class="text-xs font-bold" :class="msgSending ? 'text-primary-600' : msgSent ? 'text-green-700' : 'text-red-700'" x-text="msgSending ? 'Sending...' : (msgSent ? ('Sent ✓ ' + msgSummary) : (msgError || 'Failed to send'))"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-4">
                        <button type="button" @click="closeMessageDrawer()" class="px-3 py-2.5 rounded-lg border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50">Cancel</button>
                        <button type="submit" :disabled="msgSending" class="px-3 py-2.5 rounded-lg bg-green-600 hover:bg-green-500 text-white text-xs font-bold" :class="msgSending ? 'opacity-60 cursor-not-allowed' : ''">
                            <i class="fab fa-whatsapp mr-1"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Copy toast -->
    <div x-show="toast" x-transition class="fixed bottom-6 right-6 z-[70] px-4 py-2 rounded-xl bg-gray-900 text-white text-xs font-bold flex items-center gap-2" style="display:none;"><i class="fa-solid fa-check text-green-400"></i><span x-text="toastMsg"></span></div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function contactDrawer(){
    return {
        drawerOpen:false,
        selected:null,
        loading:false,
        search:'',
        type:'all',
        toast:false,
        toastMsg:'Copied!',
        contacts: @js($contacts),
        searchable(c){
            return ((c.name||'') + ' ' + (c.notify||'') + ' ' + (c.verifiedName||'') + ' ' + (c.id||'') + ' ' + (c.jid||'')).toLowerCase();
        },
        matchesFilter(c){
            const q = (this.search||'').toLowerCase().trim();
            const qOk = !q || this.searchable(c).includes(q);
            const isBiz = !!(c.verifiedName);
            const typeOk = this.type === 'all' || (this.type === 'business' ? isBiz : !isBiz);
            return qOk && typeOk;
        },
        filtered(){
            return (this.contacts || []).filter(c => this.matchesFilter(c));
        },
        openDrawer(contact){
            this.drawerOpen=true;
            this.loading=true;
            this.selected=null;
            setTimeout(()=>{ this.selected=contact; this.loading=false; }, 250);
        },
        closeDrawer(){ this.drawerOpen=false; setTimeout(()=>{ this.selected=null; }, 300); },
        msgOpen:false,
        msgTo:null,
        msgSending:false,
        msgSent:false,
        msgError:'',
        msgSummary:'',
        openMessageDrawer(contact){
            this.closeDrawer();
            this.msgTo=contact;
            this.msgError='';
            this.msgSent=false;
            this.msgSummary='';
            this.msgOpen=true;
            setTimeout(()=>{ this.$refs.msgText && this.$refs.msgText.focus(); }, 400);
        },
        closeMessageDrawer(){ this.msgOpen=false; this.msgSending=false; setTimeout(()=>{ this.msgTo=null; }, 300); },
        async sendMessage(e){
            if(this.msgSending) return;
            const form = e.target;
            const text = (form.querySelector('textarea[name="text"]') || {}).value || '';
            if(!text.trim()){ this.msgError='Please type a message.'; return; }
            this.msgSending=true;
            this.msgError='';
            this.msgSent=false;
            try{
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const fd = new FormData(form);
                const start = await fetch('{{ route('whatsapp.messages.send.post') }}', {
                    method:'POST',
                    headers:{ 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' },
                    body: fd,
                }).then(r=>r.json());
                if(!(start && start.success && start.batch_id)){
                    this.msgError=(start && start.message) || 'Failed to start sending.';
                    return;
                }
                let done=false, results=[], sleep = (ms)=>new Promise(r=>setTimeout(r, ms));
                while(!done){
                    const params = new URLSearchParams();
                    params.append('batch_id', start.batch_id);
                    const pr = await fetch('{{ route('whatsapp.messages.send-process') }}', {
                        method:'POST',
                        headers:{ 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8' },
                        body: params.toString(),
                    }).then(r=>r.json());
                    if(!(pr && pr.success)){
                        this.msgError=(pr && pr.message) || 'Failed to process send.';
                        done=true; break;
                    }
                    if(pr.current){ results.push(pr.current); }
                    done = !!pr.done;
                    if(!done) await sleep(5200);
                }
                this.msgSent = results.some(r => r.success === true) || (results.length === 0 && !this.msgError);
                this.msgSummary = results.some(r => r.success === true) ? 'message delivered' : '';
                if(results.length && results[results.length-1] && results[results.length-1].success === false){
                    this.msgError = results[results.length-1].message || 'Message failed to send.';
                    this.msgSent = false;
                }
                if(this.msgSent){
                    form.querySelector('textarea[name="text"]').value='';
                    form.querySelector('input[name="caption"]').value='';
                    setTimeout(()=>{ this.closeMessageDrawer(); }, 1800);
                }
            }catch(err){
                this.msgError='Network error. Please try again.';
            }finally{
                this.msgSending=false;
            }
        },
        copyJid(t){
            if(!t) return;
            navigator.clipboard.writeText(String(t)).then(()=>{
                this.toastMsg='Copied: ' + t;
                this.toast=true;
                setTimeout(()=>{ this.toast=false; }, 1500);
            });
        }
    }
}
</script>
@endpush