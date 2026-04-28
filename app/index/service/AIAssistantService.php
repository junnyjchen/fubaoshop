<?php
/**
 * AI助手服务层
 * 符宝网 - 支持大模型API配置
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
    
    // 大模型API配置
    const API_PROVIDERS = [
        'doubao' => [
            'name' => '豆包 (Volcengine)',
            'base_url' => 'https://ark.cn-beijing.volces.com/api/v3',
            'models' => [
                'doubao-seed-2-0-pro-260215' => '豆包 Pro 2.0',
                'doubao-seed-2-0-lite-260215' => '豆包 Lite 2.0',
                'doubao-seed-2-0-mini-260215' => '豆包 Mini 2.0',
            ]
        ],
        'deepseek' => [
            'name' => 'DeepSeek',
            'base_url' => 'https://api.deepseek.com/v1',
            'models' => [
                'deepseek-v3-2-251201' => 'DeepSeek V3.2',
                'deepseek-chat' => 'DeepSeek Chat',
            ]
        ],
        'kimi' => [
            'name' => 'Kimi (月之暗面)',
            'base_url' => 'https://api.moonshot.cn/v1',
            'models' => [
                'kimi-k2-5-260127' => 'Kimi K2.5',
                'moonshot-v1-8k' => 'Moonshot V1 8K',
                'moonshot-v1-32k' => 'Moonshot V1 32K',
            ]
        ],
        'zhipu' => [
            'name' => '智谱 GLM',
            'base_url' => 'https://open.bigmodel.cn/api/paas/v4',
            'models' => [
                'glm-5-0-260211' => 'GLM-5.0',
                'glm-4' => 'GLM-4',
                'glm-4-flash' => 'GLM-4 Flash',
            ]
        ],
        'custom' => [
            'name' => '自定义API',
            'base_url' => '',
            'models' => []
        ],
    ];
    
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
                    'provider' => !empty($config['api_provider']) ? $config['api_provider'] : 'doubao',
                    'api_key' => !empty($config['api_key']) ? $this->decryptApiKey($config['api_key']) : '',
                    'api_url' => !empty($config['api_url']) ? $config['api_url'] : '',
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
            'provider' => 'doubao',
            'api_key' => '',
            'api_url' => '',
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
     * 获取API提供商列表
     */
    public function getApiProviders()
    {
        return self::API_PROVIDERS;
    }
    
    /**
     * 根据提供商获取模型列表
     */
    public function getModelsByProvider($provider)
    {
        if (isset(self::API_PROVIDERS[$provider])) {
            return self::API_PROVIDERS[$provider]['models'];
        }
        return [];
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
        
        // 处理API配置
        if (isset($data['provider']) && !empty($data['provider'])) {
            $saveData['api_provider'] = trim($data['provider']);
        }
        
        // API密钥加密存储
        if (isset($data['api_key']) && !empty(trim($data['api_key']))) {
            $saveData['api_key'] = $this->encryptApiKey(trim($data['api_key']));
        }
        
        // 自定义API地址
        if (isset($data['api_url'])) {
            $saveData['api_url'] = trim($data['api_url']);
        }
        
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
     * 加密API密钥
     */
    public function encryptApiKey($apiKey)
    {
        // 简单的Base64编码，实际生产环境建议使用更安全的加密方式
        return base64_encode($apiKey);
    }
    
    /**
     * 解密API密钥
     */
    public function decryptApiKey($encryptedKey)
    {
        return base64_decode($encryptedKey);
    }
    
    /**
     * 测试API连接
     */
    public function testApiConnection($provider, $apiKey, $model)
    {
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API密钥不能为空'];
        }
        
        $config = self::API_PROVIDERS[$provider] ?? null;
        if (!$config) {
            return ['success' => false, 'message' => '不支持的API提供商'];
        }
        
        // 构建请求URL
        $baseUrl = $config['base_url'];
        if ($provider === 'custom') {
            $baseUrl = rtrim($apiKey, '/'); // custom模式下apiKey实际上是完整URL
        }
        
        $url = rtrim($baseUrl, '/') . '/chat/completions';
        
        // 构建测试请求
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => '你好']
            ],
            'max_tokens' => 10
        ];
        
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return ['success' => false, 'message' => '连接失败: ' . $error];
            }
            
            if ($httpCode === 200) {
                return ['success' => true, 'message' => '连接成功！'];
            } else {
                $result = json_decode($response, true);
                $errorMsg = $result['error']['message'] ?? '未知错误';
                return ['success' => false, 'message' => 'API错误 (' . $httpCode . '): ' . $errorMsg];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '请求异常: ' . $e->getMessage()];
        }
    }
    
    /**
     * 调用大模型API
     */
    public function callApi($messages, $stream = false)
    {
        $config = $this->getConfig();
        
        if (!$config['enable_api']) {
            return ['error' => 'API未启用'];
        }
        
        $apiKey = $config['api_key'];
        if (empty($apiKey)) {
            return ['error' => 'API密钥未配置'];
        }
        
        $provider = $config['provider'];
        $providerConfig = self::API_PROVIDERS[$provider] ?? null;
        
        if (!$providerConfig && $provider !== 'custom') {
            return ['error' => '不支持的API提供商: ' . $provider];
        }
        
        // 构建请求URL
        if ($provider === 'custom') {
            $baseUrl = !empty($config['api_url']) ? $config['api_url'] : '';
            if (empty($baseUrl)) {
                return ['error' => '自定义API地址未配置'];
            }
        } else {
            $baseUrl = $providerConfig['base_url'];
        }
        
        $url = rtrim($baseUrl, '/') . '/chat/completions';
        
        // 构建请求参数
        $params = [
            'model' => $config['model'],
            'messages' => $messages,
            'temperature' => $config['temperature'] / 10, // 转换为0-2范围
            'max_tokens' => $config['max_tokens'],
            'top_p' => $config['top_p'] / 100, // 转换为0-1范围
        ];
        
        // 添加系统提示词
        if (!empty($config['system_prompt'])) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => $config['system_prompt']
            ]);
            $params['messages'] = $messages;
        }
        
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return ['error' => '请求失败: ' . $error];
            }
            
            if ($httpCode !== 200) {
                $result = json_decode($response, true);
                $errorMsg = $result['error']['message'] ?? 'API返回错误 (' . $httpCode . ')';
                return ['error' => $errorMsg];
            }
            
            $result = json_decode($response, true);
            return [
                'content' => $result['choices'][0]['message']['content'] ?? '',
                'usage' => $result['usage'] ?? [],
                'id' => $result['id'] ?? ''
            ];
            
        } catch (\Exception $e) {
            return ['error' => '调用异常: ' . $e->getMessage()];
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
                Db::name('ai_knowledge_item')->where('id', $id)->setInc('hit_count');
            }
            
            return $detail;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 获取快捷问题
     */
    public function getQuickQuestions()
    {
        $config = $this->getConfig();
        $questions = $config['quick_questions'] ?? '';
        
        if (empty($questions)) {
            return [
                '什么是符箓？',
                '如何请购开光符？',
                '犯太岁如何化解？'
            ];
        }
        
        return array_filter(array_map('trim', explode("\n", $questions)));
    }
    
    /**
     * 匹配知识库
     */
    public function matchKnowledge($query)
    {
        $query = trim($query);
        if (empty($query)) {
            return null;
        }
        
        try {
            // 简单的关键词匹配
            $list = Db::name('ai_knowledge_item')
                ->where('is_enable', 1)
                ->select()
                ->toArray();
            
            $bestMatch = null;
            $bestScore = 0;
            
            foreach ($list as $item) {
                $score = 0;
                $question = strtolower($item['question']);
                $queryLower = strtolower($query);
                
                // 精确匹配
                if (strpos($question, $queryLower) !== false) {
                    $score = 100;
                }
                // 关键词匹配
                $keywords = preg_split('/[\s,，]+/', $queryLower);
                foreach ($keywords as $kw) {
                    if (strlen($kw) >= 2 && strpos($question, $kw) !== false) {
                        $score += 20;
                    }
                }
                
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $item;
                }
            }
            
            // 匹配阈值
            if ($bestScore >= 30 && $bestMatch) {
                Db::name('ai_knowledge_item')->where('id', $bestMatch['id'])->setInc('hit_count');
                return [
                    'content' => $bestMatch['answer'],
                    'match_rate' => min(100, $bestScore),
                    'source' => 'knowledge'
                ];
            }
        } catch (\Exception $e) {
            // 忽略错误
        }
        
        return null;
    }
    
    /**
     * 获取聊天回复
     */
    public function getChatResponse($message, $history = [])
    {
        $config = $this->getConfig();
        
        // 先匹配知识库
        if ($config['knowledge_first']) {
            $knowledgeResult = $this->matchKnowledge($message);
            if ($knowledgeResult && $knowledgeResult['match_rate'] >= 70) {
                return $knowledgeResult;
            }
        }
        
        // 如果启用API且知识库未匹配或未设置知识库优先
        if ($config['enable_api']) {
            // 构建消息列表
            $messages = [];
            
            // 添加历史对话
            foreach ($history as $h) {
                $messages[] = [
                    'role' => $h['role'],
                    'content' => $h['content']
                ];
            }
            
            // 添加当前消息
            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];
            
            // 调用API
            $result = $this->callApi($messages);
            
            if (isset($result['error'])) {
                return [
                    'content' => '抱歉，贫道暂时无法回复此问题。' . $result['error'],
                    'match_rate' => 0,
                    'source' => 'error'
                ];
            }
            
            return [
                'content' => $result['content'],
                'match_rate' => 0,
                'source' => 'api'
            ];
        }
        
        // 内置回复
        return [
            'content' => $this->getBuiltInResponse($message),
            'match_rate' => 0,
            'source' => 'builtin'
        ];
    }
    
    /**
     * 内置回复
     */
    private function getBuiltInResponse($message)
    {
        $lowerMsg = mb_strtolower($message);
        
        // 常见问题回复
        $patterns = [
            '符箓' => '符箓，乃道教重要法器，承载天地人之信息，可用于祈福、辟邪、镇宅等。符箓需经高功法师敕封方有灵效，请购请至符宝网。',
            '开光' => '开光，乃通过法师诵经存想，将天地灵气注入法物之中，使其具有灵性。开光后的符箓方可发挥其应有的作用。',
            '太岁' => '犯太岁，乃流年与生肖相冲相克，主运程多有波折。可通过安太岁、化太岁符等方式化解。符宝网提供专业化解太岁服务。',
            '请购' => '施主可至符宝网请购开光符箓，本店所有符箓均由高功法师亲手敕封，功效灵验。',
            '功效' => '符箓功效因种类而异，有招财符、平安符、姻缘符、事业符等，请根据自身需求选择合适的符箓。',
            'hello' => '施主好，贫道道玄，在此恭候多时。有什么道家文化方面的问题，尽管问道。',
            '你好' => '施主好，贫道道玄，在此恭候多时。有什么道家文化方面的问题，尽管问道。',
            '谢谢' => '施主客气，修道之人当广结善缘。愿施主福寿安康，万事顺遂。',
        ];
        
        foreach ($patterns as $keyword => $response) {
            if (strpos($lowerMsg, $keyword) !== false) {
                return $response;
            }
        }
        
        return '施主所问，贫道需要思索片刻。建议施主可先浏览符宝网符箓百科，或联系客服详询。';
    }
}
