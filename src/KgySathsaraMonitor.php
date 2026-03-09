<?php

namespace KgySathsara\Monitor;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use KgySathsara\Monitor\Models\KgySathsaraLog;

class KgySathsaraMonitor
{
    /**
     * @var array Threshold values
     */
    protected $thresholds;

    /**
     * @var string Author name
     */
    protected $author = 'KGY Sathsara';

    public function __construct()
    {
        $this->thresholds = config('kgy-sathsara.thresholds', [
            'cpu' => 80,
            'memory' => 80,
            'disk' => 10,
            'load' => 4.0
        ]);
    }

    /**
     * Check system health
     * 
     * @return array
     */
    public function checkHealth()
    {
        $data = [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'load' => $this->getSystemLoad(),
            'uptime' => $this->getUptime(),
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'checked_by' => $this->author,
            'checked_at' => now(),
        ];

        $data['alerts'] = $this->checkThresholds($data);
        $data['alert_sent'] = !empty($data['alerts']);

        // Send alerts if needed
        if ($data['alert_sent']) {
            $this->sendAlerts($data['alerts'], $data);
        }

        // Save to database
        $log = KgySathsaraLog::create([
            'cpu_usage' => $data['cpu'],
            'memory_usage' => $data['memory'],
            'disk_usage' => $data['disk'],
            'system_load' => $data['load'],
            'alert_sent' => $data['alert_sent'],
            'alert_data' => $data['alerts'],
            'checked_at' => $data['checked_at'],
            'metadata' => json_encode($data)
        ]);

        // Fire event
        event('kgy-sathsara.monitor.completed', $log);

        return $data;
    }

