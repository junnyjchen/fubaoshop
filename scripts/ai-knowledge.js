/**
 * 符宝网 AI助手「道玄」知识库
 * 包含：符箓知识、道家文化、符宝网服务、常见问题等
 */

const AI_KnowledgeBase = {
    // 版本信息
    version: '1.0.0',
    updated: '2024-01-20',
    totalItems: 128,
    
    // 欢迎语
    welcome: {
        title: '道玄居士',
        greeting: '施主有缘相见，贫道云游四方，精通道家经典。今有何疑惑尽管道来，贫道必当倾囊相告。',
        tips: [
            '可询问各类符箓的用途与讲究',
            '可了解道家基础知识与文化',
            '可咨询符宝网商品与服务',
            '可请教请符注意事项'
        ]
    },
    
    // 符箓分类
    talismans: {
        name: '符箓百科',
        items: [
            {
                id: 'pingan',
                name: '平安符',
                icon: '☯',
                price: '¥299起',
                effects: ['护佑出入平安', '驱邪避祸', '化解小人'],
                usage: '挂于大门内侧或随身携带，保持干燥不可沾水',
                suitable: ['本命年', '犯太岁', '运势低迷', '外出频繁']
            },
            {
                id: 'caishen',
                name: '财神符',
                icon: '✦',
                price: '¥399起',
                effects: ['招财进宝', '财源广进', '守财聚财'],
                usage: '贴于财位或供奉于神龛，朝向门口',
                suitable: ['做生意', '财运不佳', '投资理财', '创业开张']
            },
            {
                id: 'wenchang',
                name: '文昌符',
                icon: '❋',
                price: '¥359起',
                effects: ['开启智慧', '学业进步', '考试顺利'],
                usage: '放于书桌或书包内，学生可随身携带',
                suitable: ['学生', '考试', '升职', '考证']
            },
            {
                id: 'taisui',
                name: '太岁符',
                icon: '☸',
                price: '¥299',
                effects: ['化解冲破害太岁', '趋吉避凶', '平安无事'],
                usage: '贴身携带，犯太岁年份必请',
                suitable: ['本命年', '属相冲太岁', '鼠/马/兔/鸡/龙/狗']
            },
            {
                id: 'heshen',
                name: '和合符',
                icon: '☯',
                price: '¥399起',
                effects: ['姻缘和合', '感情和睦', '化解争执'],
                usage: '放于枕头下或卧室',
                suitable: ['单身求姻缘', '夫妻不和', '分手复合']
            },
            {
                id: 'anquan',
                name: '安全符',
                icon: '✦',
                price: '¥299',
                effects: ['出行平安', '防止意外', '化险为夷'],
                usage: '随身携带或挂于车内',
                suitable: ['司机', '经常出行', '高危职业']
            },
            {
                id: 'jixiang',
                name: '吉祥符',
                icon: '❋',
                price: '¥259',
                effects: ['福运亨通', '诸事顺利', '吉祥如意'],
                usage: '贴于家中或随身携带',
                suitable: ['所有人', '新年开运', '搬家装修']
            },
            {
                id: 'bimian',
                name: '避邪符',
                icon: '☸',
                price: '¥359',
                effects: ['驱邪避煞', '镇宅安家', '净化气场'],
                usage: '贴于大门或住宅四角',
                suitable: ['乔迁新居', '住宅附近有医院/墓地', '感觉不安']
            },
            {
                id: 'jieming',
                name: '解噩梦符',
                icon: '☯',
                price: '¥199',
                effects: ['化解噩梦', '安神定魄', '夜夜安眠'],
                usage: '放于枕头下',
                suitable: ['失眠多梦', '经常做噩梦', '精神不安']
            },
            {
                id: 'feishi',
                name: '飞逝符',
                icon: '✦',
                price: '¥499',
                effects: ['学业进步', '金榜题名', '前途光明'],
                usage: '放于书房或书包内',
                suitable: ['中高考', '考研考公', '学业不顺']
            }
        ]
    },
    
    // 道家知识
    taoism: {
        name: '道家文化',
        basics: [
            {
                question: '道家基础理论',
                answer: `道家以"道"为核心，讲求道法自然、无为而治。

【阴阳学说】
阴阳是道家最基本的世界观：
- 阴阳对立：日与夜、热与冷、动与静
- 阴阳互根：阴中有阳，阳中有阴
- 阴阳消长：此消彼长，循环往复

【五行学说】
金、木、水、火、土五行：
- 相生：木→火→土→金→水→木
- 相克：木→土→水→火→金→木
- 五行与人：肝属木、心属火、脾属土、肺属金、肾属水

【八卦基础】
乾、坤、震、巽、坎、离、艮、兑
代表天、地、雷、风、水、火、山、泽

道家符箓正是运用这些理论，调和阴阳五行，达到祈福转运之效。`,
                tags: ['入门', '阴阳', '五行', '八卦']
            },
            {
                question: '道家三清',
                answer: `道家最高神祇为"三清"：

【元始天尊】
又称"盘古"，代表宇宙最原始的状态
居玉清境，象征"无极"

【灵宝天尊】
又称"通天教主"，代表混沌开始
居上清境，象征"太极"

【道德天尊】
即"太上老君"，代表阴阳分判
居太清境，象征"三清归一"

三清是道家最高神格体系，统领诸天神灵。`,
                tags: ['入门', '神灵', '三清']
            },
            {
                question: '什么是开光',
                answer: `开光是道家重要仪式，为法器注入灵力：

【开光条件】
- 必须由有道行的法师主持
- 需设坛焚香，诵经祈祷
- 选择吉日良时进行
- 法师需有传承法脉

【开光流程】
1. 净坛：清除不净之气
2. 上香：请神降临
3. 诵经：诵读开光经咒
4. 敕笔：法师敕令毛笔
5. 点睛：以朱砂点开七窍
6. 发牒：宣告此物已开光

【开光与加持的区别】
- 开光：注入灵魂，法器正式成为灵器
- 加持：增强能量，临时强化效力

符宝网所有符箓均经高功法师开光加持，确保灵验。`,
                tags: ['入门', '开光', '仪式']
            },
            {
                question: '如何请符',
                answer: `请符有讲究，心诚则灵：

【请符前准备】
1. 明确需求：知道需要什么符
2. 心存善念：不可有害人之心
3. 了解禁忌：知晓符箓使用规矩

【请符流程】
1. 选择正规道观或法师
2. 说明自身需求和生辰
3. 法师根据八字推荐
4. 请回后妥善保管
5. 保持恭敬之心

【请符禁忌】
❌ 心存恶念者，符不灵
❌ 符箓不可让外人随意触碰
❌ 不可用污秽之物接触
❌ 女子经期避免接触
❌ 符箓不可见血

【符箓保存】
- 纸质符：一年为佳
- 木质符：注意防潮
- 金属符：十年八年可用

符到之处，心存善念，方能灵验。`,
                tags: ['进阶', '请符', '禁忌']
            },
            {
                question: '什么是太岁',
                answer: `太岁是道教信仰中的星辰神祇：

【什么是太岁】
- 太岁是木星轨道上的神祇
- 共60位，轮流值年
- 每60年一循环
- 每年有一位太岁当值

【犯太岁】
指个人生肖与当年太岁产生冲、破、害、刑、值等关系：
- 值太岁：本人生肖即是太岁
- 冲太岁：与太岁相冲（六大冲）
- 破太岁：与太岁相破
- 害太岁：与太岁相害
- 刑太岁：与太岁相刑

【2024甲辰年犯太岁生肖】
龙（值太岁、刑太岁）
狗（冲太岁）
牛（破太岁）
羊（害太岁）
兔（刑太岁）

犯太岁者运势多舛，宜请太岁符化解。`,
                tags: ['进阶', '太岁', '犯太岁']
            },
            {
                question: '道家如何修行',
                answer: `道家修行讲究性命双修：

【修心】
- 清心寡欲
- 恬淡虚无
- 积德行善
- 慈爱万物

【修身】
- 导引吐纳
- 太极拳法
- 静坐冥想
- 药饵养生

【修命】
- 内丹修炼
- 辟谷服气
- 符咒法术
- 科仪祭祀

【日常修行】
1. 晨起焚香，诵读经典
2. 每日静坐，至少一刻钟
3. 行善积德，不求回报
4. 饮食有节，起居有常
5. 亲近自然，顺应天道

修行非一朝一夕之功，需持之以恒，方能证道成真。`,
                tags: ['进阶', '修行', '道教']
            }
        ],
        categories: [
            { name: '入门知识', desc: '道家基础理论', count: 15 },
            { name: '符箓详解', desc: '各类符箓用途', count: 28 },
            { name: '命理基础', desc: '八字五行命理', count: 22 },
            { name: '风水堪舆', desc: '阳宅阴宅风水', count: 18 },
            { name: '道教科仪', desc: '法事祭祀仪轨', count: 12 },
            { name: '道家人物', desc: '历代高道仙真', count: 8 }
        ]
    },
    
    // 符宝网服务
    fubao: {
        name: '符宝网服务',
        services: [
            {
                name: '一物一码',
                icon: '📜',
                desc: '正品认证，扫码查验',
                details: '每件商品配有唯一防伪码，可扫码查验真伪，确保您请到正品开光符箓。'
            },
            {
                name: '免费领取',
                icon: '🎁',
                desc: '新用户免费请符',
                details: '新用户可免费领取一张开光平安符，体验符宝网服务。'
            },
            {
                name: '如愿晒单',
                icon: '☸',
                desc: '分享灵验故事',
                details: '分享您的请符灵验经历，帮助更多人了解道家文化。'
            },
            {
                name: '快速下单',
                icon: '⚡',
                desc: '便捷购物体验',
                details: '简化购物流程，快速请到心仪符箓。'
            },
            {
                name: '玄门学堂',
                icon: '📚',
                desc: '道法传承教学',
                details: '学习道家基础知识，了解中华传统文化。'
            },
            {
                name: 'AI道玄',
                icon: '☯',
                desc: '智能问答助手',
                details: '24小时在线，解答您关于符箓和道家文化的疑问。'
            }
        ],
        policies: [
            { name: '正品保证', desc: '所有符箓均经正规道观开光' },
            { name: '7天无理由', desc: '未开封商品7天可退' },
            { name: '隐私保护', desc: '您的信息完全保密' },
            { name: '在线客服', desc: '24小时为您服务' }
        ]
    },
    
    // 常见问题
    faq: [
        {
            q: '符箓多久见效？',
            a: '符箓见效时间因人而异，一般来说：\n\n【短期效果】7-30天\n- 心理上会有安定感\n- 睡眠质量改善\n- 心态更加平和\n\n【中期效果】1-3个月\n- 运势开始转好\n- 贵人运增强\n- 做事更加顺利\n\n【长期效果】3-6个月\n- 整体运势明显改善\n- 事业财运均有提升\n- 家庭和睦安康\n\n关键在于心诚，以及配合自身努力。',
            tags: ['常见问题', '效果']
        },
        {
            q: '符箓失效了怎么办？',
            a: '符箓灵力会随时间和环境逐渐消散，属正常现象：\n\n【纸质符】\n- 一般有效期1年\n- 到期后请新的替换\n- 如有污损立即更换\n\n【处理旧符】\n- 不可随意丢弃\n- 建议焚烧处理\n- 焚烧时念"恭送"二字\n- 选择户外干净处焚烧\n\n【请新符】\n- 建议每年请一次\n- 重要符箓可提前请\n- 选正规渠道请购',
            tags: ['常见问题', '保养']
        },
        {
            q: '可以同时请多道符吗？',
            a: '可以同时请多道符，但有讲究：\n\n【可以同请】\n- 太岁符 + 平安符\n- 财神符 + 招财符\n- 文昌符 + 考试符\n\n【不宜同请】\n- 功效相冲的符\n- 超过3道同功效符\n\n【注意事项】\n1. 不同功能的符可同请\n2. 同功效符选最需要的\n3. 符箓间保持距离\n4. 定期清理失效符\n\n如有疑问，可咨询AI道玄。',
            tags: ['常见问题', '请符']
        },
        {
            q: '符箓可以送人吗？',
            a: '符箓作为礼物有一定讲究：\n\n【可以送】\n✅ 家人之间可互送\n✅ 夫妻情侣可互送\n✅ 父母可送子女\n\n【不宜送】\n❌ 不建议送外人\n❌ 不确定对方是否需要\n❌ 不了解对方信仰\n\n【送符注意】\n- 最好告知使用方法\n- 附带开光证书\n- 表达美好祝愿\n\n其实，最好的方式是帮对方请符，而非直接送符。',
            tags: ['常见问题', '禁忌']
        },
        {
            q: '请符需要八字吗？',
            a: '并非所有符都需要八字，但以下情况建议提供：\n\n【必须提供八字】\n- 太岁符（精准化解）\n- 化解冲煞类符\n- 风水调理类符\n\n【可选提供】\n- 个人祈福符\n- 学业事业符\n- 姻缘和合符\n\n【无需提供】\n- 通用平安符\n- 基础招财符\n- 新年吉祥符\n\n提供八字可让法师更精准地为您选符调符，效果更佳。',
            tags: ['常见问题', '请符']
        },
        {
            q: '符宝网的符为什么有效？',
            a: `符宝网符箓灵验，源于四大保障：

【一、传承正统】
符箓绘制遵循道教正统一脉，由有传承的法师绘制，非野路子可比。

【二、开光加持】
每道符必经七七四十九天开光加持，注入真正灵力，而非普通印刷品。

【三、一物一码】
每道符配有唯一防伪码，可溯源查验，确保正品。

【四、善心为本】
符宝网以弘扬道家文化为己任，以善心待人，符到之处，福泽绵长。

选择符宝网，就是选择正统、正信、正念。`,
            tags: ['常见问题', '符宝网']
        }
    ],
    
    // 关键词匹配表
    keywords: {
        '平安': ['pingan', '安全'],
        '财运': ['caishen', '招财'],
        '学业': ['wenchang', '考试', '智慧'],
        '太岁': ['taisui', '本命年', '犯太岁'],
        '姻缘': ['heshen', '感情', '桃花'],
        '健康': ['anquan', '身体', '养生'],
        '镇宅': ['bimian', '辟邪', '驱邪'],
        '开光': ['开光', '加持', '仪式'],
        '请符': ['请符', '怎么请', '如何请'],
        '效果': ['效果', '多久见效', '灵验'],
        '保存': ['保存', '有效期', '多久'],
        '禁忌': ['禁忌', '注意', '不可以']
    },
    
    // 快捷问题
    quickQuestions: [
        { text: '什么是平安符，如何使用？', category: 'talisman' },
        { text: '太岁符和化太岁有什么区别？', category: 'taoism' },
        { text: '道家的五行属什么？', category: 'taoism' },
        { text: '如何正确请符？', category: 'guide' },
        { text: '符箓一般能保存多久？', category: 'faq' },
        { text: '开光和加持有什么区别？', category: 'taoism' },
        { text: '什么是犯太岁？', category: 'taoism' },
        { text: '符宝网的符为什么有效？', category: 'fubao' }
    ],
    
    // 回复模板
    templates: {
        greeting: '施主有缘相见，贫道云游四方，精通道家经典。今有何疑惑尽管道来，贫道必当倾囊相告。',
        notUnderstand: '施主所问，贫道还需细思。能否请施主换个说法，或直接说明所求何事？贫道定当尽力解答。',
        endChat: '今日相见甚欢，若有疑惑随时可问。愿施主诸事顺遂，福寿安康。☯ 道玄告辞。'
    }
};

