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
        Projects
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Projects</li>
      </ol>
    </section>

    
    <section class="content">
      
      {!!session()->has('success') ? '<div class="callout callout-success"><h4><i class="icon fa fa-check"></i> Success</h4><p>'. session()->get('success') .'</p></div>' : ''!!}

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Projects  <span class="label label-info">{{ $pagination->total() }}</span></h3>

              <div class="box-tools">
                <form method="get" action="{{ url('admin/accelohub/projects') }}">
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
                  <th>ID</th>
                  <th>ID::Acello</th>
                  <th>ID::Hubstaff</th>
                  <th>Hubstaff Description</th>
                  <th>Date Created</th>
                  <th>Status</th>
                  <th>Task Migrated</th>
                  <th>Task Pending</th>
                  <th>Total Task</th>
                </tr>
                @forelse($records as $record)
                <tr>
                  <td>{{ $record->id }}</td>
                  <td><strong>{{ $record->accelo_project_id }}</strong> :: {{ $record->accelo_name }}</td>
                  <td><strong>{{ $record->hubstaff_project_id }}</strong> :: {{ $record->hubstaff_name }}</td>
                  <td>{{ $record->hubstaff_desc }}</td>
                  <td>{{ $record->created_at->diffForHumans() }}</td>
                  <td>
                    @if( $record->status )
                    <span class="label label-success">Active</span>
                    @else
                    <span class="label label-warning">Inactive</span>
                    @endif
                  </td>
                  <td>{{ $record->task_migrated }}</td>
                  <td>{{ $record->task_pending }}</td>
                  <td>{{ $record->task_pending + $record->task_migrated }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4">no records</td>
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
