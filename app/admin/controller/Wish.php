<?php
namespace app\admin\controller;

use think\Db;

/**
 * 如愿管理控制器
 */
class Wish extends Common
{
    protected $wishModel;
    protected $wishCommentModel;
    protected $wishLikeModel;
    protected $wishCategoryModel;
    
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->wishModel = Db::name('wish');
        $this->wishCommentModel = Db::name('wish_comment');
        $this->wishLikeModel = Db::name('wish_like');
        $this->wishCategoryModel = Db::name('wish_category');
    }
    
    /**
     * 如愿列表
     */
    public function Index()
    {
        // 获取筛选参数
        $where = [];
        $type = input('type', '', 'trim');
        $status = input('status', '', 'trim');
        $keywords = input('keywords', '', 'trim');
        
        if (!empty($type)) {
            $where['wish_type'] = $type;
        }
        if ($status !== '') {
            $where['wish_status'] = $status;
        }
        if (!empty($keywords)) {
            $where['title|content'] = ['like', '%'.$keywords.'%'];
        }
        
        // 获取列表
        $list = $this->wishModel
            ->where($where)
            ->order('id desc')
            ->paginate(20)
            ->each(function($item) {
                if (!empty($item['images'])) {
                    $images = json_decode($item['images'], true);
                    $item['first_image'] = !empty($images) ? $images[0] : '';
                } else {
                    $item['first_image'] = '';
                }
                $item['time_text'] = date('Y-m-d H:i', $item['add_time']);
                return $item;
            });
        
        // 统计数据
        $stats = [
            'total' => $this->wishModel->count(),
            'pending' => $this->wishModel->where(['wish_status' => 0])->count(),
            'published' => $this->wishModel->where(['wish_status' => 1])->count(),
            'hidden' => $this->wishModel->where(['wish_status' => 2])->count(),
        ];
        
        $this->assign('list', $list);
        $this->assign('stats', $stats);
        $this->assign('type', $type);
        $this->assign('status', $status);
        $this->assign('keywords', $keywords);
        
        return $this->fetch();
    }
    
    /**
     * 如愿详情
     */
    public function Detail()
    {
        $id = input('id', 0, 'intval');
        
        if ($id <= 0) {
            $this->error('參數錯誤');
        }
        
        $detail = $this->wishModel->where(['id' => $id])->find();
        if (empty($detail)) {
            $this->error('內容不存在');
        }
        
        // 处理图片
        if (!empty($detail['images'])) {
            $detail['images'] = json_decode($detail['images'], true);
        } else {
            $detail['images'] = [];
        }
        
        // 获取评论
        $comments = $this->wishCommentModel
            ->where(['wish_id' => $id])
            ->order('id desc')
            ->select();
        
        $this->assign('detail', $detail);
        $this->assign('comments', $comments);
        
        return $this->fetch();
    }
    
    /**
     * 保存数据
     */
    public function Save()
    {
        if (!IS_POST) {
            return json(['code' => 1, 'msg' => '請求方式錯誤']);
        }
        
        $id = input('id', 0, 'intval');
        $data = [
            'wish_status' => input('wish_status', 1, 'intval'),
            'is_recommend' => input('is_recommend', 0, 'intval'),
            'sort' => input('sort', 100, 'intval'),
        ];
        
        if ($id > 0) {
            $data['upd_time'] = time();
            $result = $this->wishModel->where(['id' => $id])->update($data);
        } else {
            $result = false;
        }
        
        if ($result !== false) {
            return json(['code' => 0, 'msg' => '操作成功']);
        } else {
            return json(['code' => 1, 'msg' => '操作失敗']);
        }
    }
    
    /**
     * 删除
     */
    public function Delete()
    {
        $id = input('id', 0, 'intval');
        
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '參數錯誤']);
        }
        
        // 删除主记录
        $this->wishModel->where(['id' => $id])->delete();
        
        // 删除评论
        $this->wishCommentModel->where(['wish_id' => $id])->delete();
        
        // 删除点赞
        $this->wishLikeModel->where(['wish_id' => $id])->delete();
        
        return json(['code' => 0, 'msg' => '刪除成功']);
    }
    
    /**
     * 批量审核
     */
    public function BatchAudit()
    {
        $ids = input('ids', '', 'trim');
        $status = input('status', 1, 'intval');
        
        if (empty($ids)) {
            return json(['code' => 1, 'msg' => '請選擇要操作的項目']);
        }
        
        $id_arr = explode(',', $ids);
        $this->wishModel->where(['id' => ['in', $id_arr]])->update([
            'wish_status' => $status,
            'upd_time' => time()
        ]);
        
        return json(['code' => 0, 'msg' => '操作成功']);
    }
    
    /**
     * 分类管理
     */
    public function Category()
    {
        if (IS_POST) {
            return $this->saveCategory();
        }
        
        $list = $this->wishCategoryModel->order('sort asc, id asc')->select();
        
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    /**
     * 保存分类
     */
    private function saveCategory()
    {
        $id = input('id', 0, 'intval');
        $data = [
            'name' => input('name', '', 'trim'),
            'icon' => input('icon', '', 'trim'),
            'color' => input('color', '', 'trim'),
            'desc' => input('desc', '', 'trim'),
            'is_enable' => input('is_enable', 1, 'intval'),
            'sort' => input('sort', 100, 'intval'),
        ];
        
        if (empty($data['name'])) {
            return json(['code' => 1, 'msg' => '分類名稱不能為空']);
        }
        
        if ($id > 0) {
            $this->wishCategoryModel->where(['id' => $id])->update($data);
        } else {
            $data['add_time'] = time();
            $this->wishCategoryModel->insert($data);
        }
        
        return json(['code' => 0, 'msg' => '保存成功']);
    }
    
    /**
     * 删除分类
     */
    public function DeleteCategory()
    {
        $id = input('id', 0, 'intval');
        
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '參數錯誤']);
        }
        
        $this->wishCategoryModel->where(['id' => $id])->delete();
        
        return json(['code' => 0, 'msg' => '刪除成功']);
    }
    
    /**
     * 评论管理
     */
    public function Comment()
    {
        $where = [];
        $keywords = input('keywords', '', 'trim');
        
        if (!empty($keywords)) {
            $where['content'] = ['like', '%'.$keywords.'%'];
        }
        
        $list = $this->wishCommentModel
            ->where($where)
            ->order('id desc')
            ->paginate(20)
            ->each(function($item) {
                $wish = $this->wishModel->where(['id' => $item['wish_id']])->find();
                $item['wish_title'] = $wish['title'] ?? '未知';
                $item['time_text'] = date('Y-m-d H:i', $item['add_time']);
                return $item;
            });
        
        $this->assign('list', $list);
        
        return $this->fetch();
    }
    
    /**
     * 删除评论
     */
    public function DeleteComment()
    {
        $id = input('id', 0, 'intval');
        
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '參數錯誤']);
        }
        
        $comment = $this->wishCommentModel->where(['id' => $id])->find();
        if ($comment) {
            $this->wishCommentModel->where(['id' => $id])->delete();
            // 更新评论数
            $this->wishModel->where(['id' => $comment['wish_id']])->setDec('comment_count');
        }
        
        return json(['code' => 0, 'msg' => '刪除成功']);
    }
}
