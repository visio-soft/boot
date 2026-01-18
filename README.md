# Ubuntu Developer Setup

Ansible playbooks for setting up a Laravel development environment on **Ubuntu 24.04**.

## 📁 Dosya Yapısı

| Dosya | Açıklama |
|-------|----------|
| `software.yml` | Yazılım kurulumları (PHP, Node, DB, IDE) |
| `projects.yml` | Proje kurulumları (clone, migrate, horizon) |
| `run.sh` | İnteraktif kurulum scripti |

## 🚀 Kurulum

```bash
chmod +x run.sh
./run.sh
```

Script açıldığında tüm bileşenler seçili gelir. İstediğinizi toggle edebilirsiniz:

```
[1] ✓ Sistem Paketleri (git, curl, acl, supervisor)
[2] ✓ PHP 8.4 + Composer + Extensions
[3] ✓ Node.js 20 + NPM
[4] ✓ PostgreSQL + Redis
[5] ✓ Nginx + Valet Linux
[6] ✓ VS Code + DBeaver
[7] ✓ Proje Kurulumları

[a] Tümünü Seç  [n] Tümünü Kaldır  [s] Başlat  [q] Çıkış
```

## ⚡ Hızlı Kurulum (Menüsüz)

```bash
./run.sh --all    # Tüm bileşenleri kur
```

## ⚙️ Proje Ayarları

`projects.yml` dosyasını düzenleyin:

```yaml
projects:
  - { name: "myapp", repo: "git@github.com:user/repo.git", db: "myapp_db", user: "myapp_user" }
```

## 📊 Kurulum Sonrası

```bash
valet status                  # Valet kontrolü
sudo supervisorctl status     # Horizon kontrolü
```

Projeler: `http://proje-adi.test`
