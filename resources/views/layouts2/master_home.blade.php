<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Company Bootstrap Template - Index</title>
  <meta content="" name="descriptison">
  <meta content="" name="keywords">

  <!-- Favicons -->
  @vite(['resources/frontend/assets/img/favicon.png'])
  @vite(['resources/frontend/assets/img/apple-touch-icon.png'])

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Roboto:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  @vite([
    'resources/frontend/assets/vendor/bootstrap/css/bootstrap.min.css',
    'resources/frontend/assets/vendor/icofont/icofont.min.css',
    'resources/frontend/assets/vendor/boxicons/css/boxicons.min.css',
    'resources/frontend/assets/vendor/animate.css/animate.min.css',
    'resources/frontend/assets/vendor/venobox/venobox.css',
    'resources/frontend/assets/vendor/owl.carousel/assets/owl.carousel.min.css',
    'resources/frontend/assets/vendor/aos/aos.css',
    'resources/frontend/assets/vendor/remixicon/remixicon.css',
    'resources/frontend/assets/css/style.css'
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

  <!-- Vendor JS Files -->
  @vite([
    'resources/frontend/assets/vendor/jquery/jquery.min.js',
    'resources/frontend/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    'resources/frontend/assets/vendor/jquery.easing/jquery.easing.min.js',
    'resources/frontend/assets/vendor/php-email-form/validate.js',
    'resources/frontend/assets/vendor/jquery-sticky/jquery.sticky.js',
    'resources/frontend/assets/vendor/isotope-layout/isotope.pkgd.min.js',
    'resources/frontend/assets/vendor/venobox/venobox.min.js',
    'resources/frontend/assets/vendor/waypoints/jquery.waypoints.min.js',
    'resources/frontend/assets/vendor/owl.carousel/owl.carousel.min.js',
    'resources/frontend/assets/vendor/aos/aos.js',
    'resources/frontend/assets/js/main.js'
  ])

</body>

</html>
