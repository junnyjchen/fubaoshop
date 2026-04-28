<?php
/**
 * AI助手服务层
 * 符宝网
 */

namespace app\index\service;

use think\facade\Cache;
use think\facade\Db;

class AIAssistantService
{
    // 配置缓存键
    const CONFIG_CACHE_KEY = 'ai_chat_config';
    const CONFIG_CACHE_TIME = 86400;
    
    // 知识库缓存键
    const KNOWLEDGE_CACHE_KEY = 'ai_knowledge_list';
    
    /**
     * 获取配置
     */
    public function getConfig()
    {
        $cache = Cache::get(self::CONFIG_CACHE_KEY);
        if ($cache) {
            return $cache;
        }
        
        // 从数据库读取
        try {
            $config = Db::name('ai_chat_config')->find(1);
            if ($config) {
                $result = [
                    'enable_api' => (bool)$config['is_enable'],
                    'knowledge_first' => (bool)($config['knowledge_first'] ?? true),
                    'stream_response' => (bool)($config['stream_response'] ?? true),
                    'model' => !empty($config['model']) ? $config['model'] : 'doubao-seed-2-0-pro-260215',
                    'temperature' => isset($config['temperature']) ? floatval($config['temperature']) : 0.7,
                    'top_p' => isset($config['top_p']) ? intval($config['top_p']) : 80,
                    'max_tokens' => isset($config['max_tokens']) ? intval($config['max_tokens']) : 2048,
                    'enable_thinking' => (bool)($config['enable_thinking'] ?? false),
                    'show_thinking' => (bool)($config['show_thinking'] ?? false),
                    'system_prompt' => $config['system_prompt'] ?? $this->getDefaultSystemPrompt(),
                    'welcome' => $config['welcome'] ?? '施主好，贫道道玄，有何疑惑尽管道来。',
                    'quick_questions' => $config['quick_questions'] ?? ''
                ];
            } else {
                $result = $this->getDefaultConfig();
            }
        } catch (\Exception $e) {
            $result = $this->getDefaultConfig();
        }
        
        Cache::set(self::CONFIG_CACHE_KEY, $result, self::CONFIG_CACHE_TIME);
        return $result;
    }
    
    /**
     * 默认配置
     */
    public function getDefaultConfig()
    {
        return [
            'enable_api' => false,
            'knowledge_first' => true,
            'stream_response' => true,
            'model' => 'doubao-seed-2-0-pro-260215',
            'temperature' => 10,
            'top_p' => 80,
            'max_tokens' => 2048,
            'enable_thinking' => false,
            'show_thinking' => false,
            'system_prompt' => $this->getDefaultSystemPrompt(),
            'welcome' => '施主好，贫道道玄，有何疑惑尽管道来。',
            'quick_questions' => "什么是符箓？\n如何请购开光符？\n犯太岁如何化解？"
        ];
    }
    
    /**
     * 默认系统提示词
     */
    public function getDefaultSystemPrompt()
    {
        return <<<'TEXT'
你是一位精通道家文化的AI助手，名叫道玄。你需要以道家的智慧和口吻来回答用户的问题。

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

如遇超出知识范围的问题，礼貌引导并建议寻求专业帮助。
TEXT;
    }
    
