/**
 * AI助手JavaScript
 * 符宝网
 */

// 全局变量
var isTyping = false;
var chatHistory = [];

/**
 * 初始化
 */
function initAI() {
    // 加载历史记录
    loadChatHistory();
    
    // 加载配置
    loadConfig();
    
    // 渲染历史消息
    renderHistory();
    
    // 自动调整输入框高度
    var input = document.getElementById('inputField');
    autoResize(input);
}

/**
 * 加载配置
 */
function loadConfig() {
    fetch('/api/ai/chat/config')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.code === 0 && data.data) {
                updateStatus(data.data);
            }
        })
        .catch(function() {
            console.log('使用默认配置');
        });
}

/**
 * 更新状态显示
 */
function updateStatus(config) {
    var statusText = document.getElementById('aiStatusText');
    if (config.enable_api) {
        var modelName = config.model ? config.model.split('-')[0] : 'AI';
        statusText.textContent = 'AI在线 · ' + modelName;
    } else {
        statusText.textContent = '知识库模式';
    }
}

/**
 * 加载聊天历史
 */
function loadChatHistory() {
    var saved = localStorage.getItem('aiChatHistory');
    if (saved) {
        chatHistory = JSON.parse(saved);
    }
}

/**
 * 保存聊天历史
 */
function saveChatHistory() {
    // 保持最近50条
    if (chatHistory.length > 50) {
        chatHistory = chatHistory.slice(-50);
    }
    localStorage.setItem('aiChatHistory', JSON.stringify(chatHistory));
}

/**
 * 渲染历史消息
 */
function renderHistory() {
    if (chatHistory.length === 0) return;
    
    var messages = document.getElementById('messages');
    var welcome = document.getElementById('welcomeSection');
    if (welcome) welcome.style.display = 'none';
    
    chatHistory.forEach(function(msg) {
        appendMessage(msg.role, msg.content, formatTime(new Date(msg.time)));
    });
}

/**
 * 发送消息
 */
function sendMessage() {
    var input = document.getElementById('inputField');
    var message = input.value.trim();
    if (!message || isTyping) return;
    
    // 隐藏欢迎区域
    var welcome = document.getElementById('welcomeSection');
    if (welcome) welcome.style.display = 'none';
    
    // 添加用户消息
    addUserMessage(message);
    input.value = '';
    autoResize(input);
    
    // 保存到历史
    saveToHistory('user', message);
    
    // 发送请求
    isTyping = true;
    showTyping();
    
    fetch('/api/ai/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: message })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        hideTyping();
        
        if (data.code === 0 && data.data) {
            addAIMessage(data.data.content, data.data.match_rate);
            saveToHistory('ai', data.data.content);
        } else {
            addAIMessage('抱歉，贫道暂时无法回复此问题。', 0);
        }
        
        isTyping = false;
    })
    .catch(function(err) {
        hideTyping();
        addAIMessage('抱歉，贫道暂时无法回复此问题。', 0);
        isTyping = false;
        console.error('请求错误:', err);
    });
}

/**
 * 发送快捷问题
 */
function sendQuickQuestion(question) {
    var input = document.getElementById('inputField');
    input.value = question;
    sendMessage();
}

/**
 * 添加用户消息
 */
function addUserMessage(text) {
    var time = formatTime(new Date());
    appendMessage('user', text, time);
}

/**
 * 添加AI消息
 */
function addAIMessage(text, matchRate) {
    var time = formatTime(new Date());
    appendMessage('ai', text, time);
}

/**
 * 追加消息到列表
 */
function appendMessage(role, text, time) {
    var messages = document.getElementById('messages');
    
    var html = '<div class="ai-message ' + role + '">';
    html += '<div class="ai-message-avatar">' + (role === 'ai' ? '☯' : '👤') + '</div>';
    html += '<div class="ai-message-content">';
    html += '<div class="ai-message-bubble">' + escapeHtml(text) + '</div>';
    html += '<div class="ai-message-time">' + time + '</div>';
    html += '</div></div>';
    
    messages.insertAdjacentHTML('beforeend', html);
    scrollToBottom();
}

/**
 * 保存到历史
 */
function saveToHistory(role, content) {
    chatHistory.push({
        role: role,
        content: content,
        time: Date.now()
    });
    saveChatHistory();
}

/**
 * 显示打字中
 */
function showTyping() {
    var messages = document.getElementById('messages');
    var html = '<div class="ai-message ai" id="typingIndicator">';
    html += '<div class="ai-message-avatar">☯</div>';
    html += '<div class="ai-message-content">';
    html += '<div class="ai-typing"><div class="ai-typing-dot"></div><div class="ai-typing-dot"></div><div class="ai-typing-dot"></div></div>';
    html += '</div></div>';
    messages.insertAdjacentHTML('beforeend', html);
    scrollToBottom();
}

/**
 * 隐藏打字中
 */
function hideTyping() {
    var typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
}

/**
 * 滚动到底部
 */
function scrollToBottom() {
    var messages = document.getElementById('messages');
    messages.scrollTop = messages.scrollHeight;
}

/**
 * 自动调整输入框高度
 */
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 100) + 'px';
}

/**
 * 处理按键
 */
function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

/**
 * 切换菜单
 */
function toggleMenu() {
    var mask = document.getElementById('menuMask');
    var panel = document.getElementById('menuPanel');
    
    if (mask && panel) {
        mask.classList.toggle('show');
        panel.classList.toggle('show');
    }
}

/**
 * 清空对话
 */
function clearChat() {
    if (!confirm('确定要清空所有对话记录吗？')) return;
    
    chatHistory = [];
    localStorage.removeItem('aiChatHistory');
    
    var messages = document.getElementById('messages');
    messages.innerHTML = '';
    
    var welcome = document.getElementById('welcomeSection');
    if (welcome) welcome.style.display = 'block';
    
    toggleMenu();
    showToast('对话已清空');
}

/**
 * 导出对话
 */
function exportChat() {
    if (chatHistory.length === 0) {
        showToast('暂无对话记录');
        return;
    }
    
    var content = '符宝网AI道玄对话记录\n';
    content += '导出时间: ' + formatDate(new Date()) + '\n\n';
    
    chatHistory.forEach(function(msg) {
        var role = msg.role === 'ai' ? '道玄' : '施主';
        content += '【' + role + '】' + formatTime(new Date(msg.time)) + '\n';
        content += msg.content + '\n\n';
    });
    
    var blob = new Blob([content], { type: 'text/plain' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'ai-chat-' + Date.now() + '.txt';
    a.click();
    URL.revokeObjectURL(url);
    
    toggleMenu();
    showToast('对话已导出');
}

/**
 * 格式化时间
 */
function formatTime(date) {
    var hours = date.getHours().toString().padStart(2, '0');
    var minutes = date.getMinutes().toString().padStart(2, '0');
    return hours + ':' + minutes;
}

/**
 * 格式化日期
 */
function formatDate(date) {
    var year = date.getFullYear();
    var month = (date.getMonth() + 1).toString().padStart(2, '0');
    var day = date.getDate().toString().padStart(2, '0');
    return year + '-' + month + '-' + day + ' ' + formatTime(date);
}

/**
 * HTML转义
 */
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * 显示提示
 */
function showToast(msg) {
    var toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(function() {
        toast.classList.remove('show');
    }, 2000);
}

/**
 * 返回
 */
function goBack() {
    window.history.back();
}

/**
 * 页面跳转
 */
function goPage(url) {
    window.location.href = url;
}
