-- ============================================
-- 符宝网 - 完整数据库安装脚本
-- 包含所有自定义模块
-- 版本: 1.0.0
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- AI知识库表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_ai_knowledge_category`;
CREATE TABLE `sxo_ai_knowledge_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `icon` varchar(50) DEFAULT '' COMMENT '图标',
  `sort` int(11) unsigned DEFAULT 100 COMMENT '排序',
  `is_enable` tinyint(1) unsigned DEFAULT 1 COMMENT '是否启用',
  `add_time` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI知识分类表';

INSERT INTO `sxo_ai_knowledge_category` VALUES 
(1, '符箓百科', 'talisman', 100, 1, UNIX_TIMESTAMP()),
(2, '道家文化', 'taoism', 90, 1, UNIX_TIMESTAMP()),
(3, '常见问题', 'faq', 80, 1, UNIX_TIMESTAMP()),
(4, '服务介绍', 'service', 70, 1, UNIX_TIMESTAMP());

DROP TABLE IF EXISTS `sxo_ai_knowledge_item`;
CREATE TABLE `sxo_ai_knowledge_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) unsigned DEFAULT 0 COMMENT '分类ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题/问题',
  `content` text COMMENT '内容/回复',
  `keywords` varchar(500) DEFAULT '' COMMENT '关键词(逗号分隔)',
  `tags` varchar(500) DEFAULT '' COMMENT '标签',
  `match_count` int(11) unsigned DEFAULT 0 COMMENT '匹配次数',
  `rating` decimal(3,2) unsigned DEFAULT 5.00 COMMENT '满意度评分',
  `is_enable` tinyint(1) unsigned DEFAULT 1 COMMENT '是否启用',
  `sort` int(11) unsigned DEFAULT 100 COMMENT '排序',
  `add_time` int(11) unsigned DEFAULT 0,
  `upd_time` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_is_enable` (`is_enable`),
  KEY `idx_add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI知识条目表';

-- 默认知识数据
INSERT INTO `sxo_ai_knowledge_item` (`category_id`, `title`, `content`, `keywords`, `tags`) VALUES
(1, '平安符的功效和使用方法', '平安符乃道家护身之宝\n\n【功效】\n• 护佑出入平安\n• 驱邪避祸\n• 化解小人\n• 保佑身体健康\n\n【使用方法】\n1. 挂于大门内侧，头朝外\n2. 可随身携带，放于钱包或口袋\n3. 保持干燥，不可沾水\n\n【适用人群】\n• 经常出差旅行\n• 体质较弱易招阴邪\n• 运势低迷期', '平安符,护身符,功效,使用方法', '平安,护身,入门'),
(1, '财神符适合哪些人请？', '财神符乃招财进宝之宝\n\n【适合人群】\n• 经商做生意\n• 财运不佳\n• 投资理财\n• 创业开张\n• 期望加薪升职\n\n【功效】\n• 招财进宝\n• 财源广进\n• 守财聚财\n• 贵人相助\n\n【摆放位置】\n财位（大门对角线）或办公桌。', '财神符,招财符,财运,适合人群', '财运,招财,经商'),
(1, '文昌符对考试有帮助吗？', '文昌符乃求学问道之宝\n\n【适合人群】\n• 学生备考\n• 中考高考\n• 考研考公\n• 职称考试\n• 学业进步\n\n【功效】\n• 开启智慧\n• 学业进步\n• 考试顺利\n• 头脑清晰\n• 记忆增强\n\n【使用方法】\n挂于书房或床头，学生可随身携带。', '文昌符,学业符,考试符,文昌塔', '学业,考试,智慧'),
(1, '太岁符怎么化解犯太岁？', '太岁符专门化解犯太岁\n\n【犯太岁类型】\n• 值太岁：本命年\n• 冲太岁：六大冲\n• 破太岁\n• 害太岁\n• 刑太岁\n\n【化解方法】\n1. 请太岁符贴身携带\n2. 犯太岁年份必请\n3. 可配合安太岁法事\n\n【注意事项】\n• 每年都要关注流年太岁\n• 正月十五前化太岁效果最佳', '太岁符,犯太岁,化解太岁,本命年', '太岁,化解,本命年'),
(2, '什么是开光？', '开光是道家重要仪式\n\n【开光条件】\n• 必须由有道行的法师主持\n• 需设坛焚香，诵经祈祷\n• 选择吉日良时进行\n• 法师需诚心持咒\n\n【开光流程】\n1. 净坛：清净道场\n2. 上香：诚心供奉\n3. 诵经：诵读道经\n4. 敕笔：法师持咒\n5. 点睛：开光点眼\n6. 发牒：颁发证明\n\n【注意事项】\n开光后的圣品才具有灵力。', '开光,加持,仪式,法事', '开光,入门,仪式'),
(2, '如何正确请符？', '请符有讲究，心诚则灵\n\n【请符流程】\n1. 选择正规道观或店铺\n2. 说明自身需求\n3. 法师根据八字推荐\n4. 心存善念，不可强求\n5. 请回后妥善保管\n\n【禁忌】\n• 心存恶念者符不灵\n• 不可用污秽之物接触\n• 符箓不可让外人随意触碰\n• 女子经期宜避开\n\n【保养】\n纸质符有效期约一年，到期请新的替换。', '请符,禁忌,如何请符,流程', '请符,禁忌,入门'),
(2, '什么是犯太岁？', '太岁是道教信仰中的星辰神祇\n\n【太岁简介】\n太岁是当年轮值的岁君，掌管人间吉凶。冲犯太岁会带来不顺。\n\n【犯太岁类型】\n• 值太岁：本命年出生\n• 冲太岁：与当年太岁相冲\n• 破太岁：与当年太岁相破\n• 害太岁：与当年太岁相害\n• 刑太岁：与当年太岁相刑\n\n【化解方法】\n• 请太岁符\n• 安太岁\n• 拜太岁\n• 多做善事', '太岁,犯太岁,命理,冲太岁', '太岁,命理,进阶'),
(2, '道家的五行学说', '五行是道家核心理论\n\n【五行】\n金、木、水、火、土\n\n【相生】\n木生火 → 火生土 → 土生金 → 金生水 → 水生木\n\n【相克】\n木克土 → 土克水 → 水克火 → 火克金 → 金克木\n\n【五行与人】\n• 木：青色、绿色，东方，肝胆\n• 火：赤色、红色，南方，心脏\n• 土：黄色，中心，脾胃\n• 金：白色、银色，西方，肺\n• 水：黑色、蓝色，北方，肾', '五行,金木水火土,相生相克,道家理论', '五行,入门,理论'),
(3, '符箓多久见效？', '符箓见效时间因人而异\n\n【短期效果】7-30天\n• 心态渐趋平和\n• 睡眠质量改善\n• 心理上有安定感\n\n【中期效果】1-3个月\n• 贵人运开始增强\n• 做事更加顺利\n• 整体运势转好\n\n【长期效果】3-6个月\n• 事业财运明显提升\n• 家庭和睦安康\n• 福报日渐增长\n\n关键在于：心诚 + 配合自身努力。', '见效,多久见效,效果,时间', '效果,常见问题'),
(3, '符箓失效了怎么办？', '符箓灵力会逐渐消散\n\n【纸质符】\n• 有效期约一年\n• 到期请新符替换\n\n【处理旧符】\n• 不可随意丢弃\n• 建议焚烧处理\n• 焚烧时念诵感谢\n\n【请新符】\n• 选择正规渠道\n• 每年请一次更新\n• 可在原请符处续请', '失效,过期,处理,更换', '保养,常见问题'),
(3, '可以同时请多道符吗？', '可以同时请多道符\n\n【可以同请】\n• 太岁符 + 平安符\n• 财神符 + 招财符\n• 文昌符 + 学业符\n\n【不宜同请】\n• 功效相冲的符\n• 超过3道同功效符\n\n【注意事项】\n数量不是越多越好，应根据实际需求选择。', '多道符,同时请,数量', '请符,常见问题'),
(3, '符箓可以送人吗？', '符箓作为礼物有讲究\n\n【可以送】\n• 家人之间可互送\n• 夫妻情侣可互送\n• 送给长辈表达孝心\n\n【不宜送】\n• 不建议送外人\n• 不确定对方是否需要\n• 对方心存疑虑\n\n【送符注意】\n送符如送福，要心存善念。', '送人,送符,礼物,禁忌', '禁忌,常见问题'),
(4, '符宝网一物一码是什么？', '一物一码是符宝网的正品认证服务\n\n【功能】\n• 每件商品唯一防伪码\n• 扫码查验真伪\n• 开光信息可查\n• 法师信息可追溯\n\n【保障】\n确保您请到正品开光符箓。\n\n【使用方法】\n刮开防伪涂层，扫码验证。', '一物一码,防伪,正品,验证', '服务,正品'),
(4, '如何免费领取符箓？', '新用户可免费领取\n\n【领取条件】\n• 首次注册用户\n• 每个账号限领一次\n• 完成手机验证\n\n【领取流程】\n1. 注册符宝网账号\n2. 完成手机验证\n3. 进入免费领取页面\n4. 选择心仪符箓\n5. 提交领取申请\n\n【发货说明】\n审核通过后48小时内发货。', '免费领取,新用户,福利,活动', '服务,免费');

DROP TABLE IF EXISTS `sxo_ai_chat_config`;
CREATE TABLE `sxo_ai_chat_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '道玄' COMMENT '助手名称',
  `avatar` varchar(500) DEFAULT '' COMMENT '助手头像',
  `welcome` text COMMENT '欢迎语',
  `system_prompt` text COMMENT '系统提示词',
  `temperature` decimal(2,1) unsigned DEFAULT 0.7 COMMENT '温度参数',
  `max_tokens` int(11) unsigned DEFAULT 1000 COMMENT '最大token',
  `is_enable` tinyint(1) unsigned DEFAULT 1 COMMENT '是否启用',
  `upd_time` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI聊天配置表';