    /**
     * Get CPU usage
     * 
     * @return float
     */
    protected function getCpuUsage()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $cores = $this->getCpuCores();
            return $cores > 0 ? round(($load[0] / $cores) * 100, 2) : 0;
        }
        
        // Alternative method for Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic cpu get loadpercentage');
            preg_match('/\d+/', $output, $matches);
            return isset($matches[0]) ? (float) $matches[0] : 0;
        }
        
        return 0;
    }

    /**
     * Get number of CPU cores
     * 
     * @return int
     */
    protected function getCpuCores()
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $cores = shell_exec('echo %NUMBER_OF_PROCESSORS%');
            return (int) $cores;
        }
        
        if (file_exists('/proc/cpuinfo')) {
            $cores = shell_exec('nproc');
            return (int) $cores ?: 1;
        }
        
        return 1;
    }

    /**
     * Get memory usage
     * 
     * @return float
     */
    protected function getMemoryUsage()
    {
        if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
            preg_match('/MemFree:\s+(\d+)/', $meminfo, $free);
            
            if (isset($total[1])) {
                $totalMem = (float) $total[1];
                
                if (isset($available[1])) {
                    $availableMem = (float) $available[1];
                    $usedMem = $totalMem - $availableMem;
                } elseif (isset($free[1])) {
                    $freeMem = (float) $free[1];
                    $usedMem = $totalMem - $freeMem;
                } else {
                    return 0;
                }
                
                return round(($usedMem / $totalMem) * 100, 2);
            }
        }
        
        // For Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize');
            preg_match_all('/\d+/', $output, $matches);
            if (isset($matches[0][0]) && isset($matches[0][1])) {
                $free = (float) $matches[0][0];
                $total = (float) $matches[0][1];
                $used = $total - $free;
                return round(($used / $total) * 100, 2);
            }
        }
        
        return 0;
    }

    /**
     * Get disk usage
     * 
     * @return float
     */
    protected function getDiskUsage()
    {
        $path = config('kgy-sathsara.disk_path', '/');
        
        if (is_dir($path)) {
            $free = disk_free_space($path);
            $total = disk_total_space($path);
            
            if ($total > 0) {
                $used = $total - $free;
                return round(($used / $total) * 100, 2);
            }
        }
        
        return 0;
    }

    /**
     * Get system load average
     * 
     * @return float
     */
    protected function getSystemLoad()
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0], 2);
        }
        return 0;
    }

    /**
     * Get system uptime
     * 
     * @return string
     */
    protected function getUptime()
    {
        if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $seconds = (float) explode(' ', $uptime)[0];
            
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            
            return "{$days}d {$hours}h {$minutes}m";
        }
        
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('net stats srv');
            if (preg_match('/Statistics since (.+)/', $output, $matches)) {
                return $matches[1];
            }
        }
        
        return 'Unknown';
    }

    /**
     * Check thresholds
     * 
     * @param array $data
     * @return array
     */
    protected function checkThresholds($data)
    {
        $alerts = [];
        
        if ($data['cpu'] > $this->thresholds['cpu']) {
            $alerts[] = [
                'type' => 'CPU',
                'value' => $data['cpu'],
                'threshold' => $this->thresholds['cpu'],
                'message' => "⚠️ CPU usage is {$data['cpu']}% (Threshold: {$this->thresholds['cpu']}%)"
            ];
        }
        
        if ($data['memory'] > $this->thresholds['memory']) {
            $alerts[] = [
                'type' => 'Memory',
                'value' => $data['memory'],
                'threshold' => $this->thresholds['memory'],
                'message' => "⚠️ Memory usage is {$data['memory']}% (Threshold: {$this->thresholds['memory']}%)"
            ];
        }
        
        if ($data['disk'] > $this->thresholds['disk']) {
            $alerts[] = [
                'type' => 'Disk',
                'value' => $data['disk'],
                'threshold' => $this->thresholds['disk'],
                'message' => "⚠️ Disk usage is {$data['disk']}% (Threshold: {$this->thresholds['disk']}%)"
            ];
        }
        
        if ($data['load'] > $this->thresholds['load']) {
            $alerts[] = [
                'type' => 'Load',
                'value' => $data['load'],
                'threshold' => $this->thresholds['load'],
                'message' => "⚠️ System load is {$data['load']} (Threshold: {$this->thresholds['load']})"
            ];
        }
        
        return $alerts;
    }

    /**
     * Send alerts
     * 
     * @param array $alerts
     * @param array $data
     * @return void
     */
    protected function sendAlerts($alerts, $data)
    {
        // Log to Laravel log
        foreach ($alerts as $alert) {
            Log::warning("[KGY Sathsara Monitor] {$alert['message']}");
        }
        
        // Send email
        if (config('kgy-sathsara.notifications.email.enabled')) {
            $this->sendEmailAlert($alerts, $data);
        }
        
        // Send Slack
        if (config('kgy-sathsara.notifications.slack.enabled')) {
            $this->sendSlackAlert($alerts, $data);
        }
        
        // Send Telegram
        if (config('kgy-sathsara.notifications.telegram.enabled')) {
            $this->sendTelegramAlert($alerts, $data);
        }
    }

    /**
     * Send email alert
     */
    // protected function sendEmailAlert($alerts, $data)
    // {
    //     $to = config('kgy-sathsara.notifications.email.to');
    //     $subject = "⚠️ KGY Sathsara Monitor Alert - " . now()->format('Y-m-d H:i:s');
        
    //     $body = "System Alert from KGY Sathsara Monitor\n\n";
    //     $body .= "Time: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
    //     foreach ($alerts as $alert) {
    //         $body .= $alert['message'] . "\n";
    //     }
        
    //     $body .= "\nCurrent Stats:\n";
    //     $body .= "CPU: {$data['cpu']}%\n";
    //     $body .= "Memory: {$data['memory']}%\n";
    //     $body .= "Disk: {$data['disk']}%\n";
    //     $body .= "Load: {$data['load']}\n";
        
    //     try {
    //         mail($to, $subject, $body);
    //     } catch (\Exception $e) {
    //         Log::error("KGY Sathsara email alert failed: " . $e->getMessage());
    //     }
    // }

    protected function sendEmailAlert($alerts, $data)
{
    $to = config('kgy-sathsara.notifications.email.to');
    if (!$to) {
        \Log::warning('KGY Monitor: No email recipient configured');
        return;
    }
    
    $subject = "⚠️ KGY Sathsara Monitor Alert - " . now()->format('Y-m-d H:i:s');
    
    // Build email body
    $body = "⚠️ System Alert from KGY Sathsara Monitor\n\n";
    $body .= "Time: " . now()->format('Y-m-d H:i:s') . "\n\n";
    $body .= "Alerts:\n";
    
    foreach ($alerts as $alert) {
        $body .= "- {$alert['type']}: {$alert['value']}% (Threshold: {$alert['threshold']}%)\n";
    }
    
    $body .= "\nCurrent Stats:\n";
    $body .= "CPU: {$data['cpu']}%\n";
    $body .= "Memory: {$data['memory']}%\n";
    $body .= "Disk: {$data['disk']}%\n";
    $body .= "Load: {$data['load']}\n";
    
    try {
        // Use Laravel Mail
        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });
        
        \Log::info('KGY Monitor: Email alert sent via MailHog to ' . $to);
        
    } catch (\Exception $e) {
        \Log::error('KGY Monitor email error: ' . $e->getMessage());
        
        // Fallback to mail() function
        $headers = "From: " . config('mail.from.address') . "\r\n";
        if (mail($to, $subject, $body, $headers)) {
            \Log::info('KGY Monitor: Email alert sent via mail()');
        }
    }
}

    /**
     * Send Slack alert
     */
    protected function sendSlackAlert($alerts, $data)
    {
        $webhook = config('kgy-sathsara.notifications.slack.webhook');
        if (!$webhook) return;
        
        $blocks = [
            [
                'type' => 'header',
                'text' => [
                    'type' => 'plain_text',
                    'text' => '🚨 KGY Sathsara Monitor Alert',
                    'emoji' => true
                ]
            ],
            [
                'type' => 'section',
                'fields' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Time:*\n" . now()->format('Y-m-d H:i:s')
                    ],
                    [
                        'type' => 'mrkdwn',
                        'text' => "*Monitor:*\nKGY Sathsara v" . KgySathsaraServiceProvider::VERSION
                    ]
                ]
            ]
        ];
        
        foreach ($alerts as $alert) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "⚠️ *{$alert['type']}:* {$alert['value']}% (Threshold: {$alert['threshold']}%)"
                ]
            ];
        }
        
        $blocks[] = [
            'type' => 'section',
            'fields' => [
                [
                    'type' => 'mrkdwn',
                    'text' => "*CPU:* {$data['cpu']}%"
                ],
                [
                    'type' => 'mrkdwn',
                    'text' => "*Memory:* {$data['memory']}%"
                ],
                [
                    'type' => 'mrkdwn',
                    'text' => "*Disk:* {$data['disk']}%"
                ],
                [
                    'type' => 'mrkdwn',
                    'text' => "*Load:* {$data['load']}"
                ]
            ]
        ];
        
        try {
            Http::post($webhook, ['blocks' => $blocks]);
        } catch (\Exception $e) {
            Log::error("KGY Sathsara Slack alert failed: " . $e->getMessage());
        }
    }

    /**
     * Send Telegram alert
     */
    protected function sendTelegramAlert($alerts, $data)
    {
        $botToken = config('kgy-sathsara.notifications.telegram.bot_token');
        $chatId = config('kgy-sathsara.notifications.telegram.chat_id');
        
        if (!$botToken || !$chatId) return;
        
        $message = "🚨 *KGY Sathsara Monitor Alert*\n\n";
        $message .= "Time: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        foreach ($alerts as $alert) {
            $message .= "⚠️ *{$alert['type']}:* {$alert['value']}% (Threshold: {$alert['threshold']}%)\n";
        }
        
        $message .= "\n*Current Stats:*\n";
        $message .= "CPU: {$data['cpu']}%\n";
        $message .= "Memory: {$data['memory']}%\n";
        $message .= "Disk: {$data['disk']}%\n";
        $message .= "Load: {$data['load']}\n";
        
        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
        } catch (\Exception $e) {
            Log::error("KGY Sathsara Telegram alert failed: " . $e->getMessage());
        }
    }
}