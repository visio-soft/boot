#!/bin/bash

# ============================================================
# Ubuntu Developer Setup Script
# Interactive installation with component selection
# ============================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Installation options (all enabled by default)
declare -A COMPONENTS=(
    ["system"]=1
    ["php"]=1
    ["nginx"]=1
    ["database"]=1
    ["redis"]=1
    ["nodejs"]=1
    ["devtools"]=0
    ["antigravity"]=0
    ["projects"]=1
)

COMPONENT_KEYS=("system" "php" "nginx" "database" "redis" "nodejs" "devtools" "antigravity" "projects")
COMPONENT_LABELS=(
    "System Packages (git, curl, acl, supervisor)"
    "PHP 8.4 + PHP-FPM + Composer"
    "Nginx Web Server"
    "PostgreSQL Database"
    "Redis Server"
    "Node.js 20 + NPM"
    "VS Code + DBeaver"
    "Google Antigravity Editor"
    "Project Setup (Native Laravel, Nginx, Horizon)"
)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Target user for installation (always current user)
TARGET_USER="${SUDO_USER:-$USER}"

# Functions
print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }

toggle_component() {
    local key=$1
    if [ "${COMPONENTS[$key]}" -eq 1 ]; then
        COMPONENTS[$key]=0
    else
        COMPONENTS[$key]=1
    fi
}

select_all() {
    for key in "${COMPONENT_KEYS[@]}"; do
        COMPONENTS[$key]=1
    done
}

select_none() {
    for key in "${COMPONENT_KEYS[@]}"; do
        COMPONENTS[$key]=0
    done
}

get_selected_tags() {
    local tags=""
    for key in "${COMPONENT_KEYS[@]}"; do
        if [ "${COMPONENTS[$key]}" -eq 1 ] && [ "$key" != "projects" ]; then
            if [ -n "$tags" ]; then
                tags="$tags,$key"
            else
                tags="$key"
            fi
        fi
    done
    echo "$tags"
}

display_menu() {
    clear
    print_header "Ubuntu Developer Setup - Installation Options"
    
    echo -e "${CYAN}Installation User: ${GREEN}$TARGET_USER${NC}\n"
    
    echo -e "Select components to install (toggle with number):\n"
    
    for i in "${!COMPONENT_KEYS[@]}"; do
        local key="${COMPONENT_KEYS[$i]}"
        local label="${COMPONENT_LABELS[$i]}"
        local num=$((i + 1))
        
        if [ "${COMPONENTS[$key]}" -eq 1 ]; then
            echo -e "  ${GREEN}[$num] ✓ $label${NC}"
        else
            echo -e "  ${RED}[$num] ✗ $label${NC}"
        fi
    done
    
    echo ""
    echo -e "  ${CYAN}[a] Select All${NC}"
    echo -e "  ${CYAN}[n] Select None${NC}"
    echo -e "  ${CYAN}[s] Start Installation${NC}"
    echo -e "  ${CYAN}[q] Quit${NC}"
    echo ""
}

run_installation() {
    print_header "Starting Installation"
    
    print_info "Target User: $TARGET_USER"
    
    # Install dependencies
    print_info "Installing dependencies..."
    sudo apt update
    sudo apt install -y ansible git curl acl software-properties-common
    
    # Get selected software tags
    local tags=$(get_selected_tags)
    
    # Run software installation if any software selected
    if [ -n "$tags" ]; then
        print_header "Software Installation (setup.yml)"
        print_info "Selected: $tags"
        print_info "Installing for user: $TARGET_USER"
        sudo ansible-playbook "$SCRIPT_DIR/setup.yml" --tags "$tags" --extra-vars "target_user=$TARGET_USER"
    fi
    
    
    # Run project setup if selected
    if [ "${COMPONENTS["projects"]}" -eq 1 ]; then
        print_header "Project Setup (projects.yml)"
        print_info "Setting up projects for user: $TARGET_USER"
        sudo ansible-playbook "$SCRIPT_DIR/projects.yml" --extra-vars "target_user=$TARGET_USER"
    fi

    # Run Manager Setup (Antigravity)
    if [ "${COMPONENTS["antigravity"]}" -eq 1 ]; then
        setup_antigravity_manager
    fi
    
    print_header "Installation Complete! 🎉"
    echo -e "Installation user: ${GREEN}$TARGET_USER${NC}"
    echo -e "Next steps:"
    echo -e "  1. Access Manager: ${YELLOW}http://manager.test${NC}"
    echo -e "  2. Access applications:"
    echo -e "     - zone: ${YELLOW}http://zone.test${NC}"
    echo -e "     - gate: ${YELLOW}http://gate.test${NC}"
    echo -e "  3. Check services: ${YELLOW}sudo systemctl status nginx postgresql redis-server${NC}"
}

setup_antigravity_manager() {
    print_header "Setting up Antigravity Manager"
    print_info "Configuring Manager App..."

    # Nginx
    cat <<EOF > manager.test
server {
    listen 80;
    listen [::]:80;
    server_name manager.test;
    root $SCRIPT_DIR/manager/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_index index.php;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

    sudo mv manager.test /etc/nginx/sites-available/manager.test
    sudo ln -sf /etc/nginx/sites-available/manager.test /etc/nginx/sites-enabled/manager.test
    sudo systemctl reload nginx

    # Hosts
    if ! grep -q "manager.test" /etc/hosts; then
        echo "127.0.0.1 manager.test" | sudo tee -a /etc/hosts
    fi

    # Database
    sudo -u postgres psql -c "CREATE USER manager WITH PASSWORD 'secret';" >/dev/null 2>&1 || true
    sudo -u postgres psql -c "CREATE DATABASE manager OWNER manager;" >/dev/null 2>&1 || true

    # Env
    print_info "Configuring .env..."
    cd "$SCRIPT_DIR/manager"
    
    if [ ! -f .env ]; then
        cp .env.example .env 2>/dev/null || true
    fi

    if [ -f .env ]; then
        sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
        sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" .env
        sed -i "s/^DB_PORT=.*/DB_PORT=5432/" .env
        sed -i "s/^DB_DATABASE=.*/DB_DATABASE=manager/" .env
        sed -i "s/^DB_USERNAME=.*/DB_USERNAME=manager/" .env
        sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=secret/" .env
        sed -i "s|^APP_URL=.*|APP_URL=http://manager.test|" .env
    fi

    # Fix permissions
    sudo chown -R $TARGET_USER:www-data storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache

    # Migrate
    print_info "Running Migrations..."
    php artisan migrate --force

    print_success "Antigravity Manager Setup Complete!"
}

# Check for --all flag (skip menu)
if [ "$1" == "--all" ] || [ "$1" == "-a" ]; then
    select_all
    run_installation
    exit 0
fi

# Interactive menu
while true; do
    display_menu
    read -p "Your choice: " choice
    
    case $choice in
        1) toggle_component "system" ;;
        2) toggle_component "php" ;;
        3) toggle_component "nginx" ;;
        4) toggle_component "database" ;;
        5) toggle_component "redis" ;;
        6) toggle_component "nodejs" ;;
        7) toggle_component "devtools" ;;
        8) toggle_component "antigravity" ;;
        9) toggle_component "projects" ;;
        a|A) select_all ;;
        n|N) select_none ;;
        s|S) run_installation; exit 0 ;;
        q|Q) echo "Exiting..."; exit 0 ;;
        *) print_error "Invalid choice" ;;
    esac
done
