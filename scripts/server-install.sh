#!/bin/bash
# ============================================
# 符宝网 - 服务器一键安装脚本
# 版本: 1.0.0
# ============================================

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# 项目目录
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WWW_ROOT="/www/wwwroot/fubao"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   符宝网 - 服务器一键安装脚本       ${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# 检查是否为root用户
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}提示: 建议使用root权限运行此脚本${NC}"
    echo ""
fi

# 检查PHP
check_php() {
    echo -e "${CYAN}[1/7] 检查PHP环境...${NC}"
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -r 'echo PHP_VERSION;')
        echo -e "${GREEN}✓ PHP已安装: $PHP_VERSION${NC}"
    else
        echo -e "${RED}✗ PHP未安装，请先安装PHP${NC}"
        exit 1
    fi
    
    # 检查必要扩展
    echo -e "${CYAN}检查PHP扩展...${NC}"
    EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "openssl" "curl" "gd" "zip")
    for ext in "${EXTENSIONS[@]}"; do
        if php -m | grep -q "$ext"; then
            echo -e "  ${GREEN}✓${ext}${NC}"
        else
            echo -e "  ${YELLOW}⚠ ${ext} 未检测到${NC}"
        fi
    done
}

# 检查MySQL
check_mysql() {
    echo ""
    echo -e "${CYAN}[2/7] 检查MySQL环境...${NC}"
    if command -v mysql &> /dev/null; then
        MYSQL_VERSION=$(mysql --version | grep -oP '\d+\.\d+\.\d+')
        echo -e "${GREEN}✓ MySQL已安装: $MYSQL_VERSION${NC}"
    else
        echo -e "${RED}✗ MySQL未安装，请先安装MySQL${NC}"
        exit 1
    fi
}

# 检查Composer
check_composer() {
    echo ""
    echo -e "${CYAN}[3/7] 检查Composer...${NC}"
    if command -v composer &> /dev/null; then
        COMPOSER_VERSION=$(composer --version | grep -oP '\d+\.\d+\.\d+')
        echo -e "${GREEN}✓ Composer已安装: $COMPOSER_VERSION${NC}"
    else
        echo -e "${YELLOW}⚠ Composer未安装，正在安装...${NC}"
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php --install-dir=/usr/local/bin --filename=composer
        rm composer-setup.php
        echo -e "${GREEN}✓ Composer安装完成${NC}"
    fi
}

# 创建目录
create_directories() {
    echo ""
    echo -e "${CYAN}[4/7] 创建目录结构...${NC}"
    
    # 确定Web根目录
    if [ -d "/www/wwwroot" ]; then
        WWW_ROOT="/www/wwwroot/fubao"
    elif [ -d "/var/www" ]; then
        WWW_ROOT="/var/www/fubao"
    else
        WWW_ROOT="$PROJECT_DIR"
    fi
    
    echo -e "Web根目录: ${GREEN}$WWW_ROOT${NC}"
    
    # 创建必要目录
    mkdir -p "$WWW_ROOT/runtime"
    mkdir -p "$WWW_ROOT/public/uploads"
    mkdir -p "$WWW_ROOT/runtime/cache"
    mkdir -p "$WWW_ROOT/runtime/log"
    mkdir -p "$WWW_ROOT/runtime/temp"
    
    echo -e "${GREEN}✓ 目录创建完成${NC}"
}

# 安装依赖
install_dependencies() {
    echo ""
    echo -e "${CYAN}[5/7] 安装Composer依赖...${NC}"
    
    cd "$PROJECT_DIR"
    
    echo -e "${YELLOW}这可能需要几分钟，请耐心等待...${NC}"
    
    # 使用国内镜像（可选）
    # composer config -g repo.packagist composer https://packagist.phpcomposer.com
    
    # 安装依赖
    if composer install --no-dev --prefer-dist --optimize-autoloader 2>&1; then
        echo -e "${GREEN}✓ Composer依赖安装完成${NC}"
    else
        echo -e "${YELLOW}⚠ 部分依赖安装失败，尝试忽略平台要求...${NC}"
        composer install --ignore-platform-reqs --no-dev
    fi
}

