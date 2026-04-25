# 符宝网 - 宝塔面板部署教程

## 环境要求
- 宝塔面板 7.0+
- PHP 8.0+
- MySQL 5.7+ 或 8.0
- Nginx 1.18+
- Redis（可选，推荐安装）

---

## 一、服务器环境配置

### 1.1 安装宝塔面板
```bash
# CentOS
yum install -y wget && wget -O install.sh https://download.bt.cn/install/install_6.0.sh && sh install.sh

# Ubuntu/Debian
wget -O install.sh https://download.bt.cn/install/install_6.0.sh && bash install.sh
```

### 1.2 安装软件
登录宝塔面板，在软件商店安装：
- **Nginx 1.20** 或 **1.22**
- **PHP 8.0**（选择编译安装）
- **MySQL 5.7** 或 **8.0**
- **Redis**（可选）
- **phpMyAdmin**（数据库管理）

### 1.3 安装PHP扩展
在宝塔面板 → 软件商店 → PHP 8.0 → 安装扩展，安装以下扩展：
- `fileinfo`（必须）
- `opcache`（推荐）
- `pdo_mysql`（必须）
- `mbstring`（必须）
- `curl`（必须）
- `gd`（必须）
- `zip`（必须）
- `openssl`（必须）
- `redis`（可选）

### 1.4 解除PHP禁用函数
在宝塔面板 → PHP 8.0 → 禁用函数，删除以下禁用：
- `exec`
- `shell_exec`
- `proc_open`
- `putenv`
- `chmod`

---

## 二、创建数据库

### 2.1 登录phpMyAdmin
访问 `http://你的服务器IP:888/phpmyadmin`

### 2.2 创建数据库
```sql
-- 创建数据库
CREATE DATABASE IF NOT EXISTS `fubao_shop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 创建用户（如果需要远程访问）
CREATE USER 'fubao_user'@'localhost' IDENTIFIED BY '你的密码';
GRANT ALL PRIVILEGES ON fubao_shop.* TO 'fubao_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2.3 导入数据表
在phpMyAdmin中选择 `fubao_shop` 数据库，点击导入，选择以下SQL文件：
- `docs/database/ai_assistant.sql`
- `docs/database/freepick.sql`
- `docs/database/quick_order.sql`
- `docs/database/wish.sql`
- `docs/database/certificate.sql`

---

## 三、上传代码

### 3.1 下载代码
```bash
# 在服务器上执行
cd /www/wwwroot
git clone https://github.com/junnyjchen/fubaoshop.git fubao
```

或者通过宝塔面板 → 文件 → 上传zip包并解压

### 3.2 目录权限
```bash
chmod -R 755 /www/wwwroot/fubao
chown -R www:www /www/wwwroot/fubao
```

---

## 四、Nginx站点配置

### 4.1 创建站点
在宝塔面板 → 网站 → 添加站点：
- 域名：填写你的域名（如 `fubao.com`）
- 根目录：`/www/wwwroot/fubao/public`
- PHP版本：选择 8.0
- 勾选：创建数据库、FTP

### 4.2 SSL证书（可选）
在站点设置 → SSL → Let's Encrypt 或其他证书

### 4.3 Nginx配置
点击站点 → 设置 → 配置修改，替换为以下配置：

```nginx
server
{
    listen 80;
    listen 443 ssl http2;
    server_name 你的域名;
    index index.php index.html;
    root /www/wwwroot/fubao/public;

    # SSL配置（如果启用SSL）
    ssl_certificate /www/server/panel/vhost/cert/你的域名/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/你的域名/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-RSA-AES256-SHA:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA384;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # 安全头
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    # 伪静态（ShopMX必需）
    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php?_url=/$1 last;
            break;
        }
    }

    # 禁止访问隐藏文件
    location ~ /\. {
        deny all;
    }

    # 禁止访问敏感目录
    location ~* ^/(runtime|application|app|config|views|composer\.json|\.env) {
        deny all;
    }

    # 静态资源缓存
    location ~ .*\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        access_log off;
    }

    # PHP配置
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-80.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # 日志
    access_log /www/wwwlogs/fubao_access.log;
    error_log /www/wwwlogs/fubao_error.log;
}
```

点击保存，然后重载Nginx配置。

