@extends('layouts.app')

@section('title', 'AI Chat')

@section('content')
<div class="h-[calc(100vh-10rem)] flex flex-col max-w-4xl mx-auto w-full">
    <!-- Chat Header -->
    <div class="flex items-center justify-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                <i class="fas fa-robot text-2xl text-white"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">FEEDTAN AI</h1>
                <p class="text-xs text-gray-500">Powered by Google Gemini</p>
            </div>
        </div>
    </div>

    <!-- Chat Messages -->
    <div id="chatMessages" class="flex-1 overflow-y-auto px-4 space-y-6 mb-4"></div>

    <!-- Input Area -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-3">
        <div class="flex items-end gap-3">
            <textarea 
                id="chatInput" 
                placeholder="Message FEEDTAN AI..." 
                rows="1"
                class="flex-1 resize-none px-4 py-3 bg-gray-50 dark:bg-gray-800 border-0 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                style="min-height: 52px; max-height: 200px;"
            ></textarea>
            <button 
                id="sendBtn" 
                onclick="sendMessage()"
                class="p-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl transition-all disabled:opacity-50"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <path d="M22 2L11 13"/>
                    <path d="M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
            </button>
        </div>
        <p class="text-center text-[10px] text-gray-400 mt-2">FEEDTAN AI can make mistakes. Consider checking important information.</p>
    </div>
</div>

<script>
let chatHistory = [];
let isLoading = false;

function addMessageToChat(role, text) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'flex gap-4 w-full max-w-3xl mx-auto';
    
    if (role === 'user') {
        messageDiv.innerHTML = `
            <div class="flex-1 flex justify-end">
                <div class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-3 rounded-2xl rounded-tr-sm max-w-[75%]">
                    <div class="whitespace-pre-wrap text-sm">${escapeHtml(text)}</div>
                </div>
            </div>
            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-xs text-gray-500"></i>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-xs text-white"></i>
            </div>
            <div class="flex-1">
                <div class="text-gray-900 dark:text-white px-4 py-3 rounded-2xl rounded-tl-sm max-w-[75%]">
                    <div class="whitespace-pre-wrap text-sm">${formatMessage(text)}</div>
                </div>
            </div>
        `;
    }
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    return messageDiv;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatMessage(text) {
    text = escapeHtml(text);
    // Handle bold
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Handle italic
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    // Handle newlines
    text = text.replace(/\n/g, '<br>');
    return text;
}

function addLoadingMessage() {
    const chatMessages = document.getElementById('chatMessages');
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'flex gap-4 w-full max-w-3xl mx-auto';
    loadingDiv.id = 'loading-message';
    loadingDiv.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-robot text-xs text-white"></i>
        </div>
        <div class="flex-1">
            <div class="px-4 py-3">
                <div class="flex space-x-1">
                    <span class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                    <span class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span>
                    <span class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0.4s;"></span>
                </div>
            </div>
        </div>
    `;
    chatMessages.appendChild(loadingDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    return loadingDiv;
}

function autoResizeTextarea() {
    const textarea = document.getElementById('chatInput');
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
}

async function sendMessage() {
    if (isLoading) return;
    
    const chatInput = document.getElementById('chatInput');
    const message = chatInput.value.trim();
    if (!message) return;
    
    isLoading = true;
    chatInput.value = '';
    autoResizeTextarea();
    
    addMessageToChat('user', message);
    chatHistory.push({role: 'user', text: message});
    
    const loadingDiv = addLoadingMessage();
    
    try {
        const response = await fetch('{{ route('dashboard.ai-chat') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                message: message,
                history: chatHistory
            })
        });
        
        const data = await response.json();
        loadingDiv.remove();
        
        if (data.success) {
            addMessageToChat('model', data.response);
            chatHistory.push({role: 'model', text: data.response});
        } else {
            addMessageToChat('model', 'Error: ' + (data.message || 'Something went wrong'));
        }
    } catch (error) {
        loadingDiv.remove();
        addMessageToChat('model', 'Error: ' + error.message);
    }
    
    isLoading = false;
}

document.addEventListener('DOMContentLoaded', function() {
    // Add welcome message
    addMessageToChat('model', 'Hi there! I\'m FEEDTAN AI, your personal assistant. How can I help you with your payments, transactions, bills, or anything else today?');
    
    const chatInput = document.getElementById('chatInput');
    
    // Auto resize textarea
    chatInput.addEventListener('input', autoResizeTextarea);
    
    // Handle enter key
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
});
</script>
@endsection
