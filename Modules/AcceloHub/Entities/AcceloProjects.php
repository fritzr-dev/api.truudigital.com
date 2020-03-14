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
    	
}
