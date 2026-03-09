<?php

namespace KgySathsara\Monitor\Controllers;

use Illuminate\Routing\Controller;
use KgySathsara\Monitor\Models\KgySathsaraLog;
use KgySathsara\Monitor\KgySathsaraMonitor;

class KgySathsaraDashboardController extends Controller
{
    protected $monitor;

    public function __construct(KgySathsaraMonitor $monitor)
    {
        $this->monitor = $monitor;
    }

    public function index()
    {
        $logs = KgySathsaraLog::latest()->paginate(50);
        
        $latest = $logs->first();
        
        $stats = [
            'avg_cpu' => KgySathsaraLog::whereDate('checked_at', today())->avg('cpu_usage'),
            'avg_memory' => KgySathsaraLog::whereDate('checked_at', today())->avg('memory_usage'),
            'avg_disk' => KgySathsaraLog::whereDate('checked_at', today())->avg('disk_usage'),
            'alert_count' => KgySathsaraLog::where('alert_sent', true)->whereDate('checked_at', today())->count(),
            'total_checks' => KgySathsaraLog::count(),
        ];

        $chartData = KgySathsaraLog::latest()->take(24)->get()->reverse();

        return view('kgy-sathsara::dashboard', [
            'logs' => $logs,
            'latest' => $latest,
            'stats' => $stats,
            'chartData' => $chartData,
            'thresholds' => config('kgy-sathsara.thresholds'),
            'author' => 'KGY Sathsara',
            'version' => \KgySathsara\Monitor\KgySathsaraServiceProvider::VERSION
        ]);
    }

    public function runCheck()
    {
        $result = $this->monitor->checkHealth();
        
        return redirect()->route('kgy-sathsara.dashboard')
            ->with('success', 'Monitor check completed!')
            ->with('result', $result);
    }

    public function clearAlerts()
    {
        session()->forget('kgy-sathsara-alert');
        
        return redirect()->route('kgy-sathsara.dashboard')
            ->with('success', 'Alerts cleared!');
    }
}