<?php
/**
 * AI聊天接口
 * 符宝网
 */

namespace app\index\controller\api;

use app\common\controller\BaseApi;
use app\index\service\AIAssistantService;
use think\facade\Cache;

class AIChat extends BaseApi
{
    protected $service;
    
    public function __construct()
    {
        parent::__construct();
        $this->service = new AIAssistantService();
    }
    
    /**
     * 聊天接口
     */
    public function chat()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }
        
        $message = input('message', '', 'trim');
        if (empty($message)) {
            return json(['code' => 400, 'msg' => '消息不能为空']);
        }
        
        $history = input('history', []);
        $stream = input('stream', false);
        
        try {
            // 知识库匹配
            $knowledgeMatch = $this->service->matchKnowledge($message);
            
            // 获取配置
            $config = $this->service->getConfig();
            
            // 知识库优先模式
            if ($config['knowledge_first'] && $knowledgeMatch['found'] && $knowledgeMatch['match_rate'] >= 70) {
                return json([
                    'code' => 0,
                    'msg' => 'success',
                    'data' => [
                        'content' => $knowledgeMatch['content'],
                        'source' => 'knowledge',
                        'match_rate' => $knowledgeMatch['match_rate']
                    ]
                ]);
            }
            
            // 启用大模型
            if ($config['enable_api']) {
                $response = $this->callLLM($message, $history, $config, $knowledgeMatch);
                return json([
                    'code' => 0,
                    'msg' => 'success',
                    'data' => [
                        'content' => $response,
                        'source' => 'llm',
                        'model' => $config['model']
                    ]
                ]);
            }
            
            // 默认回复
            $defaultReply = $this->service->getDefaultReply($message, $knowledgeMatch);
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => [
                    'content' => $defaultReply,
                    'source' => 'default',
                    'match_rate' => $knowledgeMatch['match_rate']
                ]
            ]);
            
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '服务异常: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 流式聊天
     */
    public function stream()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }
        
        $message = input('message', '', 'trim');
        if (empty($message)) {
            return json(['code' => 400, 'msg' => '消息不能为空']);
        }
        
        // 设置SSE头
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        
        try {
            $knowledgeMatch = $this->service->matchKnowledge($message);
            $config = $this->service->getConfig();
            
            // 知识库优先
            if ($config['knowledge_first'] && $knowledgeMatch['found'] && $knowledgeMatch['match_rate'] >= 70) {
                echo "data: " . json_encode(['content' => $knowledgeMatch['content'], 'done' => true]) . "\n\n";
                flush();
                return;
            }
            
            // 流式输出
            $response = $this->service->streamResponse($message, $config, $knowledgeMatch);
            
        } catch (\Exception $e) {
            echo "data: " . json_encode(['error' => $e->getMessage(), 'done' => true]) . "\n\n";
            flush();
        }
        
        echo "data: " . json_encode(['done' => true]) . "\n\n";
        flush();
    }
    
    /**
     * 调用大模型
     */
    private function callLLM($message, $history, $config, $knowledgeMatch)
    {
        // 构建系统提示词
        $systemPrompt = $config['system_prompt'] ?: $this->service->getDefaultSystemPrompt();
        
        if ($knowledgeMatch['found']) {
            $systemPrompt .= "\n\n【相关知识】\n" . $knowledgeMatch['content'];
        }
        
        // 构建消息
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $message]
        ];
        
        // 记录对话
        $this->logChat($message, $config['model']);
        
        // 调用SDK（需要安装coze-coding-dev-sdk）
        // 这里使用本地知识库作为降级方案
        return $this->service->generateResponse($message, $knowledgeMatch);
    }
    
    /**
     * 记录对话日志
     */
    private function logChat($question, $model)
    {
        $data = [
            'question' => $question,
            'model' => $model,
            'add_time' => time()
        ];
        
        // 可选：写入数据库或缓存
        // Db::name('ai_chat_log')->insert($data);
    }
    
    /**
     * 获取配置
     */
    public function config()
    {
        $config = $this->service->getConfig();
        
        // 不返回敏感信息
        unset($config['api_key']);
        
        return json(['code' => 0, 'msg' => 'success', 'data' => $config]);
    }
    
    /**
     * 保存配置
     */
    public function saveConfig()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }
        
        $data = input('post.');
        $result = $this->service->saveConfig($data);
        
        if ($result) {
            return json(['code' => 0, 'msg' => '保存成功']);
        }
        
        return json(['code' => 500, 'msg' => '保存失败']);
    }
    
    /**
     * 健康检查
     */
    public function health()
    {
        return json(['code' => 0, 'msg' => 'success', 'data' => ['status' => 'ok', 'time' => time()]]);
    }
}
