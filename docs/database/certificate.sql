-- 一物一码证书功能数据库
-- ShopMX 觅智商城

-- ----------------------------
-- 证书模板表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_certificate_template`;
CREATE TABLE `sxo_certificate_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '模板名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '模板编码',
  `content` text COMMENT '证书内容模板',
  `image` varchar(500) NOT NULL DEFAULT '' COMMENT '证书背景图',
  `is_enable` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用 0-否 1-是',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
  `add_time` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
  `upd_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='证书模板表';

-- ----------------------------
-- 证书码表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_certificate_code`;
CREATE TABLE `sxo_certificate_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '证书码(唯一)',
  `qrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码链接',
  `template_id` int(11) NOT NULL DEFAULT 0 COMMENT '模板ID',
  `goods_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品ID(0-未绑定)',
  `goods_sku_id` int(11) NOT NULL DEFAULT 0 COMMENT 'SKU ID',
  `order_id` int(11) NOT NULL DEFAULT 0 COMMENT '订单ID',
  `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单编号',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0-未激活 1-已激活 2-已绑定 3-已验证',
  `bind_time` int(11) NOT NULL DEFAULT 0 COMMENT '绑定时间',
  `activate_time` int(11) NOT NULL DEFAULT 0 COMMENT '激活时间',
  `verify_count` int(11) NOT NULL DEFAULT 0 COMMENT '验证次数',
  `last_verify_time` int(11) NOT NULL DEFAULT 0 COMMENT '最后验证时间',
  `add_time` int(11) NOT NULL DEFAULT 0 COMMENT '生成时间',
  `upd_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `goods_id` (`goods_id`),
  KEY `template_id` (`template_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='证书码表';

-- ----------------------------
-- 证书验证记录表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_certificate_verify_log`;
CREATE TABLE `sxo_certificate_verify_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_id` int(11) NOT NULL DEFAULT 0 COMMENT '证书ID',
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '证书码',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT '验证IP',
  `user_agent` varchar(500) NOT NULL DEFAULT '' COMMENT 'User Agent',
  `province` varchar(50) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(50) NOT NULL DEFAULT '' COMMENT '城市',
  `add_time` int(11) NOT NULL DEFAULT 0 COMMENT '验证时间',
  PRIMARY KEY (`id`),
  KEY `code_id` (`code_id`),
  KEY `add_time` (`add_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='证书验证记录表';

-- ----------------------------
-- 批量生成证书记录表
-- ----------------------------
DROP TABLE IF EXISTS `sxo_certificate_batch`;
CREATE TABLE `sxo_certificate_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_no` varchar(64) NOT NULL DEFAULT '' COMMENT '批次号',
  `template_id` int(11) NOT NULL DEFAULT 0 COMMENT '模板ID',
  `quantity` int(11) NOT NULL DEFAULT 0 COMMENT '生成数量',
  `goods_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品ID(可选)',
  `goods_sku_id` int(11) NOT NULL DEFAULT 0 COMMENT 'SKU ID(可选)',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0-处理中 1-已完成 2-失败',
  `add_time` int(11) NOT NULL DEFAULT 0 COMMENT '生成时间',
  `upd_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_no` (`batch_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='批量生成记录表';

-- ----------------------------
-- 商品证书绑定表(扩展原商品表关联)
-- ----------------------------
DROP TABLE IF EXISTS `sxo_goods_certificate`;
CREATE TABLE `sxo_goods_certificate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL DEFAULT 0 COMMENT '商品ID',
  `goods_sku_id` int(11) NOT NULL DEFAULT 0 COMMENT 'SKU ID(0-通用)',
  `template_id` int(11) NOT NULL DEFAULT 0 COMMENT '证书模板ID',
  `is_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否必填 0-否 1-是',
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT '每件商品绑定数量',
  `add_time` int(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
  `upd_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品证书绑定表';

-- ----------------------------
-- 初始化证书模板数据
-- ----------------------------
INSERT INTO `sxo_certificate_template` (`id`, `name`, `code`, `content`, `is_enable`, `sort`, `add_time`) VALUES
(1, '开光证书', 'kaiguang', '<p>本<strong>{goods_name}</strong>已于<strong>{kaiguang_date}</strong>在<strong>{temple_name}</strong>由<strong>{master_name}</strong>主持开光仪式。</p><p>开光加持，灵力注入，可护佑持有者平安吉祥。</p>', 1, 1, UNIX_TIMESTAMP()),
(2, '鉴定证书', 'jianding', '<p>经权威鉴定，本<strong>{goods_name}</strong>为正品，材质<strong>{material}</strong>，品质等级<strong>{grade}</strong>。</p><p>鉴定编号：{verify_no}</p>', 1, 2, UNIX_TIMESTAMP()),
(3, '传承证书', 'chuicheng', '<p>此<strong>{goods_name}</strong>传承有序，来源正宗。特此证明其文化价值与收藏意义。</p><p>传承编号：{inherit_no}</p>', 1, 3, UNIX_TIMESTAMP());
