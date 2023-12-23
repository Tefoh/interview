<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('key:generate');
        try {
            DB::connection()->getPdo();
            if (! Schema::hasTable('migration')) {
                Artisan::call('migrate');
                Artisan::call('db:seed');
            }
            return 0;
        } catch (\Exception $exception) {
            $this->error('Database connection has error');
            $this->error($exception->getMessage());
            return 0;
        }
    }
}
