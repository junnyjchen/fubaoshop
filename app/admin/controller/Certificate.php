<?php
/**
 * 一物一码证书管理 - 优化版
 * 符宝网
 * 
 * @author  符宝网
 * @link    https://fubao.com
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
     * 获取统计数据
     */
    public function getStats()
    {
        $today_start = strtotime(date('Y-m-d'));
        $yesterday_start = strtotime('-1 day', $today_start);
        
        // 今日生成
        $today_count = Db::name('certificate_code')
            ->where('add_time', '>=', $today_start)
            ->count();
        
        // 昨日生成
        $yesterday_count = Db::name('certificate_code')
            ->whereBetween('add_time', [$yesterday_start, $today_start])
            ->count();
        
        // 累计证书
        $total_count = Db::name('certificate_code')->count();
        
        // 累计验证
        $total_verify = Db::name('certificate_verify_log')->count();
        
        // 今日验证
        $today_verify = Db::name('certificate_verify_log')
            ->where('add_time', '>=', $today_start)
            ->count();
        
        // 各状态数量
        $status_stats = Db::name('certificate_code')
            ->field('status, count(*) as count')
            ->group('status')
            ->select();
        $status_data = [-1 => 0, 0 => 0, 1 => 0, 2 => 0, 3 => 0];
        foreach ($status_stats as $v) {
            $status_data[$v['status']] = $v['count'];
        }
        
        // 模板数量
        $template_count = Db::name('certificate_template')->where(['is_enable' => 1])->count();
        
        return json([
            'code' => 0, 
            'msg' => 'success', 
            'data' => [
                'today_count' => $today_count,
                'yesterday_count' => $yesterday_count,
                'total_count' => $total_count,
                'total_verify' => $total_verify,
                'today_verify' => $today_verify,
                'status_data' => $status_data,
                'template_count' => $template_count,
            ]
        ]);
    }
    
    /**
     * 证书码列表
     */
    public function index()
    {
        if (IS_AJAX) {
            $keywords = input('keywords', '', 'trim');
            $template_id = input('template_id', 0, 'intval');
            $status = input('status', -1, 'intval');
            $date_range = input('date_range', '', 'trim');
            $page = input('page', 1, 'intval');
            $limit = input('limit', 20, 'intval');
            
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
            if (!empty($date_range)) {
                $dates = explode(' - ', $date_range);
                if (count($dates) == 2) {
                    $where[] = ['add_time', '>=', strtotime($dates[0])];
                    $where[] = ['add_time', '<=', strtotime($dates[1]) + 86399];
                }
            }
            
            $list = Db::name('certificate_code')
                ->where($where)
                ->order('id desc')
                ->page($page, $limit)
                ->select();
            
            $templates = Db::name('certificate_template')->column('name', 'id');
            foreach ($list as &$v) {
                $v['template_name'] = $templates[$v['template_id']] ?? '未知';
                $v['status_text'] = $this->getStatusText($v['status']);
                $v['add_time'] = date('Y-m-d H:i', $v['add_time']);
                $v['bind_time'] = $v['bind_time'] > 0 ? date('Y-m-d H:i', $v['bind_time']) : '-';
                $v['code_short'] = strlen($v['code']) > 12 ? substr($v['code'], 0, 8) . '...' . substr($v['code'], -4) : $v['code'];
            }
            
            $total = Db::name('certificate_code')->where($where)->count();
            return json(['code' => 0, 'msg' => '', 'count' => $total, 'data' => $list]);
        }
        
        $templates = Db::name('certificate_template')->where(['is_enable' => 1])->order('sort asc')->select();
        $this->assign('templates', $templates);
        return $this->fetch();
    }
    
    /**
     * 模板列表
     */
    public function template()
    {
        if (IS_AJAX) {
            $list = Db::name('certificate_template')
                ->order('sort asc, id desc')
                ->select();
            foreach ($list as &$v) {
                $v['is_enable_text'] = $v['is_enable'] == 1 ? '启用' : '禁用';
                $v['add_time'] = date('Y-m-d H:i', $v['add_time']);
                $v['thumb'] = empty($v['thumb']) ? '/static/images/no_image.jpg' : $v['thumb'];
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
            'description' => input('description', '', 'trim'),
            'content' => input('content', '', 'trim'),
            'thumb' => input('thumb', '', 'trim'),
            'is_enable' => input('is_enable', 1, 'intval'),
            'sort' => input('sort', 100, 'intval'),
            'upd_time' => time(),
        ];
        
        try {
            if ($id > 0) {
                Db::name('certificate_template')->where(['id' => $id])->update($data);
                return json(['code' => 0, 'msg' => '更新成功']);
            } else {
                $data['add_time'] = time();
                Db::name('certificate_template')->insert($data);
                return json(['code' => 0, 'msg' => '添加成功']);
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '保存失败']);
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
            return json(['code' => 1, 'msg' => '该模板已被 ' . $count . ' 个证书使用，无法删除']);
        }
        
        Db::name('certificate_template')->where(['id' => $id])->delete();
        return json(['code' => 0, 'msg' => '删除成功']);
    }
    
    /**
     * 生成证书
     */
    public function generate()
    {
        if (IS_AJAX) {
            $template_id = input('template_id', 0, 'intval');
            $quantity = input('quantity', 1, 'intval');
            
            if ($template_id <= 0) {
                return json(['code' => 1, 'msg' => '请选择模板']);
            }
            if ($quantity <= 0 || $quantity > 10000) {
                return json(['code' => 1, 'msg' => '数量必须在1-10000之间']);
            }
            
            $template = Db::name('certificate_template')->where(['id' => $template_id])->find();
            if (empty($template)) {
                return json(['code' => 1, 'msg' => '模板不存在']);
            }
            
            // 生成批次号
            $batch_no = 'B' . date('YmdHis') . sprintf('%04d', rand(0, 9999));
            
            // 生成证书
            $data = [];
            for ($i = 0; $i < $quantity; $i++) {
                $data[] = [
                    'code' => $this->generateUniqueCode(),
                    'template_id' => $template_id,
                    'batch_no' => $batch_no,
                    'status' => 0,
                    'add_time' => time(),
                ];
            }
            
            Db::name('certificate_code')->insertAll($data);
            
            return json([
                'code' => 0, 
                'msg' => '成功生成 ' . $quantity . ' 个证书',
                'data' => ['batch_no' => $batch_no]
            ]);
        }
        
        $templates = Db::name('certificate_template')->where(['is_enable' => 1])->order('sort asc')->select();
        $this->assign('templates', $templates);
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
     * 批量激活
     */
    public function batchActivate()
    {
        $ids = input('ids', '');
        if (empty($ids)) {
            return json(['code' => 1, 'msg' => '请选择要激活的证书']);
        }
        
        $id_arr = explode(',', $ids);
        $count = Db::name('certificate_code')
            ->whereIn('id', $id_arr)
            ->where('status', 0)
            ->update([
                'status' => 1,
                'upd_time' => time()
            ]);
        
        return json(['code' => 0, 'msg' => '成功激活 ' . $count . ' 个证书']);
    }
    
    /**
     * 批量禁用
     */
    public function batchDisable()
    {
        $ids = input('ids', '');
        if (empty($ids)) {
            return json(['code' => 1, 'msg' => '请选择要禁用的证书']);
        }
        
        $id_arr = explode(',', $ids);
        $count = Db::name('certificate_code')
            ->whereIn('id', $id_arr)
            ->where('status', '<', 2)
            ->update([
                'status' => -1,
                'upd_time' => time()
            ]);
        
        return json(['code' => 0, 'msg' => '成功禁用 ' . $count . ' 个证书']);
    }
    
    /**
     * 一键激活全部未激活证书
     */
    public function activateAll()
    {
        $template_id = input('template_id', 0, 'intval');
        $where = [['status', '=', 0]];
        if ($template_id > 0) {
            $where[] = ['template_id', '=', $template_id];
        }
        
        $count = Db::name('certificate_code')
            ->where($where)
            ->update([
                'status' => 1,
                'upd_time' => time()
            ]);
        
        return json(['code' => 0, 'msg' => '成功激活 ' . $count . ' 个证书']);
    }
    
    /**
     * 绑定商品
     */
    public function bindGoods()
    {
        $code_id = input('code_id', 0, 'intval');
        if ($code_id <= 0) {
            $this->error('参数错误');
        }
        
        $certificate = Db::name('certificate_code')->where(['id' => $code_id])->find();
        if (empty($certificate)) {
            $this->error('证书不存在');
        }
        
        $goods_id = input('goods_id', 0, 'intval');
        if ($goods_id > 0) {
            Db::name('certificate_code')->where(['id' => $code_id])->update([
                'goods_id' => $goods_id,
                'status' => 2,
                'bind_time' => time(),
                'upd_time' => time(),
            ]);
            return json(['code' => 0, 'msg' => '绑定成功']);
        }
        
        $this->assign('code_id', $code_id);
        return $this->fetch();
    }
    
    /**
     * 商品证书绑定
     */
    public function goodsBind()
    {
        $goods_id = input('goods_id', 0, 'intval');
        $this->assign('goods_id', $goods_id);
        
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
        
        Db::name('goods_certificate')->where(['goods_id' => $goods_id])->delete();
        
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
    
    /**
     * 证书详情
     */
    public function detail()
    {
        $id = input('id', 0, 'intval');
        if ($id <= 0) {
            $this->error('参数错误');
        }
        
        $certificate = Db::name('certificate_code')->where(['id' => $id])->find();
        if (empty($certificate)) {
            $this->error('证书不存在');
        }
        
        $template = Db::name('certificate_template')->where(['id' => $certificate['template_id']])->find();
        
        $goods = null;
        if ($certificate['goods_id'] > 0) {
            $goods = Db::name('goods')->where(['id' => $certificate['goods_id']])->find();
        }
        
        $verify_logs = Db::name('certificate_verify_log')
            ->where(['code_id' => $id])
            ->order('id desc')
            ->limit(10)
            ->select();
        
        $templates = Db::name('certificate_template')->where(['is_enable' => 1])->select();
        
        $this->assign([
            'certificate' => $certificate,
            'template' => $template,
            'goods' => $goods,
            'verify_logs' => $verify_logs,
            'templates' => $templates,
        ]);
        
        return $this->fetch();
    }
    
    /**
     * 导出证书
     */
    public function export()
    {
        $ids = input('ids', '');
        $batch_no = input('batch_no', '');
        
        $where = [];
        if (!empty($ids)) {
            $where[] = ['id', 'in', explode(',', $ids)];
        }
        if (!empty($batch_no)) {
            $where[] = ['batch_no', '=', $batch_no];
        }
        
        $list = Db::name('certificate_code')
            ->where($where)
            ->order('id desc')
            ->limit(10000)
            ->select();
        
        $templates = Db::name('certificate_template')->column('name', 'id');
        
        $csv_data = "ID,证书码,模板,状态,关联商品,验证次数,生成时间,绑定时间\n";
        foreach ($list as $v) {
            $status = $this->getStatusText($v['status']);
            $goods_name = $v['goods_id'] > 0 ? '商品ID:' . $v['goods_id'] : '-';
            $bind_time = $v['bind_time'] > 0 ? date('Y-m-d H:i:s', $v['bind_time']) : '-';
            $csv_data .= sprintf("%d,%s,%s,%s,%s,%d,%s,%s\n",
                $v['id'],
                $v['code'],
                $templates[$v['template_id']] ?? '未知',
                $status,
                $goods_name,
                $v['verify_count'],
                date('Y-m-d H:i:s', $v['add_time']),
                $bind_time
            );
        }
        
        $filename = 'certificates_' . date('YmdHis') . '.csv';
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv_data;
        exit;
    }
    
    /**
     * 更新证书状态
     */
    public function updateStatus()
    {
        if (!IS_POST) {
            return json(['code' => 1, 'msg' => '非法请求']);
        }
        
        $id = input('id', 0, 'intval');
        $status = input('status', 0, 'intval');
        
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        
        $certificate = Db::name('certificate_code')->where(['id' => $id])->find();
        if (empty($certificate)) {
            return json(['code' => 1, 'msg' => '证书不存在']);
        }
        
        if ($certificate['status'] >= 2 && $status < 0) {
            return json(['code' => 1, 'msg' => '已绑定或已验证的证书无法禁用']);
        }
        
        Db::name('certificate_code')->where(['id' => $id])->update([
            'status' => $status,
            'upd_time' => time()
        ]);
        
        return json(['code' => 0, 'msg' => '状态更新成功']);
    }
    
    /**
     * 验证记录
     */
    public function verifyLog()
    {
        if (IS_AJAX) {
            $keywords = input('keywords', '', 'trim');
            $page = input('page', 1, 'intval');
            $limit = input('limit', 20, 'intval');
            
            $where = [];
            if (!empty($keywords)) {
                $where[] = ['code', 'like', '%' . $keywords . '%'];
            }
            
            $list = Db::name('certificate_verify_log')
                ->where($where)
                ->order('id desc')
                ->page($page, $limit)
                ->select();
            
            $certificates = Db::name('certificate_code')->column('code,template_id', 'id');
            $templates = Db::name('certificate_template')->column('name', 'id');
            
            foreach ($list as &$v) {
                $v['code'] = $certificates[$v['code_id']]['code'] ?? '-';
                $template_id = $certificates[$v['code_id']]['template_id'] ?? 0;
                $v['template_name'] = $templates[$template_id] ?? '未知';
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
            $page = input('page', 1, 'intval');
            $limit = input('limit', 20, 'intval');
            
            $list = Db::name('certificate_code')
                ->field('batch_no, template_id, count(*) as total, min(add_time) as first_time, max(add_time) as last_time')
                ->group('batch_no')
                ->order('first_time desc')
                ->page($page, $limit)
                ->select();
            
            $templates = Db::name('certificate_template')->column('name', 'id');
            
            foreach ($list as &$v) {
                $v['template_name'] = $templates[$v['template_id']] ?? '未知';
                $v['first_time'] = date('Y-m-d H:i', $v['first_time']);
                $v['last_time'] = date('Y-m-d H:i', $v['last_time']);
            }
            
            $total = Db::name('certificate_code')
                ->field('batch_no')
                ->group('batch_no')
                ->count();
            
            return json(['code' => 0, 'msg' => '', 'count' => $total, 'data' => $list]);
        }
        
        return $this->fetch();
    }
    
    // ==================== 私有方法 ====================
    
    /**
     * 生成唯一证书码
     */
    private function generateUniqueCode()
    {
        $prefix = 'MZ';
        $timestamp = substr(time(), -8);
        $random = sprintf('%04d', rand(0, 9999));
        $check = strtoupper(substr(md5($prefix . $timestamp . $random), 0, 2));
        return $prefix . $timestamp . $random . $check;
    }
    
    /**
     * 获取状态文本
     */
    private function getStatusText($status)
    {
        $status_map = [
            -1 => '已禁用',
            0 => '未激活',
            1 => '已激活',
            2 => '已绑定',
            3 => '已验证',
        ];
        return $status_map[$status] ?? '未知';
    }
}
