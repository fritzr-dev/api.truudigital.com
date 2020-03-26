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
        Timesheets
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Timesheets</li>
      </ol>
    </section>

    
    <section class="content">
      
      {!!session()->has('success') ? '<div class="callout callout-success"><h4><i class="icon fa fa-check"></i> Success</h4><p>'. session()->get('success') .'</p></div>' : ''!!}

      @if ($errors->any())
        <ul class="callout callout-danger"> 
        @foreach ( $errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
        </ul>
      @endif

      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">New Import</h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
          </div>              
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        <form action="{{ url('admin/accelohub/activities/import') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="box-body">
            <div class="file-field">
              <div class="btn btn-success btn-sm">
                <span>Choose file</span>
                <input type="file" name="csv" class="form-control" required />
              </div> 
            </div> 
          </div>
          <!-- /.box-body -->
          <div class="box-footer"><button type="submit" class="btn btn-primary">Import</button></div>
        </form>      
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Timesheets</h3>

              <div class="box-tools">
                <form method="get" action="{{ url('admin/accelohub/activities') }}">
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
                  <th>Acello ID</th>
                  <th>Member</th>
                  <th>Project</th>
                  <th>Task</th>
                  <th>Start</th>
                  <th>Stop</th>
                  <th>Duration</th>
                  <th>Notes</th>
                  <th>Reasons</th>
                  <th>Date Imported</th>
                  <th>Status</th>
                </tr>
                @forelse($records as $record)
                <tr>
                  <td><strong>{{ $record->accelo_activity_id }}</strong></td>
                  <td><strong>{{ $record->Member }}</td>
                  <td>{{ $record->Project }}</td>
                  <td>{{ $record->Task_Summmary }}</td>
                  <td>{{ $record->Start }}</td>
                  <td>{{ $record->Stop }}</td>
                  <td>{{ $record->Duration }}</td>
                  <td>{{ $record->Notes }}</td>
                  <td>{{ $record->Reasons }}</td>
                  <td>{{ $record->created_at->diffForHumans() }}</td>
                  <td>
                    @if( $record->status )
                    <span class="label label-success">Migrated</span>
                    @else
                    <span class="label label-warning">Pending</span>
                    @endif
                  </td>
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
              {{ $pagination->links() }}
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
