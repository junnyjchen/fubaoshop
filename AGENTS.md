# 觅智商城 - 项目开发规范

觅智商城 ShopMX 项目开发规范文档。

## 项目概述

- **项目名称**: 觅智商城
- **版权方**: 觅智文化
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

## 核心功能模块

### 1. AI助手「道玄」
- **路由**: `/aiassistant`
- **控制器**: `app/index/controller/AIAssistant.php`
- **服务**: `app/service/AIAssistantService.php`
- **API**: `app/index/controller/api/AIChat.php`
- **视图**: `app/index/view/default/index/ai_assistant/index.html`
- **知识库**: `sxo_ai_knowledge_item` 表

### 2. 免费领取
- **路由**: `/freepick`
- **控制器**: `app/index/controller/Freepick.php`
- **视图**: `app/index/view/default/index/freepick/index.html`
- **表**: `sxo_freepick_activity`, `sxo_freepick_record`

### 3. 快速下单
- **路由**: `/quickorder`
- **控制器**: `app/index/controller/Quickorder.php`
- **视图**: `app/index/view/default/index/quickorder/index.html`
- **表**: `sxo_quick_order`

### 4. 符箓百科
- **路由**: `/wiki`
- **控制器**: `app/index/controller/Wiki.php`
- **视图**: `app/index/view/default/index/wiki/index.html`
- **表**: `sxo_wiki_category`, `sxo_wiki_article`

## 主题规范

### 觅智商城主题色
```css
:root {
    --talu-black: #1a1a2e;      /* 玄黑 */
    --talu-black-light: #16213e;
    --talu-black-dark: #0f0f23;
    --talu-red: #c41e3a;         /* 朱砂红 */
    --talu-red-light: #e63950;
    --talu-gold: #d4af37;        /* 金色 */
    --talu-gold-light: #f4d160;
    --talu-cyan: #008080;
}
```

### 品牌关键词
- 项目名: 觅智商城
- 版权: 觅智文化
- 系统名: ShopMX

## 数据库规范

### 表前缀
- 默认: `sxo_`
- 觅智特色表: `sxo_ai_*`, `sxo_freepick_*`, `sxo_quick_*`, `sxo_wiki_*`

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

## 多语言配置

- 繁体中文: `app/lang/mizhi_cht.php`
- 英文: `app/lang/mizhi_en.php`
- 站点配置: `config/mizhi_site_config.php`

## 开发命令

```bash
# 启动开发服务器
php think run

# 清除缓存
rm -rf runtime/cache/*
rm -rf runtime/log/*

# 更新数据库
mysql -u root -p mizhi_shop < docs/database/ai_assistant.sql
```

## 注意事项

1. **禁止硬编码**: 使用配置文件或数据库存储
2. **安全第一**: 所有输入必须验证
3. **品牌一致**: 所有文本使用"觅智商城"和"觅智文化"
4. **主题统一**: 使用觅智商城主题色和规范

---

**觅智商城 - 觅智文化 · 传承千年道家智慧**
