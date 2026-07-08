@extends('layouts.app')

@section('title', 'AI Chat')

@section('content')
<div class="h-[calc(100vh-10rem)] flex flex-col">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="text-xl font-bold text-primary-900 dark:text-white">AI Assistant</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Powered by Google Gemini</p>
        </div>
    </div>
    
    <div class="card flex-1 flex flex-col overflow-hidden">
        <!-- Chat Messages Container -->
        <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4"></div>
        
        <!-- Chat Input -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-4">
            <div class="flex gap-3">
                <input 
                    type="text" 
                    id="chatInput" 
                    placeholder="Ask me anything..." 
                    class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                <button 
                    id="sendBtn" 
                    onclick="sendMessage()"
                    class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20"
                >
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let chatHistory = [];

function addMessageToChat(role, text) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    
    if (role === 'user') {
        messageDiv.className = 'flex justify-end';
        messageDiv.innerHTML = `
            <div class="bg-primary-100 dark:bg-primary-900/30 text-primary-900 dark:text-white p-4 rounded-xl rounded-tr-sm max-w-[80%]">
                <div class="text-sm whitespace-pre-wrap">${text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>')}</div>
            </div>
        `;
    } else {
        messageDiv.className = 'flex justify-start';
        messageDiv.innerHTML = `
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white p-4 rounded-xl rounded-tl-sm max-w-[80%]">
                <div class="text-sm whitespace-pre-wrap">${text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>')}</div>
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
    loadingDiv.className = 'flex justify-start';
    loadingDiv.id = 'loading-message';
    loadingDiv.innerHTML = `
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white p-4 rounded-xl rounded-tl-sm">
            <div class="flex space-x-1">
                <span class="w-2 h-2 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                <span class="w-2 h-2 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span>
                <span class="w-2 h-2 bg-primary-600 rounded-full animate-bounce" style="animation-delay: 0.4s;"></span>
            </div>
        </div>
    `;
    chatMessages.appendChild(loadingDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    return loadingDiv;
}

async function sendMessage() {
    const chatInput = document.getElementById('chatInput');
    const message = chatInput.value.trim();
    if (!message) return;
    
    chatInput.value = '';
    
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
}

document.addEventListener('DOMContentLoaded', function() {
    // Add welcome message
    addMessageToChat('model', 'Hi there! How can I help you with your payments and transactions today?');
    
    // Handle enter key
    const chatInput = document.getElementById('chatInput');
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});
</script>
@endsection
