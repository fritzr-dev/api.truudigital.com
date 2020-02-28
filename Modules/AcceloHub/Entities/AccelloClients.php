<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AccelloClients extends Model
{
	protected $table = 'accelloClients';
	protected $fillable = ['accelo_client_id','hubstaff_client_id'];
}
