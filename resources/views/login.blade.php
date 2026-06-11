<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Perpustakaan UNU NTB</title>
  
  <!-- CSRF Token for Axios/Fetch -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Vite Assets -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-midnight min-h-screen flex items-center justify-center p-6">

  <!-- Mount Vue Login App -->
  <div id="login-app" 
       data-errors="{{ json_encode($errors->all()) }}" 
       data-old-email="{{ old('email') }}">
  </div>

</body>
</html>
