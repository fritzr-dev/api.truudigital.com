<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloProjects extends Model
{
	protected $table = 'acceloProjects';
	protected $fillable = ['accelo_project_id','hubstaff_project_id', 'acceloProj_data','hubstaffProj_data', 'status'];
	
    public function AcceloTasks()
    {
        return $this->hasMany(AcceloTasks::class);
    }

    public static function getAcceloDBProjects(){
      #developer demo
      /*$accelo_project_id = 290;
      $records = AcceloProjects::where('accelo_project_id', $accelo_project_id)->get();
      return $records;*/
      $records = AcceloProjects::where('status', 0)->limit(config('accelohub.cLimit'))->get();

      return $records;
    } //getAcceloDBProjects

    public static function getTicketProject(){
      #$records = AcceloProjects::get();#->limit(1);
      #developer demo
      $hubstaff_project_id = config('accelohub.project_ticket');
      $record = AcceloProjects::where('hubstaff_project_id', $hubstaff_project_id)->first();
      if($record) {
        return $record->id;
      } else {
        $project = AcceloProjects::create([
          'accelo_project_id'   => 1,
          'hubstaff_project_id' => $hubstaff_project_id,
          'acceloProj_data'     => '',
          'hubstaffProj_data'   => '',
          'status'              => 1,
        ]);
        return $project ? $project->id : 0;
      }

    } //getTicketProject

}
