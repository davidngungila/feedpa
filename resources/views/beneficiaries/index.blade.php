@extends('layouts.app')

@section('title', 'Beneficiaries')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in" x-data="beneficiaryDrawer()" @keydown.escape.window="closeDrawer()">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-address-book text-primary-500"></i> Beneficiaries
            </h2>
            <p class="text-xs text-primary-500 mt-1">Manage your beneficiaries for quick payouts</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('beneficiaries.create') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-plus mr-1"></i> Add Beneficiary
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Beneficiaries Table Card -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 flex items-center justify-between gap-2">
            <p class="text-[11px] font-semibold text-primary-700 dark:text-primary-300 flex items-center gap-1.5"><i class="fas fa-hand-pointer text-primary-500"></i> Click any row to open right drawer — full beneficiary details</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @foreach($beneficiaries as $beneficiary)
                        <tr @click="openDrawer({{ $beneficiary->id }})" class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->name }}</p>
                                <p class="text-[10px] text-primary-500">Added: {{ $beneficiary->created_at->format('M d, Y') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $beneficiary->type === 'bank' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' }}">
                                    {{ $beneficiary->type === 'bank' ? 'Bank' : 'Mobile Money' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($beneficiary->type === 'bank')
                                    <p class="text-xs text-primary-700 dark:text-primary-300">{{ $beneficiary->bank_name }}</p>
                                    <p class="text-[10px] text-primary-500 font-mono">{{ $beneficiary->account_number }}</p>
                                @else
                                    <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $beneficiary->phone }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $beneficiary->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                    {{ $beneficiary->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($beneficiaries->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $beneficiaries->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Right Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 pt-0 pb-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/60 dark:bg-dark-900/60">
                <div>
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white flex items-center gap-2"><i class="fas fa-address-book text-primary-600"></i> Beneficiary Details</h3>
                    <p class="text-[11px] text-primary-500" x-text="selected ? 'ID #' + selected.id + ' • ' + (selected.name ?? '') : ''"></p>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border flex items-center justify-center hover:bg-primary-50"><i class="fas fa-times text-primary-600"></i></button>
            </div>

            <div x-show="loading" class="p-8 text-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-primary-500 mt-3">Loading details...</p>
            </div>

            <div x-show="!loading && selected" class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5" style="display:none;" x-cloak>
                <!-- Status + identity -->
                <div class="p-4 rounded-xl border-2 flex items-center justify-between" :class="selected?.is_active ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                    <div>
                        <p class="text-xs font-bold" :class="selected?.is_active ? 'text-green-800' : 'text-red-800'" x-text="selected?.is_active ? '✓ Active beneficiary' : 'Inactive'"></p>
                        <p class="text-[11px] text-primary-500" x-text="'Added ' + (selected?.created_at ?? '')"></p>
                    </div>
                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold" :class="selected?.type === 'bank' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'" x-text="selected?.type === 'bank' ? 'Bank' : 'Mobile Money'"></span>
                </div>

                <!-- Account details -->
                <template x-if="selected?.type === 'bank'">
                    <div class="space-y-3">
                        <div class="p-4 rounded-xl bg-primary-50 border border-primary-100">
                            <p class="text-[10px] font-bold tracking-widest text-primary-500 mb-2">BANK ACCOUNT</p>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="col-span-2"><span class="text-primary-500">Bank</span><p class="font-bold text-primary-900" x-text="selected?.bank_name ?? '—'"></p></div>
                                <div><span class="text-primary-500">Account Number</span><p class="font-mono font-bold" x-text="selected?.account_number ?? '—'"></p></div>
                                <div><span class="text-primary-500">BIC / SWIFT</span><p class="font-mono font-bold" x-text="selected?.bic ?? '—'"></p></div>
                                <div class="col-span-2"><span class="text-primary-500">Transfer Type</span><p class="font-bold" x-text="selected?.transfer_type ?? '—'"></p></div>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="selected?.type === 'mobile'">
                    <div class="p-4 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500 mb-2">MOBILE MONEY</p>
                        <div class="space-y-3 text-xs">
                            <div><span class="text-primary-500">Phone</span><p class="font-mono font-bold text-sm" x-text="selected?.phone ?? '—'"></p></div>
                            <template x-if="selected?.email"><div><span class="text-primary-500">Email</span><p class="font-bold" x-text="selected?.email"></p></div></template>
                        </div>
                    </div>
                </template>

                <!-- Meta -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500">CREATED</p>
                        <p class="font-bold text-primary-900" x-text="selected?.created_at ?? '—'"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500">LAST UPDATED</p>
                        <p class="font-bold text-primary-900" x-text="selected?.updated_at ?? '—'"></p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a :href="'{{ route('beneficiaries.index') }}/' + selected?.id + '/edit'" class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold"><i class="fas fa-edit"></i> Edit</a>
                    <form :action="'{{ route('beneficiaries.index') }}/' + selected?.id" method="POST" onsubmit="return confirm('Are you sure you want to delete this beneficiary?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                    <button @click="closeDrawer()" class="col-span-2 px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function beneficiaryDrawer(){
    return {
        drawerOpen:false,
        selected:null,
        loading:false,
        openDrawer(id){
            this.drawerOpen=true;
            this.loading=true;
            this.selected=null;
            fetch('{{ url('beneficiaries') }}/' + id + '/details', {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                .then(r=>r.json())
                .then(data=>{ this.selected=data; this.loading=false; })
                .catch(()=>{ this.loading=false; alert('Failed to load details'); });
        },
        closeDrawer(){ this.drawerOpen=false; setTimeout(()=>{ this.selected=null; }, 300); }
    }
}
</script>
@endpush