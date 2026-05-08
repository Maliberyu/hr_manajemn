<p align="center">
  <img src="https://avatars.githubusercontent.com/u/219940777?v=4&size=64" width="200" alt="Laravel">
  <h1 align="center">HR Management System</h1>
</p>

<p align="center">
  <strong>Sistem Manajemen HR yang Modern & Simpel</strong><br>
  Dibangun dengan Laravel 12 • PHP 8.2 • TailwindCSS
</p>

<p align="center">
  <a href="https://github.com/Maliberyu/hr_manajemn/stargazers">
    <img src="https://img.shields.io/github/stars/Maliberyu/hr_manajemn?style=flat-square" alt="Stars">
  </a>
  <a href="https://github.com/Maliberyu/hr_manajemn/network/members">
    <img src="https://img.shields.io/github/forks/Maliberyu/hr_manajemn?style=flat-square" alt="Forks">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework?style=flat-square" alt="License">
  </a>
</p>

---

## 📞 Kontak Developer

| Platform | Detail |
|----------|--------|
| 📱 WhatsApp | [0821-2345-4683](https://wa.me/6282123454683) |
| ✉️ Email | [maliberyu@gmail.com](mailto:maliberyu@gmail.com) |
| 💼 GitHub | [@Maliberyu](https://github.com/Maliberyu) |

---

## ⚡ Fitur Utama

- ✅ Manajemen Data Karyawan
- ✅ Absensi & Cuti
- ✅ Dashboard Responsif
- ✅ Export Data (PDF/Excel)
- ✅ Multi Role Access

---

## 🛠️ Instalasi

```bash
# Clone repo
git clone https://github.com/Maliberyu/hr_manajemn.git

# Install dependencies
composer install
npm install && npm run build

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database & migrate
php artisan migrate --seed

# Jalankan server
php artisan serve
