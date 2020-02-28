<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AccelloTasks extends Model
{
	protected $table = 'acceloTasks';
	protected $fillable = ['project_id','accelo_task_id','hubstaff_task_id']; 
	
	public function AccelloProjects()
    {
        return $this->belongsTo(AccelloProjects::class);
    }	
}
