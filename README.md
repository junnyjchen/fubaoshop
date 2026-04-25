<p align="center">

# ☯ 觅智商城

觅智商城 - 觅智文化 · 传承千年道家智慧

基于 ShopMX 开源电商系统的道家文化主题商城平台

[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/junnyjchen/fubaoshop/blob/main/LICENSE)
[![ShopMX](https://img.shields.io/badge/ShopMX-v6.8.0-blue)](https://github.com/gongfuxiang/shopxo)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple)](https://www.php.net/)

</p>

---

## 📖 项目简介

觅智商城是基于 ShopMX 开源电商系统打造的道家文化主题商城。融合传统道家文化元素与现代电商技术，提供正授符箓、开光法器等传统文化产品。

### 技术栈

| 技术 | 版本 | 说明 |
|------|------|------|
| ShopMX | v6.8.0 | 基于 ShopXO 的开源电商系统 |
| ThinkPHP | 5.1 | PHP MVC 框架 |
| MySQL | 5.7+ | 数据库 |
| jQuery/LayUI | - | 前端框架 |

---

## ✨ 核心功能

### 1. AI助手「道玄」

智能问答助手，精通道家文化与命理玄学。

| 特性 | 说明 |
|------|------|
| 知识库 | 30+ 条专业知识，涵盖6大分类 |
| 咨询模式 | 道法闲聊、八字命理、符箓知识、风水调理、开光法器 |
| 交互方式 | 打字机效果、会话记忆、快捷提问 |
| 技术实现 | 知识库检索增强 + 预设问答 |

**访问地址**: `/aiassistant`

### 2. 免费领取功能

限时免费领取活动模块。

| 功能 | 说明 |
|------|------|
| 活动管理 | 创建、管理免费领取活动 |
| 领取审核 | 用户申请、后台审核流程 |
| 领取记录 | 追踪用户的领取历史 |
| 库存管理 | 控制免费商品库存 |

**访问地址**: `/freepick`

### 3. 快速下单

无需注册，快速下单模块。

| 功能 | 说明 |
|------|------|
| 免登录 | 无需注册即可下单 |
| 短信验证 | 手机号验证码确认 |
| 订单查询 | 订单号快速查询 |
| 简化流程 | 一步完成购买 |

**访问地址**: `/quickorder`

### 4. 符箓百科

道家文化知识库模块。

| 功能 | 说明 |
|------|------|
| 8大分类 | 涵盖道家文化各方面知识 |
| 文章系统 | 发布、管理知识文章 |
| 评论互动 | 用户可参与评论讨论 |
| SEO优化 | 有利于搜索引擎收录 |

**访问地址**: `/wiki`

### 5. 如愿功能

评论聚合、晒图、晒视频功能模块。

| 功能 | 说明 |
|------|------|
| 如愿类型 | 晒单分享、心愿达成、还愿感谢 |
| 内容形式 | 支持图片、视频晒单 |
| 互动功能 | 点赞、评论、分享 |
| 审核管理 | 后台审核、推荐、排序 |

**访问地址**: `/wish`

### 6. 一物一码证书

产品证书认证与防伪溯源功能。

| 功能 | 说明 |
|------|------|
| 证书模板 | 支持多种证书类型（开光/鉴定/传承） |
| 批量生成 | 支持批量生成唯一证书码 |
| 商品绑定 | 与商品/SKU绑定，支持一码一物 |
| 防伪验证 | 扫码验证，记录验证日志 |
| 溯源追踪 | 查看证书全生命周期 |

**后台地址**: `/admin/certificate`

**前台验证**: `/certificate/verify?code=xxx`

### 7. 营销弹窗

商品详情页智能营销组件。

| 弹窗 | 触发时间 | 功能 |
|------|---------|------|
| 下单提示 | 5秒后 | 显示其他用户购买动态 |
| 优惠券 | 10秒后 | 领取新人专享券 |

### 8. 觅智商城主题

玄学文化主题 UI 组件。

| 元素 | 设计 |
|------|------|
| 主色调 | 玄黑 #1a1a2e + 朱砂红 #c41e3a + 金色 #d4af37 |
| 图标 | 觅智文化符号 ☯ ❋ ✦ ⬡ ⬢ |
| 字体 | Noto Serif TC（中文）+ Noto Sans TC |
| 动效 | 古韵过渡动画 |

### 9. 多语言支持

内置多语言切换功能。

| 语言 | 状态 | 文件 |
|------|------|------|
| 繁体中文 | 默认 | `app/lang/mizhi_cht.php` |
| 英文 | 已配置 | `app/lang/mizhi_en.php` |

---

## 📁 项目结构

```
/workspace/projects/
├── app/                           # 应用目录
│   ├── index/                     # 前台应用
│   │   ├── controller/            # 控制器
│   │   │   ├── ai_assistant/      # AI助手控制器
│   │   │   ├── freepick/          # 免费领取控制器
│   │   │   ├── quickorder/        # 快速下单控制器
│   │   │   ├── wiki/              # 符箓百科控制器
│   │   │   ├── wish/              # 如愿控制器
│   │   │   └── certificate/       # 一物一码控制器
│   │   ├── service/               # 服务层
│   │   └── view/default/          # 前台视图
│   ├── admin/                     # 后台应用
│   ├── api/                       # API应用
│   └── service/                   # 公共服务
│       └── AIAssistantService.php # AI助手服务
├── config/                        # 系统配置
│   └── mizhi_site_config.php     # 觅智商城配置
├── public/demo/                   # 演示页面
├── docs/                         # 项目文档
│   └── database/                 # 数据库脚本
│       ├── ai_assistant.sql      # AI助手表
│       ├── freepick.sql          # 免费领取表
│       ├── quick_order.sql       # 快速下单表
│       ├── talisman_wiki.sql     # 符箓百科表
│       ├── wish.sql              # 如愿表
│       └── certificate.sql       # 一物一码表
└── public/                        # Web根目录
```

---

## 🚀 快速开始

### 环境要求

- PHP 7.4+ 或 PHP 8.1+
- MySQL 5.7+ 或 MySQL 8.0+
- Nginx / Apache

### 安装步骤

1. **克隆项目**
```bash
git clone https://github.com/junnyjchen/fubaoshop.git
cd fubaoshop
```

2. **导入数据库**
```bash
mysql -u root -p < docs/database/ai_assistant.sql
mysql -u root -p < docs/database/freepick.sql
mysql -u root -p < docs/database/quick_order.sql
mysql -u root -p < docs/database/wish.sql
mysql -u root -p < docs/database/certificate.sql
```

3. **配置站点**
```php
// config/mizhi_site_config.php
return [
    'home_site_name' => '觅智商城',
    'home_copyright' => '觅智文化',
];
```

4. **访问网站**
- 前台：https://yoursite.com/
- 后台：https://yoursite.com/admin
- AI助手：https://yoursite.com/aiassistant

---

## 📊 数据库表

| 表名 | 功能 | 说明 |
|------|------|------|
| `sxo_ai_knowledge_category` | AI知识分类 | 6大分类 |
| `sxo_ai_knowledge_item` | AI知识条目 | 30+条知识 |
| `sxo_ai_chat_config` | AI配置 | 助手名称、提示词等 |
| `sxo_freepick_activity` | 领取活动 | 免费领取活动 |
| `sxo_freepick_record` | 领取记录 | 用户领取记录 |
| `sxo_quick_order` | 快速订单 | 无登录订单 |
| `sxo_wiki_category` | 百科分类 | 8大知识分类 |
| `sxo_wiki_article` | 百科文章 | 知识文章 |
| `sxo_wish` | 如愿晒单 | 评论聚合主表 |
| `sxo_wish_comment` | 如愿评论 | 评论表 |
| `sxo_wish_like` | 如愿点赞 | 点赞表 |
| `sxo_wish_category` | 如愿分类 | 4种类型 |
| `sxo_certificate_template` | 证书模板 | 开光/鉴定/传承模板 |
| `sxo_certificate_code` | 证书码 | 唯一码、状态、绑定 |
| `sxo_certificate_verify_log` | 验证日志 | 扫码验证记录 |
| `sxo_certificate_batch` | 生成批次 | 批量生成记录 |
| `sxo_goods_certificate` | 商品绑定 | 商品证书关联 |

---

## 🔧 开发指南

### 添加知识库条目

```php
// 在知识库管理后台添加，或执行SQL
INSERT INTO `sxo_ai_knowledge_item` 
(`category_id`, `title`, `question`, `answer`, `tags`) 
VALUES (1, '护身符功效', '护身符|功效', '护身符功效说明...', '护身符,功效');
```

### 自定义AI助手名称

```php
// 后台配置或修改配置表
UPDATE `sxo_ai_chat_config` 
SET `ai_name` = '道玄', `ai_avatar` = '☯' 
WHERE `id` = 1;
```

### 主题配色

```css
/* 修改觅智主题颜色 */
:root {
    --talu-black: #1a1a2e;
    --talu-red: #c41e3a;
    --talu-gold: #d4af37;
}
```

---

## 📝 品牌信息

| 项目 | 内容 |
|------|------|
| 项目名称 | 觅智商城 |
| 版权方 | 觅智文化 |
| 官方网站 | https://mizhi.com |
| 技术支持 | service@mizhi.com |

---

## 📄 开源协议

本项目基于 [MIT](https://opensource.org/licenses/MIT) 开源协议发布。

基于 [ShopXO](https://github.com/gongfuxiang/shopxo) 开源电商系统开发。

---

## 🙏 致谢

- [ShopXO](https://github.com/gongfuxiang/shopxo) - 开源电商系统
- [ThinkPHP](https://www.thinkphp.cn/) - PHP框架
- [Google Fonts](https://fonts.google.com/) - 开源字体

---

<p align="center">

**☯ 觅智商城 - 觅智文化 · 传承千年道家智慧**

</p>
