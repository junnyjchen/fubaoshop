#!/bin/bash
# ================================================
# 符宝网 - 服务器一键更新脚本
# ================================================
# 使用方法:
#   bash deploy.sh                    # 交互模式
#   bash deploy.sh full               # 完整更新（含清理缓存）
#   bash deploy.sh code               # 仅更新代码
#   bash deploy.sh cache              # 仅清理缓存
# ================================================

# 配置
PROJECT_NAME="符宝网"
PROJECT_DIR="/www/wwwroot/fubao"           # 项目目录，根据实际情况修改
BACKUP_DIR="/www/wwwroot/backup/fubao"    # 备份目录

# 颜色定义
RED='\033[31m'
GREEN='\033[32m'
YELLOW='\033[33m'
BLUE='\033[34m'
PLAIN='\033[0m'

# 日志函数
log() {
    echo -e "${BLUE}[$(date '+%H:%M:%S')]${PLAIN} $1"
}

success() {
    echo -e "${GREEN}[✓]${PLAIN} $1"
}

warn() {
    echo -e "${YELLOW}[!]${PLAIN} $1"
}

error() {
    echo -e "${RED}[✗]${PLAIN} $1"
}

# 检查目录
check_dir() {
    if [ ! -d "$PROJECT_DIR" ]; then
        error "项目目录不存在: $PROJECT_DIR"
        exit 1
    fi
}

# 创建备份
backup() {
    log "正在创建备份..."
    BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="${BACKUP_DIR}/${BACKUP_DATE}"
    mkdir -p "$BACKUP_PATH"
    rsync -av --exclude='runtime/' --exclude='.git/' "$PROJECT_DIR/" "$BACKUP_PATH/code/" 2>/dev/null
    success "备份完成: $BACKUP_PATH"
}

# 更新代码
update_code() {
    log "正在更新代码..."
    cd "$PROJECT_DIR"
    if [ -d ".git" ]; then
        git pull origin main 2>/dev/null || git pull origin master 2>/dev/null
        success "代码更新完成"
    else
        warn "非Git仓库，请手动更新"
    fi
}

# 清理缓存
clear_cache() {
    log "正在清理缓存..."
    cd "$PROJECT_DIR"
    rm -rf runtime/cache/* runtime/log/* runtime/temp/* 2>/dev/null
    rm -rf h5/runtime/* 2>/dev/null
    redis-cli FLUSHDB 2>/dev/null || true
    success "缓存清理完成"
}

# 修复权限
fix_permission() {
    log "正在修复权限..."
    find "$PROJECT_DIR" -type f -exec chmod 644 {} \; 2>/dev/null
    find "$PROJECT_DIR" -type d -exec chmod 755 {} \; 2>/dev/null
    chmod -R 777 "$PROJECT_DIR/runtime" 2>/dev/null
    chmod -R 777 "$PROJECT_DIR/public/uploads" 2>/dev/null
    success "权限修复完成"
}

# 重启服务
restart_service() {
    log "正在重启服务..."
    systemctl restart php-fpm 2>/dev/null || bt 1 restart php-80 2>/dev/null || true
    systemctl restart nginx 2>/dev/null || bt 1 restart nginx 2>/dev/null || true
    success "服务重启完成"
}

# 主程序
main() {
    case ${1:-menu} in
        full)
            check_dir
            backup
            update_code
            clear_cache
            fix_permission
            restart_service
            success "更新完成!"
            ;;
        code)
            check_dir
            update_code
            ;;
        cache)
            check_dir
            clear_cache
            ;;
        backup)
            check_dir
            backup
            ;;
        perm)
            check_dir
            fix_permission
            ;;
        menu)
            echo ""
            echo "=========================================="
            echo "  符宝网 - 服务器一键更新"
            echo "=========================================="
            echo ""
            echo "  1. 完整更新"
            echo "  2. 仅更新代码"
            echo "  3. 仅清理缓存"
            echo "  4. 创建备份"
            echo "  0. 退出"
            echo ""
            read -p "请选择: " choice
            case $choice in
                1) backup; update_code; clear_cache; fix_permission; restart_service; success "完成!" ;;
                2) update_code ;;
                3) clear_cache ;;
                4) backup ;;
                0) exit ;;
            esac
            ;;
    esac
}

main "$@"
