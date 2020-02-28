<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AccelloProjects extends Model
{
	protected $table = 'accelloProjects';
	protected $fillable = ['accelo_project_id','hubstaff_project_id'];

    public function AccelloTasks()
    {
        return $this->hasMany(AccelloTasks::class);
    }
    	
}
