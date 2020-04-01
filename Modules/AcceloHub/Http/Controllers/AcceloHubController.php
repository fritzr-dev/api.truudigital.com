<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB, Route;
use Carbon\Carbon;

use Modules\AcceloHub\Entities\AcceloMembers;
use Modules\AcceloHub\Entities\AcceloConnect;
use Modules\AcceloHub\Entities\HubstaffConnect;
use Modules\AcceloHub\Entities\AcceloProjects;
use Modules\AcceloHub\Entities\AcceloTickets;
use Modules\AcceloHub\Entities\AcceloTasks;
use Modules\AcceloHub\Entities\HubstaffActivity;
use Modules\AcceloHub\Entities\AcceloSync;

class AcceloHubController extends Controller
{

    public function hook() {

      \Hooks::add('admin_menu',function(){
        if( \Helper::hasAccess('module.admin.accelohub.read') ) {

          $html = '<li class="treeview '.(\Request::is('admin/accelohub') || \Request::is('admin/accelohub/*')  || \Request::is('admin/accelohub/*') ? ' active' : '') .'"><a href="#"><i class="fa fa-book"></i> <span>Manage Accelo</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>
            <ul class="treeview-menu">
              <li'. (\Request::is('admin/accelohub/members') ? ' class="active"' : '') .'>
                <a href="'.url('admin/accelohub/members').'"><i class="fa fa-circle-o"></i> View Members</a></li>
              '. ( \Helper::hasAccess('module.admin.accelohub.create') ? '<li'. (\Request::is('admin/accelohub/member/create') ? ' class="active"' : '') .'>
                <a href="'.url('admin/accelohub/member/create').'"><i class="fa fa-circle-o"></i> New Member</a>
                <hr />
              </li>
              <li'. (\Request::is('admin/accelohub/projects') ? ' class="active"' : '') .'><a href="'.url('admin/accelohub/projects').'"><i class="fa fa-circle-o"></i> Projects</a></li>
              <li'. (\Request::is('admin/accelohub/tickets') ? ' class="active"' : '') .'><a href="'.url('admin/accelohub/tickets').'"><i class="fa fa-circle-o"></i> Tickets</a></li>
              <li'. (\Request::is('admin/accelohub/tasks') ? ' class="active"' : '') .'><a href="'.url('admin/accelohub/tasks').'"><i class="fa fa-circle-o"></i>Tasks</a></li>
              <li'. (\Request::is('admin/accelohub/activities') ? ' class="active"' : '') .'><a href="'.url('admin/accelohub/activities').'"><i class="fa fa-circle-o"></i> Timesheets</a></li>
              <li'. (\Request::is('admin/accelohub/logs') ? ' class="active"' : '') .'><a href="'.url('admin/accelohub/logs').'"><i class="fa fa-circle-o"></i> Migration Logs</a></li>' : '' ) .'
            </ul>
          </li>';
        }
        return isset($html) ? $html : '';
      }, 21);

    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('accelohub::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('accelohub::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('accelohub::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('accelohub::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Display a listing of the members.
     * @return Response
     */
    public function members(Request $request)
    {
        $limit  = $request->get('limit', config('accelohub.pLimit'));
        $search = $request->get('s');
        $sort   = $request->get('sort');
        $by     = $request->get('by');

        $members = AcceloMembers::orderByRaw("id ASC");

        if($search) {
            $members = $members->where(function($q) use($search){
              $q->where( 'accelo_member_id', 'like', "%$search%" );
              $q->orWhere('hubstaff_member_id', 'like', "%$search%" );
            });
        }
        
        $pagination = $members->paginate($limit);

        $members = $members->get();
        $members->map(function ($user) {
            $accelo_data    = json_decode($user->accelo_data);
            $hubstaff_data  = json_decode($user->hubstaff_data);
            $email = '';
            if (isset($accelo_data->firstname)) {
                $user->accelo_name = "$accelo_data->firstname $accelo_data->surname";
                $email = $accelo_data->email;

            }
            if (isset($hubstaff_data->name)) {
                $user->hubstaff_name = $hubstaff_data->name;
                $email = $hubstaff_data->email;

                /*$entry = AcceloMembers::find($user->id);
                $entry->hubstaff_full_name = $hubstaff_data->name;
                $entry->update();*/
            }

            $user->email = $email;
            #dd($accelo_data, $hubstaff_data, $user);
            return $user;
        });


        return view('accelohub::admin.members',[
                    'members' => $members,
                    'pagination' => $pagination
                    ]);

    }

    public function getAcceloHubMembers(){
      if(isset($_SESSION['ACCELO_MEMBERS']) ){
        $members_a = $_SESSION['ACCELO_MEMBERS'];
      } else {
        $members_a = AcceloConnect::getStaff();
        $_SESSION['ACCELO_MEMBERS'] = $members_a;
      }

      if(isset($_SESSION['HUBSTAFF_MEMBERS']) ){
        $members_h = $_SESSION['HUBSTAFF_MEMBERS'];
      } else {
        $members_h = HubstaffConnect::getOrganizationMembers();
        $_SESSION['HUBSTAFF_MEMBERS'] = $members_h;
        $members = array();
        foreach ($members_h as $key => $member) {
          $user_id  = $member['user_id'];
          $user     = HubstaffConnect::getUser($user_id);
          $members[] = array_merge($member, $user);
        }
        $_SESSION['HUBSTAFF_MEMBERS'] = $members;
        $members_h = $members;
      }
      return compact("members_a", "members_h");


      #dd($members_a, $members_h);
        /*$members_a = [];
        $members_a[] = array('id' => '1234', 'name' => 'Fritz' );
        $members_a[] = array('id' => '5678', 'name' => 'Darryl' );
        $members_h = [];
        $members_h[] = array('id' => '1111', 'name' => 'FritzR' );
        $members_h[] = array('id' => '2222', 'name' => 'DarrylR' );*/      
    } //getAcceloHubMembers

    public function memberCreate()
    {
      $accelo_connection    = false;#AcceloConnect::getToken();
      $hubstaff_connection  = false;

      $members = $this->getAcceloHubMembers();
      extract($members);

      $members_a = json_decode(json_encode($members_a), FALSE);
      $members_h = json_decode(json_encode($members_h), FALSE);

      return view('accelohub::admin.memberCreate', 
                  ['members_a' => $members_a, 'members_h' => $members_h]
                );
    } //memberCreate

    public function memberSave(Request $request)
    {
        #$request->get('meta')
        $post = $request->all();

        $member = AcceloMembers::where('accelo_member_id', $post['accelo_id'])->get();

        if($member->isNotEmpty()) {
            return redirect('admin/accelohub/member/create')
                        ->withErrors("Accelo member is already exists.")
                        ->withInput();
        } 

        $members = $this->getAcceloHubMembers();
        extract($members);

        $accelo_data = ''; $hubstaff_data = '';
        foreach ($members_a as $key => $member) {
          if($post['accelo_id'] == $member['id']) {
            $accelo_data    = json_encode($member);
            break;
          }
        }
        $hubstaff_full_name = '';
        foreach ($members_h as $key => $member) {
          if($post['hubstaff_id'] == $member['id']) {
            $hubstaff_full_name = $member['name'];       
            $hubstaff_data    = json_encode($member);
            break;
          }
        }

        $data = [
                'accelo_member_id'      => $post['accelo_id'],
                'hubstaff_member_id'    => $post['hubstaff_id'],
                'hubstaff_full_name'    => $hubstaff_full_name,
                'accelo_data'           => $accelo_data,
                'hubstaff_data'         => $hubstaff_data,
                'status'                => 1 ];
        try {
            $new_member = AcceloMembers::create($data);
        } catch(\Exception $e) {
            return redirect('admin/accelohub/member/create')
                        ->withErrors($e->getMessage())
                        ->withInput();
        }

        if($new_member) {
            return redirect()->
                 to('admin/accelohub/members')->
                 withSuccess('Member successfully saved!')->
                 send();
        } else {
            return redirect('admin/accelohub/member/create')
                        ->withErrors("Data not save.")
                        ->withInput();
            #return redirect()->back()->withInput(['message' => 'Data not save']); 
        }

    } //memberSave

    public function memberEdit($id)
    {
      $entry = AcceloMembers::find($id);
      if(!$entry) {
        return redirect()->to('admin/accelohub/members')->withErrors(['msg','Something went wrong.']);
      }

      $accelo_connection    = false;#AcceloConnect::getToken();
      $hubstaff_connection  = false;

      $members = $this->getAcceloHubMembers();
      extract($members);

      $members_a = json_decode(json_encode($members_a), FALSE);
      $members_h = json_decode(json_encode($members_h), FALSE);

        return view('accelohub::admin.memberEdit', 
                    ['entry' => $entry, 'members_a' => $members_a, 'members_h' => $members_h]
                );
    } //memberEdit

    public function memberUpdate(Request $request)
    {
        $id =  $request->post('id');
        $entry = AcceloMembers::find($id);
        if(!$entry) {
        return redirect()->to('admin/accelohub/members')->withErrors(['msg','Something went wrong.']);
        }

        $post = $request->all();

        $members = $this->getAcceloHubMembers();
        extract($members);

        $accelo_data = ''; $hubstaff_data = '';
        foreach ($members_a as $key => $member) {
          if($post['accelo_id'] == $member['id']) {
            $accelo_data    = json_encode($member);
            break;
          }
        }
        $hubstaff_full_name = '';
        foreach ($members_h as $key => $member) {
          if($post['hubstaff_id'] == $member['id']) {
            $hubstaff_full_name = $member['name'];            
            $hubstaff_data    = json_encode($member);
            break;
          }
        }

        $data = [
                'accelo_member_id'      => $post['accelo_id'],
                'hubstaff_member_id'    => $post['hubstaff_id'],
                'hubstaff_full_name'    => $hubstaff_full_name, 
                'accelo_data'           => $accelo_data,
                'hubstaff_data'         => $hubstaff_data];

        try {
            $entry->update($data);
        } catch(\Exception $e) {
            return redirect("admin/accelohub/member/$id/edit")
                        ->withErrors($e->getMessage())
                        ->withInput();
        }

        return redirect()->
                 to('admin/accelohub/members')->
                 withSuccess('Member successfully updated!')->
                 send();
    } //memberUpdate

    public function memberDestroy($id)
    {
      $member = AcceloMembers::find($id);
      if(!$member) {
        return redirect()->to('admin/accelohub/members')->withErrors(['msg','Something went wrong.']);
      }

      $member->delete();

      return redirect()->to('admin/accelohub/members')->withSuccess('Member successfully deleted.');
    } //memberDestroy

    public function organization(Request $request)
    {
        $limit  = $request->get('limit', config('accelohub.pLimit'));
        $search = $request->get('s');
        $sort   = $request->get('sort');
        $by     = $request->get('by');

        $members = AcceloMembers::orderByRaw("id ASC");

        if($search) {
            $members = $members->where(function($q) use($search){
              $q->where( 'accelo_member_id', 'like', "%$search%" );
              $q->orWhere('hubstaff_member_id', 'like', "%$search%" );
            });
        }
        
        $pagination = $members->paginate($limit);

        $members = $members->get();
        $members->map(function ($user) {
            $accelo_data    = json_decode($user->accelo_data);
            $hubstaff_data  = json_decode($user->hubstaff_data);
            $email = '';
            if (isset($accelo_data->firstname)) {
                $user->accelo_name = "$accelo_data->firstname $accelo_data->surname";
                $email = $accelo_data->email;
            }
            if (isset($hubstaff_data->name)) {
                $user->hubstaff_name = $hubstaff_data->name;
                $email = $hubstaff_data->email;
            }

            $user->email = $email;
            #dd($accelo_data, $hubstaff_data, $user);
            return $user;
        });


        return view('accelohub::admin.members',[
                    'members' => $members,
                    'pagination' => $pagination
                    ]);

    } //organization 

    public function projects(Request $request)
    {
        $limit  = $request->get('limit', config('accelohub.pLimit'));
        $search = $request->get('s');
        $sort   = $request->get('sort');
        $by     = $request->get('by');

        $records = AcceloProjects::orderByRaw("id ASC");

        if($search) {
            $records = $records->where(function($q) use($search){
              $q->where( 'accelo_member_id', 'like', "%$search%" );
              $q->orWhere('hubstaff_member_id', 'like', "%$search%" );
              $q->orWhere('acceloProj_data', 'like', "%$search%" );
              $q->orWhere('hubstaffProj_data', 'like', "%$search%" );
            });
        }
        
        $pagination = $records->paginate($limit);

        $records = $records->get();
        $records->map(function ($user) {
            $accelo_data    = json_decode($user->acceloProj_data);
            $hubstaff_data  = json_decode($user->hubstaffProj_data);
            #dd($accelo_data, $hubstaff_data);

            if (isset($accelo_data->title)) {
                $user->accelo_name  = $accelo_data->title;
                $user->date_created = $accelo_data->date_created;
                $user->status       = $accelo_data->standing;
            }
            if (isset($hubstaff_data->name)) {
                $user->hubstaff_name = $hubstaff_data->name;
                $user->hubstaff_desc = $hubstaff_data->description;
            }

            if($user->accelo_project_id == 1) {
                $user->accelo_name  = "_TICKETS";
                $user->hubstaff_name = "_TICKETS";
            }

            return $user;
        });


        return view('accelohub::admin.projects',[
                    'records' => $records,
                    'pagination' => $pagination
                    ]);

    } //projects 

    public function tickets(Request $request)
    {
        $limit  = $request->get('limit', config('accelohub.pLimit'));
        $search = $request->get('s');
        $sort   = $request->get('sort');
        $by     = $request->get('by');

        $records = AcceloTasks::where('type', 'TICKET')->orderByRaw("id ASC");

        if($search) {
            $records = $records->where(function($q) use($search){
              $q->where( 'project_id', 'like', "%$search%" );
              $q->orWhere('accelo_task_id', 'like', "%$search%" );
              $q->orWhere('hubstaff_task_id', 'like', "%$search%" );
              $q->orWhere('acceloTask_data', 'like', "%$search%" );
              $q->orWhere('hubstaffTask_data', 'like', "%$search%" );
            });
        }
        
        $pagination = $records->paginate($limit);

        $records = $records->get();
        $records->map(function ($record) {
            $accelo_data    = json_decode($record->acceloTask_data);
            $hubstaff_data  = json_decode($record->hubstaffTask_data);

            $record->accelo_name    = '';
            $record->date_created   = '';
            $record->hubstaff_name  = '';
            $record->hubstaff_desc  = '';                
            if (isset($accelo_data->title)) {
                $record->accelo_name  = $accelo_data->title;
                $record->date_created = $accelo_data->date_created;
            }
            if (isset($hubstaff_data->name)) {
                $record->hubstaff_name = $hubstaff_data->name;
                $record->hubstaff_desc = $hubstaff_data->description;
            }

            return $record;
        });


        return view('accelohub::admin.tickets',[
                    'records' => $records,
                    'pagination' => $pagination
                    ]);

    } //tickets

    public function tasks(Request $request)
    {

        $limit  = $request->get('limit', config('accelohub.pLimit'));
        $search = $request->get('s');
        $sort   = $request->get('sort');
        $by     = $request->get('by');

        $records = AcceloTasks::where('type', 'TASK')->orderByRaw("id ASC");

        if($search) {
            $records = $records->where(function($q) use($search){
              $q->where( 'project_id', 'like', "%$search%" );
              $q->orWhere('accelo_task_id', 'like', "%$search%" );
              $q->orWhere('hubstaff_task_id', 'like', "%$search%" );
              $q->orWhere('acceloTask_data', 'like', "%$search%" );
              $q->orWhere('hubstaffTask_data', 'like', "%$search%" );
            });
        }
        
        $pagination = $records->paginate($limit);

        $records = $records->get();
        $records->map(function ($record) {
            $project_id = $record->project_id;

            $project  = AcceloProjects::where('id', $project_id)->first(); /*->select('acceloProj_data, hubstaffProj_data')*/
            $project  = json_decode($project->hubstaffProj_data);

            $project_name   = $project->name;
            $accelo_data    = json_decode($record->acceloTask_data);
            $hubstaff_data  = json_decode($record->hubstaffTask_data);

            $record->project_name   = $project_name;
            $record->accelo_name    = '';
            $record->date_created   = '';
            $record->hubstaff_name  = '';
            $record->hubstaff_desc  = '';                
            if (isset($accelo_data->title)) {
                $record->accelo_name  = $accelo_data->title;
                $record->date_created = $accelo_data->date_created;
            }
            if ($record->hubstaff_task_id) {
                $record->hubstaff_name = $hubstaff_data->summary;
                $record->hubstaff_desc = $hubstaff_data->summary;
            }

            return $record;
        });


        return view('accelohub::admin.tasks',[
                    'records' => $records,
                    'pagination' => $pagination
                    ]);

    } //tasks

    public function activities(Request $request)
    {
        $limit  = $request->get('limit', config('accelohub.pLimit'));
        $search = $request->get('s');
        $sort   = $request->get('sort');
        $by     = $request->get('by');

        $records = HubstaffActivity::orderByRaw("id ASC");

        if($search) {
            $records = $records->where(function($q) use($search){
              $q->where( 'accelo_activity_id', 'like', "%$search%" );
              $q->orWhere('hubstaff_activity_id', 'like', "%$search%" );
              $q->orWhere('acceloActivity_data', 'like', "%$search%" );
              $q->orWhere('hubstaffActivity_data', 'like', "%$search%" );
              $q->orWhere('acceloPost_data', 'like', "%$search%" );
            });
        }
        
        $pagination = $records->paginate($limit);

        $records = $records->get();
        $records->map(function ($entry) {
            $accelo_data    = json_decode($entry->acceloPost_data);
            $hubstaff_data  = json_decode($entry->hubstaffActivity_data);

            $entry->Member        = $hubstaff_data->Member;
            $entry->Organization  = $hubstaff_data->Organization;
            $entry->Time_Zone     = $hubstaff_data->Time_Zone;
            $entry->Project       = $hubstaff_data->Project;
            $entry->Task_Summmary = $hubstaff_data->Task_Summmary;
            $entry->Start         = Carbon::parse($hubstaff_data->Start)->format('Y-m-d g:ia');
            $entry->Stop          = Carbon::parse($hubstaff_data->Stop)->format('Y-m-d g:ia');
            $entry->Duration      = $hubstaff_data->Duration;
            $entry->Manual        = $hubstaff_data->Manual;
            $entry->Notes         = $hubstaff_data->Notes;
            $entry->Reasons       = $hubstaff_data->Reasons;
          return $entry;
        });

        return view('accelohub::admin.activities',[
                    'records' => $records,
                    'pagination' => $pagination
                    ]);

    } //activities

    public function importTimesheets(Request $request){

      /*$request->validate([
        'csv' => 'required|mimes:csv'
      ]);*/
      $post = $request->all();
      $file = $request->file('csv');

      #$file = request()->file('csv');
      if(!$request->hasFile('csv')){
        return redirect('admin/accelohub/activities')
                    ->withErrors("Invalid file.")
                    ->withInput();        
      }

      /*//Display File Name
      echo 'File Name: '.$file->getClientOriginalName();
      echo '<br>';
   
      //Display File Extension
      echo 'File Extension: '.$file->getClientOriginalExtension();
      echo '<br>';
   
      //Display File Real Path
      echo 'File Real Path: '.$file->getRealPath();
      echo '<br>';*/
      $field =
      [
        "Member"        => 0,
        "Organization"  => 1,
        "Time_Zone"     => 2,
        "Project"       => 3,
        "Task_Summmary" => 4,
        "Start"         => 5,
        "Stop"          => 6,
        "Duration"      => 7,
        "Manual"        => 8,
        "Notes"         => 9,
        "Reasons"       => 10,
      ];

      $row = 1;
      $import = [];
      if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
        $x = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $num = count($data);
          #echo "<p> $num fields in line $row: <br /></p>\n";
          if($row == 1) {
            $h = $data;
            foreach ($h as $key => $name) {
              $field[] = str_replace(' ', '_', $name);
            }
            $field = array_flip($field);
            $row++;
            continue;
          }
          $import_row = [];
          for ($c=0; $c < $num; $c++) {
            $import_row[$field[$c]] = $data[$c];
            #echo $data[$c] . " $c<br />\n";
          }
          if($import_row) {
            $import[] = $import_row;
          }
        }
        fclose($handle);
      }

      $new_import = []; 
      $timesheets = []; 
      if($import) {
        foreach ($import as $key => $data) {
          $new_row = [];
          $import_data = json_encode($data);

          $Task_Summmary = $data['Task_Summmary'];
          #$task = explode("::", $Task_Summmary);
          #dd($task);
          $task_id = false;
          $against_type = 'task';
          if (preg_match('/TASK-(.*?)::/', $Task_Summmary, $match) == 1) {
              $task_id = isset($match[1]) ? trim($match[1]) : '';
          } else if (preg_match('/TICKET-(.*?)::/', $Task_Summmary, $match) == 1) {
            $task_id = isset($match[1]) ? trim($match[1]) : '';
            #$against_type = 'ticket';
          }

          $user_name = $data['Member'];
          $member = AcceloMembers::where('hubstaff_full_name', $user_name)->first();
          $user_id = 0;
          if($member) {
            $user_id = $member->accelo_member_id;
            $new_row['user_id']  = $user_id;
          }

          if($task_id) {     
            $task_entry = AcceloTasks::where('accelo_task_id', $task_id)->first(); 
            if(!$task_entry) { continue; }

            $task = json_decode($task_entry->acceloTask_data);
            #dd($task);
            $task_name = $task->title;
            $reason = (isset($data['Reasons']) && $data['Reasons']) ? " : ".$data['Reasons'] : '';   
            $notes = $data['Notes'].$reason;

            $class_id    = 3;

            $start  = $data['Start'];
            $end    = $data['Stop'];

            $start  = strtotime($start);
            $end    = strtotime($end);

            #$nonbillable = $end - $start;
            $time = $data['Duration'];
            $timeInSeconds = strtotime($time) - strtotime('TODAY');

            $timesheet = array(
              'subject'         => "Time Entry - #{$task_id} $task_name",
              'against_id'      => $task_id,
              'task_id'         => $task_id,
              'against_type'    => $against_type,
              'body'            => $notes,
              'owner_id'        => $user_id,
              'details'         => $notes,
              'time_allocation' => $task_id,
              'medium'          => 'note', 
              #'nonbillable'    => $nonbillable,
              #'billable'        => $billable,
              'visibility'      => 'all',
              'date_started'    => $start,
              //'date_logged'   => '1584361485',
              'class_id'        => $class_id
            );
            if(isset($task->billable) && $task->billable) {
              $timesheet['billable']     = $timeInSeconds;
            } else {
              $timesheet['nonbillable']  = $timeInSeconds;
            }            
            $timesheets[] = $timesheet;

            $now = Carbon::now();
            $new_row['user_id']               = $user_id;
            $new_row['accelo_activity_id']    = 0;
            $new_row['hubstaff_activity_id']  = 0;
            $new_row['acceloActivity_data']   = '';
            $new_row['hubstaffActivity_data'] = json_encode($data);
            $new_row['acceloPost_data']       = json_encode($timesheet);
            $new_row['created_at']            = $now;

            /*check duplicate entry*/
            $entry = HubstaffActivity::where('hubstaffActivity_data', $new_row['hubstaffActivity_data'])->first();
            if(!$entry) {
              $new_import[]        = $new_row;
            }
            /*check duplicate entry End*/

          }/* else {
            $new_import[]        = $data;
          }*/
        }
      }

      if ($new_import) {
        $new_activities = HubstaffActivity::insert($new_import);
        if($new_activities) {
            return redirect()->
                 to('admin/accelohub/activities')->
                 withSuccess('Timesheets successfully saved!')->
                 send();
        } else {
            return redirect('admin/accelohub/activities')
                        ->withErrors("Data not save.")
                        ->withInput();
            #return redirect()->back()->withInput(['message' => 'Data not save']); 
        }
      } else {
        return redirect('admin/accelohub/activities')
                    ->withErrors("No data exported.")
                    ->withInput();        
      }
    } //importTimesheets

    public function migrationLogs(Request $request)
    {
        $limit  = $request->get('limit', config('accelohub.pLimit'));
        $search = $request->get('s');
        $sort   = $request->get('sort');
        $by     = $request->get('by');

        $records = AcceloSync::orderByRaw("created_at DESC");

        if($search) {
            $records = $records->where(function($q) use($search){
              $q->where( 'module', 'like', "%$search%" );
              $q->orWhere('error', 'like', "%$search%" );
              $q->orWhere('success', 'like', "%$search%" );
            });
        }

        
        $pagination = $records->paginate($limit);

        $records = $records->get();
        $records->map(function ($entry) {
            $error    = json_decode($entry->error, true);
            $success  = json_decode($entry->success, true);
            $entry->count_error      = $error ? count($error) : 0;
            $entry->count_success    = $success ? count($success) : 0;
            
            $err_list = '';
            if($error) {
              foreach ($error as $key => $err) {
                if (isset($err['error'])) {
                  $err_list .= "<li>".$err['error']."</li>";
                } else if (isset($err['api'])) {
                  $err_list .= "<li>".$err['api']['error']."</li>";                  
                }
              }
            } else {
             $err_list = 'no error'; 
            }
            $entry->error_list    = $err_list;

            /*$success_list = '';
            if($success) {
              foreach ($success as $key => $err) {
                $success_list .= isset($err['success']) ? "<li>".$err['success']."</li>" : "";
              }
            }
            $entry->success_list    = $success_list;*/

          return $entry;
        });

        return view('accelohub::admin.migrationlogs',[
                    'records' => $records,
                    'pagination' => $pagination
                    ]);

    } //AcceloSync

    function ClearSessionMembers(){
      if (!session_id()) session_start();

      unset($_SESSION['ACCELO_MEMBERS']);
      unset($_SESSION['HUBSTAFF_MEMBERS']);
      echo "Session cleared: ACCELO_MEMBERS HUBSTAFF_MEMBERS";
    }
}
