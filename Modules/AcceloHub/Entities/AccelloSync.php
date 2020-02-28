<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AccelloSync extends Model
{
	protected $table = 'acceloSyncLogs';
	protected $fillable = ['module'];
}
