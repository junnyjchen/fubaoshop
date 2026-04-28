# 符宝网 - 宝塔面板安装教程

## 环境要求

| 软件 | 版本要求 | 说明 |
|------|---------|------|
| 宝塔面板 | 7.0+ | 必装 |
| PHP | 7.4 / 8.0 / 8.1 | 推荐8.0 |
| MySQL | 5.7+ / 8.0 | 推荐8.0 |
| Nginx | 1.18+ | 推荐1.22 |
| Redis | 可选 | 推荐安装 |

---

## 一、服务器环境配置

### 1.1 安装宝塔面板

```bash
# CentOS 7+
yum install -y wget && wget -O install.sh https://download.bt.cn/install/install_6.0.sh && sh install.sh

# Ubuntu/Debian
wget -O install.sh https://download.bt.cn/install/install_6.0.sh && bash install.sh
```

安装完成后，记录面板登录地址和密码。

### 1.2 安装软件套件

登录宝塔面板，在 **软件商店** 中安装以下软件：

| 软件 | 版本 | 备注 |
|------|------|------|
| Nginx | 1.22 | 主Web服务器 |
| PHP | 8.0 | 选择编译安装 |
| MySQL | 8.0 | 数据库 |
| Redis | 6.x | 缓存（可选） |
| phpMyAdmin | 最新 | 数据库管理 |

**安装顺序**：先安装 Nginx → PHP → MySQL → Redis

### 1.3 安装PHP扩展

在宝塔面板 → **软件商店** → **PHP 8.0** → **安装扩展**，安装以下扩展：

| 扩展 | 必须 | 说明 |
|------|------|------|
| fileinfo | ✅ | 文件信息（必须） |
| pdo_mysql | ✅ | MySQL驱动（必须） |
| mbstring | ✅ | 多字节字符串 |
| curl | ✅ | HTTP请求 |
| gd | ✅ | 图片处理 |
| zip | ✅ | ZIP压缩 |
| openssl | ✅ | SSL加密 |
| opcache | ⭐ | 性能优化 |
| redis | ⭐ | Redis缓存 |

### 1.4 解除PHP禁用函数

在宝塔面板 → **软件商店** → **PHP 8.0** → **禁用函数**，**删除**以下函数（不要禁用）：

- `exec`
- `shell_exec`
- `proc_open`
- `putenv`
- `chmod`

---

## 二、获取源码

### 2.1 下载源码到本地

从Git仓库拉取或下载ZIP包到本地电脑。

### 2.2 上传源码到服务器

**方法一：宝塔面板上传**

1. 登录宝塔面板
2. 进入 **文件** → `/www/wwwroot/`
3. 点击 **上传** 按钮
4. 选择源码ZIP包上传
5. 上传完成后，右键解压

**方法二：命令行上传**

```bash
# 安装lrzsz（如果未安装）
yum install -y lrzsz

# 进入web目录
cd /www/wwwroot

# 上传zip包（会弹出选择文件框）
rz

# 解压
unzip fubao.zip

# 重命名为项目名（可选）
mv fubao fubao_site
```

### 2.3 目录权限设置

```bash
# 进入项目目录
cd /www/wwwroot/fubao

# 设置目录权限
chmod -R 755 .
chmod -R 777 runtime
chmod -R 777 public/uploads
chmod -R 777 vendor

# 设置所有者
chown -R www:www .
```

---

## 三、创建数据库

### 3.1 登录phpMyAdmin

访问：`http://你的服务器IP:888/phpmyadmin`

### 3.2 创建数据库

1. 点击 **新建数据库**
2. 数据库名：`mizhi_shop`
3. 排序规则：`utf8mb4_unicode_ci`
4. 点击 **创建**

### 3.3 导入数据库

**第一步：导入ShopXO核心数据库**

> ⚠️ **重要**：符宝网基于ShopXO开发，需要先导入ShopXO核心数据库

