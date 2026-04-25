#!/bin/bash
# ============================================
# 符箓商城 - 自动部署到 GitHub 脚本
# 版本: 1.0.0
# ============================================

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 项目目录
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   符箓商城 - 自动部署脚本 v1.0.0    ${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# 检查 Git
if ! command -v git &> /dev/null; then
    echo -e "${RED}错误: Git 未安装${NC}"
    exit 1
fi

# 检查是否在 Git 仓库中
if [ ! -d ".git" ]; then
    echo -e "${YELLOW}初始化 Git 仓库...${NC}"
    git init
fi

# 配置 Git 用户信息（如果是首次）
if [ -z "$(git config user.email)" ]; then
    echo -e "${YELLOW}请配置 Git 用户信息：${NC}"
    read -p "用户名: " username
    read -p "邮箱: " email
    git config user.name "$username"
    git config user.email "$email"
fi

# 显示当前状态
echo -e "${GREEN}当前分支: $(git branch --show-current)${NC}"
echo -e "${GREEN}远程仓库: $(git remote get-url origin 2>/dev/null || echo '未设置')${NC}"
echo ""

# 函数：添加所有更改
add_changes() {
    echo -e "${YELLOW}添加文件到暂存区...${NC}"
    git add -A
}

# 函数：提交更改
commit_changes() {
    echo -e "${YELLOW}请输入提交信息（空则使用默认）:${NC}"
    read -p "> " commit_message
    
    if [ -z "$commit_message" ]; then
        commit_message="符箓商城 - $(date '+%Y-%m-%d %H:%M:%S')"
    fi
    
    echo -e "${YELLOW}提交更改: $commit_message${NC}"
    git commit -m "$commit_message"
}

# 函数：推送到远程
push_changes() {
    echo -e "${YELLOW}推送到远程仓库...${NC}"
    
    # 获取远程仓库地址
    remote_url=$(git remote get-url origin 2>/dev/null)
    
    if [ -z "$remote_url" ]; then
        echo -e "${YELLOW}未设置远程仓库地址${NC}"
        read -p "请输入 GitHub 仓库地址 (https://github.com/用户名/fubaoshop.git): " remote_url
        
        if [ ! -z "$remote_url" ]; then
            git remote add origin "$remote_url"
        fi
    fi
    
    # 获取当前分支
    current_branch=$(git branch --show-current)
    
    if [ -z "$current_branch" ]; then
        current_branch="master"
    fi
    
    # 推送
    git push -u origin "$current_branch"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ 推送成功！${NC}"
    else
        echo -e "${RED}✗ 推送失败！${NC}"
        echo -e "${YELLOW}提示: 请确保已创建 GitHub 仓库并且有权限${NC}"
    fi
}

# 函数：查看状态
show_status() {
    echo -e "${YELLOW}Git 状态:${NC}"
    git status
    echo ""
    echo -e "${YELLOW}最近的提交:${NC}"
    git log --oneline -5
}

# 函数：查看更改
show_diff() {
    echo -e "${YELLOW}文件更改统计:${NC}"
    git diff --stat
    echo ""
    echo -e "${YELLOW}未暂存的文件:${NC}"
    git diff --name-only
    echo ""
    echo -e "${YELLOW}未跟踪的文件:${NC}"
    git ls-files --others --exclude-standard
}

# 主菜单
show_menu() {
    echo -e "${GREEN}请选择操作：${NC}"
    echo "1) 查看状态"
    echo "2) 查看更改"
    echo "3) 完整部署（添加+提交+推送）"
    echo "4) 仅添加和提交"
    echo "5) 仅推送"
    echo "6) 设置远程仓库"
    echo "7) 创建 GitHub 仓库（浏览器打开）"
    echo "8) 退出"
    echo ""
    read -p "请输入选项 [1-8]: " choice
}

# 主循环
while true; do
    show_menu
    
    case $choice in
        1)
            echo ""
            show_status
            ;;
        2)
            echo ""
            show_diff
            ;;
        3)
            echo ""
            show_diff
            echo ""
            add_changes
            echo ""
            commit_changes
            echo ""
            push_changes
            ;;
        4)
            echo ""
            show_diff
            echo ""
            add_changes
            echo ""
            commit_changes
            ;;
        5)
            echo ""
            push_changes
            ;;
        6)
            echo ""
            echo -e "${YELLOW}请输入 GitHub 仓库地址：${NC}"
            echo -e "${YELLOW}格式: https://github.com/用户名/fubaoshop.git${NC}"
            read -p "> " new_remote
            git remote set-url origin "$new_remote"
            echo -e "${GREEN}✓ 远程仓库已更新${NC}"
            ;;
        7)
            echo ""
            echo -e "${YELLOW}请在浏览器中打开以下链接创建仓库：${NC}"
            echo -e "${GREEN}https://github.com/new${NC}"
            echo ""
            read -p "创建完成后按回车继续..."
            ;;
        8)
            echo ""
            echo -e "${GREEN}再见！${NC}"
            exit 0
            ;;
        *)
            echo ""
            echo -e "${RED}无效选项，请重试${NC}"
            ;;
    esac
    
    echo ""
    read -p "按回车继续..."
    clear
done
