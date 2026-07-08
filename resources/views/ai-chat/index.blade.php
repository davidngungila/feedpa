@extends('layouts.app')

@section('title', 'AI Chat')

@section('content')
<div class="h-[calc(100vh-7rem)] flex flex-col">
    <!-- Header -->
    <div class="card mb-4 p-4 border-l-4 border-l-primary-500">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-robot text-xl text-white"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-primary-900 dark:text-white">AI Assistant</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Powered by Google Gemini • Always ready to help</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Online</span>
            </div>
        </div>
    </div>
    
    <!-- Chat Container -->
    <div class="card flex-1 flex flex-col overflow-hidden shadow-xl">
        <!-- Chat Messages -->
        <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-6 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950"></div>
        
        <!-- Input Area -->
        <div class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <form id="chatForm" class="flex gap-4 items-end">
                <div class="flex-1">
                    <textarea 
                        id="chatInput" 
                        placeholder="Ask about payments, transactions, or anything else..." 
                        rows="1"
                        class="w-full px-5 py-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none text-sm"
                        onkeydown="handleKeyDown(event)"
                        oninput="autoResize(this)"
                    ></textarea>
                </div>
                <button 
                    id="sendBtn" 
                    type="submit"
                    class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-2xl hover:shadow-xl transition-all shadow-lg shadow-primary-900/20 flex items-center gap-2 hover:scale-105 active:scale-95"
                >
                    <span class="hidden sm:inline">Send</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
let chatHistory = [];

function formatMessage(text) {
    // Escape HTML first
    let formatted = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    
    // Format bold
    formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong class="text-primary-700 dark:text-primary-300">$1</strong>');
    
    // Format lists
    formatted = formatted.replace(/^- (.*)$/gm, '<li class="ml-4">$1</li>');
    
    // Preserve line breaks
    formatted = formatted.replace(/\n/g, '<br>');
    
    return formatted;
}

function addMessageToChat(role, text) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    
    if (role === 'user') {
        messageDiv.className = 'flex justify-end gap-3';
        messageDiv.innerHTML = `
            <div class="flex flex-col items-end max-w-[80%]">
                <div class="bg-gradient-to-br from-primary-500 to-primary-600 text-white p-5 rounded-2xl rounded-br-md shadow-lg">
                    <div class="text-sm leading-relaxed whitespace-pre-wrap">${text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>')}</div>
                </div>
                <span class="text-[10px] text-gray-400 mt-1">You</span>
            </div>
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                <i class="fas fa-user text-white text-sm"></i>
            </div>
        `;
    } else {
        messageDiv.className = 'flex justify-start gap-3';
        messageDiv.innerHTML = `
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                <i class="fas fa-robot text-white text-sm"></i>
            </div>
            <div class="flex flex-col items-start max-w-[80%]">
                <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-gray-800 dark:text-gray-200 p-5 rounded-2xl rounded-bl-md shadow-lg">
                    <div class="text-sm leading-relaxed whitespace-pre-wrap">${formatMessage(text)}</div>
                </div>
                <span class="text-[10px] text-gray-400 mt-1">AI Assistant</span>
            </div>
        `;
    }
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    return messageDiv;
}

function addLoadingMessage() {
    const chatMessages = document.getElementById('chatMessages');
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'flex justify-start gap-3';
    loadingDiv.id = 'loading-message';
    loadingDiv.innerHTML = `
        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
            <i class="fas fa-robot text-white text-sm"></i>
        </div>
        <div class="flex flex-col items-start">
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-5 rounded-2xl rounded-bl-md shadow-lg">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                    <span class="w-2.5 h-2.5 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span>
                    <span class="w-2.5 h-2.5 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0.4s;"></span>
                </div>
            </div>
            <span class="text-[10px] text-gray-400 mt-1">AI Assistant</span>
        </div>
    `;
    chatMessages.appendChild(loadingDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    return loadingDiv;
}

function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
}

function handleKeyDown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

async function sendMessage() {
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const message = chatInput.value.trim();
    if (!message) return;
    
    // Clear and reset input
    chatInput.value = '';
    chatInput.style.height = 'auto';
    
    // Disable input while sending
    chatInput.disabled = true;
    sendBtn.disabled = true;
    sendBtn.classList.add('opacity-50', 'cursor-not-allowed');
    
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
            addMessageToChat('model', '⚠️ Error: ' + (data.message || 'Something went wrong'));
        }
    } catch (error) {
        loadingDiv.remove();
        addMessageToChat('model', '⚠️ Error: ' + error.message);
    } finally {
        // Re-enable input
        chatInput.disabled = false;
        sendBtn.disabled = false;
        sendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        chatInput.focus();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Add welcome message
    addMessageToChat('model', 'Hi there! I\'m your AI assistant for the Feedtan payment system. I can help you with:\n\n• Transaction inquiries\n• Payment status updates\n• Account balance information\n• Customer details\n• And much more!\n\nHow can I help you today?');
    
    // Handle form submission
    const chatForm = document.getElementById('chatForm');
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
});
</script>
@endsection
