<?php

namespace KgySathsara\Monitor\Models;

use Illuminate\Database\Eloquent\Model;

class KgySathsaraLog extends Model
{
    protected $table = 'kgy_sathsara_logs';
    
    protected $fillable = [
        'cpu_usage',
        'memory_usage',
        'disk_usage',
        'system_load',
        'alert_sent',
        'alert_data',
        'checked_at',
        'metadata'
    ];

    protected $casts = [
        'cpu_usage' => 'float',
        'memory_usage' => 'float',
        'disk_usage' => 'float',
        'system_load' => 'float',
        'alert_sent' => 'boolean',
        'alert_data' => 'array',
        'checked_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Get logs above threshold
     */
    public function scopeAboveThreshold($query, $type = 'cpu', $threshold = 80)
    {
        return $query->where($type . '_usage', '>', $threshold);
    }

    /**
     * Get today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('checked_at', today());
    }

    /**
     * Get logs with alerts
     */
    public function scopeWithAlerts($query)
    {
        return $query->where('alert_sent', true);
    }
}