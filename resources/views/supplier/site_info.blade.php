@extends('Admin.app')

@section('content')

    <!--Start Main content container-->
    <div class="main_content_container">
        <div class="main_container  main_menu_open">
            <!--Start system bath-->
            <div class="home_pass hidden-xs">
                <ul>
                    <li class="bring_right"><span class="glyphicon glyphicon-home "></span></li>
                    <li class="bring_right"><a href="">تعديل بيانات الموقع</a></li>
                </ul>
            </div>
            <!--/End system bath-->
            <div class="page_content">
                <h1 class="heading_title">تعديل بيانات الموقع</h1>
                @if(session()->has('info_updated'))
                    <div class="alert alert-info">
                        <strong>{{ session()->get('info_updated') }}</strong>
                    </div>
                @endif
                <div class="form">
                    <form class="form-horizontal" action="{{route('admin.information.update',$info->id)}}" method="post">
                        @method('PUT')
                        @csrf
                        <div class="form-group">
                            <label for="input0" class="col-sm-2 control-label bring_right left_text">عنوان الموقع</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="input0" placeholder="عنوان الموقع" name="title" value="{{$info->title}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="input1" class="col-sm-2 control-label bring_right left_text">وصف الموقع</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" rows="3" id="input1" placeholder="وصف الموقع" name="description">{{$info->description}}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="input2" class="col-sm-2 control-label bring_right left_text">البريد الالكتروني</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="input2" placeholder="البريد الالكتروني" name="email" value="{{$info->email}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12 left_text">
                                <button type="submit" class="btn btn-danger">حفظ البيانات</button>
                                <button type="reset" class="btn btn-default">مسح الحقول</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--/End Main content container-->


@endsection
