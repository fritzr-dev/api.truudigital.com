<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB, Route;

use Modules\AcceloHub\Entities\AcceloMembers;
use Modules\AcceloHub\Entities\AcceloConnect;
use Modules\AcceloHub\Entities\HubstaffConnect;

class AcceloHubController extends Controller
{

    public function hook() {

      \Hooks::add('admin_menu',function(){
        if( \Helper::hasAccess('module.admin.accelohub.read') ) {

          $html = '<li class="treeview '.(\Request::is('admin/accelohub') || \Request::is('admin/accelohub/*')  || \Request::is('admin/accelohub/*') ? ' active' : '') .'"><a href="#"><i class="fa fa-book"></i> <span>Manage Accelo</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
          </a>
            <ul class="treeview-menu">
              <li'. (\Request::is('admin/accelohub') || \Request::is('admin/accelohub/*') ? ' class="active"' : '') .'>
                <a href="'.url('admin/accelohub/members').'"><i class="fa fa-circle-o"></i> View Members</a></li>
              '. ( \Helper::hasAccess('module.admin.accelohub.create') ? '<li'. (\Request::is('admin/accelohub/member/create') ? ' class="active"' : '') .'>
                <a href="'.url('admin/accelohub/member/create').'"><i class="fa fa-circle-o"></i> New Member</a>
                <hr />
                <a href="'.url('admin/accelohub/organization').'"><i class="fa fa-circle-o"></i> Manage Organizations</a>
                <a href="'.url('admin/accelohub/projects').'"><i class="fa fa-circle-o"></i> Manage Projects</a>
                <a href="'.url('admin/accelohub/task').'"><i class="fa fa-circle-o"></i> Manage Task</a>
                <a href="'.url('admin/accelohub/activities').'"><i class="fa fa-circle-o"></i> Members Activities</a>
                <a href="'.url('admin/accelohub/logs').'"><i class="fa fa-circle-o"></i> Sync Logs</a>
                </li>' : '' ) .'
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
        $limit  = $request->get('limit', env('LIMIT'));
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
        foreach ($members_h as $key => $member) {
          if($post['hubstaff_id'] == $member['id']) {
            $hubstaff_data    = json_encode($member);
            break;
          }
        }

        $data = [
                'accelo_member_id'      => $post['accelo_id'],
                'hubstaff_member_id'    => $post['hubstaff_id'],
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

        $data = ['name' => 'Fritz Darryl Roca'];
        $accelo_data    = json_encode($data);
        $hubstaff_data  = json_encode($data);

        $data = [
                'accelo_member_id'      => $post['accelo_id'],
                'hubstaff_member_id'    => $post['hubstaff_id'],
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
        $limit  = $request->get('limit', env('LIMIT'));
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

}
