@extends('layouts.app')

@section('title', 'Media & Files')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
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
            <div class="card overflow-hidden group">
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
                                <a href="{{ $media->url }}" target="_blank" class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Open">
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            @endif
                            <form action="{{ route('whatsapp.media.destroy', $media->id) }}" method="POST" data-ajax-delete onsubmit="return confirm('Are you sure you want to delete this file?')">
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
</div>
@endsection

@push('scripts')
<script>
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
@endsection