1. 从 [ShopXO官网](https://www.shopxo.com/download.html) 下载最新版本ShopXO
2. 解压后找到 `sql/install.sql` 文件
3. 在phpMyAdmin中选择 `mizhi_shop` 数据库
4. 点击 **导入** → 选择 `install.sql` → 点击 **执行**

**第二步：导入符宝网扩展数据库**

1. 在项目中找到 `docs/database/full_install.sql`
2. 上传到服务器 `/www/wwwroot/fubao/docs/database/` 目录
3. 在phpMyAdmin中选择 `mizhi_shop` 数据库
4. 点击 **导入** → 选择 `full_install.sql` → 点击 **执行**

### 3.4 配置数据库用户（可选）

```sql
-- 创建专用用户
CREATE USER 'fubao'@'localhost' IDENTIFIED BY '你的密码';
GRANT ALL PRIVILEGES ON mizhi_shop.* TO 'fubao'@'localhost';
FLUSH PRIVILEGES;
```

---

## 四、创建网站站点

### 4.1 添加站点

在宝塔面板 → **网站** → **添加站点**：

| 配置项 | 值 |
|-------|-----|
| 域名 | 你的域名（如：fubao.com） |
| 根目录 | `/www/wwwroot/fubao/public` |
| FTP | 创建（可选） |
| 数据库 | 选择已有 → `mizhi_shop` |
| PHP版本 | 8.0 |

点击 **提交**。

### 4.2 配置网站

1. 点击刚创建的站点 → **设置**
2. 点击 **网站目录**，确认运行目录是 `/www/wwwroot/fubao/public`
3. 点击 **伪静态**，选择 `thinkphp` → **保存**
4. 点击 **SSL**（如果有域名），申请Let's Encrypt免费证书

---

## 五、配置文件

### 5.1 创建环境配置文件

```bash
cd /www/wwwroot/fubao
```

如果 `.env` 文件不存在，创建它：

```bash
touch .env
```

编辑 `.env` 文件：

```env
APP_DEBUG = false
APP_TRACE = false

[DB]
DB_TYPE = mysql
DB_HOST = localhost
DB_PORT = 3306
DB_NAME = mizhi_shop
DB_USER = root
DB_PASSWORD = 你的MySQL密码
DB_PREFIX = sxo_
DB_CHARSET = utf8mb4
DB_DEBUG = false

[REDIS]
REDIS_HOST = localhost
REDIS_PORT = 6379
REDIS_PASSWORD = 
REDIS_SELECT = 0

[LANG]
default_lang = zh_cn
```

### 5.2 安装Composer依赖

```bash
cd /www/wwwroot/fubao

# 安装依赖
composer install --no-dev --prefer-dist --optimize-autoloader

# 如果安装失败，尝试
composer install --ignore-platform-reqs
```

---

## 六、Nginx伪静态配置

### 6.1 方式一：宝塔面板配置

在宝塔面板 → **网站** → 你的站点 → **设置** → **伪静态**：

选择 `thinkphp` 规则，保存。

### 6.2 方式二：手动配置

如果宝塔伪静态没有thinkphp选项，手动修改Nginx配置：

在宝塔面板 → **网站** → 你的站点 → **设置** → **配置文件**，在 `server {}` 块中添加：

```nginx
location / {
    if (!-e $request_filename) {
        rewrite  ^(.*)$  /index.php/$1  last;
        break;
    }
}

location ~ \.php {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
    fastcgi_connect_timeout 300;
    fastcgi_send_timeout 300;
    fastcgi_read_timeout 300;
}
```

保存后重载Nginx配置。

---

## 七、设置SiteURL

### 7.1 安装向导

访问：`http://你的域名/install.php`

按照安装向导完成配置。

### 7.2 手动配置

如果没有安装向导，手动配置SiteURL：

1. 进入数据库 → `sxo_config` 表
2. 找到 `shop_site_url` 配置项
3. 修改为你的域名：`https://fubao.com`（带https）

或者执行SQL：

```sql
UPDATE sxo_config SET value='https://fubao.com' WHERE name='shop_site_url';
```

---

## 八、清理缓存

### 8.1 命令行清理

```bash
cd /www/wwwroot/fubao

# 清理缓存
rm -rf runtime/cache/*
rm -rf runtime/log/*

# 设置权限
chown -R www:www runtime
```

### 8.2 浏览器访问测试

访问你的域名，确认网站正常显示。

---

## 九、后台登录

访问：`http://你的域名/admin`

| 项目 | 默认值 |
|------|--------|
| 用户名 | `admin` |
| 密码 | `admin123` |

⚠️ **首次登录后请立即修改密码！**

---

## 十、常见问题

### Q1: 页面显示空白

1. 开启调试模式：修改 `.env`，设置 `APP_DEBUG = true`
2. 查看 `runtime/log/` 目录下的错误日志
3. 检查PHP版本是否正确

### Q2: 数据库连接失败

1. 检查 `.env` 中数据库配置是否正确
2. 确认MySQL服务正在运行
3. 确认数据库用户权限

### Q3: 伪静态不生效

1. 确认Nginx配置中有伪静态规则
2. 执行：`nginx -t` 检查配置语法
3. 执行：`systemctl reload nginx` 重载配置

### Q4: 权限不足

```bash
# 递归设置所有权
chown -R www:www /www/wwwroot/fubao

# 设置runtime目录可写
chmod -R 777 /www/wwwroot/fubao/runtime
chmod -R 777 /www/wwwroot/fubao/public/uploads
```

### Q5: 502 Bad Gateway

1. 检查PHP-FPM是否运行：`systemctl status php-fpm-80`
2. 检查Nginx配置中的fastcgi_pass端口是否正确
3. 重启PHP-FPM：`systemctl restart php-fpm-80`

### Q6: Composer安装失败

```bash
# 清理缓存
composer clear-cache

# 使用国内镜像
composer config -g repo.packagist composer https://packagist.phpcomposer.com

# 重新安装
composer install --no-dev
```

---

## 十一、后续配置

### 11.1 配置SSL证书

在宝塔面板 → **网站** → 你的站点 → **SSL** → **Let's Encrypt** → 申请免费证书。

### 11.2 配置计划任务

```bash
crontab -e
```

添加：

```
* * * * * cd /www/wwwroot/fubao && php think crontab > /dev/null 2>&1
```

### 11.3 配置防火墙

在宝塔面板 → **安全** 中开放必要端口：
- 80（HTTP）
- 443（HTTPS）
- 888（宝塔面板）

---

## 十二、快速检查清单

| 项目 | 状态 |
|------|------|
| ✅ Nginx已安装 | ⬜ |
| ✅ PHP 8.0已安装 | ⬜ |
| ✅ PHP扩展已安装 | ⬜ |
| ✅ 禁用函数已删除 | ⬜ |
| ✅ MySQL已安装 | ⬜ |
| ✅ 数据库已创建 | ⬜ |
| ✅ 核心数据库已导入 | ⬜ |
| ✅ 扩展数据库已导入 | ⬜ |
| ✅ 源码已上传 | ⬜ |
| ✅ 目录权限已设置 | ⬜ |
| ✅ Composer依赖已安装 | ⬜ |
| ✅ .env已配置 | ⬜ |
| ✅ 站点已创建 | ⬜ |
| ✅ 伪静态已配置 | ⬜ |
| ✅ SiteURL已配置 | ⬜ |
| ✅ 缓存已清理 | ⬜ |
| ✅ 后台可登录 | ⬜ |

---

**符宝网 - 传承千年道法智慧**
