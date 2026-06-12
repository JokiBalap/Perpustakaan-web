<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perpustakaan Digital UNU NTB - Jendela Akademik & Heritage</title>
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="Portal perpustakaan resmi Universitas Nahdlatul Ulama Nusa Tenggara Barat. Cari koleksi buku akademik, karya sastra, jurnal, dan gunakan kurator cerdas berbasis AI.">
  <meta name="keywords" content="Perpustakaan UNU NTB, Universitas Nahdlatul Ulama, Mataram, Nusa Tenggara Barat, Peminjaman Buku, AI Curator, Perpustakaan Digital">
  <meta name="author" content="Universitas Nahdlatul Ulama NTB">

  <!-- Open Graph / Facebook / WhatsApp -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:title" content="Perpustakaan Digital UNU NTB">
  <meta property="og:description" content="Portal perpustakaan resmi Universitas Nahdlatul Ulama Nusa Tenggara Barat. Cari koleksi buku akademik, karya sastra, jurnal, dan gunakan kurator cerdas berbasis AI.">
  <meta property="og:image" content="{{ request()->getSchemeAndHttpHost() }}/assets/hero.png">
  <meta property="og:image:alt" content="Perpustakaan UNU NTB Hero Banner">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ url()->current() }}">
  <meta property="twitter:title" content="Perpustakaan Digital UNU NTB">
  <meta property="twitter:description" content="Portal perpustakaan resmi Universitas Nahdlatul Ulama Nusa Tenggara Barat. Cari koleksi buku akademik, karya sastra, jurnal, dan gunakan kurator cerdas berbasis AI.">
  <meta property="twitter:image" content="{{ request()->getSchemeAndHttpHost() }}/assets/hero.png">
  
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
