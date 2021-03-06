<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Admin\Entities\Usession;
use Modules\Media\Entities\Media;
use Modules\Admin\Entities\User;
use Modules\Admin\Entities\Group;
use Modules\Admin\Entities\Usermeta;
use Modules\Admin\Entities\Upermit;
use Modules\User\Http\Requests\User as UserRequest;
use DB, Hooks, Carbon\Carbon, Helper, File;
class UserAdminController extends Controller
{
  public function hook(){
    Hooks::add('admin_menu',function(){
      if( \Helper::hasAccess('module.admin.user.read') ) {

        $html = '
        <li class="treeview '. (\Request::is('admin/users') || \Request::is('admin/users/*')  || \Request::is('admin/user/*') ? ' active' : '') .'">
          <a href="#"><i class="fa fa-user"></i> <span>Manage Users</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li'.(\Request::is('admin/users') || \Request::is('admin/users/*') ? ' class="active"' : '').'><a href="'.url('admin/users').'"><i class="fa fa-circle-o"></i> View Users</a></li>
            '. ( \Helper::hasAccess('module.admin.user.create') ? '<li'. (\Request::is('admin/user/create') ? ' class="active"' : '').'><a href="'.url('admin/user/create').'"><i class="fa fa-circle-o"></i> Create User</a></li>' : '' ) .'
          </ul>
        </li>
        ';

      }
      return isset($html) ? $html : '';
    }, 21);

    Hooks::add('user_profile', function(){
      
      $user = Helper::getUser();
      $avatar = url( 'media/thumbnail/'. usermeta($user->id,'avatar') ) . '?w=50&h=50';
      $media = Media::find($avatar);
      if( $media && !File::exists(public_path( $media->path )) ) {
        $avatar = url('media/img/user2-160x160.jpg');
      } else {
        $avatar = url('media/img/avatar5.png');
        $avatar_main = url('media/img/avatar5.png');
      }

      $html = '
      <div class="user-panel">
        <div class="pull-left image">
          <img src="'. $avatar .'" class="img-circle" alt="User Image" />
        </div>
        <div class="pull-left info">
          <p>'.usermeta($user->id,'first_name').' '.usermeta($user->id,'last_name').'</p>
          
          <a href="'.url('admin/user/'.$user->id.'/update').'"><i class="fa fa-edit text-success"></i> Edit Profile</a>
        </div>
      </div>
      ';
      return $html;
    });


    Hooks::add('user_top_menu', function(){
      $user = Helper::getUser();
      $avatar = usermeta($user->id,'avatar') ? url( 'media/thumbnail/'. usermeta($user->id,'avatar') ) . '?w=25&h=25' : url('media/img/avatar5.png'); 
      $avatar_main = usermeta($user->id,'avatar') ? url( 'media/thumbnail/'. usermeta($user->id,'avatar') ) . '?w=84&h=84' : url('media/img/avatar5.png'); 
      #$avatar = url( 'media/thumbnail/'. usermeta($user->id,'avatar') ) . '?w=25&h=25';
      #$avatar_main = url( 'media/thumbnail/'. usermeta($user->id,'avatar') ) . '?w=84&h=84';

      $media = Media::find($avatar);
      if( $media && !File::exists(public_path( $media->path )) ) {
        $avatar = url('media/img/user2-160x160.jpg');
        $avatar_main = url('media/img/user2-160x160.jpg');
      } else {
        $avatar = url('media/img/avatar5.png');
        $avatar_main = url('media/img/avatar5.png');
      }

      $html = '
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="'.$avatar.'" class="user-image" alt="'.usermeta($user->id,'first_name').' '.usermeta($user->id,'last_name').'">
              <span class="hidden-xs">'.usermeta($user->id,'first_name').' '.usermeta($user->id,'last_name').'</span>
            </a>
            <ul class="dropdown-menu">
              
              <li class="user-header">
                <img src="'.$avatar_main.'" class="img-circle" alt="User Image">

                <p>
                  '.usermeta($user->id,'first_name').' '.usermeta($user->id,'last_name').'
                  <small>Member since '. $user->created_at->diffForHumans() .'</small>
                </p>
              </li>
              
              <li class="user-footer">
                <div class="pull-left">
                  <a href="'.url('admin/user/'.$user->id.'/update').'" class="btn btn-default btn-flat">Edit Profile</a>
                </div>
                <div class="pull-right">
                  <a href="'.url('admin/logout').'" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
      ';

      return $html;
    });

  }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
      //$user = User::find(4)->groups()->sync([3]);
      //die();
      //$user = User::with(['groups','permits','meta'])->find(1);
      //dd($user->toArray());
      
      $limit = $request->get('limit', env('LIMIT'));
      $search = $request->get('s');
      $sort = $request->get('sort');
      $group = $request->get('group');
      //dd( Page::paginate(1) );

      $custom_sort = ($sort && in_array($sort,['desc','asc'])) ? "gid ASC, uid $sort" : "gid ASC, uid ASC";

      $users = User::select(['users.id as uid', 'users.*','b.id as gid','b.name as gname'])
      ->orderByRaw($custom_sort)
      ->join('user_group as a', function($q){
        $q->on('a.user_id','=','users.id');
        $q->orderBy('a.id','asc');
        $q->groupBy('a.user_id');
      })
      ->join('groups as b',function($q){
        $q->on('b.id','=','a.group_id');
      });

      if($search) {
        $users = $users->where(function($q) use($search){
          $q->where( 'username', 'ilike', "%$search%" );
          $q->orWhere('email', 'ilike', "%$search%" );
        });
      }

      if($group) {
        $users = $users->where('b.id',$group);
      }



      $pagination = $users->paginate($limit);
      //dd($users->get()->toArray());

      return view('user::admin.index',[
        'users' => $users->get()->map(function($q){
          $q->meta()->get()->map(function($qq) use(&$q){
            $q->setAttribute($qq->metakey,$qq->metavalue);
          });
          return $q;
        }),
        'pagination' => $pagination
      ]);

    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

      $modules = \Helper::getModules();
      $groups = Group::get()->map(function($q){

        $q->setAttribute('active',$q->id  == old('groups',0));
        return $q;
      });


      
      return view('user::admin.create',[
        'modules' => $modules,
        'groups' => $groups
      ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    //public function store()
    public function store(UserRequest $request)
    {

      //$user = User::find(4);
      //$user = $user->groups()->sync([1]);
      //die();


      $user = User::create([
        'email'=>request()->get('email'),
        'username'=>request()->get('username'),
        'password'=>bcrypt(request()->get('password'))
      ]);
      $user->groups()->sync([request()->get('groups')]);

      if($meta = request()->get('meta')) {
        foreach ($meta as $key => $value) {

          if( $user->meta()->where('metakey', $key)->count() ) {
            $user->meta()->where('metakey', $key)->update([
              'metavalue' => $value
            ]);
          } else {
            $user->meta()->saveMany([
              new Usermeta([
                'metakey' => $key,
                'metavalue' => $value
              ]),
            ]);            
          }

        }
      }


      $custom_permits = [];
      if(request()->get('permits')){
        foreach( request()->get('permits') as $module => $methods){
          $default_methods = [
            'read' => 0,
            'create' => 0,
            'update' => 0,
            'delete' => 0
          ];

          foreach($methods as $method) {
            $default_methods[$method] = 1;
          }

          if( $user->permits()->where('module', $module)->count() ) {
            $user->permits()->where('module', $module)->update([
              'create' => $default_methods['create'],
              'read' => $default_methods['read'],
              'update' => $default_methods['update'],
              'delete' => $default_methods['delete']
            ]);
          } else {
            $custom_permits[] = new UPermit([
              'module' => $module,
              'create' => $default_methods['create'],
              'read' => $default_methods['read'],
              'update' => $default_methods['update'],
              'delete' => $default_methods['delete']
            ]);
          }

        }
      }

      if( count($custom_permits) ) {
        $user->permits()->saveMany($custom_permits);
      }

      return redirect()->
             to('admin/users?sort=desc&group='.$user->groups()->first()->id)->
             withSuccess('User has been successfully created!')->
             send();


    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('user::admin.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {

      $user = User::find($id);
      $group_id = $user->groups()->first()->id;
      
      $modules = \Helper::getModules();
      $groups = Group::get()->map(function($q) use($group_id){
        $q->setAttribute('active',$q->id  == old('groups',$group_id));
        return $q;
      });
 
      $meta=[];
      $meta['avatar']='';
      $user->meta()->get()->map(function($q) use(&$meta){
        $meta[$q->metakey]=$q->metavalue;
        return $q;
      });

      
      return view('user::admin.update',[
        'modules' => $modules,
        'groups' => $groups,
        'user' => $user,
        'meta' => $meta
      ]);

    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update($id, UserRequest $request)
    {
      $user = User::find($id);
      $user->update([
        'email'=>request()->get('email'),
      ]);

      $user->groups()->sync([request()->get('groups')]);

      if($meta = request()->get('meta')) {
        foreach ($meta as $key => $value) {

          if( $user->meta()->where('metakey', $key)->count() ) {
            $user->meta()->where('metakey', $key)->update([
              'metavalue' => $value
            ]);
          } else {
            $user->meta()->saveMany([
              new Usermeta([
                'metakey' => $key,
                'metavalue' => $value
              ]),
            ]);            
          }

        }
      }


      $custom_permits = [];
      if( request()->get('permits') ) {
          
        foreach( request()->get('permits') as $module => $methods){
          $default_methods = [
            'read' => 0,
            'create' => 0,
            'update' => 0,
            'delete' => 0
          ];

          foreach($methods as $method) {
            $default_methods[$method] = 1;
          }

          if( $user->permits()->where('module', $module)->count() ) {
            $user->permits()->where('module', $module)->update([
              'create' => $default_methods['create'],
              'read' => $default_methods['read'],
              'update' => $default_methods['update'],
              'delete' => $default_methods['delete']
            ]);
          } else {
            $custom_permits[] = new UPermit([
              'module' => $module,
              'create' => $default_methods['create'],
              'read' => $default_methods['read'],
              'update' => $default_methods['update'],
              'delete' => $default_methods['delete']
            ]);
          }

        }
      }

      if( count($custom_permits) ) {
        $user->permits()->delete();
        $user->permits()->saveMany($custom_permits);
      }

      return redirect()->
             to('admin/users?sort=desc&group='.$user->groups()->first()->id)->
             withSuccess('User has been successfully update!')->
             send();


    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
      $user = User::find($id);
      if(!$user) {
        return redirect()->to('admin/users')->withErrors(['msg','Something went wrong.']);
      }
      $user->delete();
      return redirect()->to('admin/users')->withSuccess('User successfully deleted.');
    }
}
