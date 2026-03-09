# 🚀 KGY Sathsara System Monitor

A lightweight **Laravel system monitoring package** that tracks **CPU, Memory, Disk usage, and System Load** with logging and alert support.

Developed by **KGY Sathsara**.

---

# 📋 About

**KGY Sathsara Monitor** helps Laravel developers monitor server resources directly from their application.

It collects system metrics and stores them in the database so you can:

- Monitor server performance
- Detect high resource usage
- Send alerts
- Track system history

---

# ✨ Features

- ✅ CPU usage monitoring
- ✅ Memory usage monitoring
- ✅ Disk usage monitoring
- ✅ System load monitoring
- ✅ Command-line monitoring
- ✅ Database logging
- ✅ Monitoring history
- ✅ Alert support (Email / Slack / Telegram)
- ✅ Dashboard ready data
- ✅ Customizable thresholds
- ✅ Secure access support
- ✅ Cron job compatible

---

# 📦 Installation

## Step 1 — Add Repository

Run the following command:

```bash
composer config repositories.kgy_sathsara vcs https://github.com/KgySathsara/kgy-sathsara-monitor
```

---

## Step 2 — Install the Package

```bash
composer require kgy_sathsara/monitor:dev-main
```

---

## Step 3 — Run Migration

```bash
php artisan migrate
```

This will create the monitoring logs table.

---

## Step 4 — Run Monitor Command

```bash
php artisan kgy-sathsara:monitor
```

This command collects system information:

- CPU usage
- Memory usage
- Disk usage
- System load

and saves it in the database.

---

# ⏱ Automatic Monitoring (Cron Job)

For continuous monitoring, add a cron job.

Example:

```bash
* * * * * php /path-to-your-project/artisan kgy-sathsara:monitor
```

This runs the monitor **every minute**.

---

# 📊 Stored Metrics

The package logs the following data:

| Metric | Description |
|------|------|
| CPU Usage | Current CPU usage percentage |
| Memory Usage | RAM usage percentage |
| Disk Usage | Disk storage usage |
| System Load | Server load average |
| Alert Sent | Whether an alert was triggered |

---

# 🔐 Security

You can protect monitoring access using:

- IP Whitelisting
- Secret keys
- Authentication middleware

---

# 🛠 Requirements

- PHP 8.0+
- Laravel 9 / 10 / 11
- Composer

---

# 📁 Example Command Output

```
System Monitor Report

CPU Usage: 32%
Memory Usage: 58%
Disk Usage: 71%
System Load: 0.42

Status: Normal
```

---

# 👨‍💻 Author

**KGY Sathsara**  
Associate Software Engineer  

GitHub  
https://github.com/KgySathsara

---

# ⭐ Support

If you find this package useful:

⭐ Star the repository on GitHub  
🐛 Report issues  
💡 Suggest improvements

---

# 📜 License

MIT License

### Add to composer.json
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/kgy_sathsara/kgy-sathsara-monitor"
        }
    ],
    "require": {
        "kgy_sathsara/monitor": "dev-main"
    }
}