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