INSERT INTO `sxo_ai_chat_config` VALUES 
(1, '道玄', '', '施主好，贫道道玄，有何疑惑尽管道来。', '你是一位精通道家文化的AI助手，名叫道玄。你需要以道家的智慧和口吻来回答用户的问题。回答要专业、正统、有文化底蕴。', 0.7, 1000, 1, UNIX_TIMESTAMP());

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

DROP TABLE IF EXISTS `sxo_wish_comment`;
CREATE TABLE `sxo_wish_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wish_id` int(11) unsigned DEFAULT '0' COMMENT '晒单ID',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `content` varchar(500) DEFAULT '' COMMENT '评论内容',
  `pid` int(11) unsigned DEFAULT '0' COMMENT '父评论ID',
  `like_count` int(11) unsigned DEFAULT '0' COMMENT '点赞数',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0待审核 1已发布)',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `idx_wish_id` (`wish_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='如愿晒单评论表';

DROP TABLE IF EXISTS `sxo_wish_like`;
CREATE TABLE `sxo_wish_like` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wish_id` int(11) unsigned DEFAULT '0' COMMENT '晒单ID',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wish_user` (`wish_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='如愿晒单点赞表';

DROP TABLE IF EXISTS `sxo_wish_category`;
CREATE TABLE `sxo_wish_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '分类名称',
  `icon` varchar(50) DEFAULT '' COMMENT '图标',
  `sort` int(11) unsigned DEFAULT 100 COMMENT '排序',
  `is_enable` tinyint(1) unsigned DEFAULT 1 COMMENT '是否启用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='如愿分类表';

-- ----------------------------
-- 一物一码表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_certificate`;
CREATE TABLE `sxo_certificate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '防伪码',
  `goods_id` int(11) unsigned DEFAULT '0' COMMENT '商品ID',
  `order_id` varchar(50) DEFAULT '' COMMENT '订单ID',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `talisman_name` varchar(100) DEFAULT '' COMMENT '符箓名称',
  `master_name` varchar(50) DEFAULT '' COMMENT '法师姓名',
  `openlight_time` int(11) unsigned DEFAULT '0' COMMENT '开光时间',
  `openlight_place` varchar(100) DEFAULT '' COMMENT '开光地点',
  `certificate_no` varchar(50) DEFAULT '' COMMENT '证书编号',
  `verify_count` int(11) unsigned DEFAULT '0' COMMENT '查验次数',
  `last_verify_time` int(11) unsigned DEFAULT '0' COMMENT '最后查验时间',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(0未激活 1已激活)',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  `upd_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_goods_id` (`goods_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='一物一码证书表';

-- ----------------------------
-- 免费领取表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_freepick_activity`;
CREATE TABLE `sxo_freepick_activity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT '' COMMENT '活动标题',
  `goods_id` int(11) unsigned DEFAULT '0' COMMENT '商品ID',
  `stock` int(11) unsigned DEFAULT '0' COMMENT '库存数量',
  `receive_count` int(11) unsigned DEFAULT '0' COMMENT '已领取数量',
  `limit_per_user` int(11) unsigned DEFAULT '1' COMMENT '每人限领',
  `start_time` int(11) unsigned DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) unsigned DEFAULT '0' COMMENT '结束时间',
  `is_enable` tinyint(1) unsigned DEFAULT 1 COMMENT '是否启用',
  `add_time` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='免费领取活动表';

