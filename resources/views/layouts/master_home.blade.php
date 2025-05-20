<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Company Bootstrap Template - Index</title>
  <meta content="" name="descriptison">
  <meta content="" name="keywords">

  <!-- Favicons -->
  {{-- @vite handles assets, add favicon in public directory or via Vite if needed --}}
  <link rel="icon" href="{{ asset('frontend/assets/img/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('frontend/assets/img/apple-touch-icon.png') }}">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Roboto:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vite CSS Files -->
  @vite([
    'resources/css/app.scss',
    'resources/vendor/bootstrap/css/bootstrap.min.css',
    'resources/vendor/icofont/icofont.min.css',
    'resources/vendor/boxicons/css/boxicons.min.css',
    'resources/vendor/animate.css/animate.min.css',
    'resources/vendor/venobox/venobox.css',
    'resources/vendor/owl.carousel/assets/owl.carousel.min.css',
    'resources/vendor/aos/aos.css',
    'resources/vendor/remixicon/remixicon.css'
  ])

  <!-- =======================================================
  * Template Name: Company - v2.1.0
  * Template URL: https://bootstrapmade.com/company-free-html-bootstrap-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

 
 <!-- ======= Header ======= -->
  @include('layouts.body.header')
 <!-- End Header -->

  <!-- ======= Hero Section ======= -->
 
 <!-- End Hero -->

  <main id="main">

    @yield('home_content')

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  @include('layouts.body.footer')
  <!-- End Footer -->

  <!-- Vite JS Files -->
  @vite([
    'resources/js/app.js',
    'resources/vendor/jquery/jquery.min.js',
    'resources/vendor/bootstrap/js/bootstrap.bundle.min.js',
    'resources/vendor/jquery.easing/jquery.easing.min.js',
    'resources/vendor/php-email-form/validate.js',
    'resources/vendor/jquery-sticky/jquery.sticky.js',
    'resources/vendor/isotope-layout/isotope.pkgd.min.js',
    'resources/vendor/venobox/venobox.min.js',
    'resources/vendor/waypoints/jquery.waypoints.min.js',
    'resources/vendor/owl.carousel/owl.carousel.min.js',
    'resources/vendor/aos/aos.js',
    'resources/js/main.js'
  ])

</body>

</html>