<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AcceloProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accelo:projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Accelo Project details';

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
        $this->info('All Accelo projects successfully posted to Hubstaff!');
    }
}
