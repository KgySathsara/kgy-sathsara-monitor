<?php

namespace KgySathsara\Monitor\Commands;

use Illuminate\Console\Command;
use KgySathsara\Monitor\Models\KgySathsaraLog;

class CleanLogsCommand extends Command
{
    protected $signature = 'kgy-sathsara:clean 
                            {--days=30 : Delete logs older than X days}
                            {--force : Skip confirmation}';
    
    protected $description = 'Clean old KGY Sathsara monitor logs';

    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);
        
        $count = KgySathsaraLog::where('checked_at', '<', $date)->count();
        
        if ($count === 0) {
            $this->info("No logs older than {$days} days found.");
            return 0;
        }
        
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete {$count} logs older than {$days} days?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        KgySathsaraLog::where('checked_at', '<', $date)->delete();
        
        $this->info("✅ Deleted {$count} logs older than {$days} days.");
        
        return 0;
    }
}