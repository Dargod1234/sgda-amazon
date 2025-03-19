@extends('layouts.admin')

@section('content')
<hr>
@can('users.index')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                @php $counter = 0; @endphp
                @foreach($users as $user)
                @php $counter++; @endphp
                @endforeach
                <h3>{{$counter}}</h3>
                <p>Usuarios registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <a href="{{url('/admin/users')}}" class="small-box-footer">
                Más información <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>
@endcan

@endsection

