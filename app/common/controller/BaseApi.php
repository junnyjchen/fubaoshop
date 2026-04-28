<?php
/**
 * API基础控制器
 * 符宝网
 */

namespace app\common\controller;

use think\App;
use think\facade\Session;

class BaseApi
{
    protected $request;
    protected $app;
    protected $user_id;
    protected $user_type;
    
    public function __construct(App $app = null)
    {
        $this->app = $app ?: app();
        $this->request = $this->app['request'];
        
        // 初始化用户
        $this->initUser();
        
        // 跨域设置
        $this->cors();
    }
    
    /**
     * 跨域设置
     */
    protected function cors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($this->request->isOptions()) {
            exit('ok');
        }
    }
    
    /**
     * 初始化用户
     */
    protected function initUser()
    {
        $this->user_id = Session::get('user_id', 0);
        $this->user_type = Session::get('user_type', 0);
    }
    
    /**
     * 验证登录
     */
    protected function checkLogin()
    {
        if (empty($this->user_id)) {
            return json(['code' => 401, 'msg' => '请先登录']);
        }
        return true;
    }
    
    /**
     * 获取请求参数
     */
    protected function getParams()
    {
        return $this->request->param();
    }
}
