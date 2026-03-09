<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KGY Sathsara System Monitor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 32px;
        }
        
        .header h1 small {
            font-size: 14px;
            color: #7f8c8d;
            display: block;
        }
        
        .badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            color: #7f8c8d;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #95a5a6;
            font-size: 14px;
        }
        
        .alert {
            background: #f39c12;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert-success {
            background: #27ae60;
        }
        
        .alert button {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .current-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .metric-card.cpu .metric-value { color: #e74c3c; }
        .metric-card.memory .metric-value { color: #3498db; }
        .metric-card.disk .metric-value { color: #2ecc71; }
        
        .metric-title {
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .metric-value {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #ecf0f1;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-fill {
            height: 100%;
            transition: width 0.3s;
        }
        
        .cpu .progress-fill { background: #e74c3c; }
        .memory .progress-fill { background: #3498db; }
        .disk .progress-fill { background: #2ecc71; }
        
        .threshold-text {
            color: #95a5a6;
            font-size: 14px;
        }
        
        .actions {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            overflow-x: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .alert-badge {
            background: #f39c12;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: white;
            opacity: 0.8;
        }
        
        .high-usage {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .chart {
            display: flex;
            align-items: flex-end;
            height: 200px;
            gap: 2px;
            margin-top: 20px;
        }
        
        .chart-bar {
            flex: 1;
            background: linear-gradient(to top, #667eea, #764ba2);
            border-radius: 5px 5px 0 0;
            transition: height 0.3s;
            min-width: 20px;
        }
        
        .chart-label {
            text-align: center;
            font-size: 12px;
            margin-top: 5px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
                <button onclick="this.parentElement.style.display='none'">×</button>
            </div>
        @endif
        
        @if(session('kgy-sathsara-alert'))
            <div class="alert">
                {!! session('kgy-sathsara-alert') !!}
                <button onclick="this.parentElement.style.display='none'">×</button>
            </div>
        @endif
        
        <div class="header">
            <div>
                <h1>🔍 KGY Sathsara System Monitor</h1>
                <small>by {{ $author }} | v{{ $version }}</small>
            </div>
            <div class="badge">
                {{ now()->format('Y-m-d H:i:s') }}
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Checks</h3>
                <div class="stat-value">{{ $stats['total_checks'] }}</div>
                <div class="stat-label">Since installation</div>
            </div>
            <div class="stat-card">
                <h3>Today's Alerts</h3>
                <div class="stat-value">{{ $stats['alert_count'] }}</div>
                <div class="stat-label">⚠️ Alerts triggered</div>
            </div>
            <div class="stat-card">
                <h3>Avg CPU (Today)</h3>
                <div class="stat-value">{{ number_format($stats['avg_cpu'], 1) }}%</div>
                <div class="stat-label">24-hour average</div>
            </div>
            <div class="stat-card">
                <h3>Threshold</h3>
                <div class="stat-value">{{ $thresholds['cpu'] }}%</div>
                <div class="stat-label">CPU alert threshold</div>
            </div>
        </div>
        
        @if($latest)
            <div class="current-stats">
                <div class="metric-card cpu">
                    <div class="metric-title">CPU Usage</div>
                    <div class="metric-value">{{ $latest->cpu_usage }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $latest->cpu_usage }}%"></div>
                    </div>
                    <div class="threshold-text">
                        Threshold: {{ $thresholds['cpu'] }}%
                        @if($latest->cpu_usage > $thresholds['cpu'])
                            <span class="high-usage">⚠️ High Usage!</span>
                        @endif
                    </div>
                </div>
                
                <div class="metric-card memory">
                    <div class="metric-title">Memory Usage</div>
                    <div class="metric-value">{{ $latest->memory_usage }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $latest->memory_usage }}%"></div>
                    </div>
                    <div class="threshold-text">
                        Threshold: {{ $thresholds['memory'] }}%
                        @if($latest->memory_usage > $thresholds['memory'])
                            <span class="high-usage">⚠️ High Usage!</span>
                        @endif
                    </div>
                </div>
                
                <div class="metric-card disk">
                    <div class="metric-title">Disk Usage</div>
                    <div class="metric-value">{{ $latest->disk_usage }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $latest->disk_usage }}%"></div>
                    </div>
                    <div class="threshold-text">
                        Threshold: {{ $thresholds['disk'] }}%
                        @if($latest->disk_usage > $thresholds['disk'])
                            <span class="high-usage">⚠️ High Usage!</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="chart-container">
                <h3>📊 CPU Usage History (Last 24 Checks)</h3>
                <div class="chart">
                    @foreach($chartData as $log)
                        <div style="flex: 1; text-align: center;">
                            <div class="chart-bar" style="height: {{ $log->cpu_usage * 2 }}px;"></div>
                            <div class="chart-label">{{ $log->checked_at->format('H:i') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <div class="actions">
            <a href="{{ route('kgy-sathsara.dashboard.run') }}" class="btn btn-primary">
                🔄 Run Manual Check
            </a>
            <a href="{{ route('kgy-sathsara.dashboard.clear') }}" class="btn btn-secondary">
                🧹 Clear Alerts
            </a>
        </div>
        
        <div class="table-container">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">📋 Monitoring History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>CPU</th>
                        <th>Memory</th>
                        <th>Disk</th>
                        <th>Load</th>
                        <th>Alert</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->checked_at->format('Y-m-d H:i:s') }}</td>
                            <td class="{{ $log->cpu_usage > $thresholds['cpu'] ? 'high-usage' : '' }}">
                                {{ $log->cpu_usage }}%
                            </td>
                            <td class="{{ $log->memory_usage > $thresholds['memory'] ? 'high-usage' : '' }}">
                                {{ $log->memory_usage }}%
                            </td>
                            <td class="{{ $log->disk_usage > $thresholds['disk'] ? 'high-usage' : '' }}">
                                {{ $log->disk_usage }}%
                            </td>
                            <td>{{ $log->system_load }}</td>
                            <td>
                                @if($log->alert_sent)
                                    <span class="alert-badge">⚠️ Alert Sent</span>
                                @else
                                    ✅
                                @endif
                            </td>
                            <td>
                                @if($log->cpu_usage > $thresholds['cpu'] || $log->memory_usage > $thresholds['memory'] || $log->disk_usage > $thresholds['disk'])
                                    <span style="color: #e74c3c;">Critical</span>
                                @else
                                    <span style="color: #27ae60;">Healthy</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #7f8c8d;">
                                No monitoring data yet. Run <code>php artisan kgy-sathsara:monitor</code> to start monitoring.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div style="margin-top: 20px;">
                {{ $logs->links() }}
            </div>
        </div>
        
        <div class="footer">
            <p>Developed with ❤️ by KGY Sathsara | System Monitor v{{ $version }}</p>
            <p style="font-size: 12px; margin-top: 5px;">Monitoring CPU, Memory, Disk usage with custom thresholds</p>
        </div>
    </div>
</body>
</html>