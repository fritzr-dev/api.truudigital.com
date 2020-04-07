<?php

namespace Modules\AcceloHub\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\AcceloHub\Entities\AcceloSchedule;

class AcceloProject extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'accelohub:projects {type=projects}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Accelo project and post to Hubstaff Projects.';

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
        $type = $this->argument('type');
        if($type == 'projects') {
            $result = AcceloSchedule::projects();
            $this->info('Get All Accelo Project and Post to Hubstaff and save to DB');
        } else if($type == 'tasks') {
            $result = AcceloSchedule::getProjectTasks();
            $this->info('Get All Accelo Project Tasks and save to DB');
        } else if($type == 'tasksupdate') {
            $result = AcceloSchedule::getProjectTasks('updates'); 
            $this->info('Get All Accelo Updates Tasks and save to DB');
        } else if($type == 'tickets') {
            $result = AcceloSchedule::getTickets();
            $this->info('Get All Accelo Tickets and save to DB');
        } else if($type == 'tasks2Hubstaff') {
            $result = AcceloSchedule::postProjectTasks();
            $this->info('Post all Accelo Tasks saved in DB to Hubstaff');
        } else if($type == 'tasksupdate2Hubstaff') {
            $result = AcceloSchedule::updateProjectTasks();
            $this->info('Update all Accelo Tasks saved in DB to Hubstaff');
        } else if($type == 'taskticket2Hubstaff') {
            $result = AcceloSchedule::postProjectTasks('TICKET');
            $this->info('Post all Accelo Tasks saved in DB to Hubstaff');
        } else if($type == 'timesheet2Accello') {
            $result = AcceloSchedule::timesheets();
            #dd($result);
            $this->info('Post all timesheets to Accelo');
        }
        
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
