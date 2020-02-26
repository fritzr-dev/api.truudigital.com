@extends('admin::layouts.master')
@section('page_title', env('APP_NAME') . ' | Pages')
@section('body_class', 'hold-transition skin-blue sidebar-mini')
@section('styles')
@endsection
@section('scripts')
@endsection

@section('body')
  <div class="content-wrapper">
    
    <section class="content-header">
      <h1>
        Members <a class="btn btn-success btn-header" href="{{ url('admin/accelohub/member/create') }}">New</a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Members</li>
      </ol>
    </section>

    
    <section class="content">
      
      {!!session()->has('success') ? '<div class="callout callout-success"><h4><i class="icon fa fa-check"></i> Success</h4><p>'. session()->get('success') .'</p></div>' : ''!!}

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Members</h3>

              <div class="box-tools">
                <form method="get" action="{{ url('admin/accelohub/members') }}">
                  <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" name="s" class="form-control pull-right" placeholder="Search" value="{{ request('s') }}" />

                    <div class="input-group-btn">
                      <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding">
              <table class="table table-hover">
                <tr>
                  <th width="40">ID</th>
                  <th>Acello</th>
                  <th>Hubstaff</th>
                  <th>Date Created</th>
                  <th>Status</th>
                  <th colspan="2">Action</th>
                </tr>
                @forelse($members as $member)
                <tr>
                  <td>{{ $member->id }}</td>
                  <td><strong>{{ $member->accelo_member_id }}</strong> - {{ $member->accelo_name }}</td>
                  <td><strong>{{ $member->hubstaff_member_id }}</strong> - {{ $member->hubstaff_name }}</td>
                  <td>{{ $member->created_at->diffForHumans() }}</td>
                  <td>
                    @if( $member->status )
                    <span class="label label-success">Active</span></td>
                    @else
                    <span class="label label-warning">Inactive</span></td>
                    @endif
                  <td width="30"><a data-toggle="tooltip" title="Edit" href="{{ url('admin/accelohub/member/' . $member->id . '/edit') }}"><i class="fa fa-edit"></i></a></td>
                  <td width="30"><a data-toggle="tooltip" title="Delete" href="{{ url('admin/accelohub/member/' . $member->id . '/delete') }}" onclick="return confirm('Are you sure you want to delete this item')"><i class="fa fa-trash"></i></a></td>
                </tr>
                @empty
                <tr>
                  <td colspan="6"><a href="{{ url('admin/accelohub/member/create') }}" class="btn btn-success btn-header">New Member</a></td>
                </tr>
                @endforelse
              </table>
            </div>
            <!-- /.box-body -->

            <div class="box-footer clearfix text-center">
              {{-- $pagination->links() --}}
            </div>
            <!-- /.box-footer -->

          </div>
          <!-- /.box -->
        </div>
      </div>

    </section>
    
  </div>

<!-- ./wrapper -->
@endsection
