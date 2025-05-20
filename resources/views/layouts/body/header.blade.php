
@php
  $social = DB::table('sociallinks')->first();  

@endphp

 <header id="header" class="fixed-top">
    <div class="container d-flex align-items-center">

      <h1 class="logo mr-auto"><a href="index.html"><span>Te</span>wos</a></h1>
      <!-- Uncomment below if you prefer to use an image logo -->
      <!-- <a href="index.html" class="logo mr-auto"><img src="assets/img/logo.png" alt="" class="img-fluid"></a>-->

      <nav class="nav-menu d-none d-lg-block">
        <ul>
          <li><a href="{{route('login')}}">Login</a></li>
        </ul>
      </nav><!-- .nav-menu -->

      <div class="header-social-links">
        <a href="{{$social->tw}}" class="twitter"><i class="icofont-twitter"></i></a>
        <a href="{{$social->fb}}" class="facebook"><i class="icofont-facebook"></i></a>
        <a href="{{$social->ins}}" class="instagram"><i class="icofont-instagram"></i></a>
        <a href="{{$social->ln}}" class="linkedin"><i class="icofont-linkedin"></i></i></a>
      </div>

    </div>
  </header>