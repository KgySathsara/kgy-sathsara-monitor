<?php

namespace KgySathsara\Monitor\Commands;

use Illuminate\Console\Command;
use KgySathsara\Monitor\KgySathsaraMonitor;
use KgySathsara\Monitor\Models\KgySathsaraLog;

class MonitorCommand extends Command
{
    protected $signature = 'kgy-sathsara:monitor 
                            {--alert-only : Only show if alerts triggered}
                            {--json : Output as JSON}
                            {--threshold= : Override threshold}';
    
    protected $description = 'KGY Sathsara System Monitor - Check CPU, Memory, Disk usage';

    protected $monitor;

    public function __construct(KgySathsaraMonitor $monitor)
    {
        parent::__construct();
        $this->monitor = $monitor;
    }

    public function handle()
    {
        $this->info('🚀 KGY Sathsara System Monitor v' . \KgySathsara\Monitor\KgySathsaraServiceProvider::VERSION);
        $this->newLine();
        $this->info('🔍 Monitoring system health...');
        $this->newLine();

        $result = $this->monitor->checkHealth();

        // JSON output
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
            return 0;
        }

        // Alert only mode
        if ($this->option('alert-only') && empty($result['alerts'])) {
            $this->info('✅ No alerts triggered. System is healthy!');
            return 0;
        }

        // Display table
        $headers = ['Metric', 'Value', 'Threshold', 'Status'];
        $rows = [];

        $rows[] = [
            'CPU Usage',
            $result['cpu'] . '%',
            config('kgy-sathsara.thresholds.cpu') . '%',
            $result['cpu'] > config('kgy-sathsara.thresholds.cpu') ? '❌ ALERT' : '✅ OK'
        ];

        $rows[] = [
            'Memory Usage',
            $result['memory'] . '%',
            config('kgy-sathsara.thresholds.memory') . '%',
            $result['memory'] > config('kgy-sathsara.thresholds.memory') ? '❌ ALERT' : '✅ OK'
        ];

        $rows[] = [
            'Disk Usage',
            $result['disk'] . '%',
            config('kgy-sathsara.thresholds.disk') . '%',
            $result['disk'] > config('kgy-sathsara.thresholds.disk') ? '❌ ALERT' : '✅ OK'
        ];

        $rows[] = [
            'System Load',
            $result['load'],
            config('kgy-sathsara.thresholds.load'),
            $result['load'] > config('kgy-sathsara.thresholds.load') ? '❌ ALERT' : '✅ OK'
        ];

        $this->table($headers, $rows);

        $this->newLine();
        $this->line("📊 PHP Version: {$result['php_version']}");
        $this->line("📊 Laravel Version: {$result['laravel_version']}");
        $this->line("⏰ System Uptime: {$result['uptime']}");
        $this->line("👤 Checked by: {$result['checked_by']}");
        $this->line("🕐 Checked at: {$result['checked_at']}");

        $this->newLine();

        // Show alerts
        if (!empty($result['alerts'])) {
            $this->error('⚠️ ALERTS TRIGGERED!');
            foreach ($result['alerts'] as $alert) {
                $this->warn("  - {$alert['message']}");
            }
        } else {
            $this->info('✅ No alerts triggered. System is healthy!');
        }

        // Show recent history
        $this->newLine();
        $this->info('📈 Last 5 Checks:');
        
        $recent = KgySathsaraLog::latest()->take(5)->get();
        $historyHeaders = ['Time', 'CPU', 'Memory', 'Disk', 'Alert'];
        $historyRows = [];
        
        foreach ($recent as $log) {
            $historyRows[] = [
                $log->checked_at->format('H:i:s'),
                $log->cpu_usage . '%',
                $log->memory_usage . '%',
                $log->disk_usage . '%',
                $log->alert_sent ? '⚠️' : '✅'
            ];
        }
        
        if (!empty($historyRows)) {
            $this->table($historyHeaders, $historyRows);
        }

        return 0;
    }
}