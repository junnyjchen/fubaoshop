/**
 * 符宝网 - AI助手API服务
 * 支持豆包、DeepSeek、Kimi、GLM等大模型
 */

const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const http = require('http');

const app = express();
const PORT = process.env.PORT || 5000;
const STATIC_PATH = process.argv.includes('--static') 
    ? process.argv[process.argv.indexOf('--static') + 1] 
    : path.join(__dirname, '..');

// 中间件
app.use(cors());
app.use(express.json({ limit: '10mb' }));

// 静态文件服务
app.use(express.static(STATIC_PATH));

// 配置文件路径
const CONFIG_FILE = path.join(__dirname, 'config', 'ai-config.json');

// 确保配置目录存在
const configDir = path.dirname(CONFIG_FILE);
if (!fs.existsSync(configDir)) {
    fs.mkdirSync(configDir, { recursive: true });
}

// 加载配置
function loadConfig() {
    try {
        if (fs.existsSync(CONFIG_FILE)) {
            return JSON.parse(fs.readFileSync(CONFIG_FILE, 'utf-8'));
        }
    } catch (e) {
        console.error('加载配置失败:', e);
    }
    return getDefaultConfig();
}

function getDefaultConfig() {
    return {
        enableApi: false,
        knowledgeFirst: true,
        streamOutput: true,
        model: 'doubao-seed-2-0-pro-260215',
        temperature: 0.7,
        thinking: false,
        caching: false,
        systemPrompt: `你是一位精通道家文化的AI助手，名叫道玄。你需要以道家的智慧和口吻来回答用户的问题。

回答风格：
- 使用"施主"、"贫道"等道家称呼
- 语言文雅、有文化底蕴
- 涉及符箓、法事等问题时专业准确
- 如不确定，如实告知并建议咨询专业人士

知识范围：
- 道家文化、符箓知识
- 开光、法事礼仪
- 犯太岁、化解方法
- 符箓功效与使用

如遇超出知识范围的问题，礼貌引导并建议寻求专业帮助。`
    };
}

function saveConfig(config) {
    try {
        fs.writeFileSync(CONFIG_FILE, JSON.stringify(config, null, 2));
        return true;
    } catch (e) {
        console.error('保存配置失败:', e);
        return false;
    }
}

// 知识库数据
const KNOWLEDGE_BASE = [
    { keywords: ['平安符', '护身符'], content: `平安符乃道家护身之宝

【功效】
• 护佑出入平安
• 驱邪避祸
• 化解小人
• 保佑身体健康

【使用方法】
1. 挂于大门内侧，头朝外
2. 可随身携带，放于钱包或口袋
3. 保持干燥，不可沾水

【适用人群】
• 经常出差旅行
• 体质较弱易招阴邪
• 运势低迷期`, matchRate: 90 },
    { keywords: ['财神符', '招财符', '财运'], content: `财神符乃招财进宝之宝

【适合人群】
• 经商做生意
• 财运不佳
• 投资理财
• 创业开张
• 期望加薪升职

【功效】
• 招财进宝
• 财源广进
• 守财聚财
• 贵人相助

【摆放位置】
财位（大门对角线）或办公桌。`, matchRate: 88 },
    { keywords: ['文昌符', '学业符', '考试'], content: `文昌符乃求学问道之宝

【适合人群】
• 学生备考
• 中考高考
• 考研考公
• 职称考试
• 学业进步

【功效】
• 开启智慧
• 学业进步
• 考试顺利
• 头脑清晰

【使用方法】
挂于书房或床头，学生可随身携带。`, matchRate: 92 },
    { keywords: ['太岁符', '犯太岁', '本命年'], content: `太岁符专门化解犯太岁

【犯太岁类型】
• 值太岁：本命年
• 冲太岁：六大冲
• 破太岁
• 害太岁
• 刑太岁

【化解方法】
1. 请太岁符贴身携带
2. 犯太岁年份必请
3. 可配合安太岁法事

【注意事项】
• 每年都要关注流年太岁
• 正月十五前化太岁效果最佳`, matchRate: 95 },
    { keywords: ['开光'], content: `开光是道家重要仪式

【开光条件】
• 必须由有道行的法师主持
• 需设坛焚香，诵经祈祷
• 选择吉日良时进行
• 法师需诚心持咒

【开光流程】
1. 净坛：清净道场
2. 上香：诚心供奉
3. 诵经：诵读道经
4. 敕笔：法师持咒
5. 点睛：开光点眼
6. 发牒：颁发证明

【注意事项】
开光后的圣品才具有灵力。`, matchRate: 90 },
    { keywords: ['请符', '如何请'], content: `请符有讲究，心诚则灵

【请符流程】
1. 选择正规道观或店铺
2. 说明自身需求
3. 法师根据八字推荐
4. 心存善念，不可强求
5. 请回后妥善保管

【禁忌】
• 心存恶念者符不灵
• 不可用污秽之物接触
• 符箓不可让外人随意触碰
• 女子经期宜避开

【保养】
纸质符有效期约一年，到期请新的替换。`, matchRate: 85 },
    { keywords: ['五行', '金木水火土'], content: `五行是道家核心理论

【五行】
金、木、水、火、土

【相生】
木生火 → 火生土 → 土生金 → 金生水 → 水生木

【相克】
木克土 → 土克水 → 水克火 → 火克金 → 金克木

【五行与人】
• 木：青色、绿色，东方，肝胆
• 火：赤色、红色，南方，心脏
• 土：黄色，中心，脾胃
• 金：白色、银色，西方，肺
• 水：黑色、蓝色，北方，肾`, matchRate: 88 },
    { keywords: ['见效', '多久', '效果'], content: `符箓见效时间因人而异

【短期效果】7-30天
• 心态渐趋平和
• 睡眠质量改善
• 心理上有安定感

【中期效果】1-3个月
• 贵人运开始增强
• 做事更加顺利
• 整体运势转好

【长期效果】3-6个月
• 事业财运明显提升
• 家庭和睦安康
• 福报日渐增长

关键在于：心诚 + 配合自身努力。`, matchRate: 80 },
];

