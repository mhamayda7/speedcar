@extends('Admin.app')

@section('content')


    <!--Start Main content container-->
    <div class="main_content_container">
        <div class="main_container  main_menu_open">
            <!--Start system bath-->
            <div class="home_pass hidden-xs">
                <ul>
                    <li class="bring_right"><span class="glyphicon glyphicon-home "></span></li>
                    <li class="bring_right"><a href="">إدارة المستخدمين</a></li>
                    <li class="bring_right"><a href="">عرض كل المستخدمين</a></li>
                </ul>
            </div>
            <!--/End system bath-->


            <div class="page_content">
                <h1 class="heading_title">عرض كل المستخدمين</h1>

                @if(session()->has('user_deleted'))
                    <div class="alert alert-danger">
                        <strong>{{ session()->get('user_deleted') }}</strong>
                    </div>
                @endif
                <div class="wrap">
                    <table class="table table-bordered">
                        <tr>
                            <td>#</td>
                            <td>صورة المستخدم</td>
                            <td>اسم المستخدم</td>
                            <td>الإيميل</td>
                            <td>رقم الهاتف</td>
                            <td>التحكم</td>
                        </tr>
                        @foreach($users as $key => $user)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td><img src="{{asset('usersImages/'.$user->avatar)}}" class="img-rounded user_thumb" style="width: 50px;height: 50px"></td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}
                                </td>
                                <td align="center">{{isset($user->phone) ? $user->phone : 'لا يوجد رقم هاتف' }}
                                </td>
                                <td>

                                    <a class="glyphicon glyphicon-remove" data-effect="effect-scale"
                                       data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-toggle="modal"
                                       href="#modaldemo9" title="حذف"></a>

                                </td>
                            </tr>
                        @endforeach
                    </table>

                    <nav class="text-center">
                  {{$users->links()}}
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!--/End Main content container-->
    <div class="modal" id="modaldemo9">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">حذف الإعلان</h6>
                </div>
                <form action="{{route('users.destroy','error')}}" method="post">
                    {{method_field('delete')}}
                    {{csrf_field()}}
                    <div class="modal-body">
                        <p>هل أنت متأكد من حذف هذا المستخدم ؟</p><br>
                        <input type="hidden" name="id" id="id" value="">
                        <input class="form-control" name="name" id="name" type="text" readonly>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </div>
            </div>
            </form>
        </div>
    </div>

    <script src="{{asset('assets/bundles/libscripts.bundle.js')}}"></script>

    <script>
        $('#modaldemo9').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var id = button.data('id')
            var name = button.data('name')
            var modal = $(this)
            modal.find('.modal-body #id').val(id);
            modal.find('.modal-body #name').val(name);
        })
    </script>

@endsection
