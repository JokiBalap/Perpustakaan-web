<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Perpustakaan UNU NTB</title>
  
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    body {
      background-color: var(--color-midnight);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 1.5rem;
    }
    
    .login-container {
      background-color: var(--color-parchment);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      max-width: 450px;
      width: 100%;
      padding: 3rem 2.5rem;
      border: 1px solid rgba(244, 244, 242, 0.15);
      animation: slideUp 0.4s ease-out;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 2.5rem;
    }
    
    .login-logo {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 1.2rem;
    }
    
    .login-header h2 {
      font-size: 1.8rem;
      color: var(--color-midnight);
      line-height: 1.2;
    }
    
    .login-header span {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: var(--color-teal);
      font-weight: 700;
    }
    
    .alert-error {
      background-color: rgba(192, 57, 43, 0.1);
      border-left: 4px solid var(--color-danger);
      color: var(--color-danger);
      padding: 1rem;
      border-radius: var(--radius-sm);
      margin-bottom: 1.5rem;
      font-size: 0.85rem;
    }
    
    .alert-error ul {
      list-style: none;
    }
    
    .form-group-login {
      margin-bottom: 1.5rem;
    }
    
    .form-group-login label {
      display: block;
      font-weight: 700;
      font-size: 0.85rem;
      margin-bottom: 0.4rem;
      color: var(--color-midnight);
    }
    
    .input-wrapper-login {
      position: relative;
      display: flex;
      align-items: center;
    }
    
    .input-wrapper-login i {
      position: absolute;
      left: 1rem;
      color: var(--color-teal);
    }
    
    .input-wrapper-login input {
      width: 100%;
      padding: 0.8rem 1rem 0.8rem 2.5rem;
      border: 1px solid var(--color-parchment-dark);
      border-radius: var(--radius-sm);
      outline: none;
      font-size: 0.95rem;
      background-color: var(--color-white);
      transition: var(--transition-fast);
    }
    
    .input-wrapper-login input:focus {
      border-color: var(--color-teal);
      box-shadow: 0 0 5px rgba(57, 142, 142, 0.2);
    }
    
    .remember-forgot-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      font-size: 0.85rem;
    }
    
    .remember-label {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      cursor: pointer;
    }
    
    .remember-label input {
      accent-color: var(--color-teal);
    }
    
    .login-btn {
      width: 100%;
      background-color: var(--color-teal);
      color: var(--color-white);
      border: none;
      padding: 1rem;
      border-radius: var(--radius-md);
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: var(--transition-smooth);
      box-shadow: var(--shadow-sm);
    }
    
    .login-btn:hover {
      background-color: var(--color-teal-dark);
      transform: translateY(-1px);
      box-shadow: var(--shadow-md);
    }
    
    .help-panel {
      margin-top: 2rem;
      background-color: var(--color-white);
      border: 1px dashed var(--color-teal);
      border-radius: var(--radius-md);
      padding: 1rem 1.2rem;
      font-size: 0.8rem;
    }
    
    .help-title {
      font-weight: bold;
      color: var(--color-midnight);
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.4rem;
    }
    
    .help-account {
      margin-bottom: 0.5rem;
    }
    
    .help-account:last-child {
      margin-bottom: 0;
    }
  </style>
