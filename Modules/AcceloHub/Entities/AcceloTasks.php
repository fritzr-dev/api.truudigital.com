<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloTasks extends Model
{
	protected $table = 'acceloTasks';
	protected $fillable = ['project_id','accelo_task_id','hubstaff_task_id','acceloTask_data','hubstaffTask_data','type', 'status']; 
	
	public function AcceloProjects()
    {
        return $this->belongsTo(AcceloProjects::class);
    }	

    public static function getAcceloDBTasks(){
      #developer demo
      /*$project_id = 35;
      $records = AcceloTasks::where('project_id', $project_id)->where('status', 0)->get()->map(function($task){
          $project = AcceloProjects::where('id', $task->project_id)->first();
          $task['accelo_project_id']    = $project->accelo_project_id;
          $task['hubstaff_project_id']  = $project->hubstaff_project_id;
          return $task;
       });          

      return $records;*/
      
      $records = AcceloTasks::where('status', 0)->limit(config('accelohub.cLimit'))->get()->map(function($task){
          $project = AcceloProjects::where('id', $task->project_id)->first();
          $task['accelo_project_id']    = $project->accelo_project_id;
          $task['hubstaff_project_id']  = $project->hubstaff_project_id;
          return $task;
       });          
      return $records;
    } //getAcceloDBTasks 

}
