<?php
/**
 * AI助手 - 道玄 控制器
 * 符宝网
 */

namespace app\index\controller;

use app\common\controller\Base;
use app\index\service\AIAssistantService;
use think\facade\Cache;

class AIAssistant extends Base
{
    protected $service;
    
    public function __construct()
    {
        parent::__construct();
        $this->service = new AIAssistantService();
    }
    
    /**
     * AI助手首页
     */
    public function index()
    {
        // 获取配置
        $config = $this->service->getConfig();
        $this->assign('config', $config);
        
        // 获取知识库分类
        $categories = $this->service->getKnowledgeCategories();
        $this->assign('categories', $categories);
        
        // 获取快捷问题
        $quickQuestions = $this->service->getQuickQuestions();
        $this->assign('quickQuestions', $quickQuestions);
        
        // SEO
        $this->setSeoInfo('AI道玄助手', '符宝网AI道玄助手，智能问答，道家文化咨询');
        
        return $this->fetch();
    }
    
    /**
     * 获取知识库
     */
    public function knowledge()
    {
        if ($this->request->isAjax()) {
            $category = input('category', '');
            $page = input('page', 1);
            $pageSize = input('page_size', 10);
            
            $result = $this->service->getKnowledgeList($category, $page, $pageSize);
            return json(['code' => 0, 'msg' => 'success', 'data' => $result]);
        }
        
        return json(['code' => 400, 'msg' => '请求方式错误']);
    }
    
    /**
     * 知识详情
     */
    public function knowledgeDetail()
    {
        $id = input('id', 0, 'intval');
        
        if ($id <= 0) {
            return json(['code' => 400, 'msg' => '参数错误']);
        }
        
        $detail = $this->service->getKnowledgeDetail($id);
        if (empty($detail)) {
            return json(['code' => 404, 'msg' => '知识不存在']);
        }
        
        return json(['code' => 0, 'msg' => 'success', 'data' => $detail]);
    }
    
    /**
     * 获取配置
     */
    public function getConfig()
    {
        $config = $this->service->getConfig();
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
            return json(['code' => 0, 'msg' => '配置保存成功']);
        }
        
        return json(['code' => 500, 'msg' => '配置保存失败']);
    }
    
    /**
     * 测试API连接
     */
    public function testApi()
    {
        if (!$this->request->isPost()) {
            return json(['code' => 400, 'msg' => '请求方式错误']);
        }
        
        $provider = input('provider', '');
        $apiKey = input('api_key', '');
        $model = input('model', '');
        
        $result = $this->service->testApiConnection($provider, $apiKey, $model);
        
        if ($result['success']) {
            return json(['code' => 0, 'msg' => $result['message']]);
        } else {
            return json(['code' => 1, 'msg' => $result['message']]);
        }
    }
    
    /**
     * 获取API提供商列表
     */
    public function getProviders()
    {
        $providers = $this->service->getApiProviders();
        return json(['code' => 0, 'data' => $providers]);
    }
    
    /**
     * 获取模型列表
     */
    public function getModels()
    {
        $provider = input('provider', 'doubao');
        $models = $this->service->getModelsByProvider($provider);
        return json(['code' => 0, 'data' => $models]);
    }
    
    /**
     * 配置页面
     */
    public function config()
    {
        $config = $this->service->getConfig();
        $this->assign('config', $config);
        
        // SEO
        $this->setSeoInfo('AI配置 - 道玄', '符宝网AI助手配置后台');
        
        return $this->fetch();
    }
    
    /**
     * 模型配置页面
     */
    public function model()
    {
        $config = $this->service->getConfig();
        $this->assign('config', $config);
        
        // 支持的模型列表
        $models = [
            ['id' => 'doubao-seed-2-0-pro-260215', 'name' => '豆包 Pro 2.0', 'desc' => '最强大的豆包模型，适合复杂任务'],
            ['id' => 'doubao-seed-2-0-lite-260215', 'name' => '豆包 Lite 2.0', 'desc' => '轻量级豆包模型，适合日常对话'],
            ['id' => 'doubao-seed-2-0-mini-260215', 'name' => '豆包 Mini 2.0', 'desc' => '最小巧的豆包模型，响应最快'],
            ['id' => 'deepseek-v3-2-251201', 'name' => 'DeepSeek V3.2', 'desc' => '深度求索大模型，性价比高'],
            ['id' => 'kimi-k2-5-260127', 'name' => 'Kimi K2.5', 'desc' => '月之暗面大模型，长文本处理强'],
            ['id' => 'glm-5-0-260211', 'name' => 'GLM-5.0', 'desc' => '智谱清言大模型，中文理解强'],
        ];
        $this->assign('models', $models);
        
        $this->setSeoInfo('模型配置 - 道玄', 'AI模型选择配置');
        
        return $this->fetch();
    }
    
    /**
     * 训练优化页面
     */
    public function optimize()
    {
        $this->setSeoInfo('训练优化 - 道玄', 'AI训练与优化设置');
        return $this->fetch();
    }
    
    /**
     * 接口测试页面
     */
    public function test()
    {
        $this->setSeoInfo('接口测试 - 道玄', 'AI接口调试测试');
        return $this->fetch();
    }
}
