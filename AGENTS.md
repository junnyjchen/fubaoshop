# 符宝网 - 项目开发规范

符宝网 ShopMX 项目开发规范文档。

## 项目概述

- **项目名称**: 符宝网
- **版权方**: 符宝网版权所有
- **备案号**: 粤ICP备2026045883-2号
- **技术栈**: ShopMX (ShopXO v6.8.0) + ThinkPHP 5.1 + MySQL
- **主题**: 道家文化玄学风格

## 技术规范

### 目录结构
```
app/
├── index/          # 前台应用
│   ├── controller/ # 控制器
│   ├── service/    # 服务层
│   └── view/       # 视图
├── admin/          # 后台应用
└── service/        # 公共服务
```

### 命名规范
- 控制器: 大驼峰, 如 `AIAssistant.php`
- 方法: 小驼峰, 如 `IndexDoAdd`
- 视图: 小写下划线, 如 `ai_assistant/index.html`
- 数据库表: `sxo_` 前缀

### 安全规范
- 用户输入必须过滤
- SQL使用框架ORM
- XSS使用 `htmlspecialchars`
- CSRF使用框架验证

---

## 功能模块总览

### 核心功能
| 模块 | 路由 | 说明 |
|------|------|------|
| AI助手「道玄」 | `/aiassistant` | 智能问答 |
| 符箓百科 | `/wiki` | 符箓知识库 |
| 玄门百科 | `/wiki/category/xuanmen` | 道家理论 |
| 玄门动态 | `/wish` | 用户分享 |
| 玄门学堂 | `/school` | 教学课程 |

### 营销功能
| 模块 | 路由 | 说明 |
|------|------|------|
| 免费领取 | `/freepick` | 限时免费活动 |
| 快速下单 | `/quickorder` | 便捷购物 |
| 营销弹窗 | 内置 | 5秒下单提示、10秒优惠券 |

### 特色功能
| 模块 | 路由 | 说明 |
|------|------|------|
| 如愿晒单 | `/wish` | 用户评价聚合 |
| 一物一码 | `/certificate` | 正品认证 |
| 证书查验 | `/certificate/check` | 扫码验真 |

---

## 核心功能详情

### 1. AI助手「道玄」
- **前端页面**: `/aiassistant`
- **配置后台**: `/aiassistant/config`
- **模型配置**: `/aiassistant/model`
- **知识库管理**: `/aiassistant/knowledge`
- **训练优化**: `/aiassistant/optimize`
- **接口测试**: `/aiassistant/test`
- **控制器**: `app/index/controller/AIAssistant.php`
- **服务层**: `app/index/service/AIAssistantService.php`
- **API控制器**: `app/index/controller/api/AIChat.php`
- **视图目录**: `app/index/view/default/index/ai_assistant/`
- **静态资源**: `public/static/css/ai_assistant.css`, `public/static/js/ai_assistant.js`
- **功能**: 智能问答，知识库检索，道法咨询

#### 大模型配置
| 配置项 | 说明 |
|--------|------|
| 启用大模型API | 开启后使用豆包/DeepSeek等大模型 |
| 知识库优先 | 先匹配知识库，未匹配再调用大模型 |
| 流式输出 | 打字机效果实时显示回复 |
| 模型选择 | 豆包Pro/Lite/Mini、DeepSeek V3、Kimi、GLM-5 |
| Temperature | 创造性参数 (0-2) |
| 深度思考 | 启用复杂推理思考过程 |

#### API接口
```
POST /api/ai/chat         - 发送消息
GET  /api/ai/config       - 获取配置
POST /api/ai/config       - 保存配置
```

#### 文件结构
```
app/index/
├── controller/
│   ├── AIAssistant.php        # AI助手控制器
│   └── api/
│       └── AIChat.php         # AI聊天API
├── service/
│   └── AIAssistantService.php # AI服务层(核心逻辑)
└── view/default/index/ai_assistant/
    ├── index.html             # AI助手主页
    └── config.html            # 配置后台

public/static/
├── css/ai_assistant.css       # AI助手样式
└── js/ai_assistant.js        # AI助手JS
```