// 知识库检索函数
function searchKnowledge(query) {
    query = query.toLowerCase().trim();
    
    // 1. 精确匹配符箓名称
    for (const talisman of AI_KnowledgeBase.talismans.items) {
        if (query.includes(talisman.name) || query.includes(talisman.id)) {
            return {
                type: 'talisman',
                data: talisman
            };
        }
    }
    
    // 2. 匹配道家知识
    for (const item of AI_KnowledgeBase.taoism.basics) {
        if (item.tags.some(tag => query.includes(tag))) {
            return {
                type: 'taoism',
                data: item
            };
        }
    }
    
    // 3. 匹配FAQ
    for (const item of AI_KnowledgeBase.faq) {
        if (item.tags.some(tag => query.includes(tag))) {
            return {
                type: 'faq',
                data: item
            };
        }
    }
    
    // 4. 匹配关键词
    for (const [key, values] of Object.entries(AI_KnowledgeBase.keywords)) {
        if (query.includes(key)) {
            const talismanId = values[0];
            const talisman = AI_KnowledgeBase.talismans.items.find(t => t.id === talismanId);
            if (talisman) {
                return {
                    type: 'talisman',
                    data: talisman
                };
            }
        }
    }
    
    // 5. 匹配服务
    for (const service of AI_KnowledgeBase.fubao.services) {
        if (query.includes(service.name)) {
            return {
                type: 'service',
                data: service
            };
        }
    }
    
    return null;
}

