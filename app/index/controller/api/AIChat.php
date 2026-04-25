<?php
// +----------------------------------------------------------------------
// | 觅智商城 - AI聊天API控制器
// +----------------------------------------------------------------------
// | 觅智商城 - 觅智文化
// +----------------------------------------------------------------------

namespace app\index\controller\api;

use think\Db;

/**
 * AI聊天API控制器
 */
class AIChat extends BaseApi
{
    /**
     * 聊天会话存储（内存）
     */
    private static $chatHistories = [];
    
    /**
     * 预设问答库
     */
    private static $faqDatabase = [
        // 招呼语
        'greeting' => [
            'patterns' => ['你好', '您好', 'hi', 'hello', 'hi~', '在吗', '在不在', '你好啊', '打扰了', '请教'],
            'responses' => [
                '善信有缘！道玄在此恭候多时。吾通晓八字命理、符箓之道，愿为施主答疑解惑。不知有何疑问？☯',
                '施主安好！道玄虽非圣贤，但对命理之学略知一二，愿为施主指点迷津。',
                '有缘千里来相会，施主有何疑惑尽管道来，吾当知无不言。✦'
            ]
        ],
        
        // 觅智商城相关
        'shop' => [
            'patterns' => ['商城', '店铺', '请购', '购买', '哪里买', '怎么买', '商城介绍', '产品', '商品'],
            'responses' => [
                '觅智商城乃传承道家文化的平台，汇聚各类正授符箓、开光法器。善信可请购护身符、招财符、姻缘符等，皆由正规道观开光加持。☯',
                '商城所售法器皆经道观开光，灵性具足。善信可根据需要选择，吾可为你介绍各类符箓功效。'
            ]
        ],
        
        // 八字命理
        'bazi' => [
            'patterns' => ['八字', '四柱', '命盘', '命理', '算命', '批命', '看命', '运势', '大运', '流年'],
            'responses' => [
                '施主想了解八字命理？八字，又称四柱，是由出生年、月、日、时四个时间点，各配天干地支，共八字以论命。\n\n若要细论命盘，还需施主告知出生年月日时（阳历或农历皆可），吾可为你分析一二。✦',
                '命理之学，博大精深。八字可论一生运势起伏，然需结合具体时辰方能准确。施主若有兴趣，可提供生辰，吾为你细批。'
            ]
        ],
        
        // 五行
        'wuxing' => [
            'patterns' => ['五行', '缺金', '缺木', '缺水', '缺火', '缺土', '金木水火土'],
            'responses' => [
                '五行乃命理之基，金木水火土相生相克。\n\n五行缺失补救：\n- 缺金：多穿白色金色，佩戴金属饰品\n- 缺木：多穿绿色青色，家中养植物\n- 缺水：多穿黑色蓝色，多喝水\n- 缺火：多穿红色紫色，多晒太阳\n- 缺土：多穿黄色棕色，用陶瓷器皿\n\n需专业命理师根据八字分析，不可仅凭感觉判断。'
            ]
        ],
        
        // 护身符
        'hushen' => [
            'patterns' => ['护身符', '平安符', '保护符', '辟邪', '挡灾'],
            'responses' => [
                '护身符乃道家最常见的符箓之一，用于保护佩戴者平安吉祥。\n\n主要功效：\n1. 辟邪挡灾\n2. 保佑平安\n3. 化解小人\n\n注意事项：\n- 需正规寺庙或道长开光\n- 佩戴时保持恭敬心\n- 不可让外人触碰\n\n施主可至商城请购正品护身符。☘'
            ]
        ],
        
        // 招财符
        'zhaocai' => [
            'patterns' => ['招财符', '财运', '发财', '财神', '招财', '进宝', '生意'],
            'responses' => [
                '招财符是道教常用的增益符咒，用于招引财气、改善财运。\n\n使用方法：\n1. 将招财符贴于家中财位\n2. 或随身携带钱包中\n3. 配合诚心诵念\n\n功效：\n- 招揽财气\n- 改善投资运势\n- 助生意兴隆\n\n需开光后使用效果更佳。⚡'
            ]
        ],
        
        // 姻缘符
        'yinyuan' => [
            'patterns' => ['姻缘符', '姻缘', '桃花', '感情', '恋爱', '婚姻', '对象'],
            'responses' => [
                '姻缘符专门用于增进感情姻缘，是道教常见的和合符咒之一。\n\n功效：\n- 增进已有感情\n- 招引正缘桃花\n- 化解感情阻碍\n\n使用方法：\n- 随身佩戴\n- 放置于卧室床头\n\n注意：感情需两情相悦，不可强求。☯'
            ]
        ],
        
        // 学业符
        'xueye' => [
            'patterns' => ['学业符', '考试', '学业', '文昌', '考试符', '考研', '考公'],
            'responses' => [
                '学业符（文昌符）是助学业进步的符咒，特别适用于考试季节。\n\n适用人群：\n- 学生考试\n- 考研、考公\n- 职称考试\n\n功效：\n- 开发智慧\n- 增强记忆力\n- 助学业顺利\n\n使用建议：\n- 考前一个月开始使用\n- 放置于书房或书桌\n\n✦'
            ]
        ],
        
        // 开光
        'kaiguang' => [
            'patterns' => ['开光', '加持', '灵性', '仪式'],
            'responses' => [
                '开光是道教特有的宗教仪式，由持戒修行的道长主持，通过诵经、焚香、念咒等仪轨，将天地灵气注入法器之中。\n\n开光的作用：\n1. 赋予法器灵性\n2. 驱邪化煞\n3. 增强法器功效\n\n注意：开光必须在正规道观由持戒道长主持，方为有效。⬢'
            ]
        ],
        
        // 貔貅
        ['patterns' => ['貔貅', '神兽', '招财兽'],
            'responses' => [
                '貔貅是著名的招财神兽，开光后具有招财聚财的功效。\n\n佩戴方法：\n1. 头朝上佩戴为招财\n2. 左手佩戴为佳\n3. 定期用清水清洗\n\n摆放方法：\n1. 面向门或窗\n2. 头朝外招财\n3. 需开光后摆放\n\n注意事项：不让别人触碰，经常抚摸头部和尾部。'
            ]
        ],
        
        // 风水
        ['patterns' => ['风水', '家居', '摆设', '布局', '客厅', '卧室'],
            'responses' => [
                '风水学是研究环境与人的关系的学问，核心理念是「藏风聚气，得水为上」。\n\n家居风水要点：\n1. 大门 - 门口不可正对电梯或楼梯\n2. 客厅 - 沙发背后宜有靠山\n3. 卧室 - 床不宜正对镜子\n4. 厨房 - 灶台不可正对水槽\n\n施主若有具体疑问，可详细说来，吾为你分析。⬡'
            ]
        ],
        
        // 道家
        ['patterns' => ['道教', '道家', '老子', '道德经', '庄子', '道法自然'],
            'responses' => [
                '道家是以老子、庄子为代表的哲学流派，道教是结合道家思想与神仙信仰的宗教。\n\n核心思想：\n1. 觅智文化 - 顺应自然规律\n2. 阴阳学说 - 阴阳平衡\n3. 五行理论 - 木火土金水\n4. 天人合一 - 人与自然和谐\n\n道家追求与道合一、自然逍遥的精神境界。☯'
            ]
        ],
        
        // 太岁
        ['patterns' => ['太岁', '犯太岁', '冲太岁', '本命年', '化太岁'],
            'responses' => [
                '太岁是道教的护法神灵，主管一年运势。犯太岁可能出现运势波动。\n\n化解方法：\n1. 恭请太岁符\n2. 参加拜太岁法事\n3. 佩戴化太岁锦囊\n\n每年犯太岁生肖不同，可咨询商城客服了解详情。'
            ]
        ],
        
        // 祈福
        ['patterns' => ['祈福', '许愿', '求签', '上香', '参拜', '拜神'],
            'responses' => [
                '祈福是人与神明沟通的方式，表达对美好生活的向往。\n\n正确的祈福方法：\n1. 心诚则灵 - 虔诚恭敬，心无杂念\n2. 选对时间 - 农历初一、十五\n3. 选对场所 - 正规道观寺庙\n4. 正确的步骤 - 先拜天公，再拜主神\n\n心诚则灵，施主可至就近道观参拜。'
            ]
        ],
        
        // 易经
        ['patterns' => ['易经', '八卦', '六十四卦', '占卜', '算卦'],
            'responses' => [
                '《易经》是中华文明的源头经典，被誉为群经之首。\n\n基本概念：\n1. 阴阳 - 万物基本元素\n2. 八卦 - 乾兑离震巽坎艮坤\n3. 六十四卦 - 八卦相叠\n\n入门建议：\n1. 先学八卦基本含义\n2. 了解爻辞解释\n3. 逐步深入\n\n《易经》智慧深远，吾可为你讲解一二。'
            ]
        ],
        
        // 谢谢
        ['patterns' => ['谢谢', '感谢', '多谢', '辛苦了', '好的'],
            'responses' => [
                '施主客气了，能为施主解惑，乃道玄之幸。☯',
                '不客气，愿施主福寿安康。',
                '善哉善哉，若有疑问，随时可来问道。✦'
            ]
        ],
        
        // 再见
        ['patterns' => ['再见', '拜拜', '走了', '告辞', '下次见'],
            'responses' => [
                '施主慢走，愿你一路平安，有缘再会！☯',
                '善信珍重，道玄在此恭候下次问道。✦',
                '福生无量天尊，施主后会有期。'
            ]
        ]
    ];
    