#### 支持的模型
- 豆包 Pro 2.0 (doubao-seed-2-0-pro-260215)
- 豆包 Lite 2.0 (doubao-seed-2-0-lite-260215)
- 豆包 Mini 2.0 (doubao-seed-2-0-mini-260215)
- DeepSeek V3.2 (deepseek-v3-2-251201)
- Kimi K2.5 (kimi-k2-5-260127)
- GLM-5.0 (glm-5-0-260211)

### 2. 免费领取
- **路由**: `/freepick`
- **控制器**: `app/index/controller/Freepick.php`
- **视图**: `app/index/view/default/index/freepick/index.html`
- **表**: `sxo_freepick_activity`, `sxo_freepick_record`
- **功能**: 新用户免费领取，限时活动

### 3. 快速下单
- **路由**: `/quickorder`
- **控制器**: `app/index/controller/Quickorder.php`
- **视图**: `app/index/view/default/index/quickorder/index.html`
- **表**: `sxo_quick_order`
- **功能**: 便捷下单，指定商品快速购买

### 4. 符箓百科
- **路由**: `/wiki`
- **控制器**: `app/index/controller/Wiki.php`
- **视图**: `app/index/view/default/index/wiki/index.html`
- **表**: `sxo_wiki_category`, `sxo_wiki_article`
- **功能**: 符箓知识科普，分类浏览

### 5. 如愿晒单
- **路由**: `/wish`
- **控制器**: `app/index/controller/Wish.php`
- **视图**: `app/index/view/default/index/wish/index.html`
- **表**: `sxo_wish`, `sxo_wish_images`
- **功能**: 用户评价聚合，晒图晒视频

### 6. 一物一码
- **路由**: `/certificate`
- **控制器**: `app/index/controller/Certificate.php`
- **后台控制器**: `app/admin/controller/Certificate.php`
- **前台视图**: `app/index/view/default/index/certificate/index.html`
- **后台视图**: `app/admin/view/admin/certificate/`
- **后台路由**:
  - `/admin/certificate/index` - 证书管理（优化版）
  - `/admin/certificate/template` - 模板管理
  - `/admin/certificate/generate` - 生成证书
  - `/admin/certificate/detail` - 证书详情
  - `/admin/certificate/verifyLog` - 验证记录
  - `/admin/certificate/batch` - 批次记录
- **表**: `sxo_certificate_code`, `sxo_certificate_template`, `sxo_certificate_verify_log`
- **功能**: 正品认证，扫码查验，批量生成

#### 管理后台功能
| 功能 | 说明 |
|------|------|
| 统计看板 | 今日生成、累计证书、验证次数等 |
| 证书管理 | 列表、搜索、批量激活/禁用、导出 |
| 证书详情 | 查看证书、下载二维码、更新状态 |
| 模板管理 | 创建/编辑证书模板 |
| 批次记录 | 按批次管理证书 |
| 验证记录 | 查看扫码验证历史 |

#### API接口
```
GET  /admin/certificate/getStats      - 获取统计数据
POST /admin/certificate/batchActivate - 批量激活
POST /admin/certificate/batchDisable  - 批量禁用
POST /admin/certificate/updateStatus   - 更新状态
GET  /admin/certificate/export         - 导出证书
POST /admin/certificate/deleteBatch     - 删除批次
```

---

## 主题规范

### 符宝网主题色
```css
:root {
    --primary: #c41e3a;         /* 朱砂红 */
    --primary-light: #e63950;
    --gold: #d4af37;            /* 金色 */
    --gold-light: #f4d160;
    --dark: #1a1a2e;            /* 玄黑 */
    --dark-light: #16213e;
    --dark-bg: #0f0f23;
}
```

### 品牌符号
- ☯ 道家阴阳
- ✦ 五行星
- ❋ 八卦
- ☸ 万字符

### 品牌关键词
- 项目名: 符宝网
- 版权: 符宝网版权所有
- 系统名: ShopMX

---

## 数据库规范

### 表前缀
- 默认: `sxo_`
- 特色表: `sxo_ai_*`, `sxo_freepick_*`, `sxo_quick_*`, `sxo_wiki_*`, `sxo_wish_*`, `sxo_certificate_*`

