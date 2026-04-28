<?php
/**
 * 基础控制器
 * 符宝网
 */

namespace app\common\controller;

use think\App;
use think\exception\ValidateException;
use think\facade\View;
use think\facade\Session;

class Base
{
    protected $request;
    protected $app;
    protected $view;
    protected $user_id;
    protected $user_type;
    
    public function __construct(App $app = null)
    {
        $this->app = app();
        $this->request = $this->app['request'];
        $this->view = View::init();
        
        // 初始化用户
        $this->initUser();
        
        // 初始化
        $this->initialize();
    }
    
    protected function initialize()
    {
        // 子类可重写
    }
    
    /**
     * 初始化用户
     */
    protected function initUser()
    {
        // 从Session获取用户ID
        $this->user_id = Session::get('user_id', 0);
        $this->user_type = Session::get('user_type', 0);
    }
    
    /**
     * 验证登录
     */
    protected function checkLogin($redirect = true)
    {
        if (empty($this->user_id)) {
            if ($redirect) {
                $this->redirect('/login.html');
            }
            return false;
        }
        return true;
    }
    
    /**
     * 设置SEO信息
     */
    protected function setSeoInfo($title = '', $keywords = '', $description = '')
    {
        $this->assign('seo_title', $title);
        $this->assign('seo_keywords', $keywords);
        $this->assign('seo_description', $description);
    }
    
    /**
     * 分配变量
     */
    protected function assign($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->view->assign($k, $v);
            }
        } else {
            $this->view->assign($name, $value);
        }
        return $this;
    }
    
    /**
     * 渲染视图
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $replace, $config);
    }
    
    /**
     * 操作成功
     */
    protected function success($msg = '操作成功', $url = '', $data = '', $wait = 3)
    {
        if (empty($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        }
        
        $result = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait
        ];
        
        if ($this->request->isAjax()) {
            return json($result);
        }
        
        $this->assign('result', $result);
        return $this->fetch('public/success');
    }
    
    /**
     * 操作失败
     */
    protected function error($msg = '操作失败', $url = '', $data = '', $wait = 3)
    {
        if (empty($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        }
        
        $result = [
            'code' => 1,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait
        ];
        
        if ($this->request->isAjax()) {
            return json($result);
        }
        
        $this->assign('result', $result);
        return $this->fetch('public/error');
    }
    
    /**
     * JSON返回
     */
    protected function json($data = [], $code = 0, $msg = '')
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }
    
    /**
     * 重定向
     */
    protected function redirect($url, $params = [], $code = 302)
    {
        return redirect($url, $params, $code);
    }
}
