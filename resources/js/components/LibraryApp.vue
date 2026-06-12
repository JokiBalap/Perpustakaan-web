<template>
  <div class="min-h-screen bg-parchment text-midnight flex flex-col font-sans">
    <!-- TOAST NOTIFICATION -->
    <div class="fixed top-6 right-6 z-50 flex flex-col gap-3 max-w-sm pointer-events-none">
      <div v-if="toast.show" 
           :class="[
             'p-4 rounded-xl shadow-lg border text-white font-medium flex items-center gap-3 transition-all duration-300 transform translate-x-0 animate-bounce pointer-events-auto',
             toast.type === 'success' ? 'bg-teal border-teal-dark' : '',
             toast.type === 'danger' ? 'bg-danger border-danger-dark' : '',
             toast.type === 'info' ? 'bg-midnight border-black' : ''
           ]">
        <i v-if="toast.type === 'success'" class="fa-solid fa-circle-check text-lg"></i>
        <i v-else-if="toast.type === 'danger'" class="fa-solid fa-triangle-exclamation text-lg"></i>
        <i v-else class="fa-solid fa-circle-info text-lg"></i>
        <span>{{ toast.message }}</span>
      </div>
    </div>

    <!-- HEADER / NAVIGATION -->
    <header :class="['bg-midnight text-white sticky top-0 z-40 shadow-md', currentUser && currentUser.role === 'Mahasiswa' ? 'hidden md:block' : '']">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center py-4 gap-4">
        <!-- Brand -->
        <div class="flex items-center gap-4 cursor-pointer" @click="setViewTab('home')">
          <div class="h-12 w-12 flex items-center justify-center bg-white bg-opacity-10 rounded-full p-2">
            <img src="/assets/logo.png" alt="Logo UNU NTB" class="h-10 w-auto hover:scale-110 transition-transform">
          </div>
          <div>
            <h1 class="text-xl font-bold tracking-tight">Perpustakaan UNU NTB</h1>
            <span class="text-xs tracking-wider text-teal-light font-bold">Lentera Peradaban & Akademik</span>
          </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex flex-wrap gap-1 md:gap-2 justify-center">
          <button @click="setViewTab('home')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2', activeTab === 'home' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-house"></i> Beranda
          </button>
          <button @click="setViewTab('browse')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2', activeTab === 'browse' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-magnifying-glass"></i> Cari Buku
          </button>
          <button @click="setViewTab('curator')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2', activeTab === 'curator' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-wand-magic-sparkles"></i> AI Curator
          </button>
          <button @click="setViewTab('dashboard')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2', activeTab === 'dashboard' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-user-check"></i> Dashboard
          </button>
          <button @click="setViewTab('database')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2', activeTab === 'database' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-database"></i> Katalog & Log
          </button>
          <button v-if="currentUser.role === 'Pustakawan'" @click="setViewTab('admin-loans')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2', activeTab === 'admin-loans' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-book-bookmark"></i> Data Peminjam
          </button>
          <button v-if="currentUser.role === 'Pustakawan'" @click="setViewTab('admin-students')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2', activeTab === 'admin-students' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-users"></i> Data Mahasiswa
          </button>
          <button v-if="currentUser.role === 'Pustakawan'" @click="setViewTab('admin-backup')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2', activeTab === 'admin-backup' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-cloud-arrow-up"></i> Backup Data
          </button>
          <button @click="setViewTab('notifications')" :class="['px-3 py-2 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2 relative', activeTab === 'notifications' ? 'bg-teal text-white' : 'hover:bg-midnight-light text-parchment text-opacity-80']">
            <i class="fa-solid fa-bell"></i> Notifikasi
            <span v-if="unreadNotifsCount > 0" class="absolute -top-1 -right-1 bg-danger text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full font-bold border-2 border-midnight">
              {{ unreadNotifsCount }}
            </span>
          </button>
        </nav>

        <!-- User Profile Menu -->
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2 bg-midnight-light p-1.5 rounded-lg border border-black border-opacity-20 cursor-pointer" @click="setViewTab('dashboard')">
            <div class="h-8 w-8 bg-teal text-white rounded-full flex items-center justify-center font-bold text-sm">
              {{ userInitials }}
            </div>
            <div class="hidden lg:block text-left text-xs">
              <p class="font-bold text-white leading-tight">{{ currentUser.name }}</p>
              <p class="text-teal-light font-medium">{{ currentUser.role }}</p>
            </div>
          </div>
          <button @click="logout" class="bg-danger hover:bg-danger-dark text-white p-2 rounded-lg text-sm transition-colors" title="Keluar">
            <i class="fa-solid fa-right-from-bracket"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- DATE SIMULATION BAR -->
    <div :class="['bg-gradient-to-r from-midnight-light via-midnight to-midnight-light border-b border-teal/15 py-1.5 px-3 shadow-inner', currentUser && currentUser.role === 'Mahasiswa' ? 'hidden md:block' : '']">
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 text-white text-[11px] md:text-xs">
        <!-- Clock widget with dynamic pulsing/spinning effect -->
        <div class="flex items-center gap-1.5 bg-white bg-opacity-5 px-3 py-1 rounded-full border border-white/10 hover:border-teal/30 transition-all duration-300">
          <i class="fa-solid fa-clock text-teal-light animate-spin-slow text-xs"></i>
          <span class="font-mono font-bold text-teal-light tracking-wide" id="sim-current-date-badge">{{ formattedSimDate }}</span>
        </div>
        <!-- Time Travel buttons grouped inside a sleek pill -->
        <div class="flex items-center gap-1.5 bg-white bg-opacity-5 p-0.5 rounded-full border border-white/10">
          <button @click="simulateTime(1)" class="px-3 py-1 bg-teal/20 hover:bg-teal text-white text-[10px] md:text-xs font-bold rounded-full transition-all duration-300 transform active:scale-95 shadow-sm border border-teal/30 hover:border-teal flex items-center gap-1">
            <i class="fa-solid fa-forward-step text-[9px] md:text-xs"></i> +1 Hari
          </button>
          <button @click="simulateTime(7)" class="px-3 py-1 bg-teal/20 hover:bg-teal text-white text-[10px] md:text-xs font-bold rounded-full transition-all duration-300 transform active:scale-95 shadow-sm border border-teal/30 hover:border-teal flex items-center gap-1">
            <i class="fa-solid fa-forward text-[9px] md:text-xs"></i> +7 Hari
          </button>
        </div>
      </div>
    </div>

    <!-- MAIN VIEWPORT -->
    <main :class="['flex-grow max-w-7xl w-full mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8', currentUser && currentUser.role === 'Mahasiswa' ? 'pb-20 md:pb-8' : '']">
      
      <!-- 1. HOME SECTION -->
      <section v-if="activeTab === 'home'" class="space-y-8 animate-fadeIn">
        <div class="relative bg-gradient-to-r from-midnight to-midnight-light rounded-2xl p-6 sm:p-8 md:p-12 text-white overflow-hidden shadow-lg border border-teal border-opacity-20">
          <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-teal rounded-full opacity-10 blur-xl"></div>
          <div class="relative max-w-2xl">
            <h2 class="text-xl sm:text-2xl md:text-4xl font-extrabold leading-tight text-parchment">Temukan Inspirasi & Pengetahuan di Perpustakaan UNU NTB</h2>
            <p class="mt-3 text-xs sm:text-sm md:text-base text-parchment text-opacity-80">Akses katalog literatur ilmiah, khazanah budaya, dan teknologi masa kini. Dilengkapi dengan asisten rekomendasi AI pribadi Anda.</p>
            <div class="mt-6 sm:mt-8 flex gap-2 max-w-md bg-white rounded-lg p-1 shadow-md">
              <input type="text" v-model="heroSearchText" @keyup.enter="handleHeroSearch" placeholder="Cari judul, penulis, no. klasifikasi (DDC), atau ISBN..." class="flex-grow px-3 sm:px-4 py-1.5 sm:py-2 text-midnight text-xs sm:text-sm outline-none rounded-lg">
              <button @click="handleHeroSearch" class="bg-teal hover:bg-teal-dark text-white px-4 sm:px-6 py-1.5 sm:py-2 rounded-md font-bold transition-colors flex items-center gap-2 text-xs sm:text-sm">
                <i class="fa-solid fa-magnifying-glass"></i> <span class="hidden sm:inline">Cari</span>
              </button>
            </div>
          </div>
        </div>

        <div>
          <div class="flex justify-between items-end border-b border-parchment-dark pb-2 sm:pb-3 mb-4 sm:mb-6">
            <h3 class="text-sm sm:text-base md:text-lg font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-fire text-amber"></i> Buku Terpopuler</h3>
            <button @click="setViewTab('browse')" class="text-xs sm:text-sm font-semibold text-teal hover:underline">Lihat Semua Katalog</button>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
            <div v-for="book in popularBooks" :key="book.id" @click="openBookDetails(book.id)" class="bg-white rounded-lg sm:rounded-xl shadow hover:shadow-lg hover:-translate-y-1 transform transition-all duration-300 p-2 sm:p-3 cursor-pointer group">
              <div class="aspect-[3/4] rounded-lg overflow-hidden shadow-book relative bg-parchment border-l-4 border-black border-opacity-20 mb-2 sm:mb-3">
                <img v-if="book.imagePath" :src="'/' + book.imagePath" class="w-full h-full object-cover">
                <div v-else :style="getBookCoverStyle(book)" class="w-full h-full p-4 flex flex-col justify-between text-white relative">
                  <div class="absolute inset-0 bg-gradient-to-r from-black/25 via-transparent to-transparent pointer-events-none"></div>
                  <div class="flex justify-between text-[10px] font-bold opacity-85">
                    <span class="border border-white/40 px-1 rounded uppercase tracking-wider">{{ book.genre }}</span>
                    <span>{{ book.publishedYear }}</span>
                  </div>
                  <div class="my-auto text-center">
                    <p class="font-extrabold text-sm leading-tight line-clamp-3" :style="{ color: book.coverStyle.text }">{{ book.title }}</p>
                    <p class="text-[10px] opacity-90 mt-1 line-clamp-1" :style="{ color: book.coverStyle.text }">{{ book.author }}</p>
                  </div>
                  <div class="text-[9px] font-bold opacity-80 uppercase tracking-widest text-center" :style="{ color: book.coverStyle.accent || '#fff' }">UNU NTB</div>
                </div>
              </div>
              <div>
                <h4 class="font-bold text-xs sm:text-sm text-midnight line-clamp-1 group-hover:text-teal transition-colors">{{ book.title }}</h4>
                <p class="text-[10px] sm:text-xs text-midnight opacity-75 line-clamp-1 mt-0.5">{{ book.author }}</p>
                <div class="flex items-center justify-between mt-2 sm:mt-3 pt-1.5 sm:pt-2 border-t border-parchment-dark">
                  <span :class="['px-1.5 sm:px-2 py-0.5 text-[9px] sm:text-[10px] font-bold rounded-full', book.stock > 0 ? 'bg-teal/10 text-teal' : 'bg-danger/10 text-danger']">
                    {{ book.stock > 0 ? 'Tersedia' : 'Dipesan' }}
                  </span>
                  <span class="text-xs font-bold text-amber flex items-center gap-1"><i class="fa-solid fa-star"></i> {{ book.popularity }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div>
          <div class="flex justify-between items-end border-b border-parchment-dark pb-2 sm:pb-3 mb-4 sm:mb-6">
            <h3 class="text-sm sm:text-base md:text-lg font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-clock text-teal"></i> Koleksi Terbaru</h3>
            <button @click="setViewTab('browse')" class="text-xs sm:text-sm font-semibold text-teal hover:underline">Lihat Semua Katalog</button>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
            <div v-for="book in recentBooks" :key="book.id" @click="openBookDetails(book.id)" class="bg-white rounded-lg sm:rounded-xl shadow hover:shadow-lg hover:-translate-y-1 transform transition-all duration-300 p-2 sm:p-3 cursor-pointer group">
              <div class="aspect-[3/4] rounded-lg overflow-hidden shadow-book relative bg-parchment border-l-4 border-black border-opacity-20 mb-2 sm:mb-3">
                <img v-if="book.imagePath" :src="'/' + book.imagePath" class="w-full h-full object-cover">
                <div v-else :style="getBookCoverStyle(book)" class="w-full h-full p-4 flex flex-col justify-between text-white relative">
                  <div class="absolute inset-0 bg-gradient-to-r from-black/25 via-transparent to-transparent pointer-events-none"></div>
                  <div class="flex justify-between text-[10px] font-bold opacity-85">
                    <span class="border border-white/40 px-1 rounded uppercase tracking-wider">{{ book.genre }}</span>
                    <span>{{ book.publishedYear }}</span>
                  </div>
                  <div class="my-auto text-center">
                    <p class="font-extrabold text-sm leading-tight line-clamp-3" :style="{ color: book.coverStyle.text }">{{ book.title }}</p>
                    <p class="text-[10px] opacity-90 mt-1 line-clamp-1" :style="{ color: book.coverStyle.text }">{{ book.author }}</p>
                  </div>
                  <div class="text-[9px] font-bold opacity-80 uppercase tracking-widest text-center" :style="{ color: book.coverStyle.accent || '#fff' }">UNU NTB</div>
                </div>
              </div>
              <div>
                <h4 class="font-bold text-xs sm:text-sm text-midnight line-clamp-1 group-hover:text-teal transition-colors">{{ book.title }}</h4>
                <p class="text-[10px] sm:text-xs text-midnight opacity-75 line-clamp-1 mt-0.5">{{ book.author }}</p>
                <div class="flex items-center justify-between mt-2 sm:mt-3 pt-1.5 sm:pt-2 border-t border-parchment-dark">
                  <span :class="['px-1.5 sm:px-2 py-0.5 text-[9px] sm:text-[10px] font-bold rounded-full', book.stock > 0 ? 'bg-teal/10 text-teal' : 'bg-danger/10 text-danger']">
                    {{ book.stock > 0 ? 'Tersedia' : 'Dipesan' }}
                  </span>
                  <span class="text-xs font-bold text-amber flex items-center gap-1"><i class="fa-solid fa-star"></i> {{ book.popularity }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 2. BROWSE CATALOG SECTION -->
      <section v-if="activeTab === 'browse'" class="animate-fadeIn space-y-6">
        <h2 class="text-xl font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-book-open text-teal"></i> Katalog Perpustakaan</h2>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
          <!-- Filters sidebar -->
          <aside class="bg-white p-6 rounded-xl border border-parchment-dark shadow-sm h-fit space-y-6">
            <div>
              <h3 class="text-sm font-bold text-midnight mb-3">Pencarian</h3>
              <div class="relative flex items-center">
                <input type="text" v-model="searchQuery" @keyup.enter="() => {}" placeholder="Ketik judul, penulis, no. klasifikasi (DDC), atau ISBN..." class="w-full px-3 py-2 text-sm border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
                <i class="fa-solid fa-magnifying-glass absolute right-3 text-teal"></i>
              </div>
              <p class="text-[10px] text-midnight/50 mt-1"><i class="fa-solid fa-circle-info"></i> Cari langsung dengan Nomor Klasifikasi DDC (contoh: <strong class="font-mono">701</strong>, <strong class="font-mono">615.4</strong>)</p>
            </div>

            <div>
              <h3 class="text-sm font-bold text-midnight mb-3">Kategori / Genre</h3>
              <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                <label v-for="genre in uniqueGenres" :key="genre" class="flex items-center gap-2 text-xs font-medium cursor-pointer text-midnight">
                  <input type="checkbox" :value="genre" v-model="selectedGenres" class="accent-teal rounded">
                  <span>{{ genre }}</span>
                </label>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-bold text-midnight mb-3">Ketersediaan</h3>
              <label class="flex items-center gap-2 text-xs font-medium cursor-pointer text-midnight">
                <input type="checkbox" v-model="availableOnly" class="accent-teal rounded">
                <span>Tersedia untuk Dipinjam</span>
              </label>
            </div>

            <div>
              <h3 class="text-sm font-bold text-midnight mb-3">Urutkan</h3>
              <select v-model="sortKey" class="w-full px-2 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight cursor-pointer">
                <option value="popularity">Popularitas Tertinggi</option>
                <option value="title-asc">Judul A - Z</option>
                <option value="title-desc">Judul Z - A</option>
                <option value="year-desc">Tahun Baru - Lama</option>
              </select>
            </div>
          </aside>

          <!-- Book Grid Results -->
          <div class="lg:col-span-3 space-y-4">
            <p class="text-sm font-medium text-midnight opacity-70">Ditemukan <strong class="text-teal">{{ filteredBooks.length }}</strong> buku di katalog.</p>
            <div v-if="filteredBooks.length === 0" class="bg-white rounded-xl border border-parchment-dark p-12 text-center text-midnight opacity-60">
              <i class="fa-solid fa-magnifying-glass-minus text-4xl text-teal mb-3 block"></i>
              <h3 class="font-bold text-lg">Buku Tidak Ditemukan</h3>
              <p class="text-sm mt-1">Ganti kata kunci pencarian atau sesuaikan filter untuk menemukan hasil lainnya.</p>
            </div>
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-6">
              <div v-for="book in filteredBooks" :key="book.id" @click="openBookDetails(book.id)" class="bg-white rounded-xl shadow hover:shadow-lg hover:-translate-y-1 transform transition-all duration-300 p-3 cursor-pointer group">
                <div class="aspect-[3/4] rounded-lg overflow-hidden shadow-book relative bg-parchment border-l-4 border-black border-opacity-20 mb-3">
                  <img v-if="book.imagePath" :src="'/' + book.imagePath" class="w-full h-full object-cover">
                  <div v-else :style="getBookCoverStyle(book)" class="w-full h-full p-4 flex flex-col justify-between text-white relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/25 via-transparent to-transparent pointer-events-none"></div>
                    <div class="flex justify-between text-[10px] font-bold opacity-85">
                      <span class="border border-white/40 px-1 rounded uppercase tracking-wider">{{ book.genre }}</span>
                      <span>{{ book.publishedYear }}</span>
                    </div>
                    <div class="my-auto text-center">
                      <p class="font-extrabold text-sm leading-tight line-clamp-3" :style="{ color: book.coverStyle.text }">{{ book.title }}</p>
                      <p class="text-[10px] opacity-90 mt-1 line-clamp-1" :style="{ color: book.coverStyle.text }">{{ book.author }}</p>
                    </div>
                    <div class="text-[9px] font-bold opacity-80 uppercase tracking-widest text-center" :style="{ color: book.coverStyle.accent || '#fff' }">UNU NTB</div>
                  </div>
                </div>
                <div>
                  <h4 class="font-bold text-sm text-midnight line-clamp-1 group-hover:text-teal transition-colors">{{ book.title }}</h4>
                  <p class="text-xs text-midnight opacity-75 line-clamp-1 mt-0.5">{{ book.author }}</p>
                  <div class="flex items-center justify-between mt-3 pt-2 border-t border-parchment-dark">
                    <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-full', book.stock > 0 ? 'bg-teal/10 text-teal' : 'bg-danger/10 text-danger']">
                      {{ book.stock > 0 ? 'Tersedia' : 'Dipesan' }}
                    </span>
                    <span class="text-xs font-bold text-amber flex items-center gap-1"><i class="fa-solid fa-star"></i> {{ book.popularity }}</span>
                  </div>
                  <div v-if="book.classNumber" class="mt-1.5">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-midnight/8 text-midnight/60 rounded text-[9px] font-mono font-bold border border-midnight/10">
                      <i class="fa-solid fa-barcode text-[7px]"></i> {{ book.classNumber }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 3. AI CURATOR TOOL SECTION -->
      <section v-if="activeTab === 'curator'" class="animate-fadeIn space-y-6">
        <h2 class="text-xl font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-wand-magic-sparkles text-teal"></i> AI Curator Tool</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Intake Controls -->
          <div class="bg-white p-6 rounded-xl border border-parchment-dark shadow-sm h-fit space-y-5">
            <h3 class="font-bold text-midnight text-sm border-b pb-2">Asisten Rekomendasi Pintar</h3>
            <p class="text-xs text-midnight opacity-80 leading-relaxed">Beritahu AI apa minat bacaan Anda saat ini atau bagikan topik yang sedang Anda pelajari, dan kami akan mencarikan buku yang paling sesuai dari katalog perpustakaan UNU NTB.</p>
            
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-midnight mb-2">Pilih Genre Favorit</label>
                <select v-model="curatorGenre" class="w-full px-2 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight cursor-pointer">
                  <option value="any">Semua Kategori</option>
                  <option v-for="genre in uniqueGenres" :key="genre" :value="genre">{{ genre }}</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-midnight mb-2">Gaya Penulisan / Pendekatan</label>
                <select v-model="curatorStyle" class="w-full px-2 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight cursor-pointer">
                  <option value="any">Apa Saja</option>
                  <option value="academic">Akademik & Ilmiah Berat</option>
                  <option value="narrative">Fiksi Naratif & Sastra</option>
                  <option value="light">Populer & Ringan Dibaca</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-bold text-midnight mb-2">Topik Kunci / Kata Kunci Minat (Pisahkan dengan koma)</label>
                <input type="text" v-model="curatorKeywords" placeholder="Contoh: sejarah, algoritma, filsafat, pergerakan..." class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
              </div>

              <button @click="generateAIRecommendations" :disabled="curatorLoading" class="w-full py-3 bg-teal hover:bg-teal-dark disabled:bg-teal/50 text-white rounded-lg font-bold text-xs shadow-md transition-colors flex items-center justify-center gap-2">
                <i v-if="curatorLoading" class="fa-solid fa-spinner fa-spin"></i>
                <i v-else class="fa-solid fa-brain"></i>
                <span>{{ curatorLoading ? 'Menganalisis Minat...' : 'Dapatkan Rekomendasi' }}</span>
              </button>
            </div>
          </div>

          <!-- Recommendations display -->
          <div class="lg:col-span-2 min-h-[400px] flex flex-col">
            <!-- Placeholder -->
            <div v-if="curatorRecommendations.length === 0" class="flex-grow bg-white border border-dashed border-parchment-dark rounded-xl flex flex-col items-center justify-center p-12 text-center text-midnight opacity-60">
              <i class="fa-solid fa-robot text-5xl text-teal mb-4"></i>
              <h3 class="font-bold text-base">Asisten Menunggu Input</h3>
              <p class="text-xs mt-1 max-w-sm">Tentukan genre favorit dan topik minat Anda di panel kiri untuk mulai menghasilkan rekomendasi buku cerdas berbasis AI.</p>
            </div>
            <!-- Results Panel -->
            <div v-else class="flex-grow bg-white border border-teal/30 p-6 rounded-xl shadow-premium space-y-6">
              <div class="flex items-center gap-3 border-b pb-3">
                <i class="fa-solid fa-brain text-2xl text-teal"></i>
                <div>
                  <h3 class="font-extrabold text-midnight">Rekomendasi Terpilih AI</h3>
                  <p class="text-[10px] text-midnight opacity-75">Berdasarkan data matriks kepuasan dan kecocokan topik Anda.</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="{ book, score, reason } in curatorRecommendations" :key="book.id" class="bg-parchment-light border border-parchment-dark rounded-xl p-4 flex flex-col justify-between shadow-sm relative overflow-hidden group">
                  <div class="absolute top-0 right-0 bg-teal text-white text-[9px] font-extrabold px-2 py-1 rounded-bl-lg">
                    Cocok: {{ Math.round(score) }}%
                  </div>
                  <div>
                    <div class="flex gap-4">
                      <!-- Mini Book Cover -->
                      <div @click="openBookDetails(book.id)" class="w-16 aspect-[3/4] rounded shadow-book overflow-hidden bg-white shrink-0 border-l-2 border-black/25 cursor-pointer">
                        <img v-if="book.imagePath" :src="'/' + book.imagePath" class="w-full h-full object-cover">
                        <div v-else :style="getBookCoverStyle(book)" class="w-full h-full p-1.5 flex flex-col justify-between text-[6px] text-white">
                          <span class="uppercase tracking-widest text-[5px] truncate font-bold">{{ book.genre }}</span>
                          <span class="font-bold text-[7px] leading-tight text-center truncate">{{ book.title }}</span>
                          <span class="text-[5px] text-center font-bold">AI SELECT</span>
                        </div>
                      </div>
                      <div class="text-left">
                        <h4 @click="openBookDetails(book.id)" class="font-extrabold text-sm text-midnight hover:text-teal transition-colors cursor-pointer line-clamp-2 leading-snug">{{ book.title }}</h4>
                        <p class="text-[11px] text-midnight opacity-75 mt-0.5">{{ book.author }}</p>
                        <span :class="['inline-block mt-2 px-1.5 py-0.5 text-[8px] font-bold rounded', book.stock > 0 ? 'bg-teal/15 text-teal' : 'bg-danger/15 text-danger']">
                          {{ book.stock > 0 ? 'Stok Tersedia' : 'Stok Habis' }}
                        </span>
                      </div>
                    </div>
                    <div class="mt-4 bg-white p-3 rounded-lg border border-parchment-dark text-[11px] leading-relaxed text-midnight italic">
                      "{{ reason }}"
                    </div>
                  </div>
                  <div class="mt-4 pt-3 border-t border-parchment-dark flex gap-2 justify-end">
                    <button @click="openBookDetails(book.id)" class="px-3 py-1.5 bg-midnight text-white text-[10px] font-bold rounded hover:bg-black transition-colors">Detail</button>
                    <button v-if="book.stock > 0" @click="borrowBook(book.id)" class="px-3 py-1.5 bg-teal text-white text-[10px] font-bold rounded hover:bg-teal-dark transition-colors">Pinjam Instan</button>
                    <button v-else @click="joinReservation(book.id)" class="px-3 py-1.5 bg-amber text-white text-[10px] font-bold rounded hover:bg-amber-dark transition-colors">Antre Reservasi</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 4. DASHBOARD SECTION -->
      <section v-if="activeTab === 'dashboard'" class="animate-fadeIn space-y-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <h2 class="text-xl font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-user-circle text-teal"></i> {{ currentUser.role === 'Pustakawan' ? 'Dashboard Pustakawan' : 'Dashboard Anggota' }}</h2>
          <div v-if="currentUser.role === 'Pustakawan'" class="flex items-center gap-2 bg-white px-4 py-2 border border-parchment-dark rounded-lg">
            <input type="checkbox" id="librarian-mode-checkbox" v-model="librarianModeActive" class="accent-teal rounded cursor-pointer">
            <label for="librarian-mode-checkbox" class="text-xs font-bold text-midnight cursor-pointer select-none">Aktifkan Mode Pustakawan</label>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- Left side: User profile card -->
          <div class="bg-white p-6 rounded-xl border border-parchment-dark shadow-sm h-fit text-center space-y-4">
            <div class="mx-auto h-20 w-20 bg-teal text-white text-3xl font-extrabold flex items-center justify-center rounded-full shadow">
              {{ userInitials }}
            </div>
            <div>
              <h3 class="font-extrabold text-midnight text-base leading-snug">{{ currentUser.name }}</h3>
              <p class="text-xs text-midnight opacity-75 mt-0.5">{{ currentUser.nim ? 'NIM: ' + currentUser.nim : 'NIDN/NIK Pustakawan' }}</p>
              <p class="text-xs text-teal font-bold uppercase mt-2 tracking-wider">{{ currentUser.prodi || 'Departemen Perpustakaan' }}</p>
            </div>
            <div class="pt-2 border-t border-parchment-dark">
              <button @click="openChangePasswordModal" class="w-full py-2 bg-midnight hover:bg-black text-white rounded font-bold text-xs shadow transition-colors flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-key"></i> Ubah Kata Sandi
              </button>
            </div>
          </div>

          <!-- Right side: Stats and tables -->
          <div class="md:col-span-3 space-y-6">
            <!-- Stats cards -->
            <div class="grid grid-cols-3 gap-4">
              <div class="bg-white p-4 rounded-xl border border-parchment-dark shadow-sm text-center">
                <span class="block text-xs font-bold text-midnight opacity-70 mb-1">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? 'Total Peminjaman Aktif' : 'Buku Dipinjam' }}
                </span>
                <strong class="text-2xl font-black text-teal">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? allLoans.length : currentUser.borrowedBooks.length }}
                </strong>
              </div>
              <div class="bg-white p-4 rounded-xl border border-parchment-dark shadow-sm text-center">
                <span class="block text-xs font-bold text-midnight opacity-70 mb-1">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? 'Total Antrean' : 'Dalam Antrean' }}
                </span>
                <strong class="text-2xl font-black text-amber">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? allReservations.length : currentUser.reservations.length }}
                </strong>
              </div>
              <div class="bg-white p-4 rounded-xl border border-parchment-dark shadow-sm text-center">
                <span class="block text-xs font-bold text-midnight opacity-70 mb-1">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? 'Buku Kehabisan Stok' : 'Daftar Keinginan' }}
                </span>
                <strong class="text-2xl font-black text-danger">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? outOfStockBooks.length : currentUser.wishlist.length }}
                </strong>
              </div>
            </div>

            <!-- TABLE 1: Loans -->
            <div class="bg-white rounded-xl border border-parchment-dark shadow-sm overflow-hidden">
              <div class="px-5 py-4 border-b border-parchment-dark flex justify-between items-center bg-parchment-light">
                <span class="font-bold text-midnight text-sm flex items-center gap-2">
                  <i class="fa-solid fa-book-reader text-teal"></i>
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? 'Peminjaman Aktif Perpustakaan' : 'Buku yang Sedang Dipinjam' }}
                </span>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-teal/10 text-teal rounded-full">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? allLoans.length : currentUser.borrowedBooks.length }}
                </span>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                  <thead class="bg-parchment-light text-midnight font-bold border-b border-parchment-dark">
                    <tr v-if="currentUser.role === 'Pustakawan' && librarianModeActive">
                      <th class="px-4 py-3">Buku</th>
                      <th class="px-4 py-3">Peminjam (Mahasiswa)</th>
                      <th class="px-4 py-3">Jatuh Tempo</th>
                      <th class="px-4 py-3">Sisa Waktu</th>
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                    <tr v-else>
                      <th class="px-4 py-3">Buku</th>
                      <th class="px-4 py-3">Tanggal Pinjam</th>
                      <th class="px-4 py-3">Jatuh Tempo</th>
                      <th class="px-4 py-3">Sisa Waktu</th>
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-parchment-dark text-midnight font-medium">
                    <!-- Librarian Mode -->
                    <template v-if="currentUser.role === 'Pustakawan' && librarianModeActive">
                      <tr v-if="allLoans.length === 0">
                        <td colspan="5" class="text-center py-6 text-midnight opacity-55">Tidak ada peminjaman aktif di perpustakaan.</td>
                      </tr>
                      <tr v-for="loan in allLoans" :key="loan.id" class="hover:bg-parchment-light">
                        <td class="px-4 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-8 aspect-[3/4] rounded shadow-sm overflow-hidden bg-parchment shrink-0 border-l border-black/25">
                              <img v-if="getBookById(loan.bookId)?.imagePath" :src="'/' + getBookById(loan.bookId).imagePath" class="w-full h-full object-cover">
                              <div v-else :style="getBookCoverStyle(getBookById(loan.bookId))" class="w-full h-full text-[4px] text-white p-0.5 truncate font-bold text-center">
                                {{ loan.bookTitle }}
                              </div>
                            </div>
                            <div>
                              <strong @click="openBookDetails(loan.bookId)" class="text-midnight hover:text-teal cursor-pointer">{{ loan.bookTitle }}</strong>
                              <p class="text-[10px] text-midnight opacity-70 mt-0.5">{{ getBookById(loan.bookId)?.author }}</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <strong class="block">{{ loan.userName }}</strong>
                          <span class="text-[10px] text-midnight opacity-75">NIM: {{ loan.userNim }}</span>
                        </td>
                        <td class="px-4 py-3">{{ formatDisplayDate(loan.dueDate) }}</td>
                        <td class="px-4 py-3">
                          <div class="flex flex-col gap-1 w-24">
                            <span :class="['font-bold text-[10px]', getDaysDiffClass(loan.dueDate)]">
                              {{ getDaysDiffText(loan.dueDate) }}
                            </span>
                            <div class="w-full bg-parchment-dark h-1 rounded-full overflow-hidden">
                              <div :class="['h-full', getDaysDiffBarClass(loan.dueDate)]" :style="{ width: getProgressPercent(loan.borrowDate, loan.dueDate) + '%' }"></div>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <button @click="adminReturnLoan(loan.id)" class="px-3 py-1 bg-midnight hover:bg-black text-white rounded font-bold">Kembalikan</button>
                        </td>
                      </tr>
                    </template>
                    <!-- Student Mode -->
                    <template v-else>
                      <tr v-if="currentUser.borrowedBooks.length === 0">
                        <td colspan="5" class="text-center py-6 text-midnight opacity-55">Tidak ada buku yang sedang dipinjam.</td>
                      </tr>
                      <tr v-for="loan in currentUser.borrowedBooks" :key="loan.id" class="hover:bg-parchment-light">
                        <td class="px-4 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-8 aspect-[3/4] rounded shadow-sm overflow-hidden bg-parchment shrink-0 border-l border-black/25">
                              <img v-if="getBookById(loan.bookId)?.imagePath" :src="'/' + getBookById(loan.bookId).imagePath" class="w-full h-full object-cover">
                              <div v-else :style="getBookCoverStyle(getBookById(loan.bookId))" class="w-full h-full text-[4px] text-white p-0.5 truncate font-bold text-center">
                                {{ getBookById(loan.bookId)?.title }}
                              </div>
                            </div>
                            <div>
                              <strong @click="openBookDetails(loan.bookId)" class="text-midnight hover:text-teal cursor-pointer">{{ getBookById(loan.bookId)?.title }}</strong>
                              <p class="text-[10px] text-midnight opacity-70 mt-0.5">{{ getBookById(loan.bookId)?.author }}</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3">{{ formatDisplayDate(loan.borrowDate) }}</td>
                        <td class="px-4 py-3">{{ formatDisplayDate(loan.dueDate) }}</td>
                        <td class="px-4 py-3">
                          <div class="flex flex-col gap-1 w-24">
                            <span :class="['font-bold text-[10px]', getDaysDiffClass(loan.dueDate)]">
                              {{ getDaysDiffText(loan.dueDate) }}
                            </span>
                            <div class="w-full bg-parchment-dark h-1 rounded-full overflow-hidden">
                              <div :class="['h-full', getDaysDiffBarClass(loan.dueDate)]" :style="{ width: getProgressPercent(loan.borrowDate, loan.dueDate) + '%' }"></div>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <button @click="returnBook(loan.bookId)" class="px-3 py-1 bg-teal hover:bg-teal-dark text-white rounded font-bold">Kembalikan</button>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- TABLE 2: Reservations -->
            <div class="bg-white rounded-xl border border-parchment-dark shadow-sm overflow-hidden">
              <div class="px-5 py-4 border-b border-parchment-dark flex justify-between items-center bg-parchment-light">
                <span class="font-bold text-midnight text-sm flex items-center gap-2">
                  <i class="fa-solid fa-clock-rotate-left text-amber"></i>
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? 'Antrean Reservasi Perpustakaan' : 'Antrean Reservasi Aktif' }}
                </span>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber/10 text-amber rounded-full">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? allReservations.length : currentUser.reservations.length }}
                </span>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                  <thead class="bg-parchment-light text-midnight font-bold border-b border-parchment-dark">
                    <tr v-if="currentUser.role === 'Pustakawan' && librarianModeActive">
                      <th class="px-4 py-3">Buku</th>
                      <th class="px-4 py-3">Mahasiswa</th>
                      <th class="px-4 py-3">Posisi Antrean</th>
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                    <tr v-else>
                      <th class="px-4 py-3">Buku</th>
                      <th class="px-4 py-3">Tanggal Reservasi</th>
                      <th class="px-4 py-3">Posisi Antrean</th>
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-parchment-dark text-midnight font-medium">
                    <!-- Librarian Mode -->
                    <template v-if="currentUser.role === 'Pustakawan' && librarianModeActive">
                      <tr v-if="allReservations.length === 0">
                        <td colspan="4" class="text-center py-6 text-midnight opacity-55">Tidak ada antrean reservasi aktif.</td>
                      </tr>
                      <tr v-for="res in allReservations" :key="res.id" class="hover:bg-parchment-light">
                        <td class="px-4 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-8 aspect-[3/4] rounded shadow-sm overflow-hidden bg-parchment shrink-0 border-l border-black/25">
                              <img v-if="getBookById(res.bookId)?.imagePath" :src="'/' + getBookById(res.bookId).imagePath" class="w-full h-full object-cover">
                              <div v-else :style="getBookCoverStyle(getBookById(res.bookId))" class="w-full h-full text-[4px] text-white p-0.5 truncate font-bold text-center">
                                {{ res.bookTitle }}
                              </div>
                            </div>
                            <div>
                              <strong @click="openBookDetails(res.bookId)" class="text-midnight hover:text-teal cursor-pointer">{{ res.bookTitle }}</strong>
                              <p class="text-[10px] text-midnight opacity-70 mt-0.5">{{ getBookById(res.bookId)?.author }}</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <strong class="block">{{ res.userName }}</strong>
                          <span class="text-[10px] text-midnight opacity-75">NIM: {{ res.userNim }}</span>
                        </td>
                        <td class="px-4 py-3"><span class="font-bold text-amber">Antrean #{{ res.queuePosition }}</span></td>
                        <td class="px-4 py-3">
                          <button @click="cancelReservationForUser(res.userId, res.bookId)" class="px-3 py-1 bg-danger hover:bg-danger-dark text-white rounded font-bold">Batalkan</button>
                        </td>
                      </tr>
                    </template>
                    <!-- Student Mode -->
                    <template v-else>
                      <tr v-if="currentUser.reservations.length === 0">
                        <td colspan="4" class="text-center py-6 text-midnight opacity-55">Tidak ada reservasi aktif.</td>
                      </tr>
                      <tr v-for="res in currentUser.reservations" :key="res.id" class="hover:bg-parchment-light">
                        <td class="px-4 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-8 aspect-[3/4] rounded shadow-sm overflow-hidden bg-parchment shrink-0 border-l border-black/25">
                              <img v-if="getBookById(res.bookId)?.imagePath" :src="'/' + getBookById(res.bookId).imagePath" class="w-full h-full object-cover">
                              <div v-else :style="getBookCoverStyle(getBookById(res.bookId))" class="w-full h-full text-[4px] text-white p-0.5 truncate font-bold text-center">
                                {{ getBookById(res.bookId)?.title }}
                              </div>
                            </div>
                            <div>
                              <strong @click="openBookDetails(res.bookId)" class="text-midnight hover:text-teal cursor-pointer">{{ getBookById(res.bookId)?.title }}</strong>
                              <p class="text-[10px] text-midnight opacity-70 mt-0.5">{{ getBookById(res.bookId)?.author }}</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3">{{ formatDisplayDate(res.reservedDate) }}</td>
                        <td class="px-4 py-3"><span class="font-bold text-amber">Antrean #{{ res.queuePosition }}</span></td>
                        <td class="px-4 py-3">
                          <button @click="cancelReservation(res.bookId)" class="px-3 py-1 bg-danger hover:bg-danger-dark text-white rounded font-bold">Batalkan</button>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- TABLE 3: Wishlist (Student) or Out of Stock (Librarian) -->
            <div class="bg-white rounded-xl border border-parchment-dark shadow-sm overflow-hidden">
              <div class="px-5 py-4 border-b border-parchment-dark flex justify-between items-center bg-parchment-light">
                <span class="font-bold text-midnight text-sm flex items-center gap-2">
                  <i v-if="currentUser.role === 'Pustakawan' && librarianModeActive" class="fa-solid fa-triangle-exclamation text-danger"></i>
                  <i v-else class="fa-solid fa-heart text-danger"></i>
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? 'Buku Kehabisan Stok (Perlu Restock)' : 'Daftar Keinginan (Wishlist)' }}
                </span>
                <span :class="['px-2 py-0.5 text-[10px] font-bold rounded-full', currentUser.role === 'Pustakawan' && librarianModeActive ? 'bg-danger/10 text-danger' : 'bg-teal/10 text-teal']">
                  {{ currentUser.role === 'Pustakawan' && librarianModeActive ? outOfStockBooks.length : currentUser.wishlist.length }}
                </span>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left">
                  <thead class="bg-parchment-light text-midnight font-bold border-b border-parchment-dark">
                    <tr v-if="currentUser.role === 'Pustakawan' && librarianModeActive">
                      <th class="px-4 py-3">Buku</th>
                      <th class="px-4 py-3">Total Stok</th>
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                    <tr v-else>
                      <th class="px-4 py-3">Buku</th>
                      <th class="px-4 py-3">Status Stok</th>
                      <th class="px-4 py-3">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-parchment-dark text-midnight font-medium">
                    <!-- Librarian Mode -->
                    <template v-if="currentUser.role === 'Pustakawan' && librarianModeActive">
                      <tr v-if="outOfStockBooks.length === 0">
                        <td colspan="3" class="text-center py-6 text-teal font-bold opacity-80">Semua koleksi buku memiliki stok tersedia.</td>
                      </tr>
                      <tr v-for="book in outOfStockBooks" :key="book.id" class="hover:bg-parchment-light">
                        <td class="px-4 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-8 aspect-[3/4] rounded shadow-sm overflow-hidden bg-parchment shrink-0 border-l border-black/25">
                              <img v-if="book.imagePath" :src="'/' + book.imagePath" class="w-full h-full object-cover">
                              <div v-else :style="getBookCoverStyle(book)" class="w-full h-full text-[4px] text-white p-0.5 truncate font-bold text-center">
                                {{ book.title }}
                              </div>
                            </div>
                            <div>
                              <strong @click="openBookDetails(book.id)" class="text-midnight hover:text-teal cursor-pointer">{{ book.title }}</strong>
                              <p class="text-[10px] text-midnight opacity-70 mt-0.5">{{ book.author }}</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 bg-danger/10 text-danger rounded-full font-bold">Habis (0 / {{ book.totalStock }})</span></td>
                        <td class="px-4 py-3">
                          <button @click="restockBook(book.id)" class="px-3 py-1.5 bg-teal hover:bg-teal-dark text-white rounded font-bold flex items-center gap-1">
                            <i class="fa-solid fa-plus-square"></i> Restock
                          </button>
                        </td>
                      </tr>
                    </template>
                    <!-- Student Mode -->
                    <template v-else>
                      <tr v-if="currentUser.wishlist.length === 0">
                        <td colspan="3" class="text-center py-6 text-midnight opacity-55">Daftar keinginan kosong.</td>
                      </tr>
                      <tr v-for="bookId in currentUser.wishlist" :key="bookId" class="hover:bg-parchment-light">
                        <td class="px-4 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-8 aspect-[3/4] rounded shadow-sm overflow-hidden bg-parchment shrink-0 border-l border-black/25">
                              <img v-if="getBookById(bookId)?.imagePath" :src="'/' + getBookById(bookId).imagePath" class="w-full h-full object-cover">
                              <div v-else :style="getBookCoverStyle(getBookById(bookId))" class="w-full h-full text-[4px] text-white p-0.5 truncate font-bold text-center">
                                {{ getBookById(bookId)?.title }}
                              </div>
                            </div>
                            <div>
                              <strong @click="openBookDetails(bookId)" class="text-midnight hover:text-teal cursor-pointer">{{ getBookById(bookId)?.title }}</strong>
                              <p class="text-[10px] text-midnight opacity-70 mt-0.5">{{ getBookById(bookId)?.author }}</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <span :class="['px-2 py-0.5 rounded-full font-bold text-[10px]', getBookById(bookId)?.stock > 0 ? 'bg-teal/10 text-teal' : 'bg-danger/10 text-danger']">
                            {{ getBookById(bookId)?.stock > 0 ? 'Tersedia' : 'Kosong' }}
                          </span>
                        </td>
                        <td class="px-4 py-3">
                          <div class="flex gap-2">
                            <button v-if="getBookById(bookId)?.stock > 0" @click="borrowBook(bookId)" class="px-3 py-1 bg-teal hover:bg-teal-dark text-white rounded font-bold">Pinjam Instan</button>
                            <button v-else @click="joinReservation(bookId)" class="px-3 py-1 bg-amber hover:bg-amber-dark text-white rounded font-bold">Antre Reservasi</button>
                            <button @click="toggleWishlist(bookId)" class="p-1 bg-danger hover:bg-danger-dark text-white rounded" title="Hapus"><i class="fa-solid fa-trash-can"></i></button>
                          </div>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 5. DATABASE INVENTORY & LOGS SECTION -->
      <section v-if="activeTab === 'database'" class="animate-fadeIn space-y-6">
        <h2 class="text-xl font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-database text-teal"></i> Katalog Database & Log</h2>
        
        <div class="flex border-b border-parchment-dark">
          <button @click="databaseSubTab = 'books'" :class="['px-4 py-2 font-bold text-sm border-b-2 transition-colors', databaseSubTab === 'books' ? 'border-teal text-teal' : 'border-transparent text-midnight opacity-75 hover:opacity-100']">Koleksi Buku</button>
          <button @click="databaseSubTab = 'logs'" :class="['px-4 py-2 font-bold text-sm border-b-2 transition-colors', databaseSubTab === 'logs' ? 'border-teal text-teal' : 'border-transparent text-midnight opacity-75 hover:opacity-100']">Log Sirkulasi</button>
        </div>

        <!-- Book Inventory Tab -->
        <div v-if="databaseSubTab === 'books'" class="space-y-4">
          <div class="flex justify-between items-center">
            <h3 class="text-sm font-bold text-midnight">Total Terdaftar: <strong class="text-teal">{{ books.length }}</strong> Buku</h3>
            <button v-if="currentUser.role === 'Pustakawan'" @click="openAddBookModal" class="px-4 py-2 bg-teal hover:bg-teal-dark text-white rounded-lg font-bold text-xs shadow transition-colors flex items-center gap-2">
              <i class="fa-solid fa-plus"></i> Tambah Buku Baru
            </button>
          </div>
          <div class="bg-white rounded-xl border border-parchment-dark shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full text-xs text-left">
                <thead class="bg-parchment-light text-midnight font-bold border-b border-parchment-dark">
                  <tr>
                    <th class="px-4 py-3">ID / No. Klas.</th>
                    <th class="px-4 py-3">Buku / Penulis</th>
                    <th class="px-4 py-3">Genre</th>
                    <th class="px-4 py-3">Tahun</th>
                    <th class="px-4 py-3">Stok / Total</th>
                    <th v-if="currentUser.role === 'Pustakawan'" class="px-4 py-3">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-parchment-dark text-midnight font-medium">
                  <tr v-for="book in books" :key="book.id" class="hover:bg-parchment-light">
                    <td class="px-4 py-3">
                      <span class="font-mono font-bold text-teal block">{{ book.id }}</span>
                      <span v-if="book.classNumber" class="text-[10px] text-midnight/55 font-mono"><i class="fa-solid fa-barcode"></i> {{ book.classNumber }}</span>
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-3">
                        <div class="w-8 aspect-[3/4] rounded shadow-sm overflow-hidden bg-parchment shrink-0 border-l border-black/25">
                          <img v-if="book.imagePath" :src="'/' + book.imagePath" class="w-full h-full object-cover">
                          <div v-else :style="getBookCoverStyle(book)" class="w-full h-full text-[4px] text-white p-0.5 truncate font-bold text-center">
                            {{ book.title }}
                          </div>
                        </div>
                        <div>
                          <strong>{{ book.title }}</strong>
                          <p class="text-[10px] text-midnight opacity-70 mt-0.5">{{ book.author }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-3">{{ book.genre }}</td>
                    <td class="px-4 py-3">{{ book.publishedYear }}</td>
                    <td class="px-4 py-3">
                      <span :class="['px-2 py-0.5 font-bold rounded-full', book.stock > 0 ? 'bg-teal/10 text-teal' : 'bg-danger/10 text-danger']">
                        {{ book.stock }} / {{ book.totalStock }}
                      </span>
                    </td>
                    <td v-if="currentUser.role === 'Pustakawan'" class="px-4 py-3">
                      <div class="flex gap-2">
                        <button @click="openEditBookModal(book)" class="px-2.5 py-1.5 bg-midnight hover:bg-black text-white rounded font-bold"><i class="fa-solid fa-edit"></i> Edit</button>
                        <button @click="restockBook(book.id)" class="px-2.5 py-1.5 bg-teal hover:bg-teal-dark text-white rounded font-bold" title="Restock +1"><i class="fa-solid fa-plus-circle"></i></button>
                        <button @click="deleteBook(book.id)" class="px-2.5 py-1.5 bg-danger hover:bg-danger-dark text-white rounded font-bold" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Logs Tab -->
        <div v-if="databaseSubTab === 'logs'" class="space-y-4">
          <div class="bg-white rounded-xl border border-parchment-dark shadow-sm p-4 max-h-[500px] overflow-y-auto space-y-4">
            <div v-for="(log, idx) in activityLogs" :key="idx" class="flex gap-4 p-3 hover:bg-parchment-light rounded-lg border border-transparent hover:border-parchment-dark transition-all">
              <div class="shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-white text-sm"
                   :class="[
                     log.activity.includes('Login') || log.activity.includes('Logout') ? 'bg-midnight' : '',
                     log.activity.includes('Peminjaman') ? 'bg-teal' : '',
                     log.activity.includes('Pengembalian') ? 'bg-teal-dark' : '',
                     log.activity.includes('Simulasi') ? 'bg-amber' : '',
                     log.activity.includes('Keterlambatan') || log.activity.includes('Sanksi') ? 'bg-danger' : 'bg-teal'
                   ]">
                <i v-if="log.activity.includes('Login')" class="fa-solid fa-right-to-bracket"></i>
                <i v-else-if="log.activity.includes('Logout')" class="fa-solid fa-right-from-bracket"></i>
                <i v-else-if="log.activity.includes('Peminjaman')" class="fa-solid fa-book-bookmark"></i>
                <i v-else-if="log.activity.includes('Pengembalian')" class="fa-solid fa-rotate-left"></i>
                <i v-else-if="log.activity.includes('Simulasi')" class="fa-solid fa-clock"></i>
                <i v-else-if="log.activity.includes('Hapus') || log.activity.includes('Sanksi')" class="fa-solid fa-trash-can"></i>
                <i v-else class="fa-solid fa-circle-info"></i>
              </div>
              <div class="text-left">
                <div class="flex items-center gap-2">
                  <span class="font-extrabold text-sm text-midnight">{{ log.activity }}</span>
                  <span class="text-[10px] text-midnight opacity-75 font-mono">{{ formatDisplayDateTime(log.time) }}</span>
                </div>
                <p class="text-xs text-midnight opacity-80 mt-1">{{ log.detail }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 6. DATA PEMINJAM SECTION (LIBRARIAN ONLY) -->
      <section v-if="activeTab === 'admin-loans' && currentUser.role === 'Pustakawan'" class="animate-fadeIn space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-xl font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-book-bookmark text-teal"></i> Administrasi Peminjaman Buku</h2>
          <button @click="openAddLoanModal" class="px-4 py-2 bg-teal hover:bg-teal-dark text-white rounded-lg font-bold text-xs shadow transition-colors flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Catat Peminjaman Baru
          </button>
        </div>

        <div class="bg-white rounded-xl border border-parchment-dark shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
              <thead class="bg-parchment-light text-midnight font-bold border-b border-parchment-dark">
                <tr>
                  <th class="px-4 py-3">Buku</th>
                  <th class="px-4 py-3">Peminjam (Mahasiswa)</th>
                  <th class="px-4 py-3">Kontak Anggota</th>
                  <th class="px-4 py-3">Tanggal Pinjam</th>
                  <th class="px-4 py-3">Jatuh Tempo</th>
                  <th class="px-4 py-3">Status</th>
                  <th class="px-4 py-3">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-parchment-dark text-midnight font-medium">
                <tr v-if="allLoans.length === 0">
                  <td colspan="7" class="text-center py-6 text-midnight opacity-55">Tidak ada data peminjaman aktif.</td>
                </tr>
                <tr v-for="loan in allLoans" :key="loan.id" class="hover:bg-parchment-light">
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 aspect-[3/4] rounded shadow-sm overflow-hidden bg-parchment shrink-0 border-l border-black/25">
                        <img v-if="getBookById(loan.bookId)?.imagePath" :src="'/' + getBookById(loan.bookId).imagePath" class="w-full h-full object-cover">
                        <div v-else :style="getBookCoverStyle(getBookById(loan.bookId))" class="w-full h-full text-[4px] text-white p-0.5 truncate font-bold text-center">
                          {{ loan.bookTitle }}
                        </div>
                      </div>
                      <div>
                        <strong @click="openBookDetails(loan.bookId)" class="text-midnight hover:text-teal cursor-pointer">{{ loan.bookTitle }}</strong>
                        <p class="text-[10px] text-midnight opacity-70 mt-0.5">{{ getBookById(loan.bookId)?.author }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <strong class="block">{{ loan.userName }}</strong>
                    <span class="text-[10px] text-midnight opacity-75">NIM: {{ loan.userNim }}</span>
                  </td>
                  <td class="px-4 py-3">
                    <p class="block"><i class="fa-solid fa-phone text-teal text-[10px]"></i> {{ loan.userPhone || '-' }}</p>
                    <p class="text-[10px] text-midnight opacity-75"><i class="fa-solid fa-envelope text-teal text-[10px]"></i> {{ loan.userEmail }}</p>
                  </td>
                  <td class="px-4 py-3">{{ formatDisplayDate(loan.borrowDate) }}</td>
                  <td class="px-4 py-3">{{ formatDisplayDate(loan.dueDate) }}</td>
                  <td class="px-4 py-3">
                    <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold', loan.status === 'overdue' ? 'bg-danger/10 text-danger' : 'bg-teal/10 text-teal']">
                      {{ loan.status === 'overdue' ? 'Terlambat' : 'Aktif' }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex gap-2">
                      <button @click="openEditLoanModal(loan)" class="px-2 py-1 bg-midnight hover:bg-black text-white rounded font-bold"><i class="fa-solid fa-edit"></i></button>
                      <button @click="adminReturnLoan(loan.id)" class="px-2 py-1 bg-teal hover:bg-teal-dark text-white rounded font-bold" title="Kembalikan Buku"><i class="fa-solid fa-rotate-left"></i></button>
                      <button @click="adminDestroyLoan(loan.id)" class="px-2 py-1 bg-danger hover:bg-danger-dark text-white rounded font-bold" title="Hapus Transaksi"><i class="fa-solid fa-trash-can"></i></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- 7. DATA MAHASISWA SECTION (LIBRARIAN ONLY) -->
      <section v-if="activeTab === 'admin-students' && currentUser.role === 'Pustakawan'" class="animate-fadeIn space-y-6">
        <div class="flex justify-between items-center">
          <h2 class="text-xl font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-users text-teal"></i> Manajemen Akun Mahasiswa</h2>
          <button @click="openAddStudentModal" class="px-4 py-2 bg-teal hover:bg-teal-dark text-white rounded-lg font-bold text-xs shadow transition-colors flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Tambah Mahasiswa
          </button>
        </div>

        <div class="bg-white rounded-xl border border-parchment-dark shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
              <thead class="bg-parchment-light text-midnight font-bold border-b border-parchment-dark">
                <tr>
                  <th class="px-4 py-3">NIM</th>
                  <th class="px-4 py-3">Nama</th>
                  <th class="px-4 py-3">Fakultas & Prodi</th>
                  <th class="px-4 py-3">No. HP</th>
                  <th class="px-4 py-3">Email Akademik</th>
                  <th class="px-4 py-3">Kata Sandi</th>
                  <th class="px-4 py-3">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-parchment-dark text-midnight font-medium">
                <tr v-if="studentsList.length === 0">
                  <td colspan="7" class="text-center py-6 text-midnight opacity-55">Tidak ada mahasiswa terdaftar.</td>
                </tr>
                <tr v-for="student in studentsList" :key="student.id" class="hover:bg-parchment-light">
                  <td class="px-4 py-3 font-mono font-bold text-teal">{{ student.nim }}</td>
                  <td class="px-4 py-3 font-bold text-midnight">{{ student.name }}</td>
                  <td class="px-4 py-3">
                    <strong class="block text-[11px]">{{ student.faculty }}</strong>
                    <span class="text-[10px] text-midnight opacity-75">{{ student.prodi }}</span>
                  </td>
                  <td class="px-4 py-3">{{ student.phone || '-' }}</td>
                  <td class="px-4 py-3 font-mono font-bold text-midnight">{{ student.email }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5 font-mono text-[11px] font-bold bg-teal/10 text-teal px-2 py-1 rounded w-fit border border-teal/20">
                      <i class="fa-solid fa-key text-[10px] opacity-75"></i>
                      <span>{{ student.password_plain || 'password123' }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex gap-2">
                      <button @click="openEditStudentModal(student)" class="px-3 py-1.5 bg-midnight hover:bg-black text-white rounded font-bold flex items-center gap-1">
                        <i class="fa-solid fa-edit"></i> Edit / Reset
                      </button>
                      <button @click="deleteStudent(student.id)" class="px-3 py-1.5 bg-danger hover:bg-danger-dark text-white rounded font-bold flex items-center gap-1">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- 7.5 GOOGLE DRIVE BACKUP SECTION (LIBRARIAN ONLY) -->
      <section v-if="activeTab === 'admin-backup' && currentUser.role === 'Pustakawan'" class="animate-fadeIn space-y-6">
        <div class="flex justify-between items-center border-b border-parchment-dark pb-3">
          <h2 class="text-xl font-bold text-midnight flex items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up text-teal"></i> Backup & Sinkronisasi Google Drive
          </h2>
          <span class="text-xs font-semibold text-teal bg-teal/10 px-3 py-1 rounded-full">
            Admin Portal
          </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left side: Connection status & Trigger button -->
          <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-parchment-dark shadow-sm space-y-6">
              <h3 class="text-sm font-bold text-midnight border-b pb-2 flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-teal"></i> Status Koneksi
              </h3>

              <!-- Status Indicator -->
              <div class="flex items-center gap-4 bg-parchment-light p-4 rounded-xl border border-parchment-dark">
                <div :class="['h-10 w-10 rounded-full flex items-center justify-center text-white shrink-0', backupStatus.credentials_configured ? 'bg-teal' : 'bg-danger']">
                  <i :class="['fa-solid text-lg', backupStatus.credentials_configured ? 'fa-circle-check' : 'fa-triangle-exclamation']"></i>
                </div>
                <div class="text-left">
                  <h4 class="font-bold text-xs text-midnight">Kredensial API Google</h4>
                  <p :class="['text-[11px] font-semibold mt-0.5', backupStatus.credentials_configured ? 'text-teal' : 'text-danger']">
                    {{ backupStatus.credentials_configured ? 'Terkonfigurasi (Aktif)' : 'Belum Terkonfigurasi' }}
                  </p>
                </div>
              </div>

              <!-- Service Account Email -->
              <div v-if="backupStatus.credentials_configured && backupStatus.client_email" class="space-y-1.5 text-left">
                <label class="block text-[11px] font-bold text-midnight opacity-75">Email Service Account:</label>
                <div class="flex items-center gap-2 bg-parchment-light px-3 py-2 border border-parchment-dark rounded-lg text-xs font-mono select-all overflow-hidden relative font-bold">
                  <span class="truncate pr-8 w-full" :title="backupStatus.client_email">{{ backupStatus.client_email }}</span>
                  <button @click="copyToClipboard(backupStatus.client_email)" class="absolute right-2 text-teal hover:text-teal-dark" title="Salin Email">
                    <i class="fa-regular fa-copy"></i>
                  </button>
                </div>
                <p class="text-[9px] text-midnight opacity-70 leading-normal">
                  <span class="text-teal font-semibold">*Penting:</span> Bagikan folder Google Drive Anda ke email di atas sebagai **Editor** agar sistem dapat melakukan sinkronisasi otomatis.
                </p>
              </div>

              <!-- Backup Button -->
              <div class="space-y-3">
                <button 
                  @click="runBackup" 
                  :disabled="backupLoading || !backupStatus.credentials_configured"
                  :class="['w-full py-3 text-white rounded-xl font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2', 
                           backupLoading ? 'bg-teal/50 cursor-not-allowed' : (backupStatus.credentials_configured ? 'bg-teal hover:bg-teal-dark active:scale-[0.98]' : 'bg-midnight/45 cursor-not-allowed')]"
                >
                  <i v-if="backupLoading" class="fa-solid fa-spinner fa-spin"></i>
                  <i v-else class="fa-solid fa-play"></i>
                  <span>{{ backupLoading ? 'Proses Backup Sedang Berjalan...' : 'Mulai Backup Data Sekarang' }}</span>
                </button>

                <button 
                  @click="downloadLocalBackup"
                  class="w-full py-3 bg-teal hover:bg-teal-dark text-white rounded-xl font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2 active:scale-[0.98]"
                >
                  <i class="fa-solid fa-file-excel"></i>
                  <span>Unduh File Excel (.xlsx) Langsung</span>
                </button>

                <a :href="backupStatus.folder_url" target="_blank" class="w-full py-2.5 bg-midnight hover:bg-black text-white rounded-xl font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                  <i class="fa-solid fa-folder-open text-amber"></i>
                  <span>Buka Folder Google Drive</span>
                </a>
              </div>
            </div>

            <!-- Backup Progress Step Tracker -->
            <div v-if="backupLoading || backupProgressSteps.length > 0" class="bg-white p-6 rounded-2xl border border-teal/20 shadow-premium text-left space-y-4">
              <h3 class="text-xs font-bold text-midnight uppercase tracking-wider">Log Aktivitas Backup</h3>
              <div class="space-y-3">
                <div v-for="(step, idx) in backupProgressSteps" :key="idx" class="flex gap-3 text-xs items-start">
                  <div class="mt-0.5 shrink-0">
                    <i v-if="step.status === 'pending'" class="fa-regular fa-circle text-midnight opacity-40"></i>
                    <i v-else-if="step.status === 'running'" class="fa-solid fa-circle-notch fa-spin text-teal"></i>
                    <i v-else-if="step.status === 'success'" class="fa-solid fa-circle-check text-teal"></i>
                    <i v-else class="fa-solid fa-circle-xmark text-danger"></i>
                  </div>
                  <div>
                    <p :class="['font-semibold', step.status === 'running' ? 'text-teal' : (step.status === 'success' ? 'text-midnight' : (step.status === 'failed' ? 'text-danger' : 'text-midnight opacity-60'))]">{{ step.message }}</p>
                    <span v-if="step.time" class="text-[9px] text-midnight opacity-50 font-mono">{{ step.time }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right side: Setup instructions & Details checklist -->
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-parchment-dark shadow-sm text-left space-y-5">
              <h3 class="text-sm font-bold text-midnight border-b pb-2 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-teal"></i> Panduan Setup & Sinkronisasi
              </h3>

              <div class="space-y-4 text-xs text-midnight leading-relaxed">
                <!-- Checklist Step 1 -->
                <div class="flex gap-4">
                  <div class="h-6 w-6 bg-teal/10 text-teal font-extrabold flex items-center justify-center rounded-full shrink-0">1</div>
                  <div>
                    <h4 class="font-bold text-xs">Buat Google Cloud Service Account</h4>
                    <p class="mt-1 opacity-85">Akses <a href="https://console.cloud.google.com/" target="_blank" class="text-teal hover:underline font-bold">Google Cloud Console</a>, buat project baru, cari dan aktifkan <strong>Google Drive API</strong>, lalu buat <strong>Service Account</strong> baru pada menu Credentials.</p>
                  </div>
                </div>

                <!-- Checklist Step 2 -->
                <div class="flex gap-4">
                  <div class="h-6 w-6 bg-teal/10 text-teal font-extrabold flex items-center justify-center rounded-full shrink-0">2</div>
                  <div>
                    <h4 class="font-bold text-xs">Unduh File Kunci Kredensial JSON</h4>
                    <p class="mt-1 opacity-85">Buka halaman akun layanan yang baru dibuat, masuk ke tab <strong>Keys</strong>, klik <strong>Add Key</strong> > <strong>Create new key</strong>, pilih format <strong>JSON</strong>, lalu unduh kuncinya.</p>
                  </div>
                </div>

                <!-- Checklist Step 3 -->
                <div class="flex gap-4">
                  <div class="h-6 w-6 bg-teal/10 text-teal font-extrabold flex items-center justify-center rounded-full shrink-0">3</div>
                  <div>
                    <h4 class="font-bold text-xs">Simpan Kredensial di Server</h4>
                    <p class="mt-1 opacity-85">Ganti nama file kunci yang diunduh menjadi <code class="bg-parchment-dark px-1.5 py-0.5 rounded font-mono font-bold text-[11px]">google-credentials.json</code> dan simpan ke folder <code class="bg-parchment-dark px-1.5 py-0.5 rounded font-mono font-bold text-[11px]">storage/app/</code> di direktori root aplikasi Laravel ini.</p>
                  </div>
                </div>

                <!-- Checklist Step 4 -->
                <div class="flex gap-4">
                  <div class="h-6 w-6 bg-teal/10 text-teal font-extrabold flex items-center justify-center rounded-full shrink-0">4</div>
                  <div>
                    <h4 class="font-bold text-xs">Bagikan Akses Folder Google Drive</h4>
                    <p class="mt-1 opacity-85">Buka folder Google Drive Anda: <a :href="backupStatus.folder_url" target="_blank" class="text-teal hover:underline font-bold font-mono">1k0oyuX4CWvlnt_zppvbwHUWEnsz_132i</a>. Bagikan folder tersebut ke email Service Account (lihat kolom kiri) dengan tingkat akses <strong>Editor</strong>.</p>
                  </div>
                </div>
              </div>

              <!-- Alert Info -->
              <div class="bg-teal/5 border border-teal/15 p-4 rounded-xl flex gap-3 text-left">
                <i class="fa-solid fa-circle-info text-teal text-base shrink-0 mt-0.5"></i>
                <div class="text-xs">
                  <h4 class="font-bold text-midnight">Format File Hasil Backup</h4>
                  <p class="opacity-80 mt-1 leading-normal">Setiap kali proses backup dijalankan, sistem akan mengunggah data lengkap dalam dua format sekaligus:</p>
                  <ul class="list-disc pl-5 mt-1.5 space-y-1 opacity-80 leading-normal">
                    <li><strong>Workbook Excel (.xlsx)</strong>: Berisi 5 lembar kerja (Sheet) untuk masing-masing tabel database (Buku, Anggota, Peminjaman, Antrean, Log).</li>
                    <li><strong>Google Sheets (Spreadsheet)</strong>: File terkonversi yang dapat langsung dibuka, diedit, dan dibagikan secara online di Google Drive.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- 8. NOTIFICATIONS SECTION -->
      <section v-if="activeTab === 'notifications'" class="animate-fadeIn space-y-6">
        <h2 class="text-xl font-bold text-midnight flex items-center gap-2"><i class="fa-solid fa-bell text-teal"></i> Pusat Notifikasi</h2>
        
        <div class="bg-white rounded-xl border border-parchment-dark shadow-sm p-4 space-y-4 max-w-3xl">
          <div v-if="notifications.length === 0" class="text-center py-12 text-midnight opacity-55">
            <i class="fa-solid fa-bell-slash text-4xl text-teal mb-3 block"></i>
            <p class="font-bold">Tidak ada notifikasi aktif untuk akun Anda.</p>
          </div>
          <div v-for="notif in notifications" :key="notif.id" @click="markNotificationRead(notif.id)" :class="['p-4 rounded-xl border flex gap-4 transition-all cursor-pointer', notif.unread ? 'bg-teal/5 border-teal/20 font-bold' : 'bg-white border-parchment-dark opacity-85']">
            <div class="shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-white"
                 :class="[
                   notif.type === 'warning' ? 'bg-danger' : '',
                   notif.type === 'hold' ? 'bg-amber' : '',
                   notif.type === 'info' || !notif.type ? 'bg-teal' : ''
                 ]">
              <i v-if="notif.type === 'warning'" class="fa-solid fa-triangle-exclamation"></i>
              <i v-else-if="notif.type === 'hold'" class="fa-solid fa-clock-rotate-left"></i>
              <i v-else class="fa-solid fa-circle-check"></i>
            </div>
            <div class="text-left flex-grow">
              <div class="flex justify-between items-start">
                <h4 class="text-sm text-midnight">{{ notif.title }}</h4>
                <span class="text-[9px] text-midnight opacity-65 font-mono">{{ formatDisplayDateTime(notif.time) }}</span>
              </div>
              <p class="text-xs text-midnight opacity-80 mt-1 font-medium">{{ notif.message }}</p>
            </div>
          </div>
        </div>
      </section>

    </main>

    <!-- FOOTER -->
    <footer class="bg-midnight text-white py-6 border-t border-black border-opacity-30 text-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-2 text-parchment text-opacity-60">
        <p>&copy; 2026 Universitas Nahdlatul Ulama Nusa Tenggara Barat (UNU NTB). Hak Cipta Dilindungi.</p>
        <p class="font-bold text-teal-light">Perpustakaan Cerdas, Terintegrasi, & Aman</p>
      </div>
    </footer>

    <!-- MODAL 1: BOOK DETAILS -->
    <div v-if="showDetailsModal && selectedBook" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-5 md:p-8 relative flex flex-col md:flex-row gap-6 shadow-premium border border-parchment-dark animate-scaleUp">
        <button @click="showDetailsModal = false" class="absolute top-4 right-4 text-midnight opacity-70 hover:opacity-100 text-xl font-bold"><i class="fa-solid fa-times"></i></button>
        
        <!-- Book Cover in Details -->
        <div class="w-32 md:w-48 aspect-[3/4] rounded-xl overflow-hidden shadow-book bg-parchment shrink-0 border-l-4 border-black/25 mx-auto md:mx-0">
          <img v-if="selectedBook.imagePath" :src="'/' + selectedBook.imagePath" class="w-full h-full object-cover">
          <div v-else :style="getBookCoverStyle(selectedBook)" class="w-full h-full p-6 flex flex-col justify-between text-white relative">
            <div class="absolute inset-0 bg-gradient-to-r from-black/25 via-transparent to-transparent pointer-events-none"></div>
            <span class="border border-white/45 px-1.5 py-0.5 rounded text-[10px] uppercase font-bold self-start tracking-wider">{{ selectedBook.genre }}</span>
            <div class="my-auto text-center">
              <p class="font-extrabold text-lg leading-snug" :style="{ color: selectedBook.coverStyle.text }">{{ selectedBook.title }}</p>
              <p class="text-xs opacity-90 mt-1" :style="{ color: selectedBook.coverStyle.text }">{{ selectedBook.author }}</p>
            </div>
            <span class="text-[10px] font-bold tracking-widest text-center uppercase" :style="{ color: selectedBook.coverStyle.accent || '#fff' }">UNU NTB</span>
          </div>
        </div>

        <!-- Details Info -->
        <div class="flex-grow flex flex-col justify-between text-left">
          <div class="space-y-4">
            <div>
              <span class="text-xs text-teal font-extrabold tracking-wide uppercase">{{ selectedBook.genre }} &bull; {{ selectedBook.publishedYear }}</span>
              <h3 class="text-xl font-black text-midnight mt-1 leading-tight">{{ selectedBook.title }}</h3>
              <p class="text-sm font-semibold text-midnight opacity-75 mt-0.5">Penulis: {{ selectedBook.author }}</p>
            </div>

            <div>
              <h4 class="text-xs font-bold text-midnight uppercase tracking-wider mb-1">Sinopsis / Deskripsi</h4>
              <p class="text-xs leading-relaxed text-midnight opacity-80 text-justify">{{ selectedBook.description }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 bg-parchment-light p-3 rounded-lg border border-parchment-dark">
              <div>
                <span class="block text-[10px] text-midnight opacity-70">Salinan Tersedia</span>
                <strong :class="['text-sm font-black', selectedBook.stock > 0 ? 'text-teal' : 'text-danger']">
                  {{ selectedBook.stock }} Buku (dari total {{ selectedBook.totalStock }})
                </strong>
              </div>
              <div>
                <span class="block text-[10px] text-midnight opacity-70">Peringkat Popularitas</span>
                <strong class="text-sm font-black text-amber"><i class="fa-solid fa-star"></i> {{ selectedBook.popularity }} / 100</strong>
              </div>
              <div v-if="selectedBook.classNumber">
                <span class="block text-[10px] text-midnight opacity-70">No. Klasifikasi (DDC)</span>
                <strong class="text-xs font-black text-midnight">{{ selectedBook.classNumber }}</strong>
              </div>
              <div v-if="selectedBook.isbn">
                <span class="block text-[10px] text-midnight opacity-70">ISBN / ISSN</span>
                <strong class="text-xs font-black text-midnight">{{ selectedBook.isbn }}</strong>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-6 pt-4 border-t border-parchment-dark flex flex-wrap gap-3">
            <button @click="toggleWishlist(selectedBook.id)" class="px-4 py-2 border border-danger hover:bg-danger/10 text-danger rounded-lg font-bold text-xs transition-colors flex items-center gap-1">
              <i class="fa-solid fa-heart"></i>
              <span>{{ isWishlisted(selectedBook.id) ? 'Hapus Wishlist' : 'Tambah Wishlist' }}</span>
            </button>
            <button v-if="selectedBook.stock > 0" @click="borrowBook(selectedBook.id)" class="px-5 py-2 bg-teal hover:bg-teal-dark text-white rounded-lg font-bold text-xs shadow transition-colors flex-grow">Pinjam Instan</button>
            <button v-else @click="joinReservation(selectedBook.id)" class="px-5 py-2 bg-amber hover:bg-amber-dark text-white rounded-lg font-bold text-xs shadow transition-colors flex-grow">Gabung Antrean Reservasi</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: STUDENT ADD/EDIT (LIBRARIAN ONLY) -->
    <div v-if="showStudentModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 md:p-8 relative shadow-premium border border-parchment-dark animate-scaleUp">
        <button @click="showStudentModal = false" class="absolute top-4 right-4 text-midnight opacity-70 hover:opacity-100 text-xl font-bold"><i class="fa-solid fa-times"></i></button>
        <h3 class="text-lg font-bold text-midnight mb-6 border-b pb-2">
          {{ editingId ? 'Edit Data Mahasiswa' : 'Tambah Mahasiswa Baru' }}
        </h3>
        
        <form @submit.prevent="saveStudent" class="space-y-4 text-left">
          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Nama Lengkap</label>
            <input type="text" v-model="studentForm.name" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
          </div>

          <div class="flex gap-4">
            <div class="flex-1">
              <label class="block text-xs font-bold text-midnight mb-1">NIM</label>
              <input type="text" v-model="studentForm.nim" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
            <div class="flex-1">
              <label class="block text-xs font-bold text-midnight mb-1">Nomor HP</label>
              <input type="text" v-model="studentForm.phone" class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-midnight mb-1">E-mail Akademik</label>
            <input type="email" v-model="studentForm.email" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
          </div>

          <div class="flex gap-4">
            <div class="flex-1">
              <label class="block text-xs font-bold text-midnight mb-1">Fakultas</label>
              <select v-model="studentForm.faculty" required class="w-full px-2 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight cursor-pointer">
                <option value="">-- Pilih --</option>
                <option v-for="(prodis, fac) in facultyProdiMapping" :key="fac" :value="fac">{{ fac }}</option>
              </select>
            </div>
            <div class="flex-1">
              <label class="block text-xs font-bold text-midnight mb-1">Prodi</label>
              <select v-model="studentForm.prodi" required class="w-full px-2 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight cursor-pointer">
                <option value="">-- Pilih --</option>
                <option v-for="prodi in getFacultyProdis(studentForm.faculty)" :key="prodi" :value="prodi">{{ prodi }}</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-midnight mb-1">
              {{ editingId ? 'Kata Sandi Baru (Kosongkan jika tidak diubah)' : 'Kata Sandi Baru' }}
            </label>
            <div class="relative flex items-center">
              <input :type="showAdminStudentPassword ? 'text' : 'password'" v-model="studentForm.password" :required="!editingId" :minlength="!editingId || studentForm.password ? 6 : null" placeholder="Minimal 6 karakter" class="w-full pl-3 pr-10 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
              <button type="button" @click="showAdminStudentPassword = !showAdminStudentPassword" class="absolute right-3 text-teal hover:text-teal-dark focus:outline-none z-10 flex items-center">
                <i class="fa-solid" :class="showAdminStudentPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
            <!-- If editing, show the current password -->
            <div v-if="editingId && currentStudentPasswordPlain" class="mt-2 p-2 bg-parchment-light border border-parchment-dark rounded flex justify-between items-center text-[10px]">
              <span class="text-midnight opacity-75 font-bold">Kata sandi saat ini:</span>
              <span class="font-mono font-bold text-teal bg-white px-2 py-0.5 border border-parchment-dark rounded select-all">{{ currentStudentPasswordPlain }}</span>
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-3">
            <button type="button" @click="showStudentModal = false" class="px-4 py-2 border border-parchment-dark text-midnight rounded font-bold text-xs hover:bg-parchment-light transition-colors">Batal</button>
            <button type="submit" class="px-5 py-2 bg-teal hover:bg-teal-dark text-white rounded font-bold text-xs shadow transition-colors">Simpan Data</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 3: LOAN ADD/EDIT (LIBRARIAN ONLY) -->
    <div v-if="showLoanModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 md:p-8 relative shadow-premium border border-parchment-dark animate-scaleUp">
        <button @click="showLoanModal = false" class="absolute top-4 right-4 text-midnight opacity-70 hover:opacity-100 text-xl font-bold"><i class="fa-solid fa-times"></i></button>
        <h3 class="text-lg font-bold text-midnight mb-6 border-b pb-2">
          {{ editingId ? 'Edit Detail Peminjaman' : 'Catat Peminjaman Baru' }}
        </h3>
        
        <form @submit.prevent="saveLoan" class="space-y-4 text-left">
          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Pilih Mahasiswa</label>
            <select v-model="loanForm.userId" required :disabled="editingId !== null" class="w-full px-2 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight cursor-pointer">
              <option value="">-- Pilih Mahasiswa --</option>
              <option v-for="student in studentsList" :key="student.id" :value="student.id">
                {{ student.name }} (NIM: {{ student.nim }} | Telp: {{ student.phone || '-' }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Pilih Buku</label>
            <select v-model="loanForm.bookId" required :disabled="editingId !== null" class="w-full px-2 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight cursor-pointer">
              <option value="">-- Pilih Buku --</option>
              <option v-for="book in books" :key="book.id" :value="book.id" :disabled="book.stock <= 0 && !editingId">
                {{ book.title }} (Stok: {{ book.stock }})
              </option>
            </select>
          </div>

          <div class="flex gap-4">
            <div class="flex-1">
              <label class="block text-xs font-bold text-midnight mb-1">Tanggal Pinjam</label>
              <input type="date" v-model="loanForm.borrowDate" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
            <div class="flex-1">
              <label class="block text-xs font-bold text-midnight mb-1">Tanggal Jatuh Tempo</label>
              <input type="date" v-model="loanForm.dueDate" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
          </div>

          <div v-if="editingId">
            <label class="block text-xs font-bold text-midnight mb-1">Status Transaksi</label>
            <select v-model="loanForm.status" required class="w-full px-2 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight cursor-pointer">
              <option value="active">Aktif</option>
              <option value="overdue">Terlambat</option>
              <option value="returned">Sudah Kembali</option>
            </select>
          </div>

          <div class="pt-4 flex justify-end gap-3">
            <button type="button" @click="showLoanModal = false" class="px-4 py-2 border border-parchment-dark text-midnight rounded font-bold text-xs hover:bg-parchment-light transition-colors">Batal</button>
            <button type="submit" class="px-5 py-2 bg-teal hover:bg-teal-dark text-white rounded font-bold text-xs shadow transition-colors">Simpan Transaksi</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 4: BOOK ADD/EDIT (LIBRARIAN ONLY) -->
    <div v-if="showBookModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 md:p-8 relative shadow-premium border border-parchment-dark animate-scaleUp">
        <button @click="showBookModal = false" class="absolute top-4 right-4 text-midnight opacity-70 hover:opacity-100 text-xl font-bold"><i class="fa-solid fa-times"></i></button>
        <h3 class="text-lg font-bold text-midnight mb-6 border-b pb-2">
          {{ editingId ? 'Edit Detail Buku' : 'Tambah Buku Baru' }}
        </h3>
        
        <form @submit.prevent="saveBook" class="space-y-4 text-left">
          <div>
            <label class="block text-xs font-bold text-midnight mb-1">ID Buku (DDC / Kustom)</label>
            <input type="text" v-model="bookForm.id" :placeholder="editingId ? 'Masukkan ID...' : 'Kosongkan untuk otomatisasi DDC'" class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
          </div>

          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Judul Buku</label>
            <input type="text" v-model="bookForm.title" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
          </div>

          <div class="flex gap-4">
            <div class="flex-grow">
              <label class="block text-xs font-bold text-midnight mb-1">Penulis</label>
              <input type="text" v-model="bookForm.author" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
            <div class="w-24">
              <label class="block text-xs font-bold text-midnight mb-1">Tahun Terbit</label>
              <input type="number" v-model="bookForm.published_year" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex-grow">
              <label class="block text-xs font-bold text-midnight mb-1">Kategori / Genre</label>
              <input type="text" v-model="bookForm.genre" required placeholder="Filsafat, Drama, dll." class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
            <div class="w-24">
              <label class="block text-xs font-bold text-midnight mb-1">Stok Salinan</label>
              <input type="number" v-model="bookForm.stock" required class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
          </div>

          <div class="flex gap-4">
            <div class="flex-grow">
              <label class="block text-xs font-bold text-midnight mb-1">Nomor Klasifikasi (DDC)</label>
              <input type="text" v-model="bookForm.class_number" placeholder="Contoh: 615.4" class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
            <div class="w-36 flex-shrink-0">
              <label class="block text-xs font-bold text-midnight mb-1">ISBN / ISSN</label>
              <input type="text" v-model="bookForm.isbn" placeholder="Contoh: 978..." class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Deskripsi / Sinopsis</label>
            <textarea v-model="bookForm.description" required rows="3" class="w-full px-3 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight resize-none"></textarea>
          </div>

          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Unggah Gambar Sampul Buku (Opsional)</label>
            <input type="file" @change="handleBookImageUpload" accept="image/*" class="w-full text-xs text-midnight cursor-pointer border border-parchment-dark rounded px-2 py-1 bg-parchment-light">
          </div>

          <!-- Cover Color Style configuration (only if not using image) -->
          <div class="bg-parchment-light p-3 rounded-lg border border-parchment-dark space-y-3">
            <h4 class="text-[10px] font-bold text-midnight uppercase tracking-wider">Desain Sampul Vektor (Default Fallback)</h4>
            <div class="flex gap-4">
              <div class="flex-1">
                <label class="block text-[10px] text-midnight opacity-75 mb-1">Gradasi Awal</label>
                <input type="color" v-model="bookForm.coverStyle.bgStart" class="w-full h-8 cursor-pointer rounded border border-parchment-dark">
              </div>
              <div class="flex-1">
                <label class="block text-[10px] text-midnight opacity-75 mb-1">Gradasi Akhir</label>
                <input type="color" v-model="bookForm.coverStyle.bgEnd" class="w-full h-8 cursor-pointer rounded border border-parchment-dark">
              </div>
              <div class="flex-1">
                <label class="block text-[10px] text-midnight opacity-75 mb-1">Warna Teks</label>
                <input type="color" v-model="bookForm.coverStyle.text" class="w-full h-8 cursor-pointer rounded border border-parchment-dark">
              </div>
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-3">
            <button type="button" @click="showBookModal = false" class="px-4 py-2 border border-parchment-dark text-midnight rounded font-bold text-xs hover:bg-parchment-light transition-colors">Batal</button>
            <button type="submit" class="px-5 py-2 bg-teal hover:bg-teal-dark text-white rounded font-bold text-xs shadow transition-colors">Simpan Buku</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 5: CHANGE PASSWORD -->
    <div v-if="showChangePasswordModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 md:p-8 relative shadow-premium border border-parchment-dark animate-scaleUp">
        <button @click="showChangePasswordModal = false" class="absolute top-4 right-4 text-midnight opacity-70 hover:opacity-100 text-xl font-bold"><i class="fa-solid fa-times"></i></button>
        <h3 class="text-lg font-bold text-midnight mb-6 border-b pb-2">
          Ubah Kata Sandi Akun
        </h3>
        
        <form @submit.prevent="submitChangePassword" class="space-y-4 text-left">
          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Kata Sandi Saat Ini</label>
            <div class="relative flex items-center">
              <input :type="showCurrentPwd ? 'text' : 'password'" v-model="changePasswordForm.currentPassword" required class="w-full pl-3 pr-10 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight">
              <button type="button" @click="showCurrentPwd = !showCurrentPwd" class="absolute right-3 text-teal hover:text-teal-dark focus:outline-none z-10 flex items-center">
                <i class="fa-solid" :class="showCurrentPwd ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Kata Sandi Baru</label>
            <div class="relative flex items-center">
              <input :type="showNewPwd ? 'text' : 'password'" v-model="changePasswordForm.newPassword" required minlength="6" class="w-full pl-3 pr-10 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight" placeholder="Minimal 6 karakter">
              <button type="button" @click="showNewPwd = !showNewPwd" class="absolute right-3 text-teal hover:text-teal-dark focus:outline-none z-10 flex items-center">
                <i class="fa-solid" :class="showNewPwd ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-midnight mb-1">Konfirmasi Kata Sandi Baru</label>
            <div class="relative flex items-center">
              <input :type="showConfirmPwd ? 'text' : 'password'" v-model="changePasswordForm.confirmPassword" required minlength="6" class="w-full pl-3 pr-10 py-2 text-xs border border-parchment-dark rounded outline-none focus:border-teal text-midnight" placeholder="Ulangi kata sandi baru">
              <button type="button" @click="showConfirmPwd = !showConfirmPwd" class="absolute right-3 text-teal hover:text-teal-dark focus:outline-none z-10 flex items-center">
                <i class="fa-solid" :class="showConfirmPwd ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-3">
            <button type="button" @click="showChangePasswordModal = false" class="px-4 py-2 border border-parchment-dark text-midnight rounded font-bold text-xs hover:bg-parchment-light transition-colors">Batal</button>
            <button type="submit" class="px-5 py-2 bg-teal hover:bg-teal-dark text-white rounded font-bold text-xs shadow transition-colors">Perbarui Kata Sandi</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Sleek Bottom Navigation Bar for Mobile (Students Only) -->
    <div v-if="currentUser && currentUser.role === 'Mahasiswa'" class="md:hidden fixed bottom-0 left-0 right-0 bg-midnight/95 backdrop-blur-md border-t border-white/10 z-50 px-4 py-2.5 flex justify-around items-center text-white shadow-lg">
      <button @click="setViewTab('home')" :class="['flex flex-col items-center gap-1 focus:outline-none transition-colors', activeTab === 'home' ? 'text-teal-light' : 'text-parchment/65']">
        <i class="fa-solid fa-house text-base"></i>
        <span class="text-[9px] font-bold">Beranda</span>
      </button>
      <button @click="setViewTab('browse')" :class="['flex flex-col items-center gap-1 focus:outline-none transition-colors', activeTab === 'browse' ? 'text-teal-light' : 'text-parchment/65']">
        <i class="fa-solid fa-magnifying-glass text-base"></i>
        <span class="text-[9px] font-bold">Cari Buku</span>
      </button>
      <button @click="setViewTab('curator')" :class="['flex flex-col items-center gap-1 focus:outline-none transition-colors', activeTab === 'curator' ? 'text-teal-light' : 'text-parchment/65']">
        <i class="fa-solid fa-wand-magic-sparkles text-base"></i>
        <span class="text-[9px] font-bold">AI Curator</span>
      </button>
      <button @click="setViewTab('dashboard')" :class="['flex flex-col items-center gap-1 focus:outline-none transition-colors', activeTab === 'dashboard' ? 'text-teal-light' : 'text-parchment/65']">
        <i class="fa-solid fa-user-circle text-base"></i>
        <span class="text-[9px] font-bold">Dashboard</span>
      </button>
      <button @click="setViewTab('notifications')" :class="['flex flex-col items-center gap-1 focus:outline-none transition-colors relative', activeTab === 'notifications' ? 'text-teal-light' : 'text-parchment/65']">
        <i class="fa-solid fa-bell text-base"></i>
        <span v-if="unreadNotifsCount > 0" class="absolute -top-1.5 right-1.5 bg-danger text-white text-[8px] w-4.5 h-4.5 flex items-center justify-center rounded-full font-bold border border-midnight">
          {{ unreadNotifsCount }}
        </span>
        <span class="text-[9px] font-bold">Notifikasi</span>
      </button>
    </div>

  </div>
</template>

<script>
import axios from 'axios';

// Global interceptor: redirect to login on any 401 (session expired)
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      window.location.href = '/login';
      return new Promise(() => {}); // prevent further catch handling
    }
    return Promise.reject(error);
  }
);

export default {
  name: 'LibraryApp',
  data() {
    return {
      activeTab: 'home',
      databaseSubTab: 'books',
      toast: { show: false, message: '', type: 'info' },
      currentUser: {
        id: 0,
        name: '',
        nim: null,
        role: '',
        prodi: '',
        borrowedBooks: [],
        reservations: [],
        wishlist: []
      },
      books: [],
      notifications: [],
      activityLogs: [],
      simDateStr: '',
      currentSimTime: null,
      simTimerInterval: null,
      allLoans: [],
      allReservations: [],
      studentsList: [],
      librarianModeActive: false,
      heroSearchText: '',
      
      // Catalog Search & filters
      searchQuery: '',
      selectedGenres: [],
      availableOnly: false,
      sortKey: 'popularity',

      // AI Curator
      curatorGenre: 'any',
      curatorStyle: 'any',
      curatorKeywords: '',
      curatorLoading: false,
      curatorRecommendations: [],

      // Modals
      showDetailsModal: false,
      selectedBook: null,

      showStudentModal: false,
      showAdminStudentPassword: false,
      currentStudentPasswordPlain: '',
      showLoanModal: false,
      showBookModal: false,
      showChangePasswordModal: false,
      showCurrentPwd: false,
      showNewPwd: false,
      showConfirmPwd: false,
      editingId: null,

      changePasswordForm: {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      },

      // Form Models
      studentForm: {
        name: '',
        nim: '',
        email: '',
        phone: '',
        faculty: '',
        prodi: '',
        password: ''
      },
      loanForm: {
        userId: '',
        bookId: '',
        borrowDate: '',
        dueDate: '',
        status: 'active'
      },
      bookForm: {
        id: '',
        title: '',
        author: '',
        genre: '',
        class_number: '',
        isbn: '',
        published_year: 2026,
        stock: 1,
        description: '',
        coverStyle: {
          bgStart: '#2D4263',
          bgEnd: '#1C2D46',
          text: '#FFFFFF',
          accent: '#398E8E',
          pattern: 'border'
        },
        coverFile: null
      },

      facultyProdiMapping: {
        "Fakultas Kesehatan": [
          "S1 Farmasi",
          "S1 Ilmu Gizi",
          "D3 Rekam Medik dan Informasi Kesehatan (RMIK)"
        ],
        "Fakultas Pendidikan": [
          "S1 Pendidikan Guru Sekolah Dasar (PGSD)",
          "S1 Pendidikan Jasmani, Kesehatan dan Rekreasi (Penjaskesrek)",
          "S1 Pendidikan Sosiologi",
          "S1 Pendidikan Seni Drama, Tari, dan Musik (Sendratasik)"
        ],
        "Fakultas Teknik": [
          "S1 Teknik Informatika",
          "S1 Sistem Informasi",
          "S1 Teknik Lingkungan"
        ],
        "Fakultas Ekonomi": [
          "S1 Ekonomi Islam"
        ],
        "Fakultas Hukum": [
          "S1 Hukum Bisnis"
        ]
      },
      backupStatus: {
        credentials_configured: false,
        client_email: null,
        project_id: null,
        folder_id: '',
        folder_url: ''
      },
      backupLoading: false,
      backupProgressSteps: []
    }
  },
  computed: {
    userInitials() {
      if (!this.currentUser.name) return 'U';
      return this.currentUser.name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
    },
    unreadNotifsCount() {
      return this.notifications.filter(n => n.unread).length;
    },
    formattedSimDate() {
      if (!this.currentSimTime) return '';
      const date = new Date(this.currentSimTime);
      const datePart = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
      const timePart = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      return `${datePart}, ${timePart}`;
    },
    popularBooks() {
      return [...this.books].sort((a, b) => b.popularity - a.popularity).slice(0, 4);
    },
    recentBooks() {
      return [...this.books].sort((a, b) => b.publishedYear - a.publishedYear).slice(0, 4);
    },
    uniqueGenres() {
      return [...new Set(this.books.map(b => b.genre))];
    },
    outOfStockBooks() {
      return this.books.filter(b => b.stock === 0);
    },
    filteredBooks() {
      let result = this.books.filter(book => {
        const matchesSearch = book.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             book.author.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             book.genre.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             (book.id && book.id.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                             (book.classNumber && book.classNumber.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                             (book.isbn && book.isbn.toLowerCase().includes(this.searchQuery.toLowerCase()));
        const matchesGenre = this.selectedGenres.length === 0 || this.selectedGenres.includes(book.genre);
        const matchesAvailable = !this.availableOnly || book.stock > 0;
        return matchesSearch && matchesGenre && matchesAvailable;
      });

      if (this.sortKey === 'popularity') {
        result.sort((a, b) => b.popularity - a.popularity);
      } else if (this.sortKey === 'title-asc') {
        result.sort((a, b) => a.title.localeCompare(b.title));
      } else if (this.sortKey === 'title-desc') {
        result.sort((a, b) => b.title.localeCompare(a.title));
      } else if (this.sortKey === 'year-desc') {
        result.sort((a, b) => b.publishedYear - a.publishedYear);
      }
      return result;
    }
  },
  watch: {
    simDateStr(newVal) {
      if (newVal) {
        this.currentSimTime = new Date(newVal).getTime();
      }
    }
  },
  created() {
    this.fetchState();
    this.simTimerInterval = setInterval(() => {
      if (this.currentSimTime) {
        this.currentSimTime += 1000;
      }
    }, 1000);
    this.pollingInterval = setInterval(() => {
      this.fetchState();
    }, 5000);
  },
  beforeUnmount() {
    if (this.simTimerInterval) {
      clearInterval(this.simTimerInterval);
    }
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }
  },
  methods: {
    showToastMsg(message, type = 'info') {
      this.toast = { show: true, message, type };
      setTimeout(() => {
        this.toast.show = false;
      }, 4000);
    },
    async fetchState() {
      try {
        const res = await axios.get('/api/state');
        this.books = res.data.books;
        this.currentUser = res.data.user;
        this.notifications = res.data.notifications;
        this.activityLogs = res.data.logs;
        this.simDateStr = res.data.sim_date;
        this.allLoans = res.data.all_loans;
        this.allReservations = res.data.all_reservations;
        this.studentsList = res.data.students;

        // Auto librarian mode check
        if (this.currentUser.role === 'Pustakawan') {
          this.librarianModeActive = true;
        }
      } catch (err) {
        if (err.response && err.response.status === 401) {
          window.location.href = '/login';
          return;
        }
        this.showToastMsg('Gagal memuat status dari server.', 'danger');
      }
    },
    async setViewTab(tab) {
      this.activeTab = tab;
      await this.fetchState();
      if (tab === 'admin-backup') {
        await this.fetchBackupStatus();
      }
      window.scrollTo(0, 0);
    },
    async fetchBackupStatus() {
      if (this.currentUser.role !== 'Pustakawan') return;
      try {
        const res = await axios.get('/api/admin/backup-status');
        this.backupStatus = res.data;
      } catch (err) {
        console.error(err);
      }
    },
    async runBackup() {
      if (this.currentUser.role !== 'Pustakawan' || !this.backupStatus.credentials_configured) return;
      
      this.backupLoading = true;
      this.backupProgressSteps = [
        { message: 'Memulai ekspor database ke file Excel...', status: 'running', time: new Date().toLocaleTimeString() }
      ];

      try {
        const backupPromise = axios.post('/api/admin/backup-gdrive');
        
        setTimeout(() => {
          if (this.backupLoading && this.backupProgressSteps.length === 1) {
            this.backupProgressSteps[0].status = 'success';
            this.backupProgressSteps.push({
              message: 'Menghubungkan ke API Google Drive & Mengunggah File...',
              status: 'running',
              time: new Date().toLocaleTimeString()
            });
          }
        }, 1500);

        const res = await backupPromise;

        // Mark all previous steps as success
        this.backupProgressSteps.forEach(s => s.status = 'success');
        this.backupProgressSteps.push({
          message: res.data.message || 'Backup selesai diunggah!',
          status: 'success',
          time: new Date().toLocaleTimeString()
        });

        this.showToastMsg(res.data.message || 'Backup berhasil disimpan ke Google Drive!', 'success');
        await this.fetchState(); // Refresh circulation logs
      } catch (err) {
        if (this.backupProgressSteps.length > 0) {
          this.backupProgressSteps[this.backupProgressSteps.length - 1].status = 'failed';
        }
        
        const errMsg = err.response && err.response.data && err.response.data.message 
          ? err.response.data.message 
          : 'Terjadi kesalahan saat mencadangkan data.';
          
        this.backupProgressSteps.push({
          message: 'Error: ' + errMsg,
          status: 'failed',
          time: new Date().toLocaleTimeString()
        });

        this.showToastMsg(errMsg, 'danger');
      } finally {
        this.backupLoading = false;
      }
    },
    copyToClipboard(text) {
      if (!text) return;
      navigator.clipboard.writeText(text).then(() => {
        this.showToastMsg('Email Service Account disalin ke clipboard!', 'success');
      }).catch(err => {
        this.showToastMsg('Gagal menyalin email.', 'danger');
      });
    },
    downloadLocalBackup() {
      window.open('/api/admin/backup-download', '_blank');
      this.showToastMsg('Memulai unduhan file backup Excel...', 'success');
    },
    handleHeroSearch() {
      this.searchQuery = this.heroSearchText;
      this.setViewTab('browse');
    },
    getBookById(id) {
      return this.books.find(b => b.id === id);
    },
    getBookCoverStyle(book) {
      if (!book || !book.coverStyle) return {};
      return {
        background: `linear-gradient(135deg, ${book.coverStyle.bgStart}, ${book.coverStyle.bgEnd})`
      };
    },
    formatDisplayDate(dateStr) {
      if (!dateStr) return '-';
      const date = new Date(dateStr);
      return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    },
    formatDisplayDateTime(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    },
    getDaysDiff(dueDateStr) {
      const due = new Date(dueDateStr);
      const current = new Date(this.simDateStr);
      const diffTime = due.getTime() - current.getTime();
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    },
    getDaysDiffText(dueDateStr) {
      const diff = this.getDaysDiff(dueDateStr);
      if (diff < 0) return `Terlambat ${Math.abs(diff)} Hari`;
      if (diff <= 2) return `${diff} Hari Lagi (Kritis)`;
      return `${diff} Hari`;
    },
    getDaysDiffClass(dueDateStr) {
      const diff = this.getDaysDiff(dueDateStr);
      if (diff < 0) return 'text-danger';
      if (diff <= 2) return 'text-amber';
      return 'text-teal';
    },
    getDaysDiffBarClass(dueDateStr) {
      const diff = this.getDaysDiff(dueDateStr);
      if (diff < 0) return 'bg-danger';
      if (diff <= 2) return 'bg-amber';
      return 'bg-teal';
    },
    getProgressPercent(borrowDateStr, dueDateStr) {
      const borrow = new Date(borrowDateStr).getTime();
      const due = new Date(dueDateStr).getTime();
      const current = new Date(this.simDateStr).getTime();
      const total = due - borrow;
      const passed = current - borrow;
      return Math.min(100, Math.max(0, (passed / total) * 100));
    },
    isWishlisted(bookId) {
      return this.currentUser.wishlist.includes(bookId);
    },
    getFacultyProdis(faculty) {
      return this.facultyProdiMapping[faculty] || [];
    },

    // API Mutations
    async simulateTime(days) {
      try {
        const res = await axios.post('/api/simulation/time', { days });
        this.showToastMsg(res.data.message, 'success');
        await this.fetchState();
      } catch (err) {
        this.showToastMsg('Gagal merubah waktu simulasi.', 'danger');
      }
    },
    async borrowBook(bookId) {
      try {
        const res = await axios.post('/api/loans', { bookId });
        this.showToastMsg(res.data.message, 'success');
        this.showDetailsModal = false;
        await this.fetchState();
      } catch (err) {
        this.showToastMsg(err.response?.data?.message || 'Gagal meminjam buku.', 'danger');
      }
    },
    async returnBook(bookId) {
      try {
        const res = await axios.post('/api/loans/return', { bookId });
        this.showToastMsg(res.data.message, 'success');
        await this.fetchState();
      } catch (err) {
        this.showToastMsg(err.response?.data?.message || 'Gagal mengembalikan buku.', 'danger');
      }
    },
    async joinReservation(bookId) {
      try {
        const res = await axios.post('/api/reservations', { bookId });
        this.showToastMsg(res.data.message, 'success');
        this.showDetailsModal = false;
        await this.fetchState();
      } catch (err) {
        this.showToastMsg(err.response?.data?.message || 'Gagal bergabung antrean.', 'danger');
      }
    },
    async cancelReservation(bookId) {
      if (confirm('Apakah Anda yakin ingin membatalkan antrean reservasi Anda?')) {
        try {
          const res = await axios.post('/api/reservations/cancel', { bookId });
          this.showToastMsg(res.data.message, 'info');
          await this.fetchState();
        } catch (err) {
          this.showToastMsg('Gagal membatalkan reservasi.', 'danger');
        }
      }
    },
    async cancelReservationForUser(userId, bookId) {
      if (confirm('Apakah Anda yakin ingin membatalkan reservasi mahasiswa ini?')) {
        try {
          const res = await axios.post('/api/reservations/cancel', { userId, bookId });
          this.showToastMsg(res.data.message, 'info');
          await this.fetchState();
        } catch (err) {
          this.showToastMsg('Gagal membatalkan reservasi.', 'danger');
        }
      }
    },
    async toggleWishlist(bookId) {
      try {
        const res = await axios.post(`/api/wishlist/${bookId}/toggle`);
        this.showToastMsg(res.data.message, 'success');
        await this.fetchState();
      } catch (err) {
        this.showToastMsg('Gagal memproses wishlist.', 'danger');
      }
    },
    async markNotificationRead(id) {
      try {
        await axios.post(`/api/notifications/${id}/read`);
        await this.fetchState();
      } catch (err) {
        // fail silently
      }
    },

    // AI Curator Recommendation generator
    generateAIRecommendations() {
      this.curatorLoading = true;
      const keywords = this.curatorKeywords.toLowerCase().split(',').map(k => k.trim()).filter(k => k.length > 0);
      
      setTimeout(() => {
        let scored = this.books.map(book => {
          let score = 0;
          if (this.curatorGenre === 'any' || book.genre === this.curatorGenre) score += 12;
          
          keywords.forEach(keyword => {
            if (book.title.toLowerCase().includes(keyword)) score += 8;
            if (book.author.toLowerCase().includes(keyword)) score += 4;
            if (book.description.toLowerCase().includes(keyword)) score += 6;
          });
          
          if (this.curatorStyle === 'academic') {
            if (['Philosophy', 'Science', 'Technology'].includes(book.genre)) score += 5;
          } else if (this.curatorStyle === 'light') {
            if (book.genre === 'Drama') score += 5;
          } else if (this.curatorStyle === 'narrative') {
            if (['Historical Fiction', 'Magical Realism', 'Modern Literature'].includes(book.genre)) score += 5;
          }
          
          score += book.popularity * 0.05;
          return { book, score };
        });
        
        scored.sort((a, b) => b.score - a.score);
        const topRecs = scored.slice(0, 2);
        
        const reasoningMap = {
          "unu-001": "Sebagai pilihan sejarah berlatar nasionalisme Indonesia, buku ini memadukan narasi sastra tinggi yang sangat cocok dengan minat budaya Anda.",
          "unu-002": "Kisah drama inspiratif tentang mimpi anak-anak Belitung. Pilihan tepat bagi Anda yang menyukai bacaan humanis, hangat, dan motivasional.",
          "unu-003": "Sastra modern yang menantang batas pikiran konvensional. Cocok untuk memperluas pemahaman sosio-politik kontemporer.",
          "unu-004": "Mahakarya Tan Malaka yang memicu penalaran logis-materialis. Pilihan emas untuk peminat filsafat dan kajian kemerdekaan berpikir.",
          "unu-005": "Sebuah roman kemanusiaan bertema perjuangan aktivis. Menawarkan pemaparan historis yang menyentuh hati pembaca sastra sejarah.",
          "unu-006": "Memadukan dongeng lokal dengan realisme magis. Buku berbobot tinggi yang menuntut imajinasi sastra tingkat lanjut.",
          "unu-007": "Buku referensi standar emas algoritma komputer. Sangat direkomendasikan untuk menunjang karir rekayasa teknologi Anda.",
          "unu-008": "Pengantar aplikatif ilmu sains data. Membantu menguasai analisis statistik dan pemodelan data secara terstruktur.",
          "unu-009": "Mengulas batas epistemologi sains modern. Membantu akademisi mengkritisi landasan ilmiah di balik fakta.",
          "unu-010": "Membahas rahasia terdalam ruang, waktu, dan lubang hitam dalam bahasa populer yang menawan.",
          "unu-011": "Ulasan kosmis yang puitis dan universal. Menghubungkan sains dengan peradaban dan kesadaran diri manusia."
        };

        this.curatorRecommendations = topRecs.map(({ book, score }) => {
          return {
            book,
            score,
            reason: reasoningMap[book.id] || `Buku ini memiliki kecocokan tinggi (${Math.round(score)}%) dengan topik minat Anda.`
          };
        });

        this.curatorLoading = false;
      }, 1500);
    },

    // Modal Actions
    openBookDetails(bookId) {
      this.selectedBook = this.books.find(b => b.id === bookId);
      this.showDetailsModal = true;
    },

    // Librarian Book CRUD
    openAddBookModal() {
      this.editingId = null;
      this.bookForm = {
        id: '',
        title: '',
        author: '',
        genre: '',
        class_number: '',
        isbn: '',
        published_year: 2026,
        stock: 1,
        description: '',
        coverStyle: {
          bgStart: '#2D4263',
          bgEnd: '#1C2D46',
          text: '#FFFFFF',
          accent: '#398E8E',
          pattern: 'border'
        },
        coverFile: null
      };
      this.showBookModal = true;
    },
    openEditBookModal(book) {
      this.editingId = book.id;
      this.bookForm = {
        id: book.id,
        title: book.title,
        author: book.author,
        genre: book.genre,
        class_number: book.classNumber || '',
        isbn: book.isbn || '',
        published_year: book.publishedYear,
        stock: book.stock,
        description: book.description,
        coverStyle: book.coverStyle ? { ...book.coverStyle } : {
          bgStart: '#2D4263',
          bgEnd: '#1C2D46',
          text: '#FFFFFF',
          accent: '#398E8E',
          pattern: 'border'
        },
        coverFile: null
      };
      this.showBookModal = true;
    },
    handleBookImageUpload(event) {
      this.bookForm.coverFile = event.target.files[0];
    },
    async saveBook() {
      const formData = new FormData();
      formData.append('id', this.bookForm.id || '');
      formData.append('title', this.bookForm.title);
      formData.append('author', this.bookForm.author);
      formData.append('genre', this.bookForm.genre);
      formData.append('class_number', this.bookForm.class_number || '');
      formData.append('isbn', this.bookForm.isbn || '');
      formData.append('published_year', this.bookForm.published_year);
      formData.append('stock', this.bookForm.stock);
      formData.append('description', this.bookForm.description);
      formData.append('cover_style', JSON.stringify(this.bookForm.coverStyle));
      
      if (this.bookForm.coverFile) {
        formData.append('cover_image', this.bookForm.coverFile);
      }

      try {
        let url = '/api/books';
        if (this.editingId) {
          url = `/api/books/${this.editingId}/update`;
        }
        const res = await axios.post(url, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        this.showToastMsg(res.data.message, 'success');
        this.showBookModal = false;
        await this.fetchState();
      } catch (err) {
        this.showToastMsg(err.response?.data?.message || 'Gagal menyimpan data buku.', 'danger');
      }
    },
    async restockBook(bookId) {
      try {
        const res = await axios.post(`/api/books/${bookId}/restock`);
        this.showToastMsg(res.data.message, 'success');
        await this.fetchState();
      } catch (err) {
        this.showToastMsg('Gagal melakukan restock.', 'danger');
      }
    },
    async deleteBook(bookId) {
      if (confirm('Apakah Anda yakin ingin menghapus buku ini secara permanen dari katalog?')) {
        try {
          const res = await axios.delete(`/api/books/${bookId}`);
          this.showToastMsg(res.data.message, 'success');
          await this.fetchState();
        } catch (err) {
          this.showToastMsg(err.response?.data?.message || 'Gagal menghapus buku.', 'danger');
        }
      }
    },

    // Librarian Student CRUD
    openAddStudentModal() {
      this.editingId = null;
      this.showAdminStudentPassword = false;
      this.currentStudentPasswordPlain = '';
      this.studentForm = {
        name: '',
        nim: '',
        email: '',
        phone: '',
        faculty: '',
        prodi: '',
        password: ''
      };
      this.showStudentModal = true;
    },
    openEditStudentModal(student) {
      this.editingId = student.id;
      this.showAdminStudentPassword = false;
      this.currentStudentPasswordPlain = student.password_plain || 'password123';
      this.studentForm = {
        name: student.name,
        nim: student.nim,
        email: student.email,
        phone: student.phone || '',
        faculty: student.faculty || '',
        prodi: student.prodi || '',
        password: ''
      };
      this.showStudentModal = true;
    },
    async saveStudent() {
      try {
        let url = '/api/admin/students';
        let payload = { ...this.studentForm };
        
        if (this.editingId) {
          url = `/api/admin/students/${this.editingId}/update`;
          // If editing and password is empty, remove it from payload to prevent validation triggers
          if (!payload.password) {
            delete payload.password;
          }
        }
        
        const res = await axios.post(url, payload);
        this.showToastMsg(res.data.message, 'success');
        this.showStudentModal = false;
        await this.fetchState();
      } catch (err) {
        this.showToastMsg(err.response?.data?.message || 'Gagal menyimpan data mahasiswa.', 'danger');
      }
    },
    async deleteStudent(id) {
      if (confirm('Apakah Anda yakin ingin menghapus akun mahasiswa ini secara permanen?')) {
        try {
          const res = await axios.delete(`/api/admin/students/${id}`);
          this.showToastMsg(res.data.message, 'success');
          await this.fetchState();
        } catch (err) {
          this.showToastMsg(err.response?.data?.message || 'Gagal menghapus akun mahasiswa.', 'danger');
        }
      }
    },

    // Librarian Loan CRUD
    openAddLoanModal() {
      this.editingId = null;
      const today = new Date(this.simDateStr);
      const due = new Date(today);
      due.setDate(due.getDate() + 14);

      this.loanForm = {
        userId: '',
        bookId: '',
        borrowDate: today.toISOString().split('T')[0],
        dueDate: due.toISOString().split('T')[0],
        status: 'active'
      };
      this.showLoanModal = true;
    },
    openEditLoanModal(loan) {
      this.editingId = loan.id;
      this.loanForm = {
        userId: loan.userId,
        bookId: loan.bookId,
        borrowDate: loan.borrowDate.split(' ')[0],
        dueDate: loan.dueDate.split(' ')[0],
        status: loan.status
      };
      this.showLoanModal = true;
    },
    async saveLoan() {
      try {
        let url = '/api/admin/loans';
        if (this.editingId) {
          url = `/api/admin/loans/${this.editingId}/update`;
        }
        const res = await axios.post(url, this.loanForm);
        this.showToastMsg(res.data.message, 'success');
        this.showLoanModal = false;
        await this.fetchState();
      } catch (err) {
        this.showToastMsg(err.response?.data?.message || 'Gagal menyimpan transaksi peminjaman.', 'danger');
      }
    },
    async adminReturnLoan(id) {
      try {
        const res = await axios.post(`/api/admin/loans/${id}/return`);
        this.showToastMsg(res.data.message, 'success');
        await this.fetchState();
      } catch (err) {
        this.showToastMsg('Gagal mengembalikan buku.', 'danger');
      }
    },
    async adminDestroyLoan(id) {
      if (confirm('Apakah Anda yakin ingin menghapus data transaksi peminjaman ini?')) {
        try {
          const res = await axios.delete(`/api/admin/loans/${id}`);
          this.showToastMsg(res.data.message, 'success');
          await this.fetchState();
        } catch (err) {
          this.showToastMsg('Gagal menghapus transaksi.', 'danger');
        }
      }
    },

    // Session logouts
    logout() {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/logout';
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_token';
      csrfInput.value = this.csrfToken;
      form.appendChild(csrfInput);
      document.body.appendChild(form);
      form.submit();
    },
    openChangePasswordModal() {
      this.changePasswordForm = {
        currentPassword: '',
        newPassword: '',
        confirmPassword: ''
      };
      this.showCurrentPwd = false;
      this.showNewPwd = false;
      this.showConfirmPwd = false;
      this.showChangePasswordModal = true;
    },
    async submitChangePassword() {
      try {
        const res = await axios.post('/api/profile/change-password', this.changePasswordForm);
        this.showToastMsg(res.data.message, 'success');
        this.showChangePasswordModal = false;
      } catch (err) {
        this.showToastMsg(err.response?.data?.message || 'Gagal mengubah kata sandi.', 'danger');
      }
    }
  }
}
</script>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-fadeIn {
  animation: fadeIn 0.4s ease-out forwards;
}
.animate-scaleUp {
  animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
.animate-spin-slow {
  animation: spin-slow 12s linear infinite;
}
</style>
