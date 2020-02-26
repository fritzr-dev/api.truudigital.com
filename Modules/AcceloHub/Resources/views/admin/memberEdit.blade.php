@extends('admin::layouts.master')
@section('page_title', env('APP_NAME') . ' | Dashboard')
@section('body_class', 'hold-transition skin-blue sidebar-mini')
@section('styles')
@endsection
@section('scripts')
@endsection

@section('body')

  <div class="content-wrapper">
    
    <section class="content-header">
      <h1>
        Edit Member <a class="btn btn-danger" href="{{ url('admin/accelohub/members') }}">Cancel</a>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ url('admin/accelohub/members') }}">Accelo Members</a></li>
        <li class="active">Edit Member</li>
      </ol>
    </section>

    <section class="content">


      @if ($errors->any())

        <ul class="callout callout-danger"> 
      @foreach ( $errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
        </ul>
      @endif

      <div class="row">
        <div class="col-lg-6">
          <form action="{{ url('admin/accelohub/member/update') }}" method="post">
            <input type="hidden" name="id" value="{{ $entry->id }}">
            @csrf

            <div class="box box-danger">
              <div class="box-header with-border">
                <h3 class="box-title">Edit Member</h3>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-sm-6 col-xs-12">
                    <label>Accelo Members</label>
                    <select name="accello_id" class="input-lg form-control">
                      <option value="">Accelo Members</option>
                      @foreach($members_a as $member)
                      <option value="{{$member->id}}" {{ old('accello_id', $entry->accelo_member_id) == $member->id ? 'selected="selected"' : '' }}>{{$member->id}} - {{$member->name}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-sm-6 col-xs-12">
                    <label>Hubstaff Members</label>
                    <select name="hubstaff_id" class="input-lg form-control">
                      <option value="0">Hubstaff Members</option>
                      @foreach($members_h as $member)
                      <option value="{{$member->id}}" {{ old('hubstaff_id', $entry->hubstaff_member_id) == $member->id ? 'selected="selected"' : '' }}>{{$member->id}} - {{$member->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div><!-- /.box-body -->
              <div class="box-footer">
                <a href="{{ url('admin/accelohub/members') }}" class="btn btn-default">Cancel</a>
                <button type="submit" class="btn btn-info pull-right">Update</button>
              </div>

            </div>
          </form>
        </div>
      </div>

    </section>
    
  </div>

<!-- ./wrapper -->
@endsection