</head>
<body>

  <div class="login-container" style="max-width: 500px;">
    <div class="login-header">
      <div class="login-logo"><img src="{{ asset('assets/logo.png') }}" alt="Logo UNU NTB" class="logo-animated"></div>
      <h2>Perpustakaan UNU NTB</h2>
      <span id="form-subtitle">Masuk ke Akun Anda</span>
    </div>
    
    @if ($errors->any())
      <div class="alert-error">
        <ul>
          @foreach ($errors->all() as $error)
            <li><i class="fa-solid fa-triangle-exclamation"></i> {{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    
    <!-- LOGIN FORM -->
    <form action="{{ url('/login') }}" method="POST" id="login-form">
      @csrf
      
      <div class="form-group-login">
        <label for="email">E-mail Akademik</label>
        <div class="input-wrapper-login">
          <i class="fa-solid fa-envelope"></i>
          <input type="email" id="email" name="email" required placeholder="name@unu-ntb.ac.id" value="{{ old('email') }}">
        </div>
      </div>
      
      <div class="form-group-login">
        <label for="password">Kata Sandi</label>
        <div class="input-wrapper-login">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
      </div>
      
      <div class="remember-forgot-row">
        <label class="remember-label">
          <input type="checkbox" name="remember">
          <span>Ingat Saya</span>
        </label>
      </div>
      
      <button type="submit" class="login-btn">Masuk</button>
      
      <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--color-charcoal);">
        Belum punya akun? <a href="#" id="link-show-register" style="color: var(--color-teal); font-weight: bold; text-decoration: none;">Daftar Akun Mahasiswa</a>
      </div>
    </form>

    <!-- REGISTER FORM -->
    <form action="{{ url('/register') }}" method="POST" id="register-form" style="display: none;">
      @csrf
      
      <div class="form-group-login">
        <label for="reg-name">Nama Lengkap</label>
        <div class="input-wrapper-login">
          <i class="fa-solid fa-user"></i>
          <input type="text" id="reg-name" name="name" required placeholder="Contoh: Siti Aisyah">
        </div>
      </div>

      <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
        <div class="form-group-login" style="flex: 1; margin-bottom: 0;">
          <label for="reg-nim">NIM</label>
          <div class="input-wrapper-login">
            <i class="fa-solid fa-id-card"></i>
            <input type="text" id="reg-nim" name="nim" required placeholder="NIM Anda">
          </div>
        </div>
        <div class="form-group-login" style="flex: 1; margin-bottom: 0;">
          <label for="reg-phone">Nomor HP</label>
          <div class="input-wrapper-login">
            <i class="fa-solid fa-phone"></i>
            <input type="text" id="reg-phone" name="phone" placeholder="08xxxxxxxxx">
          </div>
        </div>
      </div>

      <div class="form-group-login">
        <label for="reg-email">E-mail Akademik</label>
        <div class="input-wrapper-login">
          <i class="fa-solid fa-envelope"></i>
          <input type="email" id="reg-email" name="email" required placeholder="name@unu-ntb.ac.id">
        </div>
      </div>

      <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
        <div class="form-group-login" style="flex: 1; margin-bottom: 0;">
          <label for="reg-faculty">Fakultas</label>
          <div class="input-wrapper-login">
            <i class="fa-solid fa-building-columns" style="position: absolute; left: 1rem; color: var(--color-teal); z-index: 2;"></i>
            <select id="reg-faculty" name="faculty" required style="width: 100%; padding: 0.8rem 1rem 0.8rem 2.5rem; border: 1px solid var(--color-parchment-dark); border-radius: var(--radius-sm); outline: none; font-size: 0.95rem; background-color: var(--color-white); cursor: pointer;" onchange="handleRegisterFacultyChange()">
              <option value="">-- Pilih Fakultas --</option>
              <option value="Fakultas Kesehatan">Fakultas Kesehatan</option>
              <option value="Fakultas Pendidikan">Fakultas Pendidikan</option>
              <option value="Fakultas Teknik">Fakultas Teknik</option>
              <option value="Fakultas Ekonomi">Fakultas Ekonomi</option>
              <option value="Fakultas Hukum">Fakultas Hukum</option>
            </select>
          </div>
        </div>
        
        <div class="form-group-login" style="flex: 1; margin-bottom: 0;">
          <label for="reg-prodi">Program Studi</label>
          <div class="input-wrapper-login">
            <i class="fa-solid fa-graduation-cap" style="position: absolute; left: 1rem; color: var(--color-teal); z-index: 2;"></i>
            <select id="reg-prodi" name="prodi" required style="width: 100%; padding: 0.8rem 1rem 0.8rem 2.5rem; border: 1px solid var(--color-parchment-dark); border-radius: var(--radius-sm); outline: none; font-size: 0.95rem; background-color: var(--color-white); cursor: pointer;">
              <option value="">-- Pilih Prodi --</option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-group-login">
        <label for="reg-password">Kata Sandi Baru</label>
        <div class="input-wrapper-login">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="reg-password" name="password" required placeholder="Minimal 6 karakter" minlength="6">
        </div>
      </div>

      <button type="submit" class="login-btn">Daftar Sekarang</button>
      
      <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--color-charcoal);">
        Sudah punya akun? <a href="#" id="link-show-login" style="color: var(--color-teal); font-weight: bold; text-decoration: none;">Masuk ke Akun</a>
      </div>
    </form>
    
  </div>

  <script>
    // Toggle Login / Register
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const formSubtitle = document.getElementById('form-subtitle');
    
    document.getElementById('link-show-register').addEventListener('click', function(e) {
      e.preventDefault();
      loginForm.style.display = 'none';
      registerForm.style.display = 'block';
      formSubtitle.innerText = 'Daftar Akun Mahasiswa';
    });
    
    document.getElementById('link-show-login').addEventListener('click', function(e) {
      e.preventDefault();
      registerForm.style.display = 'none';
      loginForm.style.display = 'block';
      formSubtitle.innerText = 'Masuk ke Akun Anda';
    });

    // Faculty & Prodi Dropdowns Mapping
    const facultyProdiMapping = {
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
    };

    function handleRegisterFacultyChange() {
      const facultySelect = document.getElementById('reg-faculty');
      const prodiSelect = document.getElementById('reg-prodi');
      const selectedFaculty = facultySelect.value;
      
      // Clear options
      prodiSelect.innerHTML = '<option value="">-- Pilih Prodi --</option>';
      
      if (selectedFaculty && facultyProdiMapping[selectedFaculty]) {
        facultyProdiMapping[selectedFaculty].forEach(function(prodi) {
          const opt = document.createElement('option');
          opt.value = prodi;
          opt.innerText = prodi;
          prodiSelect.appendChild(opt);
        });
      }
    }
  </script>
</body>
</html>
