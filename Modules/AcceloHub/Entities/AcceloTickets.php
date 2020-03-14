<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloTickets extends Model
{
	protected $table = 'acceloTickets';
	protected $fillable = ['accelo_ticket_id','hubstaff_task_id', 'acceloTicket_data','hubstaffTask_data', 'status'];
}
