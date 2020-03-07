<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloSync extends Model
{
	protected $table = 'acceloSyncLogs';
	protected $fillable = ['module'];
}
