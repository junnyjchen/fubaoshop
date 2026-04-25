<?php
/**
 * 一物一码证书管理
 * 
 * @author  觅智文化
 * @link    https://mizhi.com
 */
namespace app\admin\controller;

use app\admin\controller\Base;
use think\Db;

/**
 * 证书管理
 */
class Certificate extends Base
{
    /**
     * 证书码列表
     */
    public function index()
    {
        if (IS_AJAX) {
            // 获取参数
            $keywords = input('keywords', '', 'trim');
            $template_id = input('template_id', 0, 'intval');
            $status = input('status', -1, 'intval');
            $page = input('page', 1, 'intval');
            $limit = input('limit', 20, 'intval');
            
            // 构建查询
            $where = [];
            if (!empty($keywords)) {
                $where[] = ['code', 'like', '%' . $keywords . '%'];
            }
            if ($template_id > 0) {
                $where[] = ['template_id', '=', $template_id];
            }
            if ($status >= 0) {
                $where[] = ['status', '=', $status];
            }
            
            // 查询数据
            $list = Db::name('certificate_code')
                ->where($where)
                ->order('id desc')
                ->page($page, $limit)
                ->select();
            
            // 获取模板名称
            $templates = Db::name('certificate_template')->column('name', 'id');
            foreach ($list as &$v) {
                $v['template_name'] = $templates[$v['template_id']] ?? '未知';
                $v['status_text'] = $this->getStatusText($v['status']);
                $v['add_time'] = date('Y-m-d H:i', $v['add_time']);
                $v['bind_time'] = $v['bind_time'] > 0 ? date('Y-m-d H:i', $v['bind_time']) : '-';
            }
            
            // 统计
            $total = Db::name('certificate_code')->where($where)->count();
            
            return json(['code' => 0, 'msg' => '', 'count' => $total, 'data' => $list]);
        }
        
        // 获取模板列表
        $templates = Db::name('certificate_template')->where(['is_enable' => 1])->order('sort asc')->select();
        $this->assign('templates', $templates);
        
        return $this->fetch();
    }
    
    /**
     * 模板管理
     */
    public function template()
    {
        if (IS_AJAX) {
            $list = Db::name('certificate_template')->order('sort asc, id desc')->select();
            foreach ($list as &$v) {
                $v['is_enable_text'] = $v['is_enable'] == 1 ? '启用' : '禁用';
                $v['add_time'] = date('Y-m-d H:i', $v['add_time']);
            }
            return json(['code' => 0, 'msg' => '', 'data' => $list]);
        }
        return $this->fetch();
    }
    