// 获取回复内容
function getResponse(query) {
    const result = searchKnowledge(query);
    
    if (!result) {
        // 尝试模糊匹配
        if (query.includes('?') || query.includes('吗') || query.includes('怎么') || query.includes('如何')) {
            return AI_KnowledgeBase.templates.notUnderstand;
        }
        return null;
    }
    
    switch (result.type) {
        case 'talisman':
            return formatTalismanResponse(result.data);
        case 'taoism':
            return result.data.answer;
        case 'faq':
            return result.data.a;
        case 'service':
            return `${result.data.name}（${result.data.icon}）\n\n${result.data.details}`;
        default:
            return null;
    }
}

// 格式化符箓回复
function formatTalismanResponse(talisman) {
    return `${talisman.icon} ${talisman.name}\n\n`
        + `【功效】\n${talisman.effects.map(e => '• ' + e).join('\n')}\n\n`
        + `【使用方法】\n${talisman.usage}\n\n`
        + `【适合人群】\n${talisman.suitable.map(s => '• ' + s).join('\n')}\n\n`
        + `【参考价格】${talisman.price}\n\n`
        + `如需请购，请访问符宝网官网，或联系AI道玄。`;
}

// 导出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { AI_KnowledgeBase, searchKnowledge, getResponse, formatTalismanResponse };
}