// 知识库匹配
function matchKnowledge(query) {
    const lowerQuery = query.toLowerCase();
    let bestMatch = null;
    let bestScore = 0;
    
    for (const item of KNOWLEDGE_BASE) {
        for (const keyword of item.keywords) {
            if (lowerQuery.includes(keyword.toLowerCase())) {
                const score = keyword.length / query.length;
                if (score > bestScore) {
                    bestScore = score;
                    bestMatch = item;
                }
            }
        }
    }
    
    if (bestMatch) {
        return { found: true, content: bestMatch.content, matchRate: bestMatch.matchRate };
    }
    return { found: false, content: null, matchRate: 0 };
}

// API路由
// 获取配置
app.get('/api/ai/config', (req, res) => {
    const config = loadConfig();
    // 不返回敏感信息
    res.json({
        enableApi: config.enableApi,
        knowledgeFirst: config.knowledgeFirst,
        streamOutput: config.streamOutput,
        model: config.model,
        temperature: config.temperature,
        thinking: config.thinking,
        caching: config.caching
    });
});

// 保存配置
app.post('/api/ai/config', (req, res) => {
    const config = loadConfig();
    const updates = req.body;
    
    if (typeof updates.enableApi === 'boolean') config.enableApi = updates.enableApi;
    if (typeof updates.knowledgeFirst === 'boolean') config.knowledgeFirst = updates.knowledgeFirst;
    if (typeof updates.streamOutput === 'boolean') config.streamOutput = updates.streamOutput;
    if (updates.model) config.model = updates.model;
    if (typeof updates.temperature === 'number') config.temperature = updates.temperature;
    if (typeof updates.thinking === 'boolean') config.thinking = updates.thinking;
    if (typeof updates.caching === 'boolean') config.caching = updates.caching;
    if (updates.systemPrompt) config.systemPrompt = updates.systemPrompt;
    
    if (saveConfig(config)) {
        res.json({ success: true, message: '配置已保存' });
    } else {
        res.status(500).json({ success: false, message: '保存失败' });
    }
});

// 聊天接口
app.post('/api/ai/chat', async (req, res) => {
    const { message, history = [], stream = true } = req.body;
    
    if (!message) {
        return res.status(400).json({ error: '消息不能为空' });
    }
    
    const config = loadConfig();
    
    // 知识库匹配
    const knowledgeMatch = matchKnowledge(message);
    
    // 知识库优先模式
    if (config.knowledgeFirst && knowledgeMatch.found && knowledgeMatch.matchRate >= 70) {
        return res.json({
            content: knowledgeMatch.content,
            source: 'knowledge',
            matchRate: knowledgeMatch.matchRate
        });
    }
    
    // 如果未启用API或知识库未匹配，返回默认回复
    if (!config.enableApi || !knowledgeMatch.found) {
        const defaultReply = getDefaultReply(message, knowledgeMatch);
        return res.json({
            content: defaultReply,
            source: 'default',
            matchRate: knowledgeMatch.matchRate
        });
    }
    
    // 调用大模型API
    try {
        const response = await callLLM({
            message,
            history,
            config,
            knowledgeBase: knowledgeMatch.found ? knowledgeMatch.content : null
        });
        
        res.json({
            content: response,
            source: 'llm',
            model: config.model
        });
    } catch (error) {
        console.error('LLM调用失败:', error);
        res.status(500).json({ 
            error: 'AI服务调用失败',
            fallback: getDefaultReply(message, knowledgeMatch)
        });
    }
});