### 常见表
| 表名 | 说明 |
|------|------|
| `sxo_ai_knowledge_category` | AI知识分类 |
| `sxo_ai_knowledge_item` | AI知识条目 |
| `sxo_ai_chat_config` | AI配置 |
| `sxo_freepick_activity` | 领取活动 |
| `sxo_freepick_record` | 领取记录 |
| `sxo_quick_order` | 快速订单 |
| `sxo_wiki_category` | 百科分类 |
| `sxo_wiki_article` | 百科文章 |
| `sxo_wish` | 如愿晒单 |
| `sxo_wish_images` | 晒单图片 |
| `sxo_certificate` | 一物一码证书 |

---

## 移动端H5

### 预览地址
- **首页**: https://90e2281d-220d-4a22-b921-85a47bbdda53.dev.coze.site/
- **后台配置**: https://90e2281d-220d-4a22-b921-85a47bbdda53.dev.coze.site/admin/bottom-bar.html

### H5功能特性
| 功能 | 说明 |
|------|------|
| 底部固定菜单栏 | 首页/百科/动态/购物车/我的 |
| 底部浮动下单栏 | 可配置内容 |
| 菜单栏显示逻辑 | 下拉超一屏或上拉时显示 |
| 浮动栏显示逻辑 | 下拉超过200px显示，上拉延迟2秒隐藏 |

### H5文件结构
```
/ (项目根目录)
├── index.html              # H5移动端首页
├── admin/
│   └── bottom-bar.html     # 底部栏配置后台
└── scripts/
    └── deploy.sh           # 一键更新脚本
```

### 底部浮动栏配置表
```sql
CREATE TABLE `sxo_bottom_bar_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT '免费领取开光符箓' COMMENT '标题',
  `desc` varchar(200) DEFAULT '新用户专属福利' COMMENT '描述',
  `btn_text` varchar(50) DEFAULT '立即领取' COMMENT '按钮文字',
  `btn_url` varchar(200) DEFAULT '/freeppick' COMMENT '跳转链接',
  `trigger_down` tinyint(1) DEFAULT 1 COMMENT '下拉触发',
  `trigger_up` tinyint(1) DEFAULT 1 COMMENT '上拉触发',
  `show_delay` int(11) DEFAULT 0 COMMENT '显示延迟(秒)',
  `hide_delay` int(11) DEFAULT 2 COMMENT '隐藏延迟(秒)',
  `is_enable` tinyint(1) DEFAULT 1 COMMENT '是否启用',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 服务器部署

### 快速安装

```bash
# 1. 上传代码到服务器
cd /www/wwwroot/fubao

# 2. 运行一键安装脚本
bash scripts/server-install.sh

# 3. 配置数据库
# - 编辑 .env 文件配置数据库连接
# - 创建数据库并导入数据

# 4. 导入数据库
mysql -u root -p mizhi_shop < docs/database/full_install.sql
```

### 完整安装文档

详细安装步骤请参考：`docs/INSTALL_SERVER.md`

### 数据库文件

| 文件 | 说明 |
|------|------|
| `docs/database/full_install.sql` | 完整数据库（含AI知识库、如愿晒单、一物一码等） |
| `docs/database/wish.sql` | 如愿晒单表 |
| `docs/database/certificate.sql` | 一物一码表 |
| `docs/database/ai_knowledge_expand.sql` | AI知识库扩展表 |

### 一键更新脚本
```bash
cd /www/wwwroot/fubao
bash scripts/deploy.sh full    # 完整更新
bash scripts/deploy.sh code   # 仅更新代码
bash scripts/deploy.sh cache  # 仅清理缓存
```

### 开发命令
```bash
# 启动开发服务器
php think run

# 清除缓存
rm -rf runtime/cache/*
rm -rf runtime/log/*

# 更新数据库
mysql -u root -p mizhi_shop < docs/database/full_install.sql
```

---

## 多语言配置

- 繁体中文: `app/lang/mizhi_cht.php`
- 英文: `app/lang/mizhi_en.php`
- 站点配置: `config/mizhi_site_config.php`

---

## 注意事项

1. **禁止硬编码**: 使用配置文件或数据库存储
2. **安全第一**: 所有输入必须验证
3. **品牌一致**: 所有文本使用"符宝网"和"符宝网版权所有"
4. **主题统一**: 使用符宝网主题色和规范

---

**符宝网 - 传承千年道法智慧**
