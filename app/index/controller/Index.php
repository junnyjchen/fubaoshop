<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2099 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://opensource.org/licenses/mit-license.php )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\module\LayoutModule;
use app\service\SeoService;
use app\service\AdminService;
use app\service\SlideService;
use app\service\GoodsService;
use app\service\GoodsCategoryService;
use app\service\ArticleService;
use app\service\OrderService;
use app\service\AppHomeNavService;
use app\service\BrandService;
use app\service\LinkService;
use app\service\LayoutService;
use app\service\ThemeAdminService;
use think\View;

/**
 * 首页
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Index extends Common
{
    /**
     * 构造方法
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-30
     * @desc    description
     */
    public function __construct()
    {
        parent::__construct();

        // web端首页状态
        $this->SiteStstusCheck('_web_home');
    }

    /**
     * 首页
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-12-02T11:11:49+0800
     */
    public function Index()
    {
        // 强制使用符宝网自定义首页模板
        return $this->fubaoHome();
    }
    
    /**
     * 符宝网自定义首页
     * @author   符宝网
     */
    public function fubaoHome()
    {
        $assign = [
            // 手机默认下导航
            'navigation'  => IsMobile() ? AppHomeNavService::AppHomeNav() : [],
            // 友情链接
            'link_list'   => LinkService::HomeLinkList(),
        ];
        // 首页楼层数据
        $assign['goods_floor_list'] = GoodsService::HomeFloorList();
        // 楼层数据顶部钩子
        $assign['plugins_view_home_floor_top_data'] = MyEventTrigger('plugins_view_home_floor_top', [
            'hook_name'    => 'plugins_view_home_floor_top',
            'is_backend'    => false,
            'user'          => $this->user,
        ]);
        
        MyViewAssign($assign);
        return View('index');
    }
}
?>