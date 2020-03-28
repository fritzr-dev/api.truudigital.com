<?php

namespace Modules\AcceloHub\Entities;

use Illuminate\Database\Eloquent\Model;

class AcceloMembers extends Model
{
	protected $table = 'acceloMembers';
	protected $fillable = ['accelo_member_id','hubstaff_member_id','accelo_data','hubstaff_data','status'];    

	public static function member_ids(){
	    $members = AcceloMembers::get();
	    $ids = [];
	    foreach ($members as $key => $member) {
	    	$ids[] = $member->hubstaff_member_id;
	    }

	    return $ids;
	}//member_ids

	public static function assign_hubstaff_ids(){
	    $members = AcceloMembers::get();
	    $ids = [];
	    foreach ($members as $key => $member) {
	    	$ids[] = array("user_id" => $member->hubstaff_member_id, "role"=> "user");
	    }

	    return $ids;
	}//assign_hubstaff_ids
	
	public static function get_HID_byAID($id){
	    $ids 		= '';
	    $members 	= AcceloMembers::where('accelo_member_id', $id)->pluck('hubstaff_member_id')->toArray();

	    if($members) {
	    	$ids = implode(',', $members);
	    }
	    return $ids;
	}//get_HID_byAIDwhere

}
