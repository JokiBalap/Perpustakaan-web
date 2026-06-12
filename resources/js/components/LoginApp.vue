<template>
  <div class="w-full max-w-md bg-white rounded-2xl shadow-premium border border-parchment-dark p-8 md:p-10 transition-all duration-300">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="flex justify-center items-center mb-4">
        <img src="/assets/logo.png" alt="Logo UNU NTB" class="h-16 w-auto animate-pulse">
      </div>
      <h2 class="text-2xl font-bold text-midnight">{{ isLogin ? 'Masuk ke Perpustakaan' : 'Daftar Akun Mahasiswa' }}</h2>
      <p class="text-xs font-semibold tracking-wider text-teal uppercase mt-1">Perpustakaan UNU NTB</p>
    </div>

    <!-- Error Alerts -->
    <div v-if="errorList.length > 0" class="mb-6 bg-danger-light bg-opacity-10 border-l-4 border-danger text-danger text-sm p-4 rounded-lg">
      <ul class="list-disc list-inside">
        <li v-for="(err, idx) in errorList" :key="idx" class="font-medium">{{ err }}</li>
      </ul>
    </div>

    <!-- Auth Form -->
    <form :action="isLogin ? '/login' : '/register'" method="POST" ref="authForm">
      <input type="hidden" name="_token" :value="csrfToken">

      <!-- LOGIN FORM -->
      <div v-if="isLogin">
        <div class="mb-5">
          <label class="block text-sm font-bold text-midnight mb-2">E-mail Akademik</label>
          <div class="relative flex items-center">
            <i class="fa-solid fa-envelope absolute left-4 text-teal"></i>
            <input type="email" name="email" v-model="loginEmail" required class="w-full py-3 pl-12 pr-4 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight" placeholder="email@unu-ntb.ac.id">
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-bold text-midnight mb-2">Kata Sandi</label>
          <div class="relative flex items-center">
            <i class="fa-solid fa-lock absolute left-4 text-teal"></i>
            <input :type="showLoginPassword ? 'text' : 'password'" name="password" required class="w-full py-3 pl-12 pr-12 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight" placeholder="••••••••">
            <button type="button" @click="showLoginPassword = !showLoginPassword" class="absolute right-4 text-teal hover:text-teal-dark focus:outline-none z-10 flex items-center">
              <i class="fa-solid" :class="showLoginPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>
        </div>

        <div class="flex justify-between items-center mb-8 text-sm">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="accent-teal rounded">
            <span class="text-midnight opacity-80 font-medium">Ingat Saya</span>
          </label>
        </div>

        <button type="submit" class="w-full py-3.5 bg-teal hover:bg-teal-dark text-white rounded-lg font-bold shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">Masuk Sekarang</button>

        <div class="text-center mt-6 text-sm text-midnight opacity-80 font-medium">
          Belum punya akun? <a href="#" @click.prevent="toggleForm" class="text-teal font-bold hover:underline">Daftar Akun Mahasiswa</a>
        </div>
      </div>

      <!-- REGISTER FORM -->
      <div v-else>
        <div class="mb-4">
          <label class="block text-sm font-bold text-midnight mb-2">Nama Lengkap</label>
          <div class="relative flex items-center">
            <i class="fa-solid fa-user absolute left-4 text-teal"></i>
            <input type="text" name="name" required class="w-full py-2.5 pl-12 pr-4 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight" placeholder="Nama Lengkap">
          </div>
        </div>

        <div class="flex gap-4 mb-4">
          <div class="flex-1">
            <label class="block text-sm font-bold text-midnight mb-2">NIM</label>
            <div class="relative flex items-center">
              <i class="fa-solid fa-id-card absolute left-4 text-teal"></i>
              <input type="text" name="nim" required class="w-full py-2.5 pl-12 pr-4 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight" placeholder="NIM">
            </div>
          </div>
          <div class="flex-1">
            <label class="block text-sm font-bold text-midnight mb-2">Nomor HP</label>
            <div class="relative flex items-center">
              <i class="fa-solid fa-phone absolute left-4 text-teal"></i>
              <input type="text" name="phone" class="w-full py-2.5 pl-12 pr-4 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight" placeholder="08xxxxxxxx">
            </div>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-bold text-midnight mb-2">E-mail Akademik</label>
          <div class="relative flex items-center">
            <i class="fa-solid fa-envelope absolute left-4 text-teal"></i>
            <input type="email" name="email" required class="w-full py-2.5 pl-12 pr-4 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight" placeholder="name@unu-ntb.ac.id">
          </div>
        </div>

        <div class="flex gap-4 mb-4">
          <div class="flex-1">
            <label class="block text-sm font-bold text-midnight mb-2">Fakultas</label>
            <div class="relative flex items-center">
              <i class="fa-solid fa-building-columns absolute left-4 text-teal z-10"></i>
              <select name="faculty" v-model="selectedFaculty" required class="w-full py-2.5 pl-12 pr-4 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight cursor-pointer appearance-none">
                <option value="">-- Pilih --</option>
                <option v-for="(prodis, fac) in facultyProdiMapping" :key="fac" :value="fac">{{ fac }}</option>
              </select>
            </div>
          </div>
          <div class="flex-1">
            <label class="block text-sm font-bold text-midnight mb-2">Prodi</label>
            <div class="relative flex items-center">
              <i class="fa-solid fa-graduation-cap absolute left-4 text-teal z-10"></i>
              <select name="prodi" v-model="selectedProdi" required class="w-full py-2.5 pl-12 pr-4 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight cursor-pointer appearance-none">
                <option value="">-- Pilih --</option>
                <option v-for="prodi in filteredProdis" :key="prodi" :value="prodi">{{ prodi }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-bold text-midnight mb-2">Kata Sandi Baru</label>
          <div class="relative flex items-center">
            <i class="fa-solid fa-lock absolute left-4 text-teal"></i>
            <input :type="showRegisterPassword ? 'text' : 'password'" name="password" required minlength="6" class="w-full py-2.5 pl-12 pr-12 border border-parchment-dark rounded-lg outline-none focus:ring-2 focus:ring-teal-light focus:border-teal transition-all bg-parchment-light text-midnight" placeholder="Minimal 6 karakter">
            <button type="button" @click="showRegisterPassword = !showRegisterPassword" class="absolute right-4 text-teal hover:text-teal-dark focus:outline-none z-10 flex items-center">
              <i class="fa-solid" :class="showRegisterPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="w-full py-3.5 bg-teal hover:bg-teal-dark text-white rounded-lg font-bold shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">Daftar Sekarang</button>

        <div class="text-center mt-6 text-sm text-midnight opacity-80 font-medium">
          Sudah punya akun? <a href="#" @click.prevent="toggleForm" class="text-teal font-bold hover:underline">Masuk ke Akun</a>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: 'LoginApp',
  props: {
    initialErrors: {
      type: Array,
      default: () => []
    },
    oldEmail: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      isLogin: true,
      showLoginPassword: false,
      showRegisterPassword: false,
      loginEmail: this.oldEmail,
      errorList: [...this.initialErrors],
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      selectedFaculty: '',
      selectedProdi: '',
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
      }
    }
  },
  computed: {
    filteredProdis() {
      if (!this.selectedFaculty) return [];
      return this.facultyProdiMapping[this.selectedFaculty] || [];
    }
  },
  watch: {
    selectedFaculty() {
      this.selectedProdi = '';
    }
  },
  methods: {
    toggleForm() {
      this.isLogin = !this.isLogin;
      this.errorList = []; // Clear errors when switching views
      this.showLoginPassword = false;
      this.showRegisterPassword = false;
    }
  }
}
</script>
