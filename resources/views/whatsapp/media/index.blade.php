@extends('layouts.app')

@section('title', 'Media & Files')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in" x-data="mediaDrawer()" @keydown.escape.window="closeDrawer()">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-folder-open text-primary-500"></i> Media & Files
            </h2>
            <p class="text-xs text-primary-500 mt-1">Upload and manage media files for WhatsApp messages</p>
        </div>
        <button type="button" id="openUploadBtn" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
            <i class="fas fa-upload mr-1"></i> Upload File
        </button>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Upload Form (hidden by default) -->
    <div id="uploadForm" class="hidden card p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
            <i class="fas fa-cloud-upload-alt"></i> Upload New File
        </h3>
        <form id="mediaUploadForm" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-1">
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">File Type *</label>
                    <select name="type" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="image">Image</option>
                        <option value="document">Document</option>
                        <option value="video">Video</option>
                        <option value="audio">Audio</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">File *</label>
                    <input type="file" name="file" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                    <i class="fas fa-cloud-upload-alt me-2"></i> Upload
                </button>
            </div>
        </form>
    </div>

    <!-- Media Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($mediaFiles as $media)
            <div class="card overflow-hidden group cursor-pointer" @click="openDrawer(@js($media))">
                <div class="h-32 bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center">
                    @if($media->type === 'image' && $media->url)
                        <img src="{{ $media->url }}" alt="{{ $media->name }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <i class="fas fa-{{ $media->type === 'document' ? 'file-pdf' : ($media->type === 'video' ? 'video' : ($media->type === 'audio' ? 'music' : 'image')) }} text-4xl text-primary-500"></i>
                    @endif
                </div>
                <div class="p-4">
                    <p class="text-xs font-bold text-primary-900 dark:text-white truncate" title="{{ $media->name }}">{{ $media->name }}</p>
                    <p class="text-[10px] text-primary-500 mt-1">{{ strtoupper($media->type) }} · {{ $media->size ? round($media->size / 1024) . ' KB' : '—' }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-[10px] text-primary-400">{{ $media->created_at->format('M d, Y') }}</span>
                        <div class="flex gap-1">
                            @if($media->url)
                                <a href="{{ $media->url }}" target="_blank" @click.stop class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Open">
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            @endif
                            <form action="{{ route('whatsapp.media.destroy', $media->id) }}" method="POST" @click.stop data-ajax-delete onsubmit="return confirm('Are you sure you want to delete this file?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Delete">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-16 text-center col-span-full">
                <i class="fas fa-folder-open text-4xl text-primary-300 mb-3 block"></i>
                <p class="text-sm font-bold text-primary-500">No media files yet</p>
                <p class="text-xs text-primary-400 mt-1">Upload files to use in WhatsApp messages.</p>
            </div>
        @endforelse
    </div>

    @if($mediaFiles->hasPages())
        <div class="p-6">
            {{ $mediaFiles->appends(request()->query())->links() }}
        </div>
    @endif

    <!-- Media Details Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 min-w-0">
                    <i class="fas fa-info-circle text-primary-500 text-sm"></i>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider">Media Details</p>
                        <p x-text="media?.name" class="text-[10px] text-primary-500 truncate"></p>
                    </div>
                </div>
                <button type="button" @click="closeDrawer()" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all" title="Close">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Loading Spinner -->
                <div x-show="loading" class="flex items-center justify-center py-16">
                    <i class="fas fa-spinner fa-spin text-3xl text-primary-500"></i>
                </div>

                <template x-if="!loading && media">
                    <div class="space-y-5">
                        <!-- Large Preview -->
                        <div class="rounded-2xl overflow-hidden bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center h-64">
                            <template x-if="media.type === 'image' && media.url">
                                <img :src="media.url" :alt="media.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!(media.type === 'image' && media.url)">
                                <i :class="'fas ' + (media.type === 'document' ? 'fa-file-pdf' : (media.type === 'video' ? 'fa-video' : (media.type === 'audio' ? 'fa-music' : 'fa-image'))) + ' text-6xl text-primary-500'"></i>
                            </template>
                        </div>

                        <!-- File Name -->
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">File Name</p>
                            <p x-text="media.name" class="text-sm font-bold text-primary-900 dark:text-white break-all"></p>
                        </div>

                        <!-- Type Badge -->
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Type</p>
                            <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">
                                <span x-text="media.type"></span>
                            </span>
                        </div>

                        <!-- File Size -->
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">File Size</p>
                            <p class="text-sm font-bold text-primary-900 dark:text-white" x-text="formatSize(media.size)"></p>
                        </div>

                        <!-- Created Date -->
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Created</p>
                            <p class="text-sm font-bold text-primary-900 dark:text-white" x-text="formatDate(media.created_at)"></p>
                        </div>

                        <!-- Open in New Tab -->
                        <a x-show="media.url" :href="media.url" target="_blank" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 hover:bg-blue-600 hover:text-white transition-all text-xs font-bold">
                            <i class="fas fa-external-link-alt"></i> Open in New Tab
                        </a>

                        <!-- Delete -->
                        <form :action="mediaDeleteUrl(media.id)" method="POST" data-ajax-delete onsubmit="return confirm('Are you sure you want to delete this file?')" class="!mb-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-300 hover:bg-red-600 hover:text-white transition-all text-xs font-bold">
                                <i class="fas fa-trash"></i> Delete File
                            </button>
                        </form>

                        <!-- Close -->
                        <button type="button" @click="closeDrawer()" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all text-xs font-bold">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
    function mediaDrawer() {
        return {
            drawerOpen: false,
            loading: true,
            media: null,
            deleteBaseUrl: '{{ route('whatsapp.media.destroy', ['media' => '']) }}',
            openDrawer(item) {
                this.loading = true;
                this.media = item;
                this.drawerOpen = true;
                setTimeout(() => {
                    this.loading = false;
                }, 250);
            },
            closeDrawer() {
                this.drawerOpen = false;
                this.media = null;
            },
            mediaDeleteUrl(id) {
                return this.deleteBaseUrl + id;
            },
            formatSize(size) {
                if (!size) return '—';
                const bytes = Number(size);
                if (bytes >= 1048576) {
                    return (bytes / 1048576).toFixed(2) + ' MB';
                }
                return Math.round(bytes / 1024) + ' KB';
            },
            formatDate(dateStr) {
                if (!dateStr) return '—';
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            },
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const uploadForm = document.getElementById('uploadForm');
        const openBtn = document.getElementById('openUploadBtn');

        if (openBtn) {
            openBtn.addEventListener('click', function () {
                uploadForm.classList.toggle('hidden');
                const icon = openBtn.querySelector('i');
                if (uploadForm.classList.contains('hidden')) {
                    openBtn.innerHTML = '<i class="fas fa-upload mr-1"></i> Upload File';
                } else {
                    openBtn.innerHTML = '<i class="fas fa-times mr-1"></i> Close';
                }
            });
        }

        const form = document.getElementById('mediaUploadForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const original = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Uploading...';

                const formData = new FormData(form);

                fetch('{{ route('whatsapp.media.upload') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = original;
                    if (data.success) {
                        alert('File uploaded successfully!');
                        location.reload();
                    } else {
                        alert(data.message || 'Upload failed.');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = original;
                    alert('Error: ' + error.message);
                });
            });
        }

        document.querySelectorAll('form[data-ajax-delete]').forEach(delForm => {
            delForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this file?')) return;

                fetch(delForm.action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete file.');
                    }
                })
                .catch(error => alert('Error: ' + error.message));
            });
        });
    });
</script>
@endpush