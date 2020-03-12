<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloTasks extends Model
{
	protected $table = 'acceloTasks';
	protected $fillable = ['project_id','accelo_task_id','hubstaff_task_id','type']; 
	
	public function AcceloProjects()
    {
        return $this->belongsTo(AcceloProjects::class);
    }	
}
