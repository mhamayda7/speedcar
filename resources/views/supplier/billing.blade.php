@extends('supplier.app')

@section('content')


    <!--Start Main content container-->
    <div class="main_content_container">
        <div class="main_container  main_menu_open">
            <!--Start system bath-->
            <div class="home_pass hidden-xs">
                <ul>
                    <li class="bring_right"><span class="glyphicon glyphicon-home "></span></li>
                    <li class="bring_right"><a href="">إدارة عمليات الشحن</a></li>
                    <li class="bring_right"><a href="">عرض كل المعاملات</a></li>
                </ul>
            </div>
            <!--/End system bath-->


            <div class="page_content">
                <h1 class="heading_title">عرض كل المعاملات</h1>


                <div class="wrap">
                    <table class="table table-bordered">
                        <tr>
                            <td>#</td>
                            <td>المزود</td>
                            <td>السائق</td>
                            <td>المبلغ</td>
                            <td>تاريخ المعاملة</td>
                        </tr>
                        @foreach($transactions as $key => $transaction)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td align="center">{{$transaction->supplier_name}}</td>
                                <td align="center">{{$transaction->driver_name}}</td>
                                <td align="center">{{$transaction->amount}} دينار  </td>
                                <td>{{$transaction->created_at}}</td>
                            </tr>
                        @endforeach
                    </table>

                    <nav class="text-center">
                        {{$transactions->links()}}
                    </nav>
                </div>
            </div>
        </div>
    </div>

@endsection
