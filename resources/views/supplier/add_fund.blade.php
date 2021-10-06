@extends('supplier.app')

@section('content')

    <div class="main_content_container">
        <div class="main_container  main_menu_open">
            <!--Start system bath-->
            <div class="home_pass hidden-xs">
                <ul>
                    <li class="bring_right"><span class="glyphicon glyphicon-home "></span></li>
                    <li class="bring_right"><a href="{{route('dashboard')}}">الصفحة الرئيسية للوحة تحكم الموقع</a></li>
                    <li class="bring_right"><a href="{{route('search_driver')}}">البحث عن السائقين</a></li>
                    <li class="bring_right">شحن الرصيد</li>
                </ul>
            </div>

            <div class="page_content">

                <h1 class="heading_title">شحن رصيد جديد</h1>
                <!--/End system bath-->
                <div class="admin_index">
                    <!--Start Site Main Options and Data-->
                    <div class="panel panel-default view_users">

                        <div class="card mb-3" style="padding: 50px">
                            <div class="row no-gutters">
                                <div class="col-md-4">
                                    <img src="{{asset('uploads/'.$driver->profile_picture)}}" class="img-thumbnail img-rounded bring_right" alt="...">
                                </div>
                                <div class="col-md-8" style="padding: 100px;">
                                    <div class="card-body" style="font-size: 20px">
                                        <p class="card-title"><span>اسم السائق : </span> {{$driver->full_name}}</p>
                                        <p class="card-text"><span>رقم جوال السائق : </span>  {{$driver->phone_number}}</p>
                                        <p class="card-text"><span>رصيد محفظة السائق : </span>  {{$driver->wallet}}  دينار  </p>
{{--                                        <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>--}}
                                    </div><br><hr>

                                    @if(session()->has('funded'))
                                        <div class="alert alert-success">
                                            <strong>{{ session()->get('funded') }}</strong>
                                        </div>
                                    @endif

                                    @if(session()->has('empty'))
                                        <div class="alert alert-danger">
                                            <strong>{{ session()->get('empty') }}</strong>
                                        </div>
                                    @endif

                                    <form class="form-horizontal" action="{{route('add_fund',$driver->id)}}" method="post">
                                        @csrf
                                        <div class="form-group">
                                            <label for="input0" class="col-sm-2 control-label bring_right left_text">المبلغ</label>
                                            @error('amount')
                                            <span class="text-danger">{{$message}}</span>
                                            @enderror
                                            <div class="col-sm-10">
                                                <select class="form-control" name="amount" required>
                                                    <option disabled hidden selected>-- اختيار المبلغ --</option>
                                                    <option value="5">5 دينار</option>
                                                    <option value="10">10 دينار</option>
                                                    <option value="15">15 دينار</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group" align="center">
                                            <button style="width: 50%" type="submit" class="btn btn-success">شحن</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!--End Site Main Options and Data-->
                </div>
            </div>
        </div>
        <!--/End Main content container-->


    </div>

@endsection
