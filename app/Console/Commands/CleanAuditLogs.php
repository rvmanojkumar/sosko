<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AuditService;

class CleanAuditLogs extends Command
{
    protected $signature = 'audit:clean {--days=90 : Days to keep logs}';
    protected $description = 'Clean old audit logs';

    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        parent::__construct();
        $this->auditService = $auditService;
    }

    public function handle()
    {
        $days = $this->option('days');
        
        $this->info("Cleaning audit logs older than {$days} days...");
        
        $this->auditService->cleanOldLogs($days);
        
        $this->info('Audit logs cleaned successfully.');
    }
}