# 设置权限
set_permissions() {
    echo ""
    echo -e "${CYAN}[6/7] 设置文件权限...${NC}"
    
    # 尝试使用www-data用户
    if id "www-data" &>/dev/null; then
        WEB_USER="www-data"
    elif id "nginx" &>/dev/null; then
        WEB_USER="nginx"
    else
        WEB_USER="www"
    fi
    
    echo -e "Web用户: ${GREEN}$WEB_USER${NC}"
    
    # 设置目录权限
    chmod -R 755 "$PROJECT_DIR"
    chmod -R 777 "$PROJECT_DIR/runtime"
    chmod -R 777 "$PROJECT_DIR/public/uploads"
    chmod -R 777 "$PROJECT_DIR/vendor"
    
    # 设置所有权
    if [ "$EUID" -eq 0 ]; then
        chown -R "$WEB_USER:$WEB_USER" "$PROJECT_DIR"
        echo -e "${GREEN}✓ 权限设置完成${NC}"
    else
        echo -e "${YELLOW}⚠ 非root用户，跳过所有权设置${NC}"
        echo -e "${YELLOW}请手动执行: chown -R $WEB_USER:$WEB_USER $PROJECT_DIR${NC}"
    fi
}

# 生成环境配置
generate_env() {
    echo ""
    echo -e "${CYAN}[7/7] 生成环境配置文件...${NC}"
    
    if [ ! -f "$PROJECT_DIR/.env" ]; then
        if [ -f "$PROJECT_DIR/example.env" ]; then
            cp "$PROJECT_DIR/example.env" "$PROJECT_DIR/.env"
            echo -e "${GREEN}✓ 环境配置文件已创建 (.env)${NC}"
            echo -e "${YELLOW}请编辑 .env 文件配置数据库信息${NC}"
        else
            cat > "$PROJECT_DIR/.env" << 'EOF'
APP_DEBUG = false
APP_TRACE = false

[DB]
DB_TYPE = mysql
DB_HOST = localhost
DB_PORT = 3306
DB_NAME = mizhi_shop
DB_USER = root
DB_PASSWORD = 
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
EOF
            echo -e "${GREEN}✓ 环境配置文件已创建${NC}"
        fi
    else
        echo -e "${GREEN}✓ 环境配置文件已存在${NC}"
    fi
}

# 完成提示
show_completion() {
    echo ""
    echo -e "${BLUE}========================================${NC}"
    echo -e "${GREEN}   安装完成！${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo ""
    echo -e "下一步操作:"
    echo ""
    echo -e "1. ${YELLOW}配置数据库${NC}"
    echo "   编辑 .env 文件，设置数据库连接信息"
    echo ""
    echo -e "2. ${YELLOW}创建数据库${NC}"
    echo "   mysql -u root -p"
    echo "   CREATE DATABASE mizhi_shop DEFAULT CHARSET utf8mb4;"
    echo ""
    echo -e "3. ${YELLOW}导入数据库${NC}"
    echo "   从ShopXO官网下载完整安装包获取 install.sql"
    echo "   mysql -u root -p mizhi_shop < /path/to/install.sql"
    echo "   mysql -u root -p mizhi_shop < docs/database/*.sql"
    echo ""
    echo -e "4. ${YELLOW}配置Web服务器${NC}"
    echo "   参考 docs/INSTALL_SERVER.md 配置Nginx/Apache"
    echo ""
    echo -e "5. ${YELLOW}访问后台${NC}"
    echo "   域名/admin"
    echo "   默认账号: admin / admin123"
    echo ""
    echo -e "详细文档: ${CYAN}docs/INSTALL_SERVER.md${NC}"
    echo ""
}

# 执行安装
main() {
    check_php
    check_mysql
    check_composer
    create_directories
    install_dependencies
    set_permissions
    generate_env
    show_completion
}

main "$@"
