<html dir="rtl" lang="ar">

<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800&display=swap" rel="stylesheet">



<style>


    body{
        font-family: 'Tajawal', sans-serif;
    }
    .get-in-touch {
        max-width: 800px;
        margin: 50px auto;
        position: relative;

    }
    .get-in-touch .title {
        /*background: url("logo (2).jpeg");*/
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 3.2em;
        line-height: 48px;
        padding-bottom: 48px;
        color: #e6ef00;
        background: #e6ef00;
        background: -moz-linear-gradient(left, #e6ef00 0%, #212123 100%) !important;
        background: -webkit-linear-gradient(left, #e6ef00 0%, #212123 100%) !important;
        background: linear-gradient(to right, #e6ef00 0%, #212123 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
    }


    .contact-form .form-field {
        position: relative;
        margin: 32px 0;
    }
    .contact-form .input-text {
        display: block;
        width: 100%;
        height: 36px;
        border-width: 0 0 2px 0;
        border-color: #e6ef00;
        font-size: 18px;
        line-height: 26px;
        font-weight: 400;
    }
    .contact-form .input-text:focus {
        outline: none;
    }
    .contact-form .input-text:focus + .label,
    .contact-form .input-text.not-empty + .label {
        -webkit-transform: translateY(-24px);
        transform: translateY(-24px);
    }
    .contact-form .label {
        position: absolute;
        bottom: 11px;
        font-size: 18px;
        line-height: 26px;
        font-weight: 400;
        color: #000000;
        cursor: text;
        transition: -webkit-transform .2s ease-in-out;
        transition: transform .2s ease-in-out;
        transition: transform .2s ease-in-out,
        -webkit-transform .2s ease-in-out;
        padding-right: 20px;
        top: -86%;
        right: -0px;
        bottom: 11px;
        font-size: 18px;
        line-height: 26px;
        font-weight: 400;
        color: #000000;
        cursor: text;
        transition: -webkit-transform .2s ease-in-out;
        transition: transform .2s ease-in-out;
        transition: transform .2s ease-in-out, -webkit-transform .2s ease-in-out;

    }
    .contact-form .submit-btn {
        display: inline-block;
        background-color: #000;
        background-image: linear-gradient(125deg, #000000, #151512);
        color: #e4ff00;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 18px;
        padding: 8px 16px;
        border-bottom: 15px;
        border-color: #e4ff00;
        border-radius: 20px;
        width:200px;
        cursor: pointer;
    }


</style>
<!------ Include the above in your HEAD tag ---------->
<title> تسجيل سائق جديد</title>
<body class="body">

    <img src="https://mrkzgulfup.com/uploads/163137403376041.jpeg" style="width: 100%;">

<section class="get-in-touch">
    <h1 class="title">تسجيل سائق جديد</h1>
    @if(session()->has('captain_registered'))
        <div align="center" class="alert alert-success">
            <strong>{{ session()->get('captain_registered') }}</strong>
        </div>
    @endif
    <form class="contact-form row" action="{{route('register_captain')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-field col-lg-6">
            <input id="full_name" name="full_name" class="input-text js-input" type="text" required>
            <label class="label" for="full_name">الإسم كاملا</label>
            @error('full_name')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>
        <div class="form-field col-lg-6 ">
            <select class="input-text js-input" name="gender" id="gender">
                <option disabled selected hidden>--  إختيار الجنس  --</option>
                <option value="1">ذكر</option>
                <option value="2">أنثى</option>
            </select>
            <label class="label" for="gender">الجنس</label>
        </div>
        <div class="form-field col-lg-6 ">
            <input id="phone_number" name="phone_number" class="input-text js-input" type="number" required>
            <label class="label" for="phone_number">رقم الجوال</label>
            @error('phone_number')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>
        <div class="form-field col-lg-6 ">
            <input id="email" name="email" class="input-text js-input" type="text" required>
            <label class="label" for="email">الإيميل</label>
        </div>

        <div class="form-field col-lg-6 ">
            <input id="password" name="password" class="input-text js-input" type="password" required>
            <label class="label" for="password">كلمة المرور</label>
        </div>
        <div class="form-field col-lg-6 ">
            <input id="date_of_birth" name="date_of_birth" class="input-text js-input" type="date" required>
            <label class="label" for="date_of_birth">تاريخ الميلاد</label>
        </div>
        <div class="form-field col-lg-6 ">
            <input id="vehicle_type" name="vehicle_type" class="input-text js-input" type="text" required>
            <label class="label" for="vehicle_type">نوع السيارة</label>
        </div>

        <div class="form-field col-lg-6 ">
            <input id="vehicle_model" name="vehicle_model" class="input-text js-input" type="text" required>
            <label class="label" for="vehicle_model">موديل السيارة</label>
        </div>
        <div class="form-field col-lg-12 ">
            <input id="licence_number" name="licence_number" class="input-text js-input" type="number" required>
            <label class="label" for="licence_number">رقم الرخصة</label>
        </div>

        <div class="form-field col-lg-6">
            <input id="profile_picture" name="profile_picture" class="input-text js-input" type="file" required>
            <label class="label" for="profile_picture">صورة شخصية</label>
        </div>

        <div class="form-field col-lg-6">
            <input id="id_proof" name="id_proof" class="input-text js-input" type="file" required>
            <label class="label" for="id_proof">صورة الرخصة</label>
        </div>
<br>
        <div class="form-field col-lg-6">
            <input id="vehicle_image" name="vehicle_image" class="input-text js-input" type="file" required>
            <label class="label" for="vehicle_image">صورة امامية للمركبة مع اللوحة</label>
        </div>

        <div class="form-field col-lg-6">
            <input id="vehicle_licence" name="vehicle_licence" class="input-text js-input" type="file" required>
            <label class="label" for="vehicle_licence">صورة رخصة المركبة</label>
        </div>

        <div class="form-field col-lg-12">
            <input class="submit-btn" type="submit" value="تسجيل">
        </div>
    </form>
</section>
<script>
    $("#alert-success").fadeTo(2000, 500).slideUp(500, function(){
        $("#alert-success").slideUp(500);
    });
</script>
</body>
</html>
