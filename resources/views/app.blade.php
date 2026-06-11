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
  
  <!-- Styling -->
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  
  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="nav-container">
      <div class="brand">
        <div class="brand-logo"><img src="{{ asset('assets/logo.png') }}" alt="Logo UNU NTB" class="logo-animated"></div>
        <div class="brand-name">
          <h1>Perpustakaan UNU NTB</h1>
          <span>Lentera Peradaban & Akademik</span>
        </div>
      </div>
      
      <nav id="main-nav">
        <button class="nav-link active" data-section="home" id="nav-btn-home">
          <i class="fa-solid fa-house"></i> Beranda
        </button>
        <button class="nav-link" data-section="browse" id="nav-btn-browse">
          <i class="fa-solid fa-magnifying-glass"></i> Cari Buku
        </button>
        <button class="nav-link" data-section="curator" id="nav-btn-curator">
          <i class="fa-solid fa-wand-magic-sparkles"></i> AI Curator
        </button>
        <button class="nav-link" data-section="dashboard" id="nav-btn-dashboard">
          <i class="fa-solid fa-user-check"></i> Dashboard
        </button>
        <button class="nav-link" data-section="database" id="nav-btn-database">
          <i class="fa-solid fa-database"></i> Katalog & Log
        </button>
        @if(auth()->user()->role === 'Pustakawan')
        <button class="nav-link" data-section="admin-loans" id="nav-btn-admin-loans">
          <i class="fa-solid fa-book-bookmark"></i> Data Peminjam
        </button>
        <button class="nav-link" data-section="admin-students" id="nav-btn-admin-students">
          <i class="fa-solid fa-users"></i> Data Mahasiswa
        </button>
        @endif
        <button class="nav-link" data-section="notifications" id="nav-btn-notifications">
          <i class="fa-solid fa-bell"></i> Notifikasi
        </button>
      </nav>

      <div class="user-menu">
        <div class="user-badge" id="header-user-badge" style="cursor: default;">
          <div class="user-avatar" id="header-user-avatar" onclick="navigateToDashboard()" style="cursor: pointer;">FR</div>
          <div class="user-details" onclick="navigateToDashboard()" style="cursor: pointer;">
            <span class="user-name" id="header-user-name">Fadhil R.</span>
            <span class="user-role" id="header-user-role">Mahasiswa</span>
          </div>
          <!-- Logout Button -->
          <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="action-btn-sm btn-danger" style="margin-left: 0.8rem; padding: 0.3rem 0.5rem; font-size: 0.8rem; border-radius: var(--radius-sm);" title="Keluar">
            <i class="fa-solid fa-right-from-bracket"></i>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Hidden Logout Form -->
  <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    @csrf
  </form>

  <!-- MAIN VIEWPORT -->
  <main>
    
    <!-- 1. HOME SECTION -->
    <section id="home-section" class="page-section active">
      <div class="hero-banner">
        <div class="hero-content">
          <h2 class="hero-title">Temukan Inspirasi & Pengetahuan di Perpustakaan UNU NTB</h2>
          <p class="hero-subtitle">Akses katalog literatur ilmiah, khazanah budaya, dan teknologi masa kini. Dilengkapi dengan asisten rekomendasi AI pribadi Anda.</p>
          <div class="hero-search-wrapper">
            <input type="text" placeholder="Cari buku berdasarkan judul, penulis, atau genre..." id="hero-search-input">
            <button onclick="handleHeroSearch()" id="hero-search-btn">
              <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>
          </div>
        </div>
      </div>

      <div class="section-header">
        <h2>Buku Terpopuler</h2>
        <span class="view-all-link" onclick="setViewTab('browse')">Lihat Semua Buku</span>
      </div>
      <div class="book-grid" id="popular-books-grid">
        <!-- Rendered dynamically -->
      </div>

      <div class="section-header">
        <h2>Koleksi Terbaru</h2>
        <span class="view-all-link" onclick="setViewTab('browse')">Lihat Semua Buku</span>
      </div>
      <div class="book-grid" id="recent-books-grid">
        <!-- Rendered dynamically -->
      </div>
    </section>

    <!-- 2. BROWSE CATALOG SECTION -->
    <section id="browse-section" class="page-section">
      <div class="section-header">
        <h2>Katalog Perpustakaan</h2>
      </div>
      
      <div class="browse-container">
        <!-- Filters Sidebar -->
        <aside class="filter-sidebar">
          <div class="filter-group">
            <h3 class="filter-title">Pencarian</h3>
            <div class="search-input-group">
              <input type="text" placeholder="Masukkan judul/penulis..." id="catalog-search-input">
              <span class="search-icon-inside"><i class="fa-solid fa-magnifying-glass"></i></span>
            </div>
          </div>
          
          <div class="filter-group">
            <h3 class="filter-title">Kategori / Genre</h3>
            <div id="genre-checkbox-filters">
              <!-- Checkboxes rendered dynamically -->
            </div>
          </div>

          <div class="filter-group">
            <h3 class="filter-title">Ketersediaan</h3>
            <label class="filter-checkbox-label">
              <input type="checkbox" id="filter-available-only">
                Tersedia untuk Dipinjam
            </label>
          </div>

          <div class="filter-group">
            <h3 class="filter-title">Urutkan</h3>
            <select class="filter-select" id="filter-sort-select">
              <option value="popularity">Popularitas Tertinggi</option>
              <option value="title-asc">Judul A - Z</option>
              <option value="title-desc">Judul Z - A</option>
              <option value="year-desc">Tahun Baru - Lama</option>
            </select>
          </div>
        </aside>

        <!-- Catalog Results -->
        <div>
          <div style="margin-bottom: 1.2rem; color: var(--color-charcoal); opacity: 0.8; font-size: 0.95rem;">
            Ditemukan <strong id="catalog-results-count">0</strong> buku di katalog.
          </div>
          <div class="book-grid" id="catalog-books-grid">
            <!-- Rendered dynamically -->
          </div>
        </div>
      </div>
    </section>

    <!-- 3. AI CURATOR TOOL SECTION -->
    <section id="curator-section" class="page-section">
      <div class="section-header">
        <h2>AI Curator Tool</h2>
      </div>

      <div class="curator-layout">
        <!-- User Preference Intake -->
        <div class="curator-intake">
          <div class="curator-intro">
            <h3>Asisten Rekomendasi Pintar</h3>
            <p>Beritahu AI apa minat bacaan Anda saat ini atau bagikan topik yang sedang Anda pelajari, dan kami akan mencarikan buku yang paling sesuai dari katalog perpustakaan UNU NTB.</p>
          </div>

          <div class="curator-form">
            <div class="form-group">
              <label class="form-label" for="curator-genre">Pilih Genre Favorit</label>
              <select id="curator-genre">
                <option value="any">Semua Kategori</option>
                <option value="Historical Fiction">Historical Fiction (Sejarah)</option>
                <option value="Drama">Drama / Fiksi Umum</option>
                <option value="Modern Literature">Modern Literature</option>
                <option value="Philosophy">Filosofi & Sosial</option>
                <option value="Science">Fisika & Sains Alam</option>
                <option value="Technology">Komputer & Teknologi</option>
                <option value="Magical Realism">Realisme Magis</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Bagaimana gaya buku yang Anda inginkan?</label>
              <select id="curator-style">
                <option value="academic">Akademik & Pemikiran Kritis</option>
                <option value="light">Inspiratif & Mudah Dipahami</option>
                <option value="narrative">Naratif Mendalam & Sastra</option>
              </select>
            </div>

            <div class="form-group input-tags-wrapper">
              <label class="form-label" for="curator-keywords">Kata Kunci / Topik yang Ingin Dipelajari</label>
              <input type="text" id="curator-keywords" placeholder="Misal: algoritma, perjuangan, aktivis, filsafat..." value="">
              <span class="tags-hint">Gunakan tanda koma (,) untuk memisahkan kata kunci.</span>
            </div>

            <button class="curator-submit-btn" id="curator-recommend-btn" onclick="generateAIRecommendations()">
              <i class="fa-solid fa-wand-magic-sparkles"></i> Dapatkan Rekomendasi
            </button>
          </div>
        </div>

        <!-- AI Recommendation Output -->
        <div class="curator-results-panel" id="curator-results-panel">
          <div class="curator-placeholder" id="curator-placeholder">
            <div class="curator-placeholder-icon"><i class="fa-solid fa-brain"></i></div>
            <h3>Menunggu Minat Anda</h3>
            <p>Isi formulasi minat di sebelah kiri untuk melihat rekomendasi personal dari kurator AI kami.</p>
          </div>
          <div id="curator-output-content" style="display: none;">
            <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem;">
              <i class="fa-solid fa-circle-check" style="color: var(--color-teal);"></i> Rekomendasi Kurator Untuk Anda:
            </h3>
            <div id="recommendations-list">
              <!-- Rendered dynamically -->
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. MEMBER DASHBOARD SECTION -->
    <section id="dashboard-section" class="page-section">
      <div class="section-header">
        <h2 id="dashboard-section-title">Dashboard Anggota</h2>
      </div>

      <div class="dashboard-grid">
        <!-- Profile Column -->
        <div class="profile-card">
          <div class="profile-avatar-large" id="profile-avatar">FR</div>
          <h3 class="profile-name" id="profile-name">Fadhil Rahmatullah</h3>
          <p style="color: var(--color-teal-dark); font-weight: bold; font-size: 0.85rem;" id="profile-nim">NIM: 2024102004</p>
          <p style="color: var(--color-charcoal); opacity: 0.7; font-size: 0.85rem; margin-bottom: 1rem;" id="profile-prodi">Teknik Informatika</p>
          
          <div class="profile-meta-list">
            <div class="profile-meta-item">
              <span class="profile-meta-label" id="stat-card-1-label">Buku Dipinjam</span>
              <span class="profile-meta-val" id="stat-borrowed-count">0</span>
            </div>
            <div class="profile-meta-item">
              <span class="profile-meta-label" id="stat-card-2-label">Dalam Antrean</span>
              <span class="profile-meta-val" id="stat-reservation-count">0</span>
            </div>
            <div class="profile-meta-item">
              <span class="profile-meta-label" id="stat-card-3-label">Daftar Keinginan</span>
              <span class="profile-meta-val" id="stat-wishlist-count">0</span>
            </div>
          </div>
        </div>

        <!-- Dashboard Details Column -->
        <div class="dashboard-main-content">
          <!-- Active Loans Section -->
          <div class="dashboard-section-card">
            <div class="dashboard-section-title">
              <span id="dashboard-loans-title-span"><i class="fa-solid fa-book-reader" style="color: var(--color-teal);"></i> Buku yang Sedang Dipinjam</span>
              <span class="count-badge-dashboard" id="badge-loans-count">0</span>
            </div>
            
            <div class="dashboard-table-wrapper">
              <table class="dashboard-table">
                <thead>
                  <tr id="dashboard-loans-thead-tr">
                    <th>Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Sisa Waktu</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="dashboard-loans-tbody">
                  <!-- Rendered dynamically -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- Reservations Section -->
          <div class="dashboard-section-card">
            <div class="dashboard-section-title">
              <span id="dashboard-reservations-title-span"><i class="fa-solid fa-clock-rotate-left" style="color: var(--color-amber);"></i> Antrean Reservasi</span>
              <span class="count-badge-dashboard" id="badge-reservations-count">0</span>
            </div>
            
            <div class="dashboard-table-wrapper">
              <table class="dashboard-table">
                <thead>
                  <tr id="dashboard-reservations-thead-tr">
                    <th>Buku</th>
                    <th>Tanggal Reservasi</th>
                    <th>Posisi Antrean</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="dashboard-reservations-tbody">
                  <!-- Rendered dynamically -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- Wishlist Section -->
          <div class="dashboard-section-card" id="dashboard-wishlist-card">
            <div class="dashboard-section-title">
              <span id="dashboard-wishlist-title-span"><i class="fa-solid fa-heart" style="color: var(--color-danger);"></i> Daftar Keinginan (Wishlist)</span>
              <span class="count-badge-dashboard" id="badge-wishlist-count">0</span>
            </div>
            
            <div class="dashboard-table-wrapper">
              <table class="dashboard-table">
                <thead>
                  <tr id="dashboard-wishlist-thead-tr">
                    <th>Buku</th>
                    <th>Status Stok</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="dashboard-wishlist-tbody">
                  <!-- Rendered dynamically -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. DATABASE CATALOG MANAGER & TRANSACTION LOGS -->
    <section id="database-section" class="page-section">
      <div class="section-header">
        <h2>Manajemen Katalog & Database</h2>
        
        <div class="librarian-mode-switch">
          <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" id="librarian-mode-checkbox" onchange="toggleLibrarianMode()">
            <span>Mode Pustakawan</span>
          </label>
          <span id="librarian-active-badge" class="librarian-badge-indicator" style="display: none;">Aktif</span>
        </div>
      </div>

      <div class="db-actions-header">
        <p class="db-title-sub">Gunakan portal ini untuk mengelola inventaris buku, menambah koleksi baru, dan meninjau log sirkulasi perpustakaan.</p>
        <button class="primary-btn" onclick="openAddBookModal()" id="db-add-book-btn" style="display: none;">
          <i class="fa-solid fa-plus"></i> Tambah Buku Baru
        </button>
      </div>

      <!-- Inventory Database -->
      <div class="dashboard-section-card" style="margin-bottom: 2.5rem;">
        <div class="dashboard-section-title">
          <span><i class="fa-solid fa-folder-open" style="color: var(--color-teal);"></i> Daftar Stok Buku</span>
        </div>
        <div class="dashboard-table-wrapper">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Buku</th>
                <th>Genre</th>
                <th>Tahun</th>
                <th>Stok / Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="db-inventory-tbody">
              <!-- Rendered dynamically -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Circulation Activity Logs -->
      <div class="dashboard-section-card">
        <div class="dashboard-section-title">
          <span><i class="fa-solid fa-receipt" style="color: var(--color-midnight);"></i> Log Aktivitas Sirkulasi</span>
        </div>
        <div class="dashboard-table-wrapper">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>Aktivitas</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody id="db-logs-tbody">
              <!-- Rendered dynamically -->
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- 5b. ADMIN LOANS SECTION (Librarian only) -->
    @if(auth()->user()->role === 'Pustakawan')
    <section id="admin-loans-section" class="page-section">
      <div class="section-header">
        <h2>Data Peminjam Buku</h2>
      </div>

      <div class="db-actions-header" style="margin-bottom: 2rem;">
        <p class="db-title-sub">Kelola semua peminjaman aktif mahasiswa perpustakaan UNU NTB. Lakukan pencatatan manual, perpanjang masa pinjam, atau selesaikan pengembalian buku.</p>
        <button class="primary-btn" onclick="openAddLoanModal()">
          <i class="fa-solid fa-plus"></i> Catat Peminjaman Baru
        </button>
      </div>

      <div class="dashboard-section-card">
        <div class="dashboard-section-title">
          <span><i class="fa-solid fa-list-check" style="color: var(--color-teal);"></i> Transaksi Peminjaman Mahasiswa</span>
        </div>
        <div class="dashboard-table-wrapper">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Mahasiswa</th>
                <th>Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="admin-loans-tbody">
              <!-- Rendered dynamically -->
            </tbody>
          </table>
        </div>
    </section>
    @endif

    <!-- 5c. ADMIN STUDENTS SECTION (Librarian only) -->
    @if(auth()->user()->role === 'Pustakawan')
    <section id="admin-students-section" class="page-section">
      <div class="section-header">
        <h2>Data Mahasiswa</h2>
      </div>

      <div class="db-actions-header" style="margin-bottom: 2rem;">
        <p class="db-title-sub">Kelola semua akun mahasiswa perpustakaan UNU NTB. Lihat email, perbarui profil, atau setel ulang password akun mereka.</p>
        <button class="primary-btn" onclick="openAddStudentModal()">
          <i class="fa-solid fa-user-plus"></i> Tambah Mahasiswa Baru
        </button>
      </div>

      <div class="dashboard-section-card">
        <div class="dashboard-section-title">
          <span><i class="fa-solid fa-users" style="color: var(--color-teal);"></i> Daftar Mahasiswa Terdaftar</span>
        </div>
        <div class="dashboard-table-wrapper">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Mahasiswa</th>
                <th>Fakultas & Prodi</th>
                <th>E-mail Akademik</th>
                <th>No. HP</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="admin-students-tbody">
              <!-- Rendered dynamically -->
            </tbody>
          </table>
        </div>
      </div>
    </section>
    @endif

    <!-- 6. NOTIFICATIONS & TIME SIMULATION -->
    <section id="notifications-section" class="page-section">
      <div class="section-header">
        <h2>Notifikasi & Kontrol Waktu</h2>
      </div>

      <div class="notification-container-grid">
        <!-- Notification list -->
        <div>
          <h3 style="font-size: 1.4rem; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-inbox" style="color: var(--color-teal);"></i> Kotak Masuk Notifikasi
          </h3>
          <div class="notif-list" id="notifications-list-container">
            <!-- Rendered dynamically -->
          </div>
        </div>

        <!-- Time Simulation Sidebar -->
        <aside class="sim-control-card">
          <div class="sim-headline">
            <span>Simulasi Waktu</span>
            <span class="sim-date-badge" id="sim-current-date-badge">11 Jun 2026</span>
          </div>
          <p style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 1.2rem;">Gunakan panel ini untuk mensimulasikan berjalannya waktu. Sangat berguna untuk menguji notifikasi keterlambatan pengembalian buku secara instan.</p>
          
          <div class="sim-controls-wrapper">
            <h4 style="font-size: 0.95rem; font-weight: bold;">Lompat Hari:</h4>
            <div class="sim-btn-group">
              <button class="sim-btn" onclick="simulateTime(1)">+1 Hari</button>
              <button class="sim-btn" onclick="simulateTime(5)">+5 Hari</button>
              <button class="sim-btn" onclick="simulateTime(15)">+15 Hari</button>
            </div>
            
            <div class="sim-status-desc">
              <p><strong>Aturan Peminjaman:</strong></p>
              <ul style="margin-left: 1.2rem; margin-top: 0.4rem; display: flex; flex-direction: column; gap: 0.2rem;">
                <li>Durasi Pinjam: 14 hari.</li>
                <li>Hari ke-12 s/d 14: Muncul pengingat jatuh tempo.</li>
                <li>Hari ke-15+: Dinyatakan terlambat (Notifikasi peringatan merah akan dikirim ke akun anggota).</li>
              </ul>
            </div>
          </div>
        </aside>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <footer>
    <div class="footer-container">
      <div class="footer-brand-desc">
        <h3>Perpustakaan UNU NTB</h3>
        <p style="font-size: 0.9rem; line-height: 1.5; color: var(--color-parchment-dark);">
          Sistem administrasi sirkulasi perpustakaan modern Universitas Nahdlatul Ulama Nusa Tenggara Barat. Bertekad melahirkan sarjana unggul berbasis keislaman dan keilmuan kontemporer.
        </p>
        <p style="font-size: 0.8rem; margin-top: 1rem; color: var(--color-teal);">
          <i class="fa-solid fa-location-dot"></i> Jl. Pendidikan No.6, Mataram, Nusa Tenggara Barat, Indonesia
        </p>
      </div>
      
      <div class="footer-links-col">
        <h4>Tautan Cepat</h4>
        <ul>
          <li><span style="cursor:pointer;" onclick="setViewTab('home')">Beranda</span></li>
          <li><span style="cursor:pointer;" onclick="setViewTab('browse')">Pencarian Buku</span></li>
          <li><span style="cursor:pointer;" onclick="setViewTab('curator')">Asisten Rekomendasi AI</span></li>
          <li><span style="cursor:pointer;" onclick="setViewTab('dashboard')">Profil Saya</span></li>
        </ul>
      </div>

      <div class="footer-links-col">
        <h4>Mode & Akses</h4>
        <ul>
          <li><span style="cursor:pointer;" onclick="setViewTab('database')">Akses Pustakawan</span></li>
          <li><span style="cursor:pointer;" onclick="setViewTab('notifications')">Simulator Sirkulasi</span></li>
          <li><a href="https://unu-ntb.ac.id" target="_blank">Situs Utama UNU NTB</a></li>
        </ul>
      </div>
    </div>
    
    <div class="footer-bottom">
      <p>&copy; 2026 Universitas Nahdlatul Ulama Nusa Tenggara Barat (UNU NTB). Seluruh hak cipta dilindungi.</p>
    </div>
  </footer>

  <!-- DYNAMIC TOAST MESSAGES CONTAINER -->
  <div class="toast-container" id="toast-container"></div>

  <!-- BOOK DETAILS MODAL -->
  <div class="modal-overlay" id="book-details-modal" onclick="closeModalOnOutsideClick(event, 'book-details-modal')">
    <div class="modal-content">
      <button class="modal-close" onclick="closeBookDetailsModal()">&times;</button>
      <div class="modal-header">
        <h3 id="modal-book-title">Judul Buku</h3>
      </div>
      <div class="modal-body">
        <div class="book-modal-details-grid">
          <!-- Left: Styled Cover Card -->
          <div class="book-card" style="pointer-events: none;">
            <div class="cover-wrapper" style="box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
              <div class="book-cover" id="modal-cover-bg">
                <div class="book-spine-shine"></div>
                <div class="book-spine-crease"></div>
                <div class="cover-header">
                  <span class="cover-genre" id="modal-cover-genre">Fiction</span>
                  <span class="cover-year" id="modal-cover-year">1999</span>
                </div>
                <div class="cover-body">
                  <div class="cover-title" id="modal-cover-title" style="font-size: 0.95rem;">Judul Buku</div>
                  <div class="cover-author" id="modal-cover-author" style="font-size: 0.65rem;">Penulis</div>
                </div>
                <div class="cover-footer">
                  <span class="cover-brand">UNU NTB</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Right: Book specs -->
          <div>
            <p style="font-style: italic; font-family: var(--font-serif); font-size: 1.1rem; color: var(--color-midnight); margin-bottom: 0.8rem;" id="modal-book-author">oleh Penulis</p>
            <div class="modal-book-desc" id="modal-book-description">Deskripsi buku di sini...</div>
            
            <div class="modal-book-meta-list">
              <div class="profile-meta-item">
                <span class="profile-meta-label">Genre</span>
                <span class="profile-meta-val" id="modal-book-genre">Fiksi</span>
              </div>
              <div class="profile-meta-item">
                <span class="profile-meta-label">Tahun Terbit</span>
                <span class="profile-meta-val" id="modal-book-year">2000</span>
              </div>
              <div class="profile-meta-item">
                <span class="profile-meta-label">Skor Popularitas</span>
                <span class="profile-meta-val" style="color: var(--color-amber);" id="modal-book-popularity"><i class="fa-solid fa-star"></i> 95</span>
              </div>
              <div class="profile-meta-item">
                <span class="profile-meta-label">Stok Tersedia</span>
                <span class="profile-meta-val" id="modal-book-stock">3 / 5</span>
              </div>
              <div class="profile-meta-item" id="modal-reservation-group" style="display: none;">
                <span class="profile-meta-label">Antrean Reservasi Saat Ini</span>
                <span class="profile-meta-val" id="modal-book-reservations-count" style="color: var(--color-amber);">0</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer-actions" id="modal-actions-container">
        <!-- Rendered dynamically based on availability & user state -->
      </div>
    </div>
  </div>

  <!-- ADD BOOK MODAL -->
  <div class="modal-overlay" id="add-book-modal" onclick="closeModalOnOutsideClick(event, 'add-book-modal')">
    <div class="modal-content">
      <button class="modal-close" onclick="closeAddBookModal()">&times;</button>
      <div class="modal-header">
        <h3 id="add-book-modal-title">Tambah Buku Baru</h3>
      </div>
      <div class="modal-body">
        <form id="add-book-form" onsubmit="handleAddBookSubmit(event)">
          <input type="hidden" id="form-book-id" value="">
          
          <div class="modal-form-group">
            <label for="form-title">Judul Buku</label>
            <input type="text" id="form-title" required placeholder="Contoh: Sang Pemula">
          </div>
          
          <div class="modal-form-group">
            <label for="form-author">Penulis</label>
            <input type="text" id="form-author" required placeholder="Contoh: Pramoedya Ananta Toer">
          </div>

          <div class="form-grid-2">
            <div class="modal-form-group">
              <label for="form-genre">Genre</label>
              <select id="form-genre" required>
                <option value="Historical Fiction">Historical Fiction</option>
                <option value="Drama">Drama</option>
                <option value="Modern Literature">Modern Literature</option>
                <option value="Philosophy">Philosophy</option>
                <option value="Science">Science</option>
                <option value="Technology">Technology</option>
                <option value="Magical Realism">Magical Realism</option>
              </select>
            </div>
            <div class="modal-form-group">
              <label for="form-year">Tahun Terbit</label>
              <input type="number" id="form-year" required min="1800" max="2027" placeholder="Contoh: 1985">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="modal-form-group">
              <label for="form-stock">Jumlah Total Salinan (Stok)</label>
              <input type="number" id="form-stock" required min="0" max="50" value="2">
            </div>
            <div class="modal-form-group">
              <label for="form-pattern">Gaya Pola Sampul</label>
              <select id="form-pattern">
                <option value="border">Border Klasik</option>
                <option value="stripes">Garis-Garis (Stripes)</option>
                <option value="circle">Lingkaran Geometris</option>
                <option value="grid">Kotak-Kotak (Grid)</option>
                <option value="waves">Gelombang (Waves)</option>
                <option value="floral">Bunga (Floral)</option>
                <option value="tech">Teknologi Modern</option>
                <option value="stars">Bintang (Stars)</option>
              </select>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="modal-form-group">
              <label for="form-bg-start">Warna Dasar Sampul (Start Hex)</label>
              <input type="color" id="form-bg-start" value="#2D4263">
            </div>
            <div class="modal-form-group">
              <label for="form-accent-color">Warna Aksen Sampul (Accent Hex)</label>
              <input type="color" id="form-accent-color" value="#398E8E">
            </div>
          </div>

          <div class="modal-form-group form-group-full">
            <label for="form-cover-image">Unggah File Sampul Buku (Opsional)</label>
            <input type="file" id="form-cover-image" accept="image/*">
            <span style="font-size: 0.75rem; opacity: 0.6; display: block; margin-top: 2px;">Format file: JPG, PNG. Jika diunggah, ini akan menggantikan gaya sampul pola di atas.</span>
          </div>

          <div class="modal-form-group form-group-full">
            <label for="form-desc">Sinopsis / Deskripsi Buku</label>
            <textarea id="form-desc" rows="4" required placeholder="Tuliskan ringkasan singkat isi buku..."></textarea>
          </div>

          <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
            <button type="button" class="action-btn-sm btn-danger" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;" onclick="closeAddBookModal()">Batal</button>
            <button type="submit" class="primary-btn" style="border-radius: var(--radius-sm);" id="add-book-submit-btn">Simpan Buku</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ADMIN LOAN MODAL -->
  <div class="modal-overlay" id="admin-loan-modal" onclick="closeModalOnOutsideClick(event, 'admin-loan-modal')">
    <div class="modal-content">
      <button class="modal-close" onclick="closeAdminLoanModal()">&times;</button>
      <div class="modal-header">
        <h3 id="admin-loan-modal-title">Catat Peminjaman Baru</h3>
      </div>
      <div class="modal-body">
        <form id="admin-loan-form" onsubmit="handleAdminLoanSubmit(event)">
          <input type="hidden" id="form-loan-id" value="">
          
          <div class="modal-form-group">
            <label for="form-loan-student">Pilih Mahasiswa</label>
            <select id="form-loan-student" required>
              <!-- Populated dynamically -->
            </select>
          </div>
          
          <div class="modal-form-group">
            <label for="form-loan-book">Pilih Buku</label>
            <select id="form-loan-book" required>
              <!-- Populated dynamically -->
            </select>
          </div>

          <div class="form-grid-2">
            <div class="modal-form-group">
              <label for="form-loan-borrow-date">Tanggal Pinjam</label>
              <input type="date" id="form-loan-borrow-date" required>
            </div>
            <div class="modal-form-group">
              <label for="form-loan-due-date">Tanggal Jatuh Tempo</label>
              <input type="date" id="form-loan-due-date" required>
            </div>
          </div>

          <div class="modal-form-group" id="form-loan-status-group" style="display: none;">
            <label for="form-loan-status">Status Peminjaman</label>
            <select id="form-loan-status">
              <option value="active">Aktif</option>
              <option value="overdue">Terlambat (Overdue)</option>
            </select>
          </div>

          <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
            <button type="button" class="action-btn-sm btn-danger" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;" onclick="closeAdminLoanModal()">Batal</button>
            <button type="submit" class="primary-btn" style="border-radius: var(--radius-sm);" id="admin-loan-submit-btn">Simpan Transaksi</button>
          </div>
        </form>
      </div>
  <!-- ADD STUDENT MODAL -->
  <div class="modal-overlay" id="add-student-modal" onclick="closeModalOnOutsideClick(event, 'add-student-modal')">
    <div class="modal-content" style="max-width: 500px;">
      <button class="modal-close" onclick="closeAddStudentModal()">&times;</button>
      <div class="modal-header">
        <h3 id="add-student-modal-title">Tambah Mahasiswa Baru</h3>
      </div>
      <div class="modal-body">
        <form id="add-student-form" onsubmit="handleAdminAddStudentSubmit(event)">
          <input type="hidden" id="form-student-id" value="">
          
          <div class="modal-form-group">
            <label for="form-student-name">Nama Lengkap</label>
            <input type="text" id="form-student-name" required placeholder="Contoh: Siti Aisyah">
          </div>
          
          <div class="form-grid-2">
            <div class="modal-form-group">
              <label for="form-student-nim">NIM</label>
              <input type="text" id="form-student-nim" required placeholder="Contoh: 2024103022">
            </div>
            <div class="modal-form-group">
              <label for="form-student-phone">Nomor HP</label>
              <input type="text" id="form-student-phone" placeholder="Contoh: 085966677788">
            </div>
          </div>

          <div class="modal-form-group">
            <label for="form-student-email">E-mail Akademik</label>
            <input type="email" id="form-student-email" required placeholder="Contoh: siti@unu-ntb.ac.id">
          </div>

          <div class="form-grid-2">
            <div class="modal-form-group">
              <label for="form-student-faculty">Fakultas</label>
              <select id="form-student-faculty" required onchange="handleModalFacultyChange()">
                <option value="">-- Pilih Fakultas --</option>
                <option value="Fakultas Kesehatan">Fakultas Kesehatan</option>
                <option value="Fakultas Pendidikan">Fakultas Pendidikan</option>
                <option value="Fakultas Teknik">Fakultas Teknik</option>
                <option value="Fakultas Ekonomi">Fakultas Ekonomi</option>
                <option value="Fakultas Hukum">Fakultas Hukum</option>
              </select>
            </div>
            <div class="modal-form-group">
              <label for="form-student-prodi">Program Studi</label>
              <select id="form-student-prodi" required>
                <option value="">-- Pilih Prodi --</option>
              </select>
            </div>
          </div>

          <div class="modal-form-group">
            <label for="form-student-password" id="label-student-password">Kata Sandi Baru</label>
            <input type="password" id="form-student-password" placeholder="Minimal 6 karakter">
            <span id="hint-student-password" style="font-size: 0.75rem; opacity: 0.6; display: block; margin-top: 2px;">Biarkan kosong jika tidak ingin merubah password.</span>
          </div>

          <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
            <button type="button" class="action-btn-sm btn-danger" style="padding: 0.6rem 1.2rem; font-size: 0.9rem;" onclick="closeAddStudentModal()">Batal</button>
            <button type="submit" class="primary-btn" style="border-radius: var(--radius-sm);" id="add-student-submit-btn">Simpan Data</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- INJECT AUTH STATE CONTEXT -->
  <script>
    window.AuthUser = @json(auth()->user());
  </script>

  <!-- JS SCRIPTS -->
  <script src="{{ asset('app.js') }}"></script>
</body>
</html>
