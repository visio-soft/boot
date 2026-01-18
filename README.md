# Ubuntu Developer Setup

Ansible playbook for setting up a Laravel development environment on **Ubuntu 24.04**.

## Features

- PHP 8.4 + Laravel Valet Linux
- PostgreSQL + Redis
- Node.js 20 + Supervisor (Horizon)
- VS Code + DBeaver

## Quick Start

```bash
chmod +x run.sh
./run.sh
```

## Configuration

Edit `setup.yml` to customize your projects:

```yaml
vars:
  target_user: "your-username"
  projects:
    - { name: "myapp", repo: "git@github.com:user/repo.git", db: "myapp_db", user: "myapp_user" }
```

## Commands

```bash
./run.sh              # Full install
./run.sh --check      # Dry run
./run.sh --help       # Show options
```

## After Install

```bash
valet status                    # Check Valet
sudo supervisorctl status       # Check Horizon
```

Projects available at `http://project-name.test`
