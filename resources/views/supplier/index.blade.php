@extends('supplier.app')

@section('content')

    <div class="main_content_container">
        <div class="main_container  main_menu_open">
            <!--Start system bath-->
            <div class="home_pass hidden-xs">
                <ul>
                    <li class="bring_right"><span class="glyphicon glyphicon-home "></span></li>
                    <li class="bring_right"><a href="">الصفحة الرئيسية للوحة تحكم الموقع</a></li>
                </ul>
            </div>
            <!--/End system bath-->
            <div class="page_content">
                <div class="page_content">
                    <div class="quick_links text-center">
                        <h1 class="heading_title">الوصول السريع</h1>
{{--                        <a href="#" target="_blank" style="background-color: #c0392b">--}}
{{--                            <h4>استعراض الموقع</h4>--}}
{{--                        </a>--}}
                        <a href="{{route('search_driver')}}" style="background-color: #2980b9">
                            <h4>شحن رصيد سائقين</h4>
                        </a>

                        <a href="{{route('billing')}}" style="background-color: #8e44ad">
                            <h4>عمليات الشحن</h4>
                        </a>

                    </div>
                    <div class="home_statics text-center">
                        <h1 class="heading_title">احصائيات عامة </h1>

                        <div style="background-color: #34495e">
                            <span class="bring_left glyphicon glyphicon-transfer"></span>

                            <h3>رصيد المحفظة</h3>

                            <p class="h4">{{$supplier->wallet}} دينار</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!--/End Main content container-->


    </div>

@endsection
