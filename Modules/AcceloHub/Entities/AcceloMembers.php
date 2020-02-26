<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloMembers extends Model
{
	protected $table = 'acceloMembers';
	protected $fillable = ['accelo_member_id','hubstaff_member_id','accelo_data','hubstaff_data','status'];    
}
