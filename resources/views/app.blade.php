<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perpustakaan Digital UNU NTB - Jendela Akademik & Heritage</title>
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="Layanan Perpustakaan Digital resmi Universitas Nahdlatul Ulama Nusa Tenggara Barat (UNU NTB). Akses ribuan katalog buku, karya ilmiah, riset akademik, dan asisten pintar rekomendasi AI.">
  <meta name="keywords" content="Perpustakaan UNU NTB, Universitas Nahdlatul Ulama, Mataram, Nusa Tenggara Barat, Peminjaman Buku, AI Curator, Perpustakaan Digital">
  <meta name="author" content="Universitas Nahdlatul Ulama NTB">

  <!-- Open Graph / Facebook / WhatsApp -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ preg_replace('/^http:/i', 'https:', url()->current()) }}">
  <meta property="og:title" content="Perpustakaan Digital UNU NTB">
  <meta property="og:description" content="Layanan Perpustakaan Digital resmi Universitas Nahdlatul Ulama Nusa Tenggara Barat (UNU NTB). Akses ribuan katalog buku, karya ilmiah, riset akademik, dan asisten pintar rekomendasi AI.">
  <meta property="og:image" content="{{ preg_replace('/^http:/i', 'https:', request()->getSchemeAndHttpHost()) }}/assets/logo.png">
  <meta property="og:image:secure_url" content="{{ preg_replace('/^http:/i', 'https:', request()->getSchemeAndHttpHost()) }}/assets/logo.png">
  <meta property="og:image:alt" content="Logo Perpustakaan UNU NTB">
  <meta property="og:image:type" content="image/png">
  <meta property="og:image:width" content="512">
  <meta property="og:image:height" content="512">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ preg_replace('/^http:/i', 'https:', url()->current()) }}">
  <meta property="twitter:title" content="Perpustakaan Digital UNU NTB">
  <meta property="twitter:description" content="Layanan Perpustakaan Digital resmi Universitas Nahdlatul Ulama Nusa Tenggara Barat (UNU NTB). Akses ribuan katalog buku, karya ilmiah, riset akademik, dan asisten pintar rekomendasi AI.">
  <meta property="twitter:image" content="{{ preg_replace('/^http:/i', 'https:', request()->getSchemeAndHttpHost()) }}/assets/logo.png">
  
  <!-- CSRF Token for Secure AJAX requests -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Vite Assets -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-parchment min-h-screen">

  <!-- Mount Vue Library App -->
  <div id="library-app"></div>

</body>
</html>
