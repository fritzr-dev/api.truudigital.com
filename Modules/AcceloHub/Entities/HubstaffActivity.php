<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class HubstaffActivity extends Model
{
	protected $table = 'acceloActivity';
	protected $fillable = ['user_id','accelo_activity_id','hubstaff_activity_id', 'acceloActivity_data','hubstaffActivity_data', 'api_error','status'];    
}
