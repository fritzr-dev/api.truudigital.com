<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloClients extends Model
{
	protected $table = 'acceloClients';
	protected $fillable = ['accelo_client_id','hubstaff_client_id'];
}