DROP TABLE IF EXISTS `sxo_freepick_record`;
CREATE TABLE `sxo_freepick_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) unsigned DEFAULT '0' COMMENT '活动ID',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `order_id` varchar(50) DEFAULT '' COMMENT '订单ID',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '状态(0领取中 1已发货 2已收货)',
  `add_time` int(11) unsigned DEFAULT '0',
  `upd_time` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='免费领取记录表';

-- ----------------------------
-- 快速下单表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_quick_order`;
CREATE TABLE `sxo_quick_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `goods_id` int(11) unsigned DEFAULT '0' COMMENT '商品ID',
  `spec_ids` varchar(200) DEFAULT '' COMMENT '规格ID',
  `quantity` int(11) unsigned DEFAULT '1' COMMENT '数量',
  `address_id` int(11) unsigned DEFAULT '0' COMMENT '地址ID',
  `remark` varchar(500) DEFAULT '' COMMENT '备注',
  `order_no` varchar(50) DEFAULT '' COMMENT '订单号',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '状态',
  `add_time` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='快速下单表';

-- ----------------------------
-- 底部浮动栏配置表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_bottom_bar_config`;
CREATE TABLE `sxo_bottom_bar_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT '免费领取开光符箓' COMMENT '标题',
  `desc` varchar(200) DEFAULT '新用户专属福利' COMMENT '描述',
  `btn_text` varchar(50) DEFAULT '立即领取' COMMENT '按钮文字',
  `btn_url` varchar(200) DEFAULT '/freepick' COMMENT '跳转链接',
  `trigger_down` tinyint(1) unsigned DEFAULT 1 COMMENT '下拉触发',
  `trigger_up` tinyint(1) unsigned DEFAULT 1 COMMENT '上拉触发',
  `show_delay` int(11) unsigned DEFAULT 0 COMMENT '显示延迟(秒)',
  `hide_delay` int(11) unsigned DEFAULT 2 COMMENT '隐藏延迟(秒)',
  `share_title` varchar(100) DEFAULT '' COMMENT '分享标题',
  `share_desc` varchar(200) DEFAULT '' COMMENT '分享描述',
  `share_icon` varchar(500) DEFAULT '' COMMENT '分享图标',
  `share_facebook` tinyint(1) unsigned DEFAULT 1 COMMENT 'Facebook分享',
  `share_twitter` tinyint(1) unsigned DEFAULT 1 COMMENT 'Twitter分享',
  `share_whatsapp` tinyint(1) unsigned DEFAULT 1 COMMENT 'WhatsApp分享',
  `share_telegram` tinyint(1) unsigned DEFAULT 1 COMMENT 'Telegram分享',
  `share_line` tinyint(1) unsigned DEFAULT 1 COMMENT 'Line分享',
  `is_enable` tinyint(1) unsigned DEFAULT 1 COMMENT '是否启用',
  `update_time` int(11) unsigned DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='底部浮动栏配置表';

INSERT INTO `sxo_bottom_bar_config` VALUES 
(1, '免费领取开光符箓', '新用户专属福利，限时领取', '立即领取', '/freepick', 1, 1, 0, 2, '符宝网 - 免费领取开光符箓', '道家正统开光符箓，免费领取，诚心祈福', '', 1, 1, 1, 1, 1, 1, UNIX_TIMESTAMP());

-- ----------------------------
-- AI训练日志表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_ai_training_log`;
CREATE TABLE `sxo_ai_training_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(500) NOT NULL DEFAULT '' COMMENT '用户问题',
  `answer` text COMMENT 'AI回复',
  `match_rate` int(11) unsigned DEFAULT 0 COMMENT '匹配度',
  `match_type` varchar(20) DEFAULT 'miss' COMMENT '匹配类型(hit/partial/miss)',
  `rating` tinyint(1) unsigned DEFAULT 0 COMMENT '用户评分',
  `user_id` int(11) unsigned DEFAULT 0 COMMENT '用户ID',
  `session_id` varchar(50) DEFAULT '' COMMENT '会话ID',
  `add_time` int(11) unsigned DEFAULT 0 COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `idx_match_type` (`match_type`),
  KEY `idx_add_time` (`add_time`),
  KEY `idx_question` (`question`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI训练日志表';

DROP TABLE IF EXISTS `sxo_ai_unrecognized`;
CREATE TABLE `sxo_ai_unrecognized` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(500) NOT NULL DEFAULT '' COMMENT '未识别问题',
  `freq` int(11) unsigned DEFAULT 1 COMMENT '出现频率',
  `last_time` int(11) unsigned DEFAULT 0 COMMENT '最后出现时间',
  `status` tinyint(1) unsigned DEFAULT 0 COMMENT '状态(0待处理 1已训练 2忽略)',
  `trained_answer` text COMMENT '训练回复',
  `add_time` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_question` (`question`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI未识别问题表';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 安装完成
-- 请确保已导入ShopXO核心数据库 install.sql
-- ============================================
