# 🚀 KGY Sathsara System Monitor

## 📋 About
Custom system monitoring package for Laravel created by **KGY Sathsara**. Monitor CPU, Memory, Disk usage with real-time alerts and beautiful dashboard.

## ✨ Features
- ✅ Real-time CPU usage monitoring
- ✅ Memory usage monitoring
- ✅ Disk space monitoring
- ✅ System load average monitoring
- ✅ Multiple alert channels (Email, Slack, Telegram)
- ✅ Beautiful dashboard with charts
- ✅ Command-line interface
- ✅ Customizable thresholds
- ✅ IP whitelist protection
- ✅ Secret key authentication
- ✅ Log retention management
- ✅ Manual and automatic checks
- ✅ History tracking

## 📦 Installation

### Step 1: Add to composer.json
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