    /**
     * 流式聊天
     */
    public function Chat()
    {
        $message = input('message', '', 'trim');
        $session_id = input('session_id', 'default');
        $mode = input('mode', 'chat');
        
        if (empty($message)) {
            return json(['code' => 400, 'msg' => '消息内容不能为空']);
        }
        
        // 获取配置
        $config = \app\service\AIAssistantService::GetAllConfig();
        $ai_name = $config['ai_name'] ?: '道玄';
        
        // 知识库检索
        $knowledge_context = '';
        if (!empty($config['enable_knowledge'])) {
            $matched = \app\service\AIAssistantService::MatchKnowledge($message, $config['knowledge_match_count'] ?: 3);
            if (!empty($matched)) {
                $knowledge_context = "\n\n以下是相关的知识库内容，供你参考回答：\n";
                foreach ($matched as $index => $item) {
                    $knowledge_context .= "\n【知识" . ($index + 1) . '】' . $item['title'] . "\n" . $item['answer'];
                }
            }
        }
        
        // 获取或创建会话历史
        if (!isset(self::$chatHistories[$session_id])) {
            self::$chatHistories[$session_id] = [];
        }
        $history = &self::$chatHistories[$session_id];
        
        // 系统提示词（根据模式调整）
        $system_prompt = $this->getSystemPrompt($mode, $ai_name, $config);
        $system_prompt .= $knowledge_context;
        
        // 构建消息
        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
        ];
        
