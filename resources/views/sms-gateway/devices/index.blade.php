@extends('layouts.app')
@section('title', 'SMS Devices')

@section('content')
<div class="space-y-4" x-data="deviceDrawer()" @keydown.escape.window="closeDrawer()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h1 class="text-xl font-bold text-primary-900">Devices</h1>
        <a href="{{ route('sms-gateway.devices.create') }}" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-xs font-bold hover:bg-primary-700">+ Register Device</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="card p-3 text-center"><p class="text-[11px] text-primary-500">TOTAL</p><p class="text-xl font-bold">{{ $stats['total'] }}</p></div>
        <div class="card p-3 text-center"><p class="text-[11px] text-primary-500">ACTIVE</p><p class="text-xl font-bold text-green-600">{{ $stats['active'] }}</p></div>
        <div class="card p-3 text-center"><p class="text-[11px] text-primary-500">ONLINE</p><p class="text-xl font-bold text-green-600">{{ $stats['online'] }}</p></div>
        <div class="card p-3 text-center"><p class="text-[11px] text-primary-500">PENDING</p><p class="text-xl font-bold text-amber-600">{{ $stats['pending'] }}</p></div>
    </div>

    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search MOSHI-01, phone..." class="flex-1 min-w-[160px] px-3 py-2 rounded-lg border border-primary-200 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            <select name="status" class="px-3 py-2 rounded-lg border border-primary-200 text-sm bg-white">
                <option value="">All Status</option>
                <option value="ACTIVE" @selected(request('status')=='ACTIVE')>ACTIVE</option>
                <option value="PENDING" @selected(request('status')=='PENDING')>PENDING</option>
                <option value="SUSPENDED" @selected(request('status')=='SUSPENDED')>SUSPENDED</option>
                <option value="REVOKED" @selected(request('status')=='REVOKED')>REVOKED</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-bold hover:bg-primary-700">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Device</th><th>Location</th><th>Phone</th><th>Status</th><th>Battery</th></tr></thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($devices as $d)
                    <tr @click="openDrawer({{ $d->id }})" class="hover:bg-primary-50/70 cursor-pointer transition-colors" title="Click to open details drawer">
                        <td class="font-bold text-primary-700">{{ $d->device_code }}<br><span class="text-xs text-primary-500 font-normal">{{ $d->name }}</span></td>
                        <td class="text-xs">{{ $d->location->name ?? '—' }}</td>
                        <td class="font-mono text-xs whitespace-nowrap">{{ $d->phone_number ?? '—' }}</td>
                        <td><span class="badge {{ $d->status==='ACTIVE' ? 'badge-green' : ($d->status==='PENDING' ? 'badge-yellow' : 'badge-red') }} text-[10px]">{{ $d->status }}</span> @if($d->isOnline()) <span class="ml-1 w-2 h-2 inline-block rounded-full bg-green-500"></span> @endif</td>
                        <td class="text-xs whitespace-nowrap">{{ $d->battery_level !== null ? $d->battery_level.'%' : '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-8 text-primary-400">No devices. Register MOSHI-01, TABORA-01...</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $devices->links() }}</div>
    </div>

    <!-- Right Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 pt-0 pb-4 border-b border-primary-100 bg-primary-50">
                <div>
                    <h3 class="text-sm font-bold text-primary-900 flex items-center gap-2"><i class="fa-solid fa-mobile-screen-button text-primary-600"></i> Device Details</h3>
                    <p class="text-[11px] text-primary-500" x-text="selected ? selected.device_code + ' • ' + (selected.name ?? '') : ''"></p>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-lg bg-white border border-primary-200 flex items-center justify-center hover:bg-primary-50"><i class="fa-solid fa-xmark text-primary-600"></i></button>
            </div>

            <div x-show="loading" class="p-8 text-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-primary-500 mt-3">Loading details...</p>
            </div>

            <div x-show="!loading && selected" class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5" style="display:none;" x-cloak>
                <!-- Status -->
                <div class="p-4 rounded-xl border-2 flex items-center justify-between" :class="selected?.status === 'ACTIVE' ? 'bg-green-50 border-green-200' : (selected?.status === 'PENDING' ? 'bg-amber-50 border-amber-200' : 'bg-red-50 border-red-200')">
                    <div>
                        <p class="text-xs font-bold" :class="selected?.status === 'ACTIVE' ? 'text-green-800' : (selected?.status === 'PENDING' ? 'text-amber-800' : 'text-red-800')" x-text="selected?.status"></p>
                        <p class="text-[11px] text-primary-500" x-text="selected?.is_online ? 'Online now' : ('Last heartbeat: ' + lastSeen())"></p>
                    </div>
                    <span x-show="selected?.is_online" class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                </div>

                <!-- Identity -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500">LOCATION</p>
                        <p class="font-bold text-primary-900" x-text="selected?.location ?? '—'"></p>
                        <p class="text-primary-500" x-text="'Created by ' + (selected?.created_by ?? '—')"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500">PHONE • SIM</p>
                        <p class="font-bold text-primary-900" x-text="selected?.phone_number ?? '—'"></p>
                        <p class="text-primary-500" x-text="(selected?.sim_operator ?? '—') + (selected?.sim_slot ? ' • SIM '+selected.sim_slot : '')"></p>
                    </div>
                </div>

                <!-- Health -->
                <div class="p-4 rounded-xl bg-gray-900">
                    <p class="text-[10px] font-bold tracking-widest text-gray-400 mb-3">DEVICE HEALTH</p>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div>
                            <p class="text-lg font-bold" :class="(selected?.battery_level ?? 0) <= 15 ? 'text-red-400' : 'text-green-300'" x-text="selected?.battery_level != null ? selected.battery_level + '%' : '—'"></p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">Battery</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-green-300" x-text="selected?.network_type ?? '—'"></p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">Network</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-green-300" x-text="selected?.signal_strength ?? '—'"></p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">Signal</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-blue-300" x-text="selected?.app_version ?? '—'"></p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">App</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-blue-300" x-text="selected?.android_version ?? '—'"></p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">Android</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-amber-300" x-text="selected?.sms_count ?? 0"></p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider">SMS Sent</p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-700 text-[10px] text-gray-400 space-y-1">
                        <p>Last sync: <span class="text-gray-300" x-text="selected?.last_sync_at ?? 'never'"></span></p>
                        <p>Last SMS: <span class="text-gray-300" x-text="selected?.last_sms_at ?? 'never'"></span></p>
                    </div>
                </div>

                <!-- Active token -->
                <template x-if="selected?.active_token">
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200">
                        <p class="text-[11px] font-bold tracking-widest text-blue-700 mb-2">ACTIVE TOKEN</p>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="font-mono font-bold text-blue-900" x-text="'••••' + (selected?.active_token?.plain_hint ?? '')"></span>
                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">auth</span>
                        </div>
                        <p class="mt-1 text-[10px] text-blue-600" x-text="'Last used: ' + (selected?.active_token?.last_used_at ?? 'never') + (selected?.active_token?.expires_at ? ' • Expires: ' + selected.active_token.expires_at : '')"></p>
                    </div>
                </template>

                @if(auth()->user()->is_admin)
                <!-- Actions -->
                <div class="grid grid-cols-2 gap-2">
                    <template x-if="selected?.status === 'PENDING'">
                        <form method="POST" :action="'{{ url('sms-gateway/devices') }}/' + selected?.id + '/activate'" class="col-span-1">
                            @csrf
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-green-600 hover:bg-green-500 text-white text-xs font-bold">Activate</button>
                        </form>
                    </template>
                    <template x-if="selected?.status === 'ACTIVE'">
                        <form method="POST" :action="'{{ url('sms-gateway/devices') }}/' + selected?.id + '/suspend'" class="col-span-1">
                            @csrf
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-white text-xs font-bold">Suspend</button>
                        </form>
                    </template>
                    <template x-if="selected?.status !== 'REVOKED'">
                        <form method="POST" :action="'{{ url('sms-gateway/devices') }}/' + selected?.id + '/revoke'" onsubmit="return confirm('Revoke this device and invalidate all tokens?')" class="col-span-1">
                            @csrf
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold">Revoke</button>
                        </form>
                    </template>
                    <form method="POST" :action="'{{ url('sms-gateway/devices') }}/' + selected?.id + '/generate-code'" class="col-span-1">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold"><i class="fa-solid fa-key me-1"></i> New Code</button>
                    </form>
                </div>
                @endif

                <template x-if="selected">
                    <a :href="'{{ url('sms-gateway/devices') }}/' + selected.id" class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-primary-200 text-xs font-bold hover:bg-primary-50">
                        <i class="fa-solid fa-gear text-primary-600"></i> Open Full Management Page
                    </a>
                </template>

                <button @click="closeDrawer()" class="w-full px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function deviceDrawer(){
    return {
        drawerOpen:false,
        selected:null,
        loading:false,
        openDrawer(id){
            this.drawerOpen=true;
            this.loading=true;
            this.selected=null;
            fetch('{{ url('sms-gateway/devices') }}/' + id + '/details', {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                .then(r=>r.json())
                .then(data=>{
                    this.selected=data;
                    this.loading=false;
                })
                .catch(()=>{this.loading=false; alert('Failed to load details');});
        },
        closeDrawer(){ this.drawerOpen=false; setTimeout(()=>{this.selected=null;},300); },
        lastSeen(){
            if(!this.selected?.last_heartbeat_at) return 'never';
            return new Date(this.selected.last_heartbeat_at).toLocaleString();
        }
    }
}
</script>
@endsection