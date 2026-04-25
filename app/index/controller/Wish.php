<?php
namespace app\index\controller;

use think\Db;

/**
 * 如愿控制器
 * 评论聚合、晒图、晒视频
 */
class Wish extends BaseIndex
{
    // 模型
    protected $wishModel;
    protected $wishCommentModel;
    protected $wishLikeModel;
    
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->wishModel = Db::name('wish');
        $this->wishCommentModel = Db::name('wish_comment');
        $this->wishLikeModel = Db::name('wish_like');
    }
    
    /**
     * 如愿首页
     */
    public function Index()
    {
        // 获取筛选参数
        $type = input('type', 'all', 'trim');
        $sort = input('sort', 'recommend', 'trim');
        $page = input('page', 1, 'intval');
        $page_size = 12;
        
        // 构建查询条件
        $where = [
            'wish_status' => 1
        ];
        
        // 类型筛选
        if ($type != 'all' && in_array($type, ['share', 'wish', 'answer'])) {
            $type_map = ['share' => 1, 'wish' => 2, 'answer' => 3];
            $where['wish_type'] = $type_map[$type];
        }
        
        // 排序
        $order = 'is_recommend desc, sort asc, add_time desc';
        if ($sort == 'new') {
            $order = 'add_time desc';
        } elseif ($sort == 'hot') {
            $order = 'like_count desc, view_count desc';
        }
        
        // 获取列表
        $list = $this->wishModel
            ->where($where)
            ->order($order)
            ->page($page, $page_size)
            ->select();
        
        // 处理图片数据
        foreach ($list as &$item) {
            if (!empty($item['images'])) {
                $item['images'] = json_decode($item['images'], true) ?: [];
            } else {
                $item['images'] = [];
            }
            // 格式化时间
            $item['time_text'] = $this->formatTime($item['add_time']);
            // 获取商品信息
            if ($item['goods_id'] > 0) {
                $goods = Db::name('goods')->where('id', $item['goods_id'])->find();
                $item['goods_title'] = $goods['title'] ?? '觅智好物';
                $item['goods_price'] = $goods['min_price'] ?? 0;
            }
        }
        
        // 获取推荐内容
        $recommend = $this->wishModel
            ->where(['wish_status' => 1, 'is_recommend' => 1])
            ->order('like_count desc')
            ->limit(6)
            ->select();
        
        foreach ($recommend as &$r) {
            if (!empty($r['images'])) {
                $r['images'] = json_decode($r['images'], true) ?: [];
            } else {
                $r['images'] = [];
            }
        }
        
        // 获取分类
        $categories = Db::name('wish_category')->where(['is_enable' => 1])->order('sort asc')->select();
        
        // 统计
        $stats = [
            'total' => $this->wishModel->where(['wish_status' => 1])->count(),
            'share' => $this->wishModel->where(['wish_status' => 1, 'wish_type' => 1])->count(),
            'wish' => $this->wishModel->where(['wish_status' => 1, 'wish_type' => 2])->count(),
            'answer' => $this->wishModel->where(['wish_status' => 1, 'wish_type' => 3])->count(),
        ];
        
        // 获取当前用户是否点赞
        $user_id = isset($this->user['id']) ? $this->user['id'] : 0;
        $wish_ids = array_column($list, 'id');
        $liked_ids = [];
        if (!empty($wish_ids) && $user_id > 0) {
            $liked = $this->wishLikeModel->where(['wish_id' => ['in', $wish_ids], 'user_id' => $user_id])->column('wish_id');
            $liked_ids = array_flip($liked);
        }
        
        // 渲染数据
        $this->assign('list', $list);
        $this->assign('recommend', $recommend);
        $this->assign('categories', $categories);
        $this->assign('stats', $stats);
        $this->assign('liked_ids', $liked_ids);
        $this->assign('type', $type);
        $this->assign('sort', $sort);
        $this->assign('page', $page);
        
        return $this->fetch();
    }
    
    /**
     * 如愿详情
     */
    public function Detail()
    {
        $id = input('id', 0, 'intval');
        
        if ($id <= 0) {
            $this->error('参数错误');
        }
        
        // 获取详情
        $detail = $this->wishModel->where(['id' => $id, 'wish_status' => 1])->find();
        if (empty($detail)) {
            $this->error('内容不存在或已下架');
        }
        
        // 更新浏览数
        $this->wishModel->where('id', $id)->setInc('view_count');
        
        // 处理图片
        if (!empty($detail['images'])) {
            $detail['images'] = json_decode($detail['images'], true) ?: [];
        } else {
            $detail['images'] = [];
        }
        $detail['time_text'] = $this->formatTime($detail['add_time']);
        
        // 获取商品信息
        if ($detail['goods_id'] > 0) {
            $goods = Db::name('goods')->where('id', $detail['goods_id'])->find();
            $detail['goods_title'] = $goods['title'] ?? '觅智好物';
            $detail['goods_price'] = $goods['min_price'] ?? 0;
            $detail['goods_images'] = !empty($goods['images']) ? json_decode($goods['images'], true) : [];
        }
        
        // 获取评论
        $comments = $this->wishCommentModel
            ->where(['wish_id' => $id, 'is_show' => 1])
            ->order('like_count desc, add_time desc')
            ->limit(50)
            ->select();
        
        foreach ($comments as &$c) {
            $c['time_text'] = $this->formatTime($c['add_time']);
        }
        
        // 判断是否点赞
        $user_id = isset($this->user['id']) ? $this->user['id'] : 0;
        $is_liked = false;
        if ($user_id > 0) {
            $is_liked = $this->wishLikeModel->where(['wish_id' => $id, 'user_id' => $user_id])->count() > 0;
        }
        
        // 获取相关推荐
        $related = $this->wishModel
            ->where(['wish_status' => 1, 'wish_type' => $detail['wish_type']])
            ->where('id', '<>', $id)
            ->order('like_count desc')
            ->limit(4)
            ->select();
        
        foreach ($related as &$r) {
            if (!empty($r['images'])) {
                $r['images'] = json_decode($r['images'], true) ?: [];
            } else {
                $r['images'] = [];
            }
            $r['time_text'] = $this->formatTime($r['add_time']);
        }
        
        // 渲染数据
        $this->assign('detail', $detail);
        $this->assign('comments', $comments);
        $this->assign('related', $related);
        $this->assign('is_liked', $is_liked);
        
        return $this->fetch();
    }
    
    /**
     * 发布如愿
     */
    public function Publish()
    {
        if (IS_POST) {
            return $this->save();
        }
        
        // 获取分类
        $categories = Db::name('wish_category')->where(['is_enable' => 1])->order('sort asc')->select();
        
        // 获取用户可晒单的商品（已收货订单）
        $user_id = isset($this->user['id']) ? $this->user['id'] : 0;
        $goods_list = [];
        if ($user_id > 0) {
            // 查询用户已收货订单中的商品
            $orders = Db::name('order')->where(['user_id' => $user_id, 'status' => 4])->column('id');
            if (!empty($orders)) {
                $order_ids = Db::name('order_goods')->where(['order_id' => ['in', $orders]])->column('goods_id');
                if (!empty($order_ids)) {
                    $goods_list = Db::name('goods')->where(['id' => ['in', array_unique($order_ids)]])->field('id,title,images,min_price')->select();
                }
            }
        }
        
        $this->assign('categories', $categories);
        $this->assign('goods_list', $goods_list);
        
        return $this->fetch();
    }
    
    /**
     * 保存发布
     */
    private function save()
    {
        $user_id = isset($this->user['id']) ? $this->user['id'] : 0;
        
        $data = [
            'goods_id' => input('goods_id', 0, 'intval'),
            'title' => input('title', '', 'trim,htmlspecialchars'),
            'content' => input('content', '', 'trim,htmlspecialchars'),
            'images' => input('images', '', 'trim'),
            'video' => input('video', '', 'trim'),
            'video_cover' => input('video_cover', '', 'trim'),
            'wish_type' => input('wish_type', 1, 'intval'),
        ];
        
        // 验证
        if (empty($data['content']) || mb_strlen($data['content']) < 10) {
            return json(['code' => 1, 'msg' => '内容不能少于10个字']);
        }
        if (mb_strlen($data['content']) > 2000) {
            return json(['code' => 1, 'msg' => '内容不能超过2000字']);
        }
        if (empty($data['images']) && empty($data['video'])) {
            return json(['code' => 1, 'msg' => '请上传图片或视频']);
        }
        
        $data['user_id'] = $user_id;
        $data['user_type'] = $user_id > 0 ? 0 : 1;
        $data['order_id'] = input('order_id', '', 'trim');
        $data['add_time'] = time();
        $data['upd_time'] = time();
        
        // 处理图片JSON
        if (!empty($data['images'])) {
            // 兼容处理
            if (is_string($data['images'])) {
                $data['images'] = json_encode(array_filter(explode(',', $data['images'])));
            }
        }
        
        // 根据配置决定是否需要审核
        $auto_publish = MyC('home_wish_auto_publish', 1, true);
        $data['wish_status'] = $auto_publish ? 1 : 0;
        
        $result = $this->wishModel->insertGetId($data);
        
        if ($result) {
            return json([
                'code' => 0,
                'msg' => $auto_publish ? '发布成功' : '提交成功，等待审核',
                'data' => ['id' => $result]
            ]);
        } else {
            return json(['code' => 1, 'msg' => '发布失败']);
        }
    }
    
    /**
     * 点赞
     */
    public function Like()
    {
        $id = input('id', 0, 'intval');
        $user_id = isset($this->user['id']) ? $this->user['id'] : 0;
        $user_type = $user_id > 0 ? 0 : 1;
        
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        
        // 检查是否已点赞
        $exist = $this->wishLikeModel->where(['wish_id' => $id, 'user_id' => $user_id, 'user_type' => $user_type])->find();
        
        if ($exist) {
            // 取消点赞
            $this->wishLikeModel->where(['wish_id' => $id, 'user_id' => $user_id, 'user_type' => $user_type])->delete();
            $this->wishModel->where('id', $id)->setDec('like_count');
            return json(['code' => 0, 'msg' => '已取消点赞', 'data' => ['action' => 'unlike']]);
        } else {
            // 添加点赞
            $this->wishLikeModel->insert([
                'wish_id' => $id,
                'user_id' => $user_id,
                'user_type' => $user_type,
                'add_time' => time()
            ]);
            $this->wishModel->where('id', $id)->setInc('like_count');
            return json(['code' => 0, 'msg' => '点赞成功', 'data' => ['action' => 'like']]);
        }
    }
    
    /**
     * 评论
     */
    public function Comment()
    {
        $id = input('id', 0, 'intval');
        $content = input('content', '', 'trim,htmlspecialchars');
        $pid = input('pid', 0, 'intval');
        $user_id = isset($this->user['id']) ? $this->user['id'] : 0;
        $user_type = $user_id > 0 ? 0 : 1;
        
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        if (empty($content) || mb_strlen($content) < 2) {
            return json(['code' => 1, 'msg' => '评论内容不能少于2个字']);
        }
        if (mb_strlen($content) > 500) {
            return json(['code' => 1, 'msg' => '评论内容不能超过500字']);
        }
        
        // 检查晒单是否存在
        $wish = $this->wishModel->where(['id' => $id, 'wish_status' => 1])->find();
        if (empty($wish)) {
            return json(['code' => 1, 'msg' => '内容不存在']);
        }
        
        $data = [
            'wish_id' => $id,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'pid' => $pid,
            'content' => $content,
            'add_time' => time()
        ];
        
        $result = $this->wishCommentModel->insertGetId($data);
        
        if ($result) {
            // 更新评论数
            $this->wishModel->where('id', $id)->setInc('comment_count');
            return json(['code' => 0, 'msg' => '评论成功', 'data' => ['id' => $result]]);
        } else {
            return json(['code' => 1, 'msg' => '评论失败']);
        }
    }
    
    /**
     * 我的如愿
     */
    public function My()
    {
        $user_id = isset($this->user['id']) ? $this->user['id'] : 0;
        
        if ($user_id <= 0) {
            $this->error('请先登录');
        }
        
        $page = input('page', 1, 'intval');
        $page_size = 12;
        $type = input('type', 'all', 'trim');
        
        $where = ['user_id' => $user_id, 'user_type' => 0];
        
        if ($type != 'all' && in_array($type, ['share', 'wish', 'answer'])) {
            $type_map = ['share' => 1, 'wish' => 2, 'answer' => 3];
            $where['wish_type'] = $type_map[$type];
        }
        
        $list = $this->wishModel
            ->where($where)
            ->order('add_time desc')
            ->page($page, $page_size)
            ->select();
        
        foreach ($list as &$item) {
            if (!empty($item['images'])) {
                $item['images'] = json_decode($item['images'], true) ?: [];
            } else {
                $item['images'] = [];
            }
            $item['time_text'] = $this->formatTime($item['add_time']);
            // 状态文本
            $status_map = [0 => '待审核', 1 => '已发布', 2 => '已下架'];
            $item['status_text'] = $status_map[$item['wish_status']] ?? '未知';
        }
        
        $this->assign('list', $list);
        $this->assign('type', $type);
        $this->assign('page', $page);
        
        return $this->fetch();
    }
    
    /**
     * 格式化时间
     */
    private function formatTime($timestamp)
    {
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return '刚刚';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . '天前';
        } else {
            return date('Y-m-d', $timestamp);
        }
    }
}
