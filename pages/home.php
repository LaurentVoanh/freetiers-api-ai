<?php
/**
 * Home/Chat Page - Main chat interface
 */
?>
<div class="chat-container">
    <!-- Chat Header -->
    <div class="chat-header">
        <h1 class="cyber-title">
            <span class="title-glow">AI Chat Gateway</span>
        </h1>
        <p class="chat-subtitle">Powered by multiple free-tier LLM providers with intelligent fallback</p>
    </div>

    <!-- Chat Messages Area -->
    <div class="chat-messages" id="chatMessages">
        <div class="welcome-message">
            <div class="welcome-icon">🤖</div>
            <h2>Welcome to FreeLLMAPI</h2>
            <p>Start chatting with AI models from multiple providers. Your requests will be automatically routed to the best available API.</p>
            <div class="feature-grid">
                <div class="feature-card">
                    <span class="feature-icon">⚡</span>
                    <h3>Fast Response</h3>
                    <p>Intelligent routing to fastest available provider</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">🔄</span>
                    <h3>Auto-Fallback</h3>
                    <p>Automatic failover if a provider is rate-limited</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">🔒</span>
                    <h3>Secure</h3>
                    <p>Your API keys are encrypted with AES-256-GCM</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">🆓</span>
                    <h3>Free Tier APIs</h3>
                    <p>Aggregating the best free tier LLM APIs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Input Area -->
    <div class="chat-input-container">
        <div class="model-selector">
            <label for="selectedModel">Model:</label>
            <select id="selectedModel" name="selectedModel">
                <option value="auto">Auto (Best Available)</option>
                <?php foreach ($models as $model): ?>
                <option value="<?php echo htmlspecialchars($model['id']); ?>">
                    <?php echo htmlspecialchars($model['display_name']); ?> 
                    (<?php echo htmlspecialchars($model['platform']); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <form class="chat-input-form" id="chatForm">
            <textarea 
                id="messageInput" 
                name="message" 
                placeholder="Type your message here..." 
                rows="3"
                required
            ></textarea>
            <button type="submit" class="send-button" id="sendButton">
                <span class="button-text">Send</span>
                <span class="button-loader" style="display: none;">⟳</span>
            </button>
        </form>
        <div class="input-hints">
            <span>Press Enter to send, Shift+Enter for new line</span>
            <span id="tokenCount">0 tokens</span>
        </div>
    </div>

    <!-- Chat Stats -->
    <div class="chat-stats">
        <div class="stat-item">
            <span class="stat-label">Requests Today:</span>
            <span class="stat-value" id="requestsToday">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Tokens Used:</span>
            <span class="stat-value" id="tokensUsed">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Avg Latency:</span>
            <span class="stat-value" id="avgLatency">0ms</span>
        </div>
    </div>
</div>

<script>
// Chat functionality
(function() {
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    const sendButton = document.getElementById('sendButton');
    const selectedModel = document.getElementById('selectedModel');
    const tokenCount = document.getElementById('tokenCount');
    
    let conversationHistory = [];
    
    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        
        // Update token count (rough estimate)
        const words = this.value.trim().split(/\s+/).filter(w => w.length > 0);
        const estimatedTokens = Math.ceil(words.length * 1.3);
        tokenCount.textContent = estimatedTokens + ' tokens';
    });
    
    // Handle form submission
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;
        
        // Add user message to chat
        addMessage(message, 'user');
        conversationHistory.push({ role: 'user', content: message });
        
        // Clear input
        messageInput.value = '';
        messageInput.style.height = 'auto';
        tokenCount.textContent = '0 tokens';
        
        // Show loading state
        setLoading(true);
        
        // Add placeholder for assistant response
        const assistantMessageDiv = addMessage('', 'assistant', true);
        
        try {
            const response = await fetch('/api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    messages: conversationHistory,
                    model_id: selectedModel.value === 'auto' ? null : parseInt(selectedModel.value),
                    stream: true
                })
            });
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Request failed');
            }
            
            // Handle streaming response
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let fullResponse = '';
            
            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                
                const chunk = decoder.decode(value);
                const lines = chunk.split('\n');
                
                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const data = line.slice(6);
                        if (data === '[DONE]') continue;
                        
                        try {
                            const parsed = JSON.parse(data);
                            const delta = parsed.choices?.[0]?.delta?.content || '';
                            if (delta) {
                                fullResponse += delta;
                                assistantMessageDiv.textContent = fullResponse;
                                chatMessages.scrollTop = chatMessages.scrollHeight;
                            }
                        } catch (e) {
                            // Ignore parse errors
                        }
                    }
                }
            }
            
            // Add to conversation history
            conversationHistory.push({ role: 'assistant', content: fullResponse });
            
        } catch (error) {
            assistantMessageDiv.textContent = 'Error: ' + error.message;
            assistantMessageDiv.classList.add('error-message');
        } finally {
            setLoading(false);
        }
    });
    
    function addMessage(content, role, isLoading = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message message-${role} ${isLoading ? 'loading' : ''}`;
        
        const avatarDiv = document.createElement('div');
        avatarDiv.className = 'message-avatar';
        avatarDiv.textContent = role === 'user' ? '👤' : '🤖';
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = content;
        
        if (isLoading) {
            contentDiv.innerHTML = '<span class="typing-indicator"><span></span><span></span><span></span></span>';
        }
        
        messageDiv.appendChild(avatarDiv);
        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        return contentDiv;
    }
    
    function setLoading(loading) {
        sendButton.disabled = loading;
        sendButton.querySelector('.button-text').style.display = loading ? 'none' : 'inline';
        sendButton.querySelector('.button-loader').style.display = loading ? 'inline' : 'none';
        messageInput.disabled = loading;
    }
    
    // Load stats
    async function loadStats() {
        try {
            const response = await fetch('/api/stats.php');
            const stats = await response.json();
            
            document.getElementById('requestsToday').textContent = stats.requests_today || 0;
            document.getElementById('tokensUsed').textContent = stats.tokens_used || 0;
            document.getElementById('avgLatency').textContent = (stats.avg_latency || 0) + 'ms';
        } catch (e) {
            console.error('Failed to load stats:', e);
        }
    }
    
    // Load stats on page load
    loadStats();
})();
</script>
