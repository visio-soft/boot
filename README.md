# bOOT - Developer Environment

A professional, local PHP development stack for Ubuntu, inspired by Laravel Herd. This project provides an automated Ansible system provisioner and a sleek Laravel dashboard to manage your development workflows.

### ⚡ Easy Install

Start a fresh Ubuntu installation with one command:

```bash
./run.sh
```

This script will interactively guide you through installing:
- **Core Stack**: PHP 8.4, Nginx, PostgreSQL, Redis, Node.js 20
- **Developer Tools**: VS Code, DBeaver, etc. (Optional)
- **bOOT Manager**: The web dashboard to manage everything.

---

### 🚀 Manager App

Once installed, access your dashboard at **[http://manager.test](http://manager.test)**.

#### **Projects**
- **One-Click Creation**: Clone from Git or start fresh Laravel projects.
- **Auto Configuration**: Automatically handles Nginx, Hosts file, and SSL.
- **Horizon Integration**: Install and monitor Laravel Horizon with a checkbox.
- **Git Verification**: Built-in SSH key validation for private repositories.

#### **System Services**
- **Control Center**: Restart Nginx, PHP-FPM, Redis, or Postgres instantly.
- **Live Logs**: View real-time logs for any service or project directly in the browser.
- **Config Editors**: Edit `php.ini` or project `.env` files with a dedicated editor.

#### **Database**
- **Instant Setup**: Create new PostgreSQL databases in seconds.
- **Quick Access**: One-click connection to TablePlus or DBeaver.

#### **Software**
- **Dev Tools**: Install essential apps like Chrome, VS Code, and Postman directly from the UI.

---

### 🛠️ Manual Usage

You can also run provisioners manually if needed:

```bash
# Full System Setup
ansible-playbook setup.yml

# Full System Setup
ansible-playbook setup.yml
```

---

*Built with ❤️ for Ubuntu PHP Developers.*
