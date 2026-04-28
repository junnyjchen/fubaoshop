# 符宝网 - 服务器安装指南

## 环境要求

- PHP >= 7.3 (推荐PHP 7.4或8.0)
- MySQL >= 5.6 (推荐5.7或8.0)
- Nginx 或 Apache
- PHP扩展：mysqlnd、pdo、pdo_mysql、mbstring、openssl、curl、gd、zip

## 安装步骤

### 1. 下载源码

```bash
cd /www/wwwroot/
git clone [仓库地址] fubao
cd fubao
```

### 2. 安装依赖

```bash
# 进入项目目录
cd /www/wwwroot/fubao

# 安装Composer依赖
composer install --no-dev

# 如果提示版本问题，尝试
composer install --ignore-platform-reqs
```

### 3. 配置数据库

#### 3.1 创建数据库

```bash
mysql -u root -p
```

```sql
CREATE DATABASE `mizhi_shop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'fubao'@'localhost' IDENTIFIED BY '你的数据库密码';
GRANT ALL PRIVILEGES ON mizhi_shop.* TO 'fubao'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3.2 导入ShopXO核心数据库

**方法一：从官网下载完整安装包获取数据库文件**

1. 访问 https://www.shopxo.com/download.html 下载最新版本ShopXO
2. 解压后找到 `sql/install.sql` 文件
3. 导入：

```bash
mysql -u fubao -p mizhi_shop < /path/to/install.sql
```

**方法二：如果已有其他站点数据库**

1. 备份现有数据库
2. 在新服务器上创建一个空数据库
3. 导入现有数据库

#### 3.3 导入自定义模块数据库

```bash
# 依次导入符宝网自定义功能表
mysql -u fubao -p mizhi_shop < docs/database/wish.sql
mysql -u fubao -p mizhi_shop < docs/database/certificate.sql
mysql -u fubao -p mizhi_shop < docs/database/ai_knowledge_expand.sql
```

### 4. 配置文件

#### 4.1 环境配置

```bash
cp example.env .env
```

编辑 `.env` 文件：

```env
APP_DEBUG = false

# 数据库配置
DB_HOST = localhost
DB_PORT = 3306
DB_NAME = mizhi_shop
DB_USER = fubao
DB_PASSWORD = 你的数据库密码
DB_PREFIX = sxo_

# URL配置
SHOP_URL = https://你的域名.com
```

#### 4.2 站点配置 (Nginx示例)

```nginx
server {
    listen 80;
    server_name fubao.com www.fubao.com;
    root /www/wwwroot/fubao/public;
    index index.php index.html;
    
    # SSL配置 (可选)
    # listen 443 ssl http2;
    # ssl_certificate /path/to/ssl/cert.pem;
    # ssl_certificate_key /path/to/ssl/key.pem;
    
    location / {
        if (!-e $request_filename) {
            rewrite ^(.*)$ /index.php/$1 last;
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
    
    # 禁止访问敏感目录
    location ~ /\. {
        deny all;
    }
    
    # 静态资源缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### 5. 权限配置

```bash
# 设置目录权限
chown -R www:www /www/wwwroot/fubao
chmod -R 755 /www/wwwroot/fubao
chmod -R 777 /www/wwwroot/fubao/runtime
chmod -R 777 /www/wwwroot/fubao/public/uploads
chmod -R 777 /www/wwwroot/fubao/vendor
```

### 6. 清理缓存

```bash
cd /www/wwwroot/fubao
rm -rf runtime/cache/*
rm -rf runtime/log/*
php think clear
```

### 7. 访问安装向导

访问 `https://你的域名/install.php` 完成安装向导。

如果没有安装向导，手动访问后台：`https://你的域名/admin`

默认管理员账号密码：
- 用户名：admin
- 密码：admin123

## 宝塔面板安装（推荐）

如果使用宝塔面板，请参考：`docs/DEPLOY_BAOTA.md`

## 常见问题

### Q1: Composer安装失败

```bash
# 清理缓存后重试
composer clear-cache
composer install --no-dev --prefer-dist

# 或者使用国内镜像
composer config -g repo.packagist composer https://packagist.phpcomposer.com
composer install
```

### Q2: 数据库连接失败

1. 检查 `.env` 数据库配置
2. 确认MySQL服务运行中
3. 确认用户权限

```bash
# 测试数据库连接
mysql -u fubao -p -h localhost mizhi_shop
```

### Q3: 页面空白

1. 开启调试模式：`.env` 中设置 `APP_DEBUG = true`
2. 查看 `runtime/log/` 目录下的错误日志
3. 检查PHP版本和扩展

### Q4: 伪静态不生效

确保Nginx配置中包含伪静态规则：

```nginx
if (!-e $request_filename) {
    rewrite ^(.*)$ /index.php/$1 last;
    break;
}
```

然后重载Nginx：

```bash
nginx -t
systemctl reload nginx
```

### Q5: 权限问题

```bash
# 递归设置所有权
chown -R www:www /www/wwwroot/fubao

# 设置runtime目录可写
chmod -R 777 /www/wwwroot/fubao/runtime
chmod -R 777 /www/wwwroot/fubao/public/uploads
```

### Q6: 内存不足

```bash
# 增加PHP内存限制
sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php/7.4/fpm/php.ini

# 或在composer命令中使用
COMPOSER_MEMORY_LIMIT=-1 composer install
```

## 安装后配置

### 1. 修改后台密码

首次登录后立即修改管理员密码！

### 2. 配置SSL证书

建议使用Let's Encrypt免费证书：

```bash
certbot --nginx -d fubao.com -d www.fubao.com
```

### 3. 配置计划任务

```bash
# 添加crontab
crontab -e

# 添加以下任务
* * * * * cd /www/wwwroot/fubao && php think crontab > /dev/null 2>&1
```

### 4. 一键更新脚本

```bash
# 完整更新
bash scripts/deploy.sh full

# 仅更新代码
bash scripts/deploy.sh code

# 仅清理缓存
bash scripts/deploy.sh cache
```

## 技术支持

- 官网：https://www.shopxo.com
- 符宝网：联系技术支持获取帮助
