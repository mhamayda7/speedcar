@extends('supplier.app')

@section('content')

    <div class="main_content_container">
        <div class="main_container  main_menu_open">
            <!--Start system bath-->
            <div class="home_pass hidden-xs">
                <ul>
                    <li class="bring_right"><span class="glyphicon glyphicon-home "></span></li>
                    <li class="bring_right"><a href="{{route('dashboard')}}">الصفحة الرئيسية للوحة تحكم الموقع</a></li>
                    <li class="bring_right">بحث السائقين</li>
                </ul>
            </div>

            <div class="page_content">

            <h1 class="heading_title">البحث عن السائقين</h1>
            <!--/End system bath-->
            <div class="form">
                @if(session()->has('not_found'))
                    <div class="alert alert-danger">
                        <strong>{{ session()->get('not_found') }}</strong>
                    </div>
                @endif
                <form class="form-horizontal" action="{{route('get_driver')}}" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="input0" class="col-sm-2 control-label bring_right left_text">رقم جوال السائق</label>

                        @error('phone')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="phone" id="input0" placeholder="رقم الجوال">
                        </div>
                    </div>
                    <div class="form-group" align="center">
                            <button style="width: 50%" type="submit" class="btn btn-danger">بحث</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
        <!--/End Main content container-->


    </div>

@endsection
