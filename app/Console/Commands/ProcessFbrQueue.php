<?php

namespace App\Console\Commands;

use App\Services\FbrService;
use Illuminate\Console\Command;

class ProcessFbrQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fbr:process-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending FBR invoice submissions';

    protected $fbrService;

    public function __construct(FbrService $fbrService)
    {
        parent::__construct();
        $this->fbrService = $fbrService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing FBR queue...');
        
        $this->fbrService->processQueue();
        
        $this->info('FBR queue processing completed.');
    }
}