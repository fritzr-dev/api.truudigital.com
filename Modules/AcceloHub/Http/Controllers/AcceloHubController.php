<?php

namespace Modules\AcceloHub\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

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
    public function members()
    {
        $members    = [];
        $pagination = [];

        return view('accelohub::admin.members', 
                    [ 'members' => $members, 'pagination' => $pagination ]
                );
    }

    public function memberCreate()
    {
        $members_a = [];
        $members_h = [];
        return view('accelohub::admin.memberCreate', 
                    ['members_a' => $members_a, 'members_h' => $members_h]
                );
    } 

}