        // 添加历史
        foreach ($history as $h) {
            $messages[] = $h;
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];
        
        // 生成回复
        $result = $this->callLLM($messages, $config);
        
        // 保存到历史
        $history[] = ['role' => 'user', 'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => $result['content']];
        
        // 限制历史长度
        $max_history = intval($config['max_history']) ?: 20;
        if (count($history) > $max_history * 2) {
            $history = array_slice($history, -$max_history * 2);
        }
        
        return json(['code' => 0, 'msg' => 'success', 'data' => ['content' => $result['content']]]);
    }
    
    /**
     * 获取系统提示词
     */
    private function getSystemPrompt($mode, $ai_name, $config)
    {
        $base_prompt = $config['system_prompt'] ?: "你是{$ai_name}，觅智商城的AI助手，精通道家文化、符箓学、八字命理。";
        
        $mode_prompts = [
            'chat' => $base_prompt . "\n\n请用道家古韵与现代白话结合的方式回答，保持友善和专业的态度。",
            'bazi' => $base_prompt . "\n\n当前模式：八字命理咨询。请引导用户提供出生年月日时（农历/阳历），并提供专业的命理分析。注意：不能做出具体的命理结论，只能提供建议性分析。",
            'fulu' => $base_prompt . "\n\n当前模式：符箓知识咨询。请详细介绍各类符箓的功效、使用方法和注意事项。",
            'fengshui' => $base_prompt . "\n\n当前模式：风水调理咨询。请提供专业的家居风水建议，包括布局、摆设、颜色等方面的指导。",
            'fashi' => $base_prompt . "\n\n当前模式：开光法器咨询。请详细介绍各类开光法器的功效、保养方法和正确的使用方法。"
        ];
        
        return $mode_prompts[$mode] ?? $base_prompt;
    }
    
