<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Traits\Helper;
use Illuminate\Support\Facades\Log;

class UpdateCurrencies extends Command
{
    use Helper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currencies:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currencies data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('test command');
        $this->updateCurrencies();
    }
}
