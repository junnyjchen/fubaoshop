-- ============================================
-- 觅智商城 - 如愿功能数据库脚本
-- 功能：评论聚合、晒图、晒视频
-- 版本: 1.0.0
-- 创建时间: 2025-01
-- ============================================

SET NAMES utf8mb4;

-- ----------------------------
-- 如愿晒单表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_wish`;
CREATE TABLE `sxo_wish` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `user_type` tinyint(1) unsigned DEFAULT '0' COMMENT '用户类型(0普通 1游客)',
  `goods_id` int(11) unsigned DEFAULT '0' COMMENT '商品ID',
  `order_id` varchar(50) DEFAULT '' COMMENT '订单ID',
  `title` varchar(200) DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `images` text COMMENT '晒图(JSON数组)',
  `video` varchar(500) DEFAULT '' COMMENT '视频URL',
  `video_cover` varchar(500) DEFAULT '' COMMENT '视频封面',
  `wish_type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型(1晒单 2许愿 3还愿)',
  `wish_status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0待审核 1已发布 2已下架)',
  `like_count` int(11) unsigned DEFAULT '0' COMMENT '点赞数',
  `view_count` int(11) unsigned DEFAULT '0' COMMENT '浏览数',
  `comment_count` int(11) unsigned DEFAULT '0' COMMENT '评论数',
  `is_recommend` tinyint(1) unsigned DEFAULT '0' COMMENT '是否推荐',
  `sort` int(11) unsigned DEFAULT 100 COMMENT '排序',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_goods_id` (`goods_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_wish_status` (`wish_status`),
  KEY `idx_is_recommend` (`is_recommend`),
  KEY `idx_add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='如愿晒单表';

-- ----------------------------
-- 如愿评论表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_wish_comment`;
CREATE TABLE `sxo_wish_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wish_id` int(11) unsigned DEFAULT '0' COMMENT '晒单ID',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `user_type` tinyint(1) unsigned DEFAULT '0' COMMENT '用户类型',
  `pid` int(11) unsigned DEFAULT '0' COMMENT '父级ID',
  `content` varchar(500) DEFAULT '' COMMENT '评论内容',
  `like_count` int(11) unsigned DEFAULT '0' COMMENT '点赞数',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '是否显示',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `idx_wish_id` (`wish_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='如愿评论表';

-- ----------------------------
-- 如愿点赞表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_wish_like`;
CREATE TABLE `sxo_wish_like` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wish_id` int(11) unsigned DEFAULT '0' COMMENT '晒单ID',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `user_type` tinyint(1) unsigned DEFAULT '0' COMMENT '用户类型',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wish_user` (`wish_id`, `user_id`, `user_type`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='如愿点赞表';

-- ----------------------------
-- 如愿分类表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_wish_category`;
CREATE TABLE `sxo_wish_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '分类名称',
  `icon` varchar(100) DEFAULT '' COMMENT '图标',
  `color` varchar(20) DEFAULT '' COMMENT '颜色',
  `desc` varchar(200) DEFAULT '' COMMENT '描述',
  `is_enable` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启用',
  `sort` int(11) unsigned DEFAULT 100 COMMENT '排序',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='如愿分类表';

-- ----------------------------
-- 初始化分类数据
-- ----------------------------
INSERT INTO `sxo_wish_category` (`id`, `name`, `icon`, `color`, `desc`, `is_enable`, `sort`, `add_time`) VALUES
(1, '晒单分享', '📦', '#c41e3a', '分享您的开箱喜悦', 1, 100, UNIX_TIMESTAMP()),
(2, '心愿达成', '✨', '#d4af37', '记录愿望实现的时刻', 1, 90, UNIX_TIMESTAMP()),
(3, '还愿感谢', '🙏', '#008080', '感谢神灵庇佑', 1, 80, UNIX_TIMESTAMP()),
(4, '使用心得', '💡', '#9370db', '分享使用体验', 1, 70, UNIX_TIMESTAMP());

-- ----------------------------
-- 初始化示例数据
-- ----------------------------
SET @NOW = UNIX_TIMESTAMP();

INSERT INTO `sxo_wish` (`id`, `user_id`, `user_type`, `goods_id`, `title`, `content`, `images`, `wish_type`, `wish_status`, `like_count`, `view_count`, `comment_count`, `is_recommend`, `sort`, `add_time`) VALUES
(1, 0, 1, 1, '🙏 愿望成真！感恩道玄庇佑', '请了一枚平安符随身携带，三个月后顺利通过考试！感恩道玄庇佑，愿所有人都能如愿以偿！🙏', '[\"https://picsum.photos/400/300?random=1\",\"https://picsum.photos/400/300?random=2\"]', 3, 1, 328, 1256, 45, 1, 100, @NOW - 86400),
(2, 0, 1, 2, '✨ 开光貔貅显灵了！', '请了一对开光貔貅放公司，没想到第二个月就谈成了大单！道玄说的没错，心诚则灵！✨', '[\"https://picsum.photos/400/300?random=3\"]', 1, 1, 256, 892, 38, 1, 95, @NOW - 172800),
(3, 0, 1, 3, '💫 姻缘符真的有效！', '佩戴姻缘符三个月，终于遇到了命中注定的那个人。感谢道玄，感谢觅智商城！💕', '[\"https://picsum.photos/400/300?random=4\",\"https://picsum.photos/400/300?random=5\"]', 2, 1, 512, 2103, 89, 1, 90, @NOW - 259200),
(4, 0, 1, 4, '🎉 招财符太灵了！', '生意人的我请了招财符放在店铺，果然财源广进！强烈推荐给做生意的朋友！', '[\"https://picsum.photos/400/300?random=6\"]', 1, 1, 189, 756, 28, 0, 85, @NOW - 345600),
(5, 0, 1, 5, '🔮 文昌塔助力考研成功', '考研备战期间，每天都在书桌旁供奉文昌塔，没想到真的顺利上岸了！感恩！', '[\"https://picsum.photos/400/300?random=7\",\"https://picsum.photos/400/300?random=8\"]', 2, 1, 421, 1567, 62, 1, 80, @NOW - 432000);

INSERT INTO `sxo_wish_comment` (`id`, `wish_id`, `user_id`, `user_type`, `content`, `like_count`, `add_time`) VALUES
(1, 1, 0, 1, '太灵验了！恭喜师兄！🙏', 25, @NOW - 43200),
(2, 1, 0, 1, '请问符咒在哪里请的？', 8, @NOW - 40000),
(3, 1, 0, 1, '心诚则灵，师兄福德深厚！✨', 15, @NOW - 36000),
(4, 2, 0, 1, '恭喜！貔貅真的很灵！', 12, @NOW - 86400),
(5, 3, 0, 1, '祝福师兄！愿天下有情人终成眷属！💕', 30, @NOW - 129600);

-- ----------------------------
-- 后台菜单配置
-- ----------------------------
-- 如愿管理菜单将自动添加到后台