    /**
     * 调用LLM API
     */
    private function callLLM($messages, $config)
    {
        $user_message = '';
        foreach (array_reverse($messages) as $m) {
            if ($m['role'] === 'user') {
                $user_message = $m['content'];
                break;
            }
        }
        
        // 生成回答
        $answer = $this->generateResponse($user_message, $config);
        
        return ['content' => $answer];
    }
    
    /**
     * 生成回答
     */
    private function generateResponse($query, $config)
    {
        $ai_name = $config['ai_name'] ?: '道玄';
        $query_lower = strtolower($query);
        
        // 知识库匹配（优先）
        $matched = \app\service\AIAssistantService::MatchKnowledge($query, 1);
        
        if (!empty($matched)) {
            $item = $matched[0];
            \app\service\AIAssistantService::IncUseCount($item['id']);
            return "善信所问，{$item['title']}，吾来为你解答：\n\n{$item['answer']}\n\n如还有疑问，尽管问来。";
        }
        
        // 预设问答匹配
        foreach (self::$faqDatabase as $faq) {
            foreach ($faq['patterns'] as $pattern) {
                if (strpos($query_lower, strtolower($pattern)) !== false) {
                    $responses = $faq['responses'];
                    return $responses[array_rand($responses)];
                }
            }
        }
        
        // 默认回复
        return "施主所言，{$ai_name}已悉。吾虽通晓命理符箓，然天机不可尽泄。施主不妨换个问法，或说说心中所惑，吾当尽力为施主指点迷津。☯\n\n例如：\n- 护身符有什么功效？\n- 如何看八字？\n- 家居风水有什么讲究？\n- 貔貅如何佩戴？";
    }
    
    /**
     * 清除会话历史
     */
    public function Clear()
    {
        $session_id = input('session_id', 'default');
        if (isset(self::$chatHistories[$session_id])) {
            unset(self::$chatHistories[$session_id]);
        }
        return json(['code' => 0, 'msg' => 'success']);
    }
    
    /**
     * 获取模式列表
     */
    public function GetModes()
    {
        $modes = [
            ['id' => 'chat', 'name' => '道法闲聊', 'icon' => '☯', 'description' => '问道解惑，畅谈道家文化'],
            ['id' => 'bazi', 'name' => '八字命理', 'icon' => '✦', 'description' => '分析命盘，解读人生运势'],
            ['id' => 'fulu', 'name' => '符箓知识', 'icon' => '❋', 'description' => '了解符箓，正确使用'],
            ['id' => 'fengshui', 'name' => '风水调理', 'icon' => '⬡', 'description' => '家居摆设，环境布局'],
            ['id' => 'fashi', 'name' => '开光法器', 'icon' => '⬢', 'description' => '法器知识，开光保养'],
        ];
        
        return json(['code' => 0, 'msg' => 'success', 'data' => ['modes' => $modes]]);
    }
}
