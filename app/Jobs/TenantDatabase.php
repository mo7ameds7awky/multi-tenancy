<?php

namespace App\Jobs;

use App\Services\TenantManager;
use App\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    protected $tenantManager;

    /**
     * Create a new job instance.
     *
     * @param Tenant $tenant
     * @param TenantManager $tenantManager
     */
    public function __construct(Tenant $tenant, TenantManager $tenantManager)
    {
        $this->tenant        = $tenant;
        $this->tenantManager = $tenantManager;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $database    = 'tenant_' . $this->tenant->id;
        $connection  = \DB::connection('tenant');
        try{
            $createMysql = $connection->statement('CREATE DATABASE ' . $database);
            $this->tenantManager->setTenant($this->tenant);
            // \DB::connection('tenant')->purge();
            \DB::purge('tenant');
            $this->migrate();
        } catch (\Exception $ex) {
            $connection->statement('DROP DATABASE ' . $database);
        }
    }

    private function migrate() {
        $migrator = app('migrator');
        $migrator->setConnection('tenant');
        if (! $migrator->repositoryExists()) {
            $migrator->getRepository()->createRepository();
        }
        $migrator->run(database_path('migrations/tenants'), []);
    }
}
