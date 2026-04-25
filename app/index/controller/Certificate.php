<?php
/**
 * 证书前台验证
 * 
 * @author  觅智文化
 */
namespace app\index\controller;

use think\Db;

/**
 * 证书验证控制器
 */
class Certificate extends Base
{
    /**
     * 验证页面
     */
    public function verify()
    {
        $code = input('code', '', 'trim');
        
        if (empty($code)) {
            $this->error('证书码不能为空');
        }
        
        // 查询证书
        $cert = Db::name('certificate_code')->where(['code' => $code])->find();
        
        if (empty($cert)) {
            $this->assign('error', '未找到该证书');
            return $this->fetch('error');
        }
        
        // 获取模板
        $template = Db::name('certificate_template')->where(['id' => $cert['template_id']])->find();
        
        // 获取商品信息
        $goods = [];
        if ($cert['goods_id'] > 0) {
            $goods = Db::name('goods')->where(['id' => $cert['goods_id']])->find();
        }
        
        // 获取SKU信息
        $sku = [];
        if ($cert['goods_sku_id'] > 0) {
            $sku = Db::name('goods_sku')->where(['id' => $cert['goods_sku_id']])->find();
        }
        
        // 记录验证日志
        $this->recordVerifyLog($cert);
        
        // 更新验证次数
        Db::name('certificate_code')
            ->where(['id' => $cert['id']])
            ->update([
                'verify_count' => $cert['verify_count'] + 1,
                'last_verify_time' => time(),
                'status' => 3, // 已验证
            ]);
        
        // 处理证书内容变量替换
        $content = $template['content'] ?? '';
        $content = str_replace([
            '{goods_name}',
            '{kaiguang_date}',
            '{temple_name}',
            '{master_name}',
            '{material}',
            '{grade}',
            '{verify_no}',
            '{inherit_no}',
        ], [
            $goods['title'] ?? '法器',
            date('Y年m月d日'),
            '觅智道场',
            '道玄真人',
            '紫檀木',
            '上品',
            strtoupper(substr($code, -8)),
            strtoupper(substr($code, -6)),
        ], $content);
        
        $this->assign('cert', $cert);
        $this->assign('template', $template);
        $this->assign('goods', $goods);
        $this->assign('sku', $sku);
        $this->assign('content', $content);
        
        return $this->fetch();
    }
    
    /**
     * 扫码页面（用于生成二维码）
     */
    public function qrcode()
    {
        $code = input('code', '', 'trim');
        if (empty($code)) {
            $this->error('证书码不能为空');
        }
        
        $cert = Db::name('certificate_code')->where(['code' => $code])->find();
        if (empty($cert)) {
            $this->error('证书不存在');
        }
        
        $this->assign('code', $code);
        return $this->fetch();
    }
    
    /**
     * 记录验证日志
     */
    private function recordVerifyLog($cert)
    {
        $ip = request()->ip();
        $user_agent = request()->header('user-agent', '');
        
        // 简单获取地区（实际项目建议使用IP库）
        $province = '';
        $city = '';
        
        Db::name('certificate_verify_log')->insert([
            'code_id' => $cert['id'],
            'code' => $cert['code'],
            'ip' => $ip,
            'user_agent' => mb_substr($user_agent, 0, 500),
            'province' => $province,
            'city' => $city,
            'add_time' => time(),
        ]);
    }
}