    /**
     * 保存配置
     */
    public function saveConfig($data)
    {
        $saveData = [
            'is_enable' => isset($data['enable_api']) ? 1 : 0,
            'temperature' => isset($data['temperature']) ? floatval($data['temperature']) : 0.7,
            'upd_time' => time()
        ];
        
        // 处理checkbox字段
        $saveData['knowledge_first'] = isset($data['knowledge_first']) ? 1 : 0;
        $saveData['stream_response'] = isset($data['stream_response']) ? 1 : 0;
        $saveData['enable_thinking'] = isset($data['enable_thinking']) ? 1 : 0;
        $saveData['show_thinking'] = isset($data['show_thinking']) ? 1 : 0;
        
        // 处理模型选择
        if (isset($data['model']) && !empty($data['model'])) {
            $saveData['model'] = trim($data['model']);
        }
        
        // 处理数值字段
        if (isset($data['top_p'])) {
            $saveData['top_p'] = intval($data['top_p']);
        }
        
        if (isset($data['max_tokens'])) {
            $saveData['max_tokens'] = intval($data['max_tokens']);
        }
        
        // 处理快捷问题
        if (isset($data['quick_questions'])) {
            $saveData['quick_questions'] = trim($data['quick_questions']);
        }
        
        // 处理系统提示词
        if (isset($data['system_prompt'])) {
            $saveData['system_prompt'] = trim($data['system_prompt']);
        }
        
        try {
            $count = Db::name('ai_chat_config')->count();
            if ($count > 0) {
                Db::name('ai_chat_config')->where('id', 1)->update($saveData);
            } else {
                $saveData['add_time'] = time();
                $saveData['welcome'] = '施主好，贫道道玄，有何疑惑尽管道来。';
                $saveData['model'] = $saveData['model'] ?? 'doubao-seed-2-0-pro-260215';
                Db::name('ai_chat_config')->insert($saveData);
            }
            
            // 清除缓存
            Cache::delete(self::CONFIG_CACHE_KEY);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 获取知识库分类
     */
    public function getKnowledgeCategories()
    {
        $cacheKey = 'ai_knowledge_categories';
        $cache = Cache::get($cacheKey);
        if ($cache) {
            return $cache;
        }
        
        try {
            $list = Db::name('ai_knowledge_category')
                ->where('is_enable', 1)
                ->order('sort', 'asc')
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            $list = $this->getDefaultCategories();
        }
        
        Cache::set($cacheKey, $list, 3600);
        return $list;
    }
    
    /**
     * 默认分类
     */
    private function getDefaultCategories()
    {
        return [
            ['id' => 1, 'name' => '符箓百科', 'icon' => 'talisman'],
            ['id' => 2, 'name' => '道家文化', 'icon' => 'taoism'],
            ['id' => 3, 'name' => '常见问题', 'icon' => 'faq'],
            ['id' => 4, 'name' => '服务介绍', 'icon' => 'service']
        ];
    }
    
    /**
     * 获取知识库列表
     */
    public function getKnowledgeList($category = '', $page = 1, $pageSize = 10)
    {
        $where = [['is_enable', '=', 1]];
        
        if (!empty($category)) {
            $where[] = ['category_id', '=', $category];
        }
        
        try {
            $count = Db::name('ai_knowledge_item')
                ->where($where)
                ->count();
            
            $list = Db::name('ai_knowledge_item')
                ->where($where)
                ->order('sort', 'asc')
                ->page($page, $pageSize)
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            $list = [];
            $count = 0;
        }
        
        return [
            'list' => $list,
            'total' => $count,
            'page' => $page,
            'page_size' => $pageSize
        ];
    }
    
    /**
     * 获取知识详情
     */
    public function getKnowledgeDetail($id)
    {
        try {
            $detail = Db::name('ai_knowledge_item')
                ->where(['id' => $id, 'is_enable' => 1])
                ->find();
            
            if ($detail) {
                // 更新匹配次数
                Db::name('ai_knowledge_item')
                    ->where('id', $id)
                    ->inc('match_count')
                    ->update();
            }
            
            return $detail;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 知识库匹配
     */
    public function matchKnowledge($query)
    {
        $query = mb_strtolower(trim($query));
        
        try {
            // 尝试精确匹配关键词
            $list = Db::name('ai_knowledge_item')
                ->where('is_enable', 1)
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            $list = $this->getBuiltinKnowledge();
        }
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($list as $item) {
            $keywords = $item['keywords'] ?? '';
            $keywordList = array_filter(array_map('trim', explode(',', $keywords)));
            
            foreach ($keywordList as $keyword) {
                $keywordLower = mb_strtolower($keyword);
                if (strpos($query, $keywordLower) !== false) {
                    $score = mb_strlen($keyword) / mb_strlen($query);
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestMatch = $item;
                    }
                }
            }
        }
        
        if ($bestMatch) {
            return [
                'found' => true,
                'content' => $bestMatch['content'],
                'match_rate' => min(100, round($bestScore * 100)),
                'id' => $bestMatch['id']
            ];
        }
        
        return [
            'found' => false,
            'content' => null,
            'match_rate' => 0,
            'id' => 0
        ];
    }
    
    /**
     * 内置知识库
     */
    private function getBuiltinKnowledge()
    {
        return [
            [
                'id' => 1,
                'keywords' => '平安符,护身符',
                'content' => "平安符乃道家护身之宝\n\n【功效】\n• 护佑出入平安\n• 驱邪避祸\n• 化解小人\n• 保佑身体健康\n\n【使用方法】\n1. 挂于大门内侧，头朝外\n2. 可随身携带，放于钱包或口袋\n3. 保持干燥，不可沾水\n\n【适用人群】\n• 经常出差旅行\n• 体质较弱易招阴邪\n• 运势低迷期"
            ],
            [
                'id' => 2,
                'keywords' => '财神符,招财符,财运',
                'content' => "财神符乃招财进宝之宝\n\n【适合人群】\n• 经商做生意\n• 财运不佳\n• 投资理财\n• 创业开张\n\n【功效】\n• 招财进宝\n• 财源广进\n• 守财聚财"
            ],
            [
                'id' => 3,
                'keywords' => '文昌符,学业符,考试',
                'content' => "文昌符乃求学问道之宝\n\n【适合人群】\n• 学生备考\n• 中考高考\n• 考研考公\n• 职称考试\n\n【功效】\n• 开启智慧\n• 学业进步\n• 考试顺利"
            ],
            [
                'id' => 4,
                'keywords' => '太岁符,犯太岁,本命年',
                'content' => "太岁符专门化解犯太岁\n\n【犯太岁类型】\n• 值太岁：本命年\n• 冲太岁：六大冲\n• 破太岁\n• 害太岁\n• 刑太岁\n\n【化解方法】\n1. 请太岁符贴身携带\n2. 犯太岁年份必请"
            ],
            [
                'id' => 5,
                'keywords' => '开光',
                'content' => "开光是道家重要仪式\n\n【开光条件】\n• 必须由有道行的法师主持\n• 需设坛焚香，诵经祈祷\n• 选择吉日良时进行\n\n【开光流程】\n1. 净坛：清净道场\n2. 上香：诚心供奉\n3. 诵经：诵读道经\n4. 敕笔：法师持咒\n5. 点睛：开光点眼\n6. 发牒：颁发证明"
            ],
            [
                'id' => 6,
                'keywords' => '请符,如何请',
                'content' => "请符有讲究，心诚则灵\n\n【请符流程】\n1. 选择正规道观或店铺\n2. 说明自身需求\n3. 法师根据八字推荐\n4. 心存善念，不可强求\n5. 请回后妥善保管\n\n【禁忌】\n• 心存恶念者符不灵\n• 不可用污秽之物接触\n• 符箓不可让外人随意触碰"
            ],
            [
                'id' => 7,
                'keywords' => '五行,金木水火土',
                'content' => "五行是道家核心理论\n\n【五行】\n金、木、水、火、土\n\n【相生】\n木生火 → 火生土 → 土生金 → 金生水 → 水生木\n\n【相克】\n木克土 → 土克水 → 水克火 → 火克金 → 金克木"
            ],
            [
                'id' => 8,
                'keywords' => '见效,多久,效果',
                'content' => "符箓见效时间因人而异\n\n【短期效果】7-30天\n• 心态渐趋平和\n• 睡眠质量改善\n\n【中期效果】1-3个月\n• 贵人运开始增强\n• 做事更加顺利\n\n【长期效果】3-6个月\n• 事业财运明显提升\n\n关键在于：心诚 + 配合自身努力。"
            ]
        ];
    }
    
    /**
     * 获取快捷问题
     */
    public function getQuickQuestions()
    {
        return [
            [
                'question' => '平安符有什么功效？',
                'category' => 'talisman'
            ],
            [
                'question' => '如何正确请符？',
                'category' => 'taoism'
            ],
            [
                'question' => '什么是犯太岁？',
                'category' => 'taoism'
            ],
            [
                'question' => '符箓多久见效？',
                'category' => 'faq'
            ],
            [
                'question' => '什么是开光？',
                'category' => 'taoism'
            ],
            [
                'question' => '文昌符对考试有帮助吗？',
                'category' => 'talisman'
            ]
        ];
    }
    
    /**
     * 生成回复
     */
    public function generateResponse($query, $knowledgeMatch)
    {
        if ($knowledgeMatch['found']) {
            return $knowledgeMatch['content'];
        }
        
        $queryLower = mb_strtolower($query);
        
        if (strpos($queryLower, '符') !== false && strpos($queryLower, '请') !== false) {
            return "施主问请符之道，贫道细细道来：

【请符流程】
1. 明确需求：知晓需要何种符箓
2. 选择正规：符宝网法物流通处
3. 提供信息：可告知生辰八字
4. 心存善念：符到之处，福报随行

【请符禁忌】
• 心存恶念者，符不灵验
• 不可用污秽之物接触
• 符箓不可让外人随意触碰

施主若有具体需求，可告诉贫道，贫道为您推荐。";
        }
        
        if (strpos($queryLower, '效') !== false || strpos($queryLower, '多久') !== false) {
            return "符箓见效之期，因人而异：

【短期效果】7-30天
• 心态渐趋平和
• 睡眠质量改善

【中期效果】1-3个月
• 贵人运开始增强
• 做事更加顺利

【长期效果】3-6个月
• 事业财运明显提升

关键在于：心诚 + 配合自身努力。";
        }
        
        return "施主所问，贫道已悉知。

道法自然，万物皆有其缘法。请施主详细说明所求，或选择以下快捷问题：

◆ 各类符箓的功效和使用方法
◆ 如何正确请符
◆ 符箓的保存和禁忌
◆ 什么是犯太岁

贫道当为施主指点迷津。";
    }
    
    /**
     * 获取默认回复
     */
    public function getDefaultReply($query, $knowledgeMatch)
    {
        if ($knowledgeMatch['found']) {
            return $knowledgeMatch['content'];
        }
        
        return $this->generateResponse($query, $knowledgeMatch);
    }
    
    /**
     * 流式输出响应
     */
    public function streamResponse($query, $config, $knowledgeMatch)
    {
        $response = $this->generateResponse($query, $knowledgeMatch);
        $chars = mb_str_split($response);
        
        foreach ($chars as $char) {
            echo "data: " . json_encode(['content' => $char, 'done' => false]) . "\n\n";
            flush();
            usleep(10000); // 10ms
        }
        
        echo "data: " . json_encode(['done' => true]) . "\n\n";
        flush();
    }
}
