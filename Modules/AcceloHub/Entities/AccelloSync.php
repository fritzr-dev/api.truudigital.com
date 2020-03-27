<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloSync extends Model
{
	protected $table = 'acceloSyncLogs';
	protected $fillable = ['module', 'error', 'success', 'result'];
	/*module
		project
		taskDB
		ticketDB
		task
		timesheets
	*/

	public static function newLog($module, $result){
	
        $post_logs = [
							'module'  => $module,
							'success' => isset($result['success']) ? json_encode($result['success']) : '',
							'error'   => isset($result['error']) ? json_encode($result['error']) : '',
							'result'  => json_encode($result)
                        ];
        $new_task = AcceloSync::create($post_logs);
	} //newLog
}
