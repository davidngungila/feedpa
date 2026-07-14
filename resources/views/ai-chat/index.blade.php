@extends('layouts.app')

@section('title', 'FEEDTAN AI')

@push('styles')
<style>
    .ai-page-shell {
        background:
            radial-gradient(circle at top left, rgba(16, 185, 129, 0.16), transparent 32%),
            linear-gradient(180deg, rgba(236, 253, 245, 0.92), rgba(255, 255, 255, 0.96));
    }

    .dark .ai-page-shell {
        background:
            radial-gradient(circle at top left, rgba(16, 185, 129, 0.16), transparent 32%),
            linear-gradient(180deg, rgba(10, 20, 14, 0.98), rgba(13, 31, 22, 0.98));
    }

    .ai-response-content p + p,
    .ai-response-content ul + p,
    .ai-response-content ol + p,
    .ai-response-content pre + p {
        margin-top: 0.75rem;
    }

    .ai-response-content ul,
    .ai-response-content ol {
        margin: 0.75rem 0;
        padding-left: 1.25rem;
    }

    .ai-response-content ul {
        list-style: disc;
    }

    .ai-response-content ol {
        list-style: decimal;
    }

    .ai-response-content code {
        background: rgba(6, 78, 59, 0.08);
        color: #065f46;
        border-radius: 0.45rem;
        padding: 0.1rem 0.35rem;
        font-size: 0.85em;
    }

    .dark .ai-response-content code {
        background: rgba(110, 231, 183, 0.12);
        color: #a7f3d0;
    }

    .ai-response-content pre {
        background: #052e16;
        color: #ecfdf5;
        border-radius: 1rem;
        padding: 1rem;
        overflow-x: auto;
        margin: 0.85rem 0;
        font-size: 0.85rem;
        line-height: 1.6;
    }

    .ai-response-content pre code {
        background: transparent;
        color: inherit;
        padding: 0;
    }

    .ai-export-surface {
        background: white;
        color: #111827;
    }

    .ai-chat-container {
        height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
    }

    @media (max-width: 1280px) {
        .ai-chat-container {
            height: calc(100vh - 90px);
        }
    }

    .ai-chat-messages-wrapper {
        flex: 1;
        overflow-y: auto;
    }

    .ai-sidebar {
        padding: 1rem !important;
    }

    .ai-sidebar-section {
        padding: 0.75rem !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="ai-page-shell border border-primary-100 dark:border-dark-border rounded-[28px] shadow-xl overflow-hidden">
        <div class="px-4 py-3 md:px-6 md:py-4 border-b border-primary-100/80 dark:border-dark-border">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 via-primary-600 to-primary-800 flex items-center justify-center shadow-lg shadow-primary-900/20">
                        <i class="fas fa-robot text-xl text-white"></i>
                    </div>
                    <h1 class="text-lg md:text-xl font-black text-primary-950 dark:text-white">FEEDTAN AI</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        id="aiExportPdfBtn"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-primary-200 bg-white/90 px-3 py-2 text-xs font-bold text-primary-800 hover:bg-primary-50 transition-all">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        <span>Export PDF</span>
                    </button>
                    <button
                        type="button"
                        id="aiClearChatBtn"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-primary-200 bg-white/90 px-3 py-2 text-xs font-bold text-primary-800 hover:bg-primary-50 transition-all">
                        <i class="fas fa-rotate-left"></i>
                        <span>New Chat</span>
                    </button>
                </div>
            </div>
        </div>

        <div id="aiChatExportSurface" class="ai-export-surface">
            <div class="grid grid-cols-1 xl:grid-cols-[300px_minmax(0,1fr)]">
                <aside class="border-b xl:border-b-0 xl:border-r border-primary-100 dark:border-dark-border bg-white/70 dark:bg-dark-card/60 ai-sidebar">
                    <div class="space-y-3">
                        <div class="rounded-2xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-900/40 ai-sidebar-section">
                            <div class="text-[11px] font-black uppercase tracking-[0.2em] text-primary-600 dark:text-primary-300">What You Can Do</div>
                            <ul class="mt-2 space-y-2 text-xs text-primary-900 dark:text-primary-100">
                                <li class="flex gap-2"><i class="fas fa-comment-dots mt-0.5 text-primary-500"></i><span>Ask about payments, bills, transactions, and workflows.</span></li>
                                <li class="flex gap-2"><i class="fas fa-image mt-0.5 text-primary-500"></i><span>Upload a screenshot or receipt and ask FEEDTAN AI to explain it.</span></li>
                                <li class="flex gap-2"><i class="fas fa-file-pdf mt-0.5 text-primary-500"></i><span>Export the current conversation as a PDF.</span></li>
                            </ul>
                        </div>

                        <div class="rounded-2xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-card ai-sidebar-section">
                            <div class="text-[11px] font-black uppercase tracking-[0.2em] text-primary-600 dark:text-primary-300">Quick Prompts</div>
                            <div class="mt-2 flex flex-col gap-1.5">
                                <button type="button" class="ai-quick-prompt text-left rounded-xl border border-primary-100 dark:border-dark-border px-2.5 py-2 text-xs text-primary-900 dark:text-primary-100 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all" data-prompt="Explain the latest payment flow in simple steps.">
                                    Explain the latest payment flow in simple steps.
                                </button>
                                <button type="button" class="ai-quick-prompt text-left rounded-xl border border-primary-100 dark:border-dark-border px-2.5 py-2 text-xs text-primary-900 dark:text-primary-100 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all" data-prompt="Summarize how bill control numbers work in FEEDTAN.">
                                    Summarize how bill control numbers work in FEEDTAN.
                                </button>
                                <button type="button" class="ai-quick-prompt text-left rounded-xl border border-primary-100 dark:border-dark-border px-2.5 py-2 text-xs text-primary-900 dark:text-primary-100 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all" data-prompt="Review this screenshot and tell me what stands out.">
                                    Review this screenshot and tell me what stands out.
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="flex flex-col ai-chat-container">
                    <div id="aiPageChatMessages" class="ai-chat-messages-wrapper px-5 py-6 md:px-8 md:py-7 space-y-5 bg-white/40 dark:bg-transparent"></div>

                    <div class="border-t border-primary-100 dark:border-dark-border px-3 py-2 md:px-4 bg-white/85 dark:bg-dark-card/85 backdrop-blur-sm">
                        <div id="aiImagePreviewWrap" class="hidden mb-1.5"></div>

                        <div class="rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-card p-1.5">
                            <textarea
                                id="aiPageChatInput"
                                rows="1"
                                placeholder="Message..."
                                class="w-full resize-none bg-transparent px-2.5 py-1.5 text-sm text-primary-950 dark:text-white placeholder:text-gray-400 focus:outline-none"
                                style="min-height: 36px; max-height: 120px;"
                            ></textarea>

                            <div class="flex flex-col gap-1.5 border-t border-primary-100 dark:border-dark-border pt-1.5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap items-center gap-1">
                                    <input id="aiImageInput" type="file" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" class="hidden">
                                    <button
                                        type="button"
                                        id="aiUploadImageBtn"
                                        class="inline-flex items-center gap-1 rounded-lg border border-primary-200 px-2 py-1 text-[11px] font-bold text-primary-800 dark:text-primary-100 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                                        <i class="fas fa-image text-xs"></i>
                                        <span>Image</span>
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    id="aiSendBtn"
                                    class="inline-flex items-center justify-center gap-1 rounded-lg bg-gradient-to-br from-primary-700 to-primary-500 px-3 py-1.5 text-[11px] font-bold text-white shadow-lg shadow-primary-900/20 hover:translate-y-[-0.5px] transition-all disabled:cursor-not-allowed disabled:opacity-60">
                                    <i class="fas fa-paper-plane text-xs"></i>
                                    <span>Send</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" referrerpolicy="no-referrer"></script>
<script>
    let aiPageHistory = [];
    let aiPageIsLoading = false;
    let aiPendingImageFile = null;
    let aiPendingImageUrl = null;

    function aiEscapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function aiFormatRichText(text) {
        const escaped = aiEscapeHtml(text ?? '');
        const parts = escaped.split(/```([\s\S]*?)```/g);
        let html = '';

        for (let i = 0; i < parts.length; i++) {
            if (i % 2 === 1) {
                html += `<pre><code>${parts[i]}</code></pre>`;
                continue;
            }

            const segment = parts[i];
            const lines = segment.split('\n');
            let inList = false;
            let listType = null;

            const closeList = () => {
                if (inList) {
                    html += listType === 'ol' ? '</ol>' : '</ul>';
                    inList = false;
                    listType = null;
                }
            };

            lines.forEach((line) => {
                const trimmed = line.trim();

                if (!trimmed) {
                    closeList();
                    return;
                }

                const orderedMatch = trimmed.match(/^\d+\.\s+(.*)$/);
                const bulletMatch = trimmed.match(/^[-*]\s+(.*)$/);

                if (orderedMatch) {
                    if (!inList || listType !== 'ol') {
                        closeList();
                        html += '<ol>';
                        inList = true;
                        listType = 'ol';
                    }
                    html += `<li>${aiFormatInline(orderedMatch[1])}</li>`;
                    return;
                }

                if (bulletMatch) {
                    if (!inList || listType !== 'ul') {
                        closeList();
                        html += '<ul>';
                        inList = true;
                        listType = 'ul';
                    }
                    html += `<li>${aiFormatInline(bulletMatch[1])}</li>`;
                    return;
                }

                closeList();
                html += `<p>${aiFormatInline(trimmed)}</p>`;
            });

            closeList();
        }

        return html || '<p></p>';
    }

    function aiFormatInline(text) {
        return text
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            .replace(/\*([^*]+)\*/g, '<em>$1</em>');
    }

    function aiAutoResizeTextarea() {
        const textarea = document.getElementById('aiPageChatInput');
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    }

    function aiScrollToBottom() {
        const wrapper = document.getElementById('aiPageChatMessages');
        wrapper.scrollTop = wrapper.scrollHeight;
    }

    function aiMessageTime() {
        return new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function aiCreateMessageCard(role, text, options = {}) {
        const wrapper = document.getElementById('aiPageChatMessages');
        const row = document.createElement('div');
        row.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'}`;

        const isUser = role === 'user';
        const imageHtml = options.imageUrl
            ? `<img src="${options.imageUrl}" alt="Uploaded attachment" class="mb-2 max-h-44 w-full rounded-xl object-cover border border-white/40">`
            : '';
        const copyButton = !isUser
            ? `<button type="button" class="ai-copy-response inline-flex items-center gap-1.5 rounded-lg border border-primary-200 px-2.5 py-1.5 text-[11px] font-bold text-primary-700 hover:bg-primary-50 transition-all" data-copy="${encodeURIComponent(text ?? '')}">
                    <i class="fas fa-copy text-xs"></i>
                    <span>Copy</span>
               </button>`
            : '';

        row.innerHTML = `
            <div class="max-w-[85%] md:max-w-[75%] ${isUser ? 'order-2' : ''}">
                <div class="flex items-center gap-1.5 mb-1.5 ${isUser ? 'justify-end' : 'justify-start'}">
                    ${isUser
                        ? '' // remove "You" label
                        : '<span class="text-[11px] font-black uppercase tracking-[0.18em] text-primary-600 dark:text-primary-300">FEEDTAN AI</span>'}
                    <span class="text-[10px] text-gray-400">${aiMessageTime()}</span>
                </div>

                <div class="${isUser
                    ? 'rounded-2xl rounded-tr-sm bg-gradient-to-br from-primary-700 to-primary-500 text-white px-3 py-1.5 shadow-lg shadow-primary-900/15'
                    : 'rounded-2xl rounded-tl-sm border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-card px-3 py-1.5 shadow-sm'}">
                    ${imageHtml}
                    <div class="${isUser ? 'text-sm leading-snug whitespace-pre-wrap' : 'ai-response-content text-sm leading-snug text-primary-950 dark:text-primary-50'}">
                        ${isUser ? aiEscapeHtml(text ?? '') : aiFormatRichText(text ?? '')}
                    </div>
                </div>

                ${copyButton ? `<div class="mt-2 flex justify-start">${copyButton}</div>` : ''}
            </div>
        `;

        wrapper.appendChild(row);
        aiScrollToBottom();
        return row;
    }

    function aiAddLoadingCard() {
        const wrapper = document.getElementById('aiPageChatMessages');
        const row = document.createElement('div');
        row.id = 'aiPageLoadingCard';
        row.className = 'flex justify-start';
        row.innerHTML = `
            <div class="max-w-[75%]">
                <div class="flex items-center gap-1.5 mb-1.5">
                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-primary-600 dark:text-primary-300">FEEDTAN AI</span>
                    <span class="text-[10px] text-gray-400">thinking...</span>
                </div>
                <div class="rounded-2xl rounded-tl-sm border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-card px-3.5 py-2.5 shadow-sm">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-primary-500 animate-bounce" style="animation-delay:0s;"></span>
                        <span class="h-2 w-2 rounded-full bg-primary-500 animate-bounce" style="animation-delay:0.15s;"></span>
                        <span class="h-2 w-2 rounded-full bg-primary-500 animate-bounce" style="animation-delay:0.3s;"></span>
                    </div>
                </div>
            </div>
        `;

        wrapper.appendChild(row);
        aiScrollToBottom();
        return row;
    }

    function aiRenderPendingImage() {
        const wrap = document.getElementById('aiImagePreviewWrap');

        if (!aiPendingImageFile || !aiPendingImageUrl) {
            wrap.classList.add('hidden');
            wrap.innerHTML = '';
            return;
        }

        wrap.classList.remove('hidden');
        wrap.innerHTML = `
            <div class="inline-flex items-center gap-3 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 shadow-sm">
                <img src="${aiPendingImageUrl}" alt="Selected preview" class="h-12 w-12 rounded-lg object-cover border border-primary-100">
                <div class="min-w-0">
                    <div class="text-[11px] font-black uppercase tracking-[0.18em] text-primary-600 dark:text-primary-300">Image</div>
                    <div class="truncate text-xs font-semibold text-primary-950 dark:text-white">${aiEscapeHtml(aiPendingImageFile.name)}</div>
                </div>
                <button type="button" id="aiRemoveImageBtn" class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-2 py-1 text-[11px] font-bold text-red-600 hover:bg-red-50 transition-all">
                    <i class="fas fa-xmark text-xs"></i>
                    <span>Remove</span>
                </button>
            </div>
        `;

        document.getElementById('aiRemoveImageBtn').addEventListener('click', aiClearPendingImage);
    }

    function aiClearPendingImage() {
        aiPendingImageFile = null;
        aiPendingImageUrl = null;
        document.getElementById('aiImageInput').value = '';
        aiRenderPendingImage();
    }

    function aiSetLoadingState(isLoading) {
        aiPageIsLoading = isLoading;
        document.getElementById('aiSendBtn').disabled = isLoading;
        document.getElementById('aiUploadImageBtn').disabled = isLoading;
    }

    async function aiSendMessage() {
        if (aiPageIsLoading) {
            return;
        }

        const input = document.getElementById('aiPageChatInput');
        const message = input.value.trim();

        if (!message && !aiPendingImageFile) {
            return;
        }

        aiSetLoadingState(true);
        const attachmentPreview = aiPendingImageUrl;

        aiCreateMessageCard('user', message || 'Please analyze this image.', {
            imageUrl: attachmentPreview
        });
        aiPageHistory.push({
            role: 'user',
            text: message || 'Please analyze this image.'
        });

        input.value = '';
        aiAutoResizeTextarea();
        const loadingCard = aiAddLoadingCard();

        try {
            const formData = new FormData();
            formData.append('message', message || 'Please analyze this image.');
            formData.append('history', JSON.stringify(aiPageHistory));

            if (aiPendingImageFile) {
                formData.append('image', aiPendingImageFile);
            }

            const response = await fetch('{{ route('dashboard.ai-chat') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();
            loadingCard.remove();

            if (data.success) {
                aiCreateMessageCard('assistant', data.response);
                aiPageHistory.push({
                    role: 'assistant',
                    text: data.response
                });
            } else {
                aiCreateMessageCard('assistant', `Error: ${data.message || 'Something went wrong.'}`);
            }
        } catch (error) {
            loadingCard.remove();
            aiCreateMessageCard('assistant', `Error: ${error.message}`);
        } finally {
            aiClearPendingImage();
            aiSetLoadingState(false);
        }
    }

    function aiResetConversation() {
        aiPageHistory = [];
        aiClearPendingImage();
        document.getElementById('aiPageChatMessages').innerHTML = '';
        aiCreateMessageCard(
            'assistant',
            "Hi there! I'm FEEDTAN AI, your personal assistant. Ask me about payments, bills, transactions, or upload an image for analysis."
        );
    }

    async function aiExportPdf() {
        const exportNode = document.getElementById('aiChatExportSurface').cloneNode(true);
        exportNode.classList.add('ai-export-surface');
        exportNode.querySelectorAll('button, textarea, input').forEach((node) => node.remove());

        const options = {
            margin: 0.4,
            filename: `feedtan-ai-chat-${new Date().toISOString().slice(0, 10)}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        };

        await html2pdf().set(options).from(exportNode).save();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('aiPageChatInput');
        const fileInput = document.getElementById('aiImageInput');

        aiResetConversation();

        input.addEventListener('input', aiAutoResizeTextarea);
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                aiSendMessage();
            }
        });

        document.getElementById('aiSendBtn').addEventListener('click', aiSendMessage);
        document.getElementById('aiUploadImageBtn').addEventListener('click', function () {
            fileInput.click();
        });
        document.getElementById('aiClearChatBtn').addEventListener('click', aiResetConversation);
        document.getElementById('aiExportPdfBtn').addEventListener('click', aiExportPdf);

        fileInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) {
                return;
            }

            aiPendingImageFile = file;
            const reader = new FileReader();
            reader.onload = function (loadEvent) {
                aiPendingImageUrl = loadEvent.target.result;
                aiRenderPendingImage();
            };
            reader.readAsDataURL(file);
        });

        document.querySelectorAll('.ai-quick-prompt').forEach((button) => {
            button.addEventListener('click', function () {
                input.value = this.dataset.prompt || '';
                aiAutoResizeTextarea();
                input.focus();
            });
        });

        document.getElementById('aiPageChatMessages').addEventListener('click', async function (event) {
            const button = event.target.closest('.ai-copy-response');
            if (!button) {
                return;
            }

            const content = decodeURIComponent(button.dataset.copy || '');
            await navigator.clipboard.writeText(content);
            const label = button.querySelector('span');
            const original = label.textContent;
            label.textContent = 'Copied';
            setTimeout(() => {
                label.textContent = original;
            }, 1200);
        });
    });
</script>
@endpush
