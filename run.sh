#!/bin/bash

# ============================================================
# Ubuntu Developer Setup Script
# Compatible with Ubuntu 22.04 and 24.04
# ============================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Check if running on Ubuntu
check_ubuntu() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        if [ "$ID" != "ubuntu" ]; then
            print_error "This script is designed for Ubuntu. Detected: $ID"
            exit 1
        fi
        print_success "Detected: $PRETTY_NAME"
    else
        print_error "Cannot detect OS. /etc/os-release not found."
        exit 1
    fi
}

# Check if running as root
check_root() {
    if [ "$EUID" -eq 0 ]; then
        print_warning "Running as root. The playbook will create a non-root user."
    fi
}

# Display usage
show_usage() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -h, --help       Show this help message"
    echo "  -c, --check      Dry run mode (preview changes without applying)"
    echo "  -v, --verbose    Enable verbose output"
    echo "  -t, --tags TAGS  Only run plays tagged with these values"
    echo "  -s, --skip TAGS  Skip plays tagged with these values"
    echo ""
    echo "Examples:"
    echo "  $0              # Run full installation"
    echo "  $0 --check      # Preview changes"
    echo "  $0 -v           # Verbose output"
    echo "  $0 -t php,node  # Only install PHP and Node.js"
    exit 0
}

# Parse arguments
ANSIBLE_ARGS=""
VERBOSE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_usage
            ;;
        -c|--check)
            ANSIBLE_ARGS="$ANSIBLE_ARGS --check"
            print_info "Running in check mode (dry run)"
            shift
            ;;
        -v|--verbose)
            VERBOSE="-v"
            shift
            ;;
        -t|--tags)
            ANSIBLE_ARGS="$ANSIBLE_ARGS --tags $2"
            shift 2
            ;;
        -s|--skip|--skip-tags)
            ANSIBLE_ARGS="$ANSIBLE_ARGS --skip-tags $2"
            shift 2
            ;;
        *)
            print_error "Unknown option: $1"
            show_usage
            ;;
    esac
done

# Main execution
print_header "Ubuntu Developer Environment Setup"

# Pre-flight checks
print_info "Running pre-flight checks..."
check_ubuntu
check_root

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLAYBOOK="$SCRIPT_DIR/setup.yml"

# Check if playbook exists
if [ ! -f "$PLAYBOOK" ]; then
    print_error "Playbook not found: $PLAYBOOK"
    exit 1
fi
print_success "Playbook found: $PLAYBOOK"

# Update system and install dependencies
print_header "Installing Dependencies"
print_info "Updating package lists..."
sudo apt update

print_info "Installing Ansible and required packages..."
sudo apt install -y ansible git curl acl software-properties-common

print_success "Dependencies installed"

# Display Ansible version
ANSIBLE_VERSION=$(ansible --version | head -n1)
print_info "$ANSIBLE_VERSION"

# Run playbook
print_header "Running Ansible Playbook"
print_info "This may take 10-30 minutes depending on your internet speed..."
echo ""

sudo ansible-playbook "$PLAYBOOK" $ANSIBLE_ARGS $VERBOSE

# Final message
print_header "Setup Complete! 🎉"
echo -e "Your development environment is ready."
echo ""
echo -e "Next steps:"
echo -e "  1. Log out and log back in (or run: ${YELLOW}source ~/.bashrc${NC})"
echo -e "  2. Test Valet: ${YELLOW}valet status${NC}"
echo -e "  3. Check Horizon: ${YELLOW}sudo supervisorctl status${NC}"
echo -e "  4. Access projects at: ${GREEN}http://project-name.test${NC}"
echo ""