    /**
     * 保存模板
     */
    public function templateSave()
    {
        if (!IS_POST) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        
        $id = input('id', 0, 'intval');
        $data = [
            'name' => input('name', '', 'trim'),
            'code' => input('code', '', 'trim'),
            'content' => input('content', '', 'trim'),
            'image' => input('image', '', 'trim'),
            'is_enable' => input('is_enable', 1, 'intval'),
            'sort' => input('sort', 0, 'intval'),
            'upd_time' => time(),
        ];
        
        if (empty($data['name']) || empty($data['code'])) {
            return json(['code' => 1, 'msg' => '名称和编码不能为空']);
        }
        
        try {
            if ($id > 0) {
                $data['id'] = $id;
                Db::name('certificate_template')->update($data);
            } else {
                $data['add_time'] = time();
                Db::name('certificate_template')->insert($data);
            }
            return json(['code' => 0, 'msg' => '保存成功']);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '保存失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 删除模板
     */
    public function templateDelete()
    {
        $id = input('id', 0, 'intval');
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        
        // 检查是否有证书使用此模板
        $count = Db::name('certificate_code')->where(['template_id' => $id])->count();
        if ($count > 0) {
            return json(['code' => 1, 'msg' => '该模板已被使用，无法删除']);
        }
        
        Db::name('certificate_template')->where(['id' => $id])->delete();
        return json(['code' => 0, 'msg' => '删除成功']);
    }
    
    /**
     * 生成证书码
     */
    public function generate()
    {
        if (IS_AJAX || IS_POST) {
            $template_id = input('template_id', 0, 'intval');
            $quantity = input('quantity', 1, 'intval');
            $goods_id = input('goods_id', 0, 'intval');
            $goods_sku_id = input('goods_sku_id', 0, 'intval');
            
            if ($template_id <= 0) {
                return json(['code' => 1, 'msg' => '请选择证书模板']);
            }
            
            if ($quantity <= 0 || $quantity > 10000) {
                return json(['code' => 1, 'msg' => '生成数量需在1-10000之间']);
            }
            
            // 检查模板是否存在
            $template = Db::name('certificate_template')->where(['id' => $template_id, 'is_enable' => 1])->find();
            if (empty($template)) {
                return json(['code' => 1, 'msg' => '证书模板不存在或已禁用']);
            }
            
            // 生成批次号
            $batch_no = 'CERT' . date('YmdHis') . rand(1000, 9999);
            
            // 记录批次
            $batch_data = [
                'batch_no' => $batch_no,
                'template_id' => $template_id,
                'quantity' => $quantity,
                'goods_id' => $goods_id,
                'goods_sku_id' => $goods_sku_id,
                'status' => 1,
                'add_time' => time(),
                'upd_time' => time(),
            ];
            Db::name('certificate_batch')->insert($batch_data);
            
            // 生成证书码
            $codes = [];
            $time = time();
            for ($i = 0; $i < $quantity; $i++) {
                $code = $this->generateUniqueCode();
                $codes[] = [
                    'code' => $code,
                    'qrcode' => $this->generateQrcodeUrl($code),
                    'template_id' => $template_id,
                    'goods_id' => $goods_id,
                    'goods_sku_id' => $goods_sku_id,
                    'status' => 0, // 未激活
                    'add_time' => $time,
                    'upd_time' => $time,
                ];
            }
            
            // 批量插入
            Db::name('certificate_code')->insertAll($codes);
            
            return json(['code' => 0, 'msg' => '成功生成 ' . $quantity . ' 个证书码', 'batch_no' => $batch_no]);
        }
        
        // 获取模板列表
        $templates = Db::name('certificate_template')->where(['is_enable' => 1])->order('sort asc')->select();
        $this->assign('templates', $templates);
        
        return $this->fetch();
    }
    
    /**
     * 导出证书码
     */
    public function export()
    {
        $ids = input('ids', '');
        if (empty($ids)) {
            return json(['code' => 1, 'msg' => '请选择要导出的证书']);
        }
        
        $id_arr = explode(',', $ids);
        $list = Db::name('certificate_code')->whereIn('id', $id_arr)->select();
        
        if (empty($list)) {
            return json(['code' => 1, 'msg' => '没有找到对应的证书']);
        }
        
        $templates = Db::name('certificate_template')->column('name', 'id');
        
        // 生成CSV
        $str = "证书码,模板,状态,生成时间\n";
        foreach ($list as $v) {
            $status = $this->getStatusText($v['status']);
            $time = date('Y-m-d H:i:s', $v['add_time']);
            $template = $templates[$v['template_id']] ?? '';
            $str .= "{$v['code']},{$template},{$status},{$time}\n";
        }
        
        $filename = 'certificates_' . date('YmdHis') . '.csv';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo $str;
        exit;
    }
    
    /**
     * 证书详情
     */
    public function detail()
    {
        $id = input('id', 0, 'intval');
        if ($id <= 0) {
            $this->error('参数错误');
        }
        
        $cert = Db::name('certificate_code')->where(['id' => $id])->find();
        if (empty($cert)) {
            $this->error('证书不存在');
        }
        
        // 获取模板
        $template = Db::name('certificate_template')->where(['id' => $cert['template_id']])->find();
        
        // 获取商品信息
        $goods = [];
        if ($cert['goods_id'] > 0) {
            $goods = Db::name('goods')->where(['id' => $cert['goods_id']])->find();
        }
        
        // 获取验证记录
        $logs = Db::name('certificate_verify_log')->where(['code_id' => $id])->order('id desc')->limit(20)->select();
        foreach ($logs as &$v) {
            $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        }
        
        $this->assign('cert', $cert);
        $this->assign('template', $template);
        $this->assign('goods', $goods);
        $this->assign('logs', $logs);
        
        return $this->fetch();
    }
    
    /**
     * 验证记录
     */
    public function verifyLog()
    {
        if (IS_AJAX) {
            $code = input('code', '', 'trim');
            $page = input('page', 1, 'intval');
            $limit = input('limit', 20, 'intval');
            
            $where = [];
            if (!empty($code)) {
                $where[] = ['code', '=', $code];
            }
            
            $list = Db::name('certificate_verify_log')
                ->where($where)
                ->order('id desc')
                ->page($page, $limit)
                ->select();
            
            foreach ($list as &$v) {
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
            
            $total = Db::name('certificate_verify_log')->where($where)->count();
            
            return json(['code' => 0, 'msg' => '', 'count' => $total, 'data' => $list]);
        }
        
        return $this->fetch();
    }
    
    /**
     * 批次记录
     */
    public function batch()
    {
        if (IS_AJAX) {
            $list = Db::name('certificate_batch')
                ->order('id desc')
                ->select();
            
            $templates = Db::name('certificate_template')->column('name', 'id');
            
            foreach ($list as &$v) {
                $v['template_name'] = $templates[$v['template_id']] ?? '未知';
                $v['status_text'] = $v['status'] == 1 ? '已完成' : ($v['status'] == 0 ? '处理中' : '失败');
                $v['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
            
            return json(['code' => 0, 'msg' => '', 'data' => $list]);
        }
        
        return $this->fetch();
    }
    
    /**
     * 删除证书
     */
    public function delete()
    {
        $ids = input('ids', '');
        if (empty($ids)) {
            return json(['code' => 1, 'msg' => '请选择要删除的证书']);
        }
        
        // 检查是否有已绑定的证书
        $id_arr = explode(',', $ids);
        $bound = Db::name('certificate_code')
            ->whereIn('id', $id_arr)
            ->where('status', '>=', 2)
            ->count();
        
        if ($bound > 0) {
            return json(['code' => 1, 'msg' => '选中的证书中包含已绑定或已验证的证书，无法删除']);
        }
        
        Db::name('certificate_code')->whereIn('id', $id_arr)->delete();
        return json(['code' => 0, 'msg' => '删除成功']);
    }
    
    /**
     * 绑定商品
     */
    public function bindGoods()
    {
        if (!IS_POST) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        
        $ids = input('ids', '');
        $goods_id = input('goods_id', 0, 'intval');
        
        if (empty($ids)) {
            return json(['code' => 1, 'msg' => '请选择要绑定的证书']);
        }
        
        if ($goods_id <= 0) {
            return json(['code' => 1, 'msg' => '请选择要绑定的商品']);
        }
        
        $id_arr = explode(',', $ids);
        
        // 更新绑定状态
        Db::name('certificate_code')
            ->whereIn('id', $id_arr)
            ->where('status', '<', 2) // 只能绑定未绑定的
            ->update([
                'goods_id' => $goods_id,
                'status' => 2,
                'bind_time' => time(),
                'upd_time' => time(),
            ]);
        
        return json(['code' => 0, 'msg' => '绑定成功']);
    }
    
    /**
     * 商品证书绑定管理
     */
    public function goodsBind()
    {
        if (IS_AJAX) {
            $goods_id = input('goods_id', 0, 'intval');
            $list = Db::name('goods_certificate')->where(['goods_id' => $goods_id])->select();
            
            $templates = Db::name('certificate_template')->column('name', 'id');
            
            foreach ($list as &$v) {
                $v['template_name'] = $templates[$v['template_id']] ?? '未知';
                $v['is_required_text'] = $v['is_required'] == 1 ? '是' : '否';
            }
            
            return json(['code' => 0, 'msg' => '', 'data' => $list]);
        }
        
        $goods_id = input('goods_id', 0, 'intval');
        $this->assign('goods_id', $goods_id);
        
        // 获取可选模板
        $templates = Db::name('certificate_template')->where(['is_enable' => 1])->order('sort asc')->select();
        $this->assign('templates', $templates);
        
        return $this->fetch();
    }
    
    /**
     * 保存商品证书绑定
     */
    public function saveGoodsBind()
    {
        if (!IS_POST) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        
        $goods_id = input('goods_id', 0, 'intval');
        $template_ids = input('template_ids', []);
        
        if ($goods_id <= 0) {
            return json(['code' => 1, 'msg' => '商品ID错误']);
        }
        
        // 删除原有绑定
        Db::name('goods_certificate')->where(['goods_id' => $goods_id])->delete();
        
        // 添加新绑定
        if (!empty($template_ids)) {
            $data = [];
            foreach ($template_ids as $template_id) {
                $data[] = [
                    'goods_id' => $goods_id,
                    'template_id' => $template_id,
                    'is_required' => 0,
                    'quantity' => 1,
                    'add_time' => time(),
                    'upd_time' => time(),
                ];
            }
            Db::name('goods_certificate')->insertAll($data);
        }
        
        return json(['code' => 0, 'msg' => '保存成功']);
    }
    
    // ==================== 私有方法 ====================
    
    /**
     * 生成唯一证书码
     */
    private function generateUniqueCode()
    {
        // 格式: 前缀 + 时间戳 + 随机数 + 校验位
        $prefix = 'MZ'; // 觅智前缀
        $timestamp = substr(time(), -8);
        $random = sprintf('%04d', rand(0, 9999));
        $check = strtoupper(substr(md5($prefix . $timestamp . $random), 0, 2));
        return $prefix . $timestamp . $random . $check;
    }
    
    /**
     * 生成二维码URL
     */
    private function generateQrcodeUrl($code)
    {
        // 返回验证URL
        return url('index/certificate/verify', ['code' => $code], true, true);
    }
    
    /**
     * 获取状态文本
     */
    private function getStatusText($status)
    {
        $status_map = [
            0 => '未激活',
            1 => '已激活',
            2 => '已绑定',
            3 => '已验证',
        ];
        return $status_map[$status] ?? '未知';
    }
}