---

## 五、配置数据库连接

### 5.1 创建数据库配置文件
复制数据库配置示例文件：
```bash
cd /www/wwwroot/fubao
cp application/database.php.example application/database.php
```

### 5.2 编辑数据库配置
```php
// 文件：application/database.php
return [
    'type'            => 'mysql',
    'hostname'        => '127.0.0.1',
    'database'        => 'fubao_shop',      // 数据库名
    'username'        => 'fubao_user',      // 数据库用户名
    'password'        => '你的密码',        // 数据库密码
    'hostport'        => '3306',
    'charset'         => 'utf8mb4',
    'prefix'          => 'sxo_',           // 表前缀（保持默认）
    'debug'           => true,
    'deploy'          => 0,
    'rw_separate'     => false,
    'master_num'      => 1,
    'slave_no'        => '',
    'fields_strict'   => true,
    'resultset_type'   => 'array',
    'auto_timestamp'  => false,
    'datetime_format'  => 'Y-m-d H:i:s',
    'sql_explain'     => false,
];
```

---

## 六、配置站点信息

### 6.1 后台数据库配置
在phpMyAdmin中执行以下SQL更新站点信息：

```sql
-- 更新站点名称
INSERT INTO `sxo_config` (`name`, `describe`, `value`, `type`, `group`, `sort`, `is_requrie`, `is_system`, `add_time`) VALUES
('home_site_name', '站点名称', '符宝网', 'text', 'basic', 0, 0, 1, UNIX_TIMESTAMP()),
('home_copyright', '版权信息', '符宝网版权所有', 'text', 'basic', 0, 0, 1, UNIX_TIMESTAMP()),
('home_site_icp', 'ICP备案号', '粤ICP备2026045883-2号', 'text', 'basic', 0, 0, 1, UNIX_TIMESTAMP()),
('common_multilingual_user_default_value', '默认语言', 'cht', 'text', 'config', 0, 0, 1, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE value=VALUES(value);
```

### 6.2 清理缓存
```bash
cd /www/wwwroot/fubao
rm -rf runtime/cache/*
rm -rf runtime/log/*
```

---

## 七、访问网站

### 7.1 前台访问
- 网址：`http://你的域名/` 或 `https://你的域名/`

### 7.2 后台访问
- 网址：`http://你的域名/admin`
- 默认管理员账号密码（首次需要安装）：
  - 安装向导：`/install`

### 7.3 首次安装
如果系统提示安装向导，访问：
```
http://你的域名/install
```

按照安装向导步骤完成安装。

---

## 八、功能模块访问

| 功能 | 访问地址 |
|------|----------|
| 首页 | `/` |
| 管理后台 | `/admin` |
| 符箓百科 | `/wiki` |
| 如愿晒单 | `/wish` |
| AI道玄 | `/aiassistant` |
| 免费领取 | `/freepick` |
| 证书验证 | `/certificate/verify` |

---

## 九、后台管理功能

### 9.1 证书管理
- 访问：`/admin/certificate`
- 功能：模板管理、生成证书、绑定商品

### 9.2 如愿管理
- 访问：`/admin/wish`
- 功能：晒单审核、分类管理

### 9.3 AI助手管理
- 访问：`/admin/aiassistant`
- 功能：知识库管理、对话配置

---

## 十、常见问题

### 10.1 500错误
```bash
# 检查日志
tail -f /www/wwwlogs/fubao_error.log

# 检查PHP-FPM
systemctl status php-fpm-80
```

### 10.2 伪静态不生效
确保Nginx配置中的伪静态规则已添加，并重载Nginx。

### 10.3 验证码不显示
确保PHP已安装GD库和FreeType。

### 10.4 上传文件失败
检查 `runtime/` 目录权限和PHP上传大小限制。

---

## 十一、安全建议

1. 修改后台管理员密码
2. 修改数据库密码
3. 启用SSL证书
4. 定期备份数据库
5. 关闭不必要的端口
6. 配置防火墙规则

---

## 技术支持

如有问题，请检查：
1. PHP错误日志
2. Nginx错误日志
3. MySQL错误日志
4. 宝塔面板日志

---

符宝网 - 传承千年道法智慧
备案号：粤ICP备2026045883-2号
