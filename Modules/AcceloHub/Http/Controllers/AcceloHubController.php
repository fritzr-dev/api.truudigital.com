<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB, Route;

use Modules\AcceloHub\Entities\AcceloMembers;
use Modules\AcceloHub\Entities\AccelloConnect;

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
            if (isset($accelo_data->name)) {
                $user->accelo_name = $accelo_data->name;
            }
            if (isset($hubstaff_data->name)) {
                $user->hubstaff_name = $hubstaff_data->name;
            }
            $user->hubstaff_name = $accelo_data->name;
            return $user;
        });


        return view('accelohub::admin.members',[
                    'members' => $members,
                    'pagination' => $pagination
                    ]);

    }

    public function memberCreate()
    {
        $members_a = [];
        $members_a[] = array('id' => '1234', 'name' => 'Fritz' );
        $members_a[] = array('id' => '5678', 'name' => 'Darryl' );
        $members_h = [];
        $members_h[] = array('id' => '1111', 'name' => 'FritzR' );
        $members_h[] = array('id' => '2222', 'name' => 'DarrylR' );        

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

        $member = AcceloMembers::where('accelo_member_id', $post['accello_id'])->get();

        if($member->isNotEmpty()) {
            return redirect('admin/accelohub/member/create')
                        ->withErrors("Accelo member is already exists.")
                        ->withInput();
        } 

        $data = ['name' => 'Fritz Darryl'];
        $accelo_data    = json_encode($data);
        $hubstaff_data  = json_encode($data);

        $data = [
                'accelo_member_id'      => $post['accello_id'],
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

        $members_a = [];
        $members_a[] = array('id' => '1234', 'name' => 'Fritz' );
        $members_a[] = array('id' => '5678', 'name' => 'Darryl' );
        $members_h = [];
        $members_h[] = array('id' => '1111', 'name' => 'FritzR' );
        $members_h[] = array('id' => '2222', 'name' => 'DarrylR' );        

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
                'accelo_member_id'      => $post['accello_id'],
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

}