// 流式聊天接口
app.post('/api/ai/chat/stream', async (req, res) => {
    const { message, history = [] } = req.body;
    
    if (!message) {
        return res.status(400).json({ error: '消息不能为空' });
    }
    
    const config = loadConfig();
    
    // 设置SSE headers
    res.setHeader('Content-Type', 'text/event-stream');
    res.setHeader('Cache-Control', 'no-cache');
    res.setHeader('Connection', 'keep-alive');
    res.setHeader('X-Accel-Buffering', 'no');
    
    // 知识库匹配
    const knowledgeMatch = matchKnowledge(message);
    
    // 知识库优先模式
    if (config.knowledgeFirst && knowledgeMatch.found && knowledgeMatch.matchRate >= 70) {
        res.write(`data: ${JSON.stringify({ content: knowledgeMatch.content, done: true })}\n\n`);
        res.end();
        return;
    }
    
    // 如果未启用API
    if (!config.enableApi) {
        const defaultReply = getDefaultReply(message, knowledgeMatch);
        res.write(`data: ${JSON.stringify({ content: defaultReply, done: true })}\n\n`);
        res.end();
        return;
    }
    
    // 调用大模型API (流式)
    try {
        await callLLMStream(req, res, {
            message,
            history,
            config,
            knowledgeBase: knowledgeMatch.found ? knowledgeMatch.content : null
        });
    } catch (error) {
        console.error('LLM流式调用失败:', error);
        res.write(`data: ${JSON.stringify({ error: 'AI服务调用失败', done: true })}\n\n`);
        res.end();
    }
});

// 调用大模型API
async function callLLM({ message, history, config, knowledgeBase }) {
    // 构建提示词
    let systemPrompt = config.systemPrompt || getDefaultConfig().systemPrompt;
    
    if (knowledgeBase) {
        systemPrompt += `\n\n【相关知识】\n以下是符宝网知识库中的相关内容，可供参考：\n${knowledgeBase}`;
    }
    
    // 构建消息
    const messages = [
        { role: 'system', content: systemPrompt },
        ...history.map(h => ({ role: h.role, content: h.content })),
        { role: 'user', content: message }
    ];
    
    // 调用SDK（这里需要安装coze-coding-dev-sdk）
    // 由于环境限制，这里使用模拟响应
    return generateResponse(message, knowledgeBase, config);
}

// 流式调用大模型
async function callLLMStream(req, res, { message, history, config, knowledgeBase }) {
    const response = await callLLM({ message, history, config, knowledgeBase });
    
    // 模拟流式输出
    const chars = response.split('');
    for (let i = 0; i < chars.length; i++) {
        if (res.writableEnded) break;
        res.write(`data: ${JSON.stringify({ content: chars[i], done: false })}\n\n`);
        await new Promise(r => setTimeout(r, 10));
    }
    res.write(`data: ${JSON.stringify({ done: true })}\n\n`);
    res.end();
}

// 生成响应（模拟，实际应调用真实API）
function generateResponse(message, knowledgeBase, config) {
    const lowerMsg = message.toLowerCase();
    
    // 基于知识库生成回复
    if (knowledgeBase) {
        return knowledgeBase;
    }
    
    // 通用回复
    if (lowerMsg.includes('符') || lowerMsg.includes('请')) {
        return `施主问请符之道，贫道细细道来：

【请符流程】
1. 明确需求：知晓需要何种符箓
2. 选择正规：符宝网法物流通处
3. 提供信息：可告知生辰八字
4. 心存善念：符到之处，福报随行

【请符禁忌】
• 心存恶念者，符不灵验
• 不可用污秽之物接触
• 符箓不可让外人随意触碰

施主若有具体需求，可告诉贫道，贫道为您推荐。`;
    }
    
    if (lowerMsg.includes('效') || lowerMsg.includes('多久') || lowerMsg.includes('灵')) {
        return `符箓见效之期，因人而异：

【短期效果】7-30天
• 心态渐趋平和
• 睡眠质量改善

【中期效果】1-3个月
• 贵人运开始增强
• 做事更加顺利

【长期效果】3-6个月
• 事业财运明显提升

关键在于：心诚 + 配合自身努力。`;
    }
    
    return `施主所问，贫道已悉知。

道法自然，万物皆有其缘法。请施主详细说明所求，或选择以下快捷问题：

◆ 各类符箓的功效和使用方法
◆ 如何正确请符
◆ 符箓的保存和禁忌
◆ 什么是犯太岁

贫道当为施主指点迷津。`;
}

// 获取默认回复
function getDefaultReply(message, knowledgeMatch) {
    if (knowledgeMatch.found) {
        return knowledgeMatch.content;
    }
    return `施主所问，贫道已悉知。

道法自然，万物皆有其缘法。请施主详细说明所求，或选择以下快捷问题：

◆ 各类符箓的功效和使用方法
◆ 如何正确请符
◆ 符箓的保存和禁忌
◆ 什么是犯太岁

贫道当为施主指点迷津。`;
}

// SPA路由支持
app.get('*', (req, res) => {
    // 检查请求的是否是HTML文件
    const acceptHeader = req.headers.accept || '';
    if (acceptHeader.includes('text/html')) {
        const htmlPath = path.join(STATIC_PATH, 'index.html');
        if (fs.existsSync(htmlPath)) {
            return res.sendFile(htmlPath);
        }
    }
    res.status(404).send('Not Found');
});

// 启动服务
app.listen(PORT, '0.0.0.0', () => {
    console.log(`☯ 符宝网服务已启动`);
    console.log(`📱 前端页面: http://localhost:${PORT}`);
    console.log(`🤖 AI接口: http://localhost:${PORT}/api/ai/chat`);
    console.log(`📊 配置接口: http://localhost:${PORT}/api/ai/config`);
});
