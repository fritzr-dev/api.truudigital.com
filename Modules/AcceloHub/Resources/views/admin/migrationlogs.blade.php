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
        Migration Logs
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Migration Logs</li>
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

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Migration Logs <span class="label label-info">{{ $pagination->total() }}</span></h3>

              <div class="box-tools">
                <form method="get" action="{{ url('admin/accelohub/logs') }}">
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
                  <th>Module</th>
                  <th>Date Migrated</th>
                  <th>Success</th>
                  <th>Error</th>
                </tr>
                @forelse($records as $record)
                <tr>
                  <td>{{ $record->module }}</td>
                  <td title="{{ $record->created_at->diffForHumans() }}">{{ $record->created_at }}</td>
                  <td>
                    {{-- <span class="label label-success">{{ $record->count_success }}</span> --}}
                    <button type="button" class="btn btn-info" style="cursor: text">{{ $record->count_success }}</button> 
                  </td>
                  <td>
                    {{-- <span class="label label-warning">{{ $record->count_error }}</span> --}}
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modal-warning-{{ $record->id }}" @if ($record->count_error < 1) style="cursor: text" @endforelse>{{ $record->count_error }}</button>    
                    @if ($record->count_error > 1)                    
                      <div class="modal modal-warning fade" id="modal-warning-{{ $record->id }}">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title"><i class="icon fa fa-warning"></i> API Error</h4>
                            </div>
                            <div class="modal-body">
                              {!! $record->error_list !!}
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
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
