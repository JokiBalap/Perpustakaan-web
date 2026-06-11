// State Management
let books = [];
let currentUser = {};
let notifications = [];
let activityLogs = [];
let currentDate = new Date();
let librarianModeActive = false;
let allLoans = [];
let allReservations = [];
let studentsList = [];

// API Call Wrapper
async function apiCall(url, method = 'GET', data = null) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const headers = {
    'X-CSRF-TOKEN': csrfToken,
    'Accept': 'application/json'
  };
  if (!(data instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }
  const options = { method, headers };
  if (data) {
    if (data instanceof FormData) {
      options.body = data;
    } else {
      options.body = JSON.stringify(data);
    }
  }
  
  try {
    const response = await fetch(url, options);
    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.message || 'Terjadi kesalahan sistem.');
    }
    return result;
  } catch (error) {
    throw error;
  }
}

// Load current state from Laravel
async function loadStateFromServer() {
  const state = await apiCall('/api/state');
  books = state.books;
  currentUser = state.user;
  notifications = state.notifications;
  activityLogs = state.logs;
  currentDate = new Date(state.sim_date);
  
  allLoans = state.all_loans || [];
  allReservations = state.all_reservations || [];
  studentsList = state.students || [];
  
  // Set librarian mode automatically based on user role
  if (currentUser && currentUser.role === 'Pustakawan') {
    librarianModeActive = true;
    const checkbox = document.getElementById("librarian-mode-checkbox");
    if (checkbox) {
      checkbox.checked = true;
      checkbox.disabled = false;
    }
  } else {
    librarianModeActive = false;
    const checkbox = document.getElementById("librarian-mode-checkbox");
    if (checkbox) {
      checkbox.checked = false;
      // Disable checkbox for students
      checkbox.disabled = true;
      checkbox.parentElement.title = "Hanya akun Pustakawan yang dapat mengaktifkan mode ini.";
    }
  }
}

// Initialization
async function initApp() {
  try {
    await loadStateFromServer();
    
    // Setup Routing and Event Listeners
    setupNavigation();
    setupSearchFilters();
    
    // Render initial layouts
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

// Refresh UI Elements
function refreshUI() {
  updateDateBadge();
  renderHomeCatalog();
  renderCatalogPage();
  renderDashboard();
  renderDatabaseInventory();
  renderNotificationsHub();
  updateUnreadNotificationBadge();
  renderAdminLoans();
  renderAdminStudents();
}

// Router & Nav Controllers
function setupNavigation() {
  const navLinks = document.querySelectorAll(".nav-link");
  navLinks.forEach(link => {
    // Clone node to clear previous event listeners
    const newLink = link.cloneNode(true);
    link.parentNode.replaceChild(newLink, link);
    
    newLink.addEventListener("click", (e) => {
      const sectionName = newLink.getAttribute("data-section");
      setViewTab(sectionName);
    });
  });
}

async function setViewTab(sectionName) {
  // Update nav active class
  document.querySelectorAll(".nav-link").forEach(lnk => lnk.classList.remove("active"));
  
  const targetBtn = document.getElementById(`nav-btn-${sectionName}`);
  if (targetBtn) targetBtn.classList.add("active");
  
  // Switch sections
  document.querySelectorAll(".page-section").forEach(sec => sec.classList.remove("active"));
  const activeSection = document.getElementById(`${sectionName}-section`);
  if (activeSection) activeSection.classList.add("active");

  try {
    // Fetch fresh state on tab change
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
  
  window.scrollTo(0, 0);
}

function navigateToDashboard() {
  setViewTab("dashboard");
}

// Date Simulation Controls
function updateDateBadge() {
  const options = { day: 'numeric', month: 'short', year: 'numeric' };
  const formattedDate = currentDate.toLocaleDateString('id-ID', options);
  document.getElementById("sim-current-date-badge").innerText = formattedDate;
}

async function simulateTime(days) {
  try {
    const result = await apiCall('/api/simulation/time', 'POST', { days });
    showToast(result.message, "success");
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

function updateUnreadNotificationBadge() {
  const unreadCount = notifications.filter(n => n.unread).length;
  const notifBtn = document.getElementById("nav-btn-notifications");
  if (notifBtn) {
    const existingBadge = notifBtn.querySelector(".nav-badge");
    if (existingBadge) existingBadge.remove();
    
    if (unreadCount > 0) {
      const badge = document.createElement("span");
      badge.className = "nav-badge";
      badge.style.backgroundColor = "var(--color-danger)";
      badge.style.color = "white";
      badge.style.fontSize = "0.7rem";
      badge.style.padding = "2px 6px";
      badge.style.borderRadius = "50%";
      badge.style.marginLeft = "5px";
      badge.innerText = unreadCount;
      notifBtn.appendChild(badge);
    }
  }
}

// Toast Alert
function showToast(message, type = "info") {
  const container = document.getElementById("toast-container");
  const toast = document.createElement("div");
  toast.className = `toast-message toast-${type}`;
  
  let icon = '<i class="fa-solid fa-circle-info"></i>';
  if (type === "success") icon = '<i class="fa-solid fa-circle-check"></i>';
  if (type === "danger") icon = '<i class="fa-solid fa-triangle-exclamation"></i>';
  
  toast.innerHTML = `
    ${icon}
    <span>${message}</span>
  `;
  
  container.appendChild(toast);
  
  setTimeout(() => {
    toast.style.animation = "slideInRight 0.35s ease-in reverse forwards";
    setTimeout(() => {
      toast.remove();
    }, 350);
  }, 4000);
}

// Render Home Catalog
function renderHomeCatalog() {
  const popularGrid = document.getElementById("popular-books-grid");
  const recentGrid = document.getElementById("recent-books-grid");
  
  const popularBooks = [...books].sort((a, b) => b.popularity - a.popularity).slice(0, 4);
  const recentBooks = [...books].sort((a, b) => b.publishedYear - a.publishedYear).slice(0, 4);
  
  popularGrid.innerHTML = popularBooks.map(book => getBookCardHTML(book)).join("");
  recentGrid.innerHTML = recentBooks.map(book => getBookCardHTML(book)).join("");
}

// Helper to get mini cover style (supports custom image cover or gradient pattern)
function getBookCoverMiniStyle(book) {
  if (book.imagePath) {
    return `background-image: url('/${book.imagePath}'); background-size: cover; background-position: center;`;
  } else {
    return `background: linear-gradient(135deg, ${book.coverStyle.bgStart}, ${book.coverStyle.bgEnd});`;
  }
}

// Generate Book Cover HTML
  function getBookCardHTML(book) {
    const isAvailable = book.stock > 0;
    const statusClass = isAvailable ? "status-available" : "status-out";
    const statusText = isAvailable ? "Tersedia" : "Dipesan";
    
    let coverHTML = "";
    if (book.imagePath) {
      coverHTML = `<img src="/${book.imagePath}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px 10px 10px 4px;">`;
    } else {
      coverHTML = `
        <div class="book-spine-shine"></div>
        <div class="book-spine-crease"></div>
        <div class="cover-header">
          <span class="cover-genre" style="border-color: rgba(255,255,255,0.4);">${book.genre}</span>
          <span class="cover-year">${book.publishedYear}</span>
        </div>
        <div class="cover-body">
          <div class="cover-title" style="color: ${book.coverStyle.text}; font-size: ${book.title.length > 20 ? '0.95rem' : '1.15rem'};">${book.title}</div>
          <div class="cover-author" style="color: ${book.coverStyle.text}; opacity: 0.9;">${book.author}</div>
        </div>
        <div class="cover-footer">
          <span class="cover-brand" style="color: ${book.coverStyle.accent || 'white'};">UNU NTB</span>
        </div>
      `;
    }
    
    return `
      <div class="book-card" onclick="openBookDetailsModal('${book.id}')">
        <div class="cover-wrapper" style="box-shadow: var(--shadow-book);">
          <div class="book-cover ${book.imagePath ? '' : 'pattern-' + book.coverStyle.pattern}" style="${book.imagePath ? 'padding: 0; background: #e0e0e0;' : 'background: linear-gradient(135deg, ' + book.coverStyle.bgStart + ', ' + book.coverStyle.bgEnd + ');'} border-left: 5px solid rgba(0,0,0,0.25);">
            ${coverHTML}
          </div>
        </div>
      <div class="book-details">
        <h4 class="book-title-meta">${book.title}</h4>
        <span class="book-author-meta">${book.author}</span>
        <div class="book-status-row">
          <span class="book-availability ${statusClass}">${statusText}</span>
          <span class="book-popularity-badge"><i class="fa-solid fa-star"></i> ${book.popularity}</span>
        </div>
      </div>
    </div>
  `;
}

// Catalog Page Rendering
function setupSearchFilters() {
  const searchInput = document.getElementById("catalog-search-input");
  const availableCheckbox = document.getElementById("filter-available-only");
  const sortSelect = document.getElementById("filter-sort-select");
  
  searchInput.addEventListener("input", renderCatalogPage);
  availableCheckbox.addEventListener("change", renderCatalogPage);
  sortSelect.addEventListener("change", renderCatalogPage);

  const genres = [...new Set(books.map(b => b.genre))];
  const genreContainer = document.getElementById("genre-checkbox-filters");
  genreContainer.innerHTML = genres.map(genre => `
    <label class="filter-checkbox-label">
      <input type="checkbox" class="genre-filter-checkbox" value="${genre}" onchange="renderCatalogPage()">
      ${genre}
    </label>
  `).join("");
}

function renderCatalogPage() {
  const searchQuery = document.getElementById("catalog-search-input").value.toLowerCase();
  const availableOnly = document.getElementById("filter-available-only").checked;
  const sortBy = document.getElementById("filter-sort-select").value;
  
  const checkedGenres = Array.from(document.querySelectorAll(".genre-filter-checkbox:checked")).map(cb => cb.value);
  
  let filtered = books.filter(book => {
    const matchesSearch = book.title.toLowerCase().includes(searchQuery) || 
                          book.author.toLowerCase().includes(searchQuery) ||
                          book.genre.toLowerCase().includes(searchQuery);
                          
    const matchesGenre = checkedGenres.length === 0 || checkedGenres.includes(book.genre);
    const matchesAvailability = !availableOnly || book.stock > 0;
    
    return matchesSearch && matchesGenre && matchesAvailability;
  });

  if (sortBy === "popularity") {
    filtered.sort((a, b) => b.popularity - a.popularity);
  } else if (sortBy === "title-asc") {
    filtered.sort((a, b) => a.title.localeCompare(b.title));
  } else if (sortBy === "title-desc") {
    filtered.sort((a, b) => b.title.localeCompare(a.title));
  } else if (sortBy === "year-desc") {
    filtered.sort((a, b) => b.publishedYear - a.publishedYear);
  }

  const catalogGrid = document.getElementById("catalog-books-grid");
  const countText = document.getElementById("catalog-results-count");
  
  countText.innerText = filtered.length;
  if (filtered.length === 0) {
    catalogGrid.innerHTML = `
      <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--color-charcoal); opacity: 0.6;">
        <i class="fa-solid fa-magnifying-glass-minus" style="font-size: 3rem; color: var(--color-teal); margin-bottom: 1rem; display: block;"></i>
        <h3>Buku Tidak Ditemukan</h3>
        <p>Ganti kata kunci pencarian atau sesuaikan filter untuk menemukan hasil lainnya.</p>
      </div>
    `;
  } else {
    catalogGrid.innerHTML = filtered.map(book => getBookCardHTML(book)).join("");
  }
}

function handleHeroSearch() {
  const query = document.getElementById("hero-search-input").value;
  setViewTab("browse");
  document.getElementById("catalog-search-input").value = query;
  renderCatalogPage();
}

// Dashboard Page Renders
function renderDashboard() {
  if (!currentUser) return;

  const profileAvatar = document.getElementById("profile-avatar");
  const profileName = document.getElementById("profile-name");
  const profileNim = document.getElementById("profile-nim");
  const profileProdi = document.getElementById("profile-prodi");
  
  const headerAvatar = document.getElementById("header-user-avatar");
  const headerName = document.getElementById("header-user-name");
  const headerRole = document.getElementById("header-user-role");

  const initials = currentUser.name.split(" ").map(w => w[0]).join("").slice(0, 2);
  profileAvatar.innerText = initials;
  headerAvatar.innerText = initials;
  
  profileName.innerText = currentUser.name;
  headerName.innerText = currentUser.name.split(" ").slice(0, 2).join(" ");
  
  profileNim.innerText = currentUser.nim ? `NIM: ${currentUser.nim}` : 'NIDN/NIK Pustakawan';
  profileProdi.innerText = currentUser.prodi || 'Departemen Perpustakaan';
  headerRole.innerText = currentUser.role;

  const sectionTitle = document.getElementById("dashboard-section-title");
  const stat1Label = document.getElementById("stat-card-1-label");
  const stat2Label = document.getElementById("stat-card-2-label");
  const stat3Label = document.getElementById("stat-card-3-label");
  
  const loansTbody = document.getElementById("dashboard-loans-tbody");
  const reservationsTbody = document.getElementById("dashboard-reservations-tbody");
  const wishlistTbody = document.getElementById("dashboard-wishlist-tbody");

  if (currentUser.role === 'Pustakawan') {
    // 1. Update Title and Labels for Pustakawan
    if (sectionTitle) sectionTitle.innerText = "Dashboard Pustakawan";
    if (stat1Label) stat1Label.innerText = "Total Peminjaman Aktif";
    if (stat2Label) stat2Label.innerText = "Total Antrean";
    if (stat3Label) stat3Label.innerText = "Buku Kehabisan Stok";

    // 2. Populate stats counts
    const outOfStockCount = books.filter(b => b.stock === 0).length;
    document.getElementById("stat-borrowed-count").innerText = allLoans.length;
    document.getElementById("badge-loans-count").innerText = allLoans.length;
    
    document.getElementById("stat-reservation-count").innerText = allReservations.length;
    document.getElementById("badge-reservations-count").innerText = allReservations.length;
    
    document.getElementById("stat-wishlist-count").innerText = outOfStockCount;
    document.getElementById("badge-wishlist-count").innerText = outOfStockCount;

    // 3. Set Table Headers and Titles
    document.getElementById("dashboard-loans-title-span").innerHTML = '<i class="fa-solid fa-book-reader" style="color: var(--color-teal);"></i> Peminjaman Aktif Perpustakaan';
    document.getElementById("dashboard-loans-thead-tr").innerHTML = '<th>Buku</th><th>Peminjam (Mahasiswa)</th><th>Jatuh Tempo</th><th>Sisa Waktu</th><th>Aksi</th>';
    
    document.getElementById("dashboard-reservations-title-span").innerHTML = '<i class="fa-solid fa-clock-rotate-left" style="color: var(--color-amber);"></i> Antrean Reservasi Perpustakaan';
    document.getElementById("dashboard-reservations-thead-tr").innerHTML = '<th>Buku</th><th>Mahasiswa</th><th>Posisi Antrean</th><th>Aksi</th>';
    
    document.getElementById("dashboard-wishlist-title-span").innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color: var(--color-danger);"></i> Buku Kehabisan Stok (Perlu Restock)';
    document.getElementById("dashboard-wishlist-thead-tr").innerHTML = '<th>Buku</th><th>Total Stok</th><th>Aksi</th>';

    // 4. Render Table 1: All active loans
    if (allLoans.length === 0) {
      loansTbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--color-charcoal); opacity: 0.5; padding: 2rem;">Tidak ada peminjaman aktif di perpustakaan.</td></tr>`;
    } else {
      loansTbody.innerHTML = allLoans.map(loan => {
        const book = books.find(b => b.id === loan.bookId);
        if (!book) return "";
        const borrowDate = new Date(loan.borrowDate);
        const dueDate = new Date(loan.dueDate);
        const diffTime = dueDate.getTime() - currentDate.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const totalLoanPeriod = 14 * 24 * 60 * 60 * 1000;
        const currentPassed = currentDate.getTime() - borrowDate.getTime();
        const progressPercent = Math.min(100, Math.max(0, (currentPassed / totalLoanPeriod) * 100));
        
        let fillClass = "";
        let remainingText = "";
        let statusClass = "status-txt-active";
        
        if (diffDays < 0) {
          fillClass = "overdue";
          remainingText = `Terlambat ${Math.abs(diffDays)} Hari`;
          statusClass = "status-txt-overdue";
        } else if (diffDays <= 2) {
          fillClass = "warning";
          remainingText = `${diffDays} Hari Lagi (Kritis)`;
          statusClass = "status-txt-warning";
        } else {
          remainingText = `${diffDays} Hari`;
          statusClass = "status-txt-active";
        }
        return `
          <tr>
            <td>
              <div class="table-book-info">
                <div class="table-book-cover-mini" style="${getBookCoverMiniStyle(book)}"></div>
                <div>
                  <strong style="color: var(--color-midnight); cursor: pointer;" onclick="openBookDetailsModal('${book.id}')">${book.title}</strong>
                  <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.7;">${book.author}</div>
                </div>
              </div>
            </td>
            <td>
              <strong>${loan.userName}</strong>
              <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.85;">NIM: ${loan.userNim}</div>
            </td>
            <td>${dueDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</td>
            <td>
              <div class="dashboard-countdown-wrapper">
                <span class="loan-status-text ${statusClass}">${remainingText}</span>
                <div class="countdown-bar-container">
                  <div class="countdown-bar-fill ${fillClass}" style="width: ${progressPercent}%;"></div>
                </div>
              </div>
            </td>
            <td>
              <button class="action-btn-sm" style="background-color: var(--color-midnight);" onclick="adminReturnLoan('${loan.id}')">Kembalikan</button>
            </td>
          </tr>
        `;
      }).join("");
    }

    // 5. Render Table 2: All reservations queue
    if (allReservations.length === 0) {
      reservationsTbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--color-charcoal); opacity: 0.5; padding: 2rem;">Tidak ada antrean reservasi aktif.</td></tr>`;
    } else {
      reservationsTbody.innerHTML = allReservations.map(res => {
        const book = books.find(b => b.id === res.bookId);
        if (!book) return "";
        return `
          <tr>
            <td>
              <div class="table-book-info">
                <div class="table-book-cover-mini" style="${getBookCoverMiniStyle(book)}"></div>
                <div>
                  <strong style="color: var(--color-midnight); cursor: pointer;" onclick="openBookDetailsModal('${book.id}')">${book.title}</strong>
                  <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.7;">${book.author}</div>
                </div>
              </div>
            </td>
            <td>
              <strong>${res.userName}</strong>
              <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.85;">NIM: ${res.userNim}</div>
            </td>
            <td><span style="font-weight: bold; color: var(--color-amber);">Antrean #${res.queuePosition}</span></td>
            <td>
              <button class="action-btn-sm btn-danger" onclick="cancelReservationForUser('${res.userId}', '${book.id}')">Batalkan</button>
            </td>
          </tr>
        `;
      }).join("");
    }

    // 6. Render Table 3: Out of stock books
    const outOfStockBooks = books.filter(b => b.stock === 0);
    if (outOfStockBooks.length === 0) {
      wishlistTbody.innerHTML = `<tr><td colspan="3" style="text-align: center; color: var(--color-teal); opacity: 0.7; padding: 2rem;">Semua koleksi buku memiliki stok tersedia.</td></tr>`;
    } else {
      wishlistTbody.innerHTML = outOfStockBooks.map(book => {
        return `
          <tr>
            <td>
              <div class="table-book-info">
                <div class="table-book-cover-mini" style="${getBookCoverMiniStyle(book)}"></div>
                <div>
                  <strong style="color: var(--color-midnight); cursor: pointer;" onclick="openBookDetailsModal('${book.id}')">${book.title}</strong>
                  <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.7;">${book.author}</div>
                </div>
              </div>
            </td>
            <td><span class="book-availability status-out">Habis (${book.stock} / ${book.totalStock})</span></td>
            <td>
              <button class="action-btn-sm" style="background-color: var(--color-teal);" onclick="restockBook('${book.id}')"><i class="fa-solid fa-plus-square"></i> Restock</button>
            </td>
          </tr>
        `;
      }).join("");
    }

  } else {
    // 1. Reset Title and Labels for Student
    if (sectionTitle) sectionTitle.innerText = "Dashboard Anggota";
    if (stat1Label) stat1Label.innerText = "Buku Dipinjam";
    if (stat2Label) stat2Label.innerText = "Dalam Antrean";
    if (stat3Label) stat3Label.innerText = "Daftar Keinginan";

    // 2. Populate stats counts
    document.getElementById("stat-borrowed-count").innerText = currentUser.borrowedBooks.length;
    document.getElementById("badge-loans-count").innerText = currentUser.borrowedBooks.length;
    
    document.getElementById("stat-reservation-count").innerText = currentUser.reservations.length;
    document.getElementById("badge-reservations-count").innerText = currentUser.reservations.length;
    
    document.getElementById("stat-wishlist-count").innerText = currentUser.wishlist.length;
    document.getElementById("badge-wishlist-count").innerText = currentUser.wishlist.length;

    // 3. Set Table Headers and Titles
    document.getElementById("dashboard-loans-title-span").innerHTML = '<i class="fa-solid fa-book-reader" style="color: var(--color-teal);"></i> Buku yang Sedang Dipinjam';
    document.getElementById("dashboard-loans-thead-tr").innerHTML = '<th>Buku</th><th>Tanggal Pinjam</th><th>Jatuh Tempo</th><th>Sisa Waktu</th><th>Aksi</th>';
    
    document.getElementById("dashboard-reservations-title-span").innerHTML = '<i class="fa-solid fa-clock-rotate-left" style="color: var(--color-amber);"></i> Antrean Reservasi';
    document.getElementById("dashboard-reservations-thead-tr").innerHTML = '<th>Buku</th><th>Tanggal Reservasi</th><th>Posisi Antrean</th><th>Aksi</th>';
    
    document.getElementById("dashboard-wishlist-title-span").innerHTML = '<i class="fa-solid fa-heart" style="color: var(--color-danger);"></i> Daftar Keinginan (Wishlist)';
    document.getElementById("dashboard-wishlist-thead-tr").innerHTML = '<th>Buku</th><th>Status Stok</th><th>Aksi</th>';

    // 4. Render Table 1: Personal loans
    if (currentUser.borrowedBooks.length === 0) {
      loansTbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--color-charcoal); opacity: 0.5; padding: 2rem;">Tidak ada buku yang sedang dipinjam.</td></tr>`;
    } else {
      loansTbody.innerHTML = currentUser.borrowedBooks.map(loan => {
        const book = books.find(b => b.id === loan.bookId);
        if (!book) return "";
        
        const borrowDate = new Date(loan.borrowDate);
        const dueDate = new Date(loan.dueDate);
        
        const diffTime = dueDate.getTime() - currentDate.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        const totalLoanPeriod = 14 * 24 * 60 * 60 * 1000;
        const currentPassed = currentDate.getTime() - borrowDate.getTime();
        const progressPercent = Math.min(100, Math.max(0, (currentPassed / totalLoanPeriod) * 100));
        
        let fillClass = "";
        let remainingText = "";
        let statusClass = "status-txt-active";
        
        if (diffDays < 0) {
          fillClass = "overdue";
          remainingText = `Terlambat ${Math.abs(diffDays)} Hari`;
          statusClass = "status-txt-overdue";
        } else if (diffDays <= 2) {
          fillClass = "warning";
          remainingText = `${diffDays} Hari Lagi (Kritis)`;
          statusClass = "status-txt-warning";
        } else {
          remainingText = `${diffDays} Hari`;
          statusClass = "status-txt-active";
        }
        
        return `
          <tr>
            <td>
              <div class="table-book-info">
                <div class="table-book-cover-mini" style="${getBookCoverMiniStyle(book)}"></div>
                <div>
                  <strong style="color: var(--color-midnight); cursor: pointer;" onclick="openBookDetailsModal('${book.id}')">${book.title}</strong>
                  <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.7;">${book.author}</div>
                </div>
              </div>
            </td>
            <td>${borrowDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</td>
            <td>${dueDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</td>
            <td>
              <div class="dashboard-countdown-wrapper">
                <span class="loan-status-text ${statusClass}">${remainingText}</span>
                <div class="countdown-bar-container">
                  <div class="countdown-bar-fill ${fillClass}" style="width: ${progressPercent}%;"></div>
                </div>
              </div>
            </td>
            <td>
              <button class="action-btn-sm" onclick="returnBook('${book.id}')">Kembalikan</button>
            </td>
          </tr>
        `;
      }).join("");
    }

    // 5. Render Table 2: Personal reservations queue
    if (currentUser.reservations.length === 0) {
      reservationsTbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--color-charcoal); opacity: 0.5; padding: 2rem;">Tidak ada reservasi aktif.</td></tr>`;
    } else {
      reservationsTbody.innerHTML = currentUser.reservations.map(res => {
        const book = books.find(b => b.id === res.bookId);
        if (!book) return "";
        
        const resDate = new Date(res.reservedDate);
        
        return `
          <tr>
            <td>
              <div class="table-book-info">
                <div class="table-book-cover-mini" style="${getBookCoverMiniStyle(book)}"></div>
                <div>
                  <strong style="color: var(--color-midnight); cursor: pointer;" onclick="openBookDetailsModal('${book.id}')">${book.title}</strong>
                  <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.7;">${book.author}</div>
                </div>
              </div>
            </td>
            <td>${resDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</td>
            <td><span style="font-weight: bold; color: var(--color-amber);">Antrean #${res.queuePosition}</span></td>
            <td>
              <button class="action-btn-sm btn-danger" onclick="cancelReservation('${book.id}')">Batalkan</button>
            </td>
          </tr>
        `;
      }).join("");
    }

    // 6. Render Table 3: Personal wishlist
    if (currentUser.wishlist.length === 0) {
      wishlistTbody.innerHTML = `<tr><td colspan="3" style="text-align: center; color: var(--color-charcoal); opacity: 0.5; padding: 2rem;">Daftar keinginan kosong.</td></tr>`;
    } else {
      wishlistTbody.innerHTML = currentUser.wishlist.map(bookId => {
        const book = books.find(b => b.id === bookId);
        if (!book) return "";
        
        const isAvailable = book.stock > 0;
        const statusClass = isAvailable ? "status-available" : "status-out";
        const statusText = isAvailable ? "Tersedia" : "Kosong";
        
        const actionBtn = isAvailable 
          ? `<button class="action-btn-sm" style="background-color: var(--color-teal);" onclick="borrowBook('${book.id}')">Pinjam Instan</button>`
          : `<button class="action-btn-sm" style="background-color: var(--color-amber);" onclick="joinReservationQueue('${book.id}')">Antre Reservasi</button>`;

        return `
          <tr>
            <td>
              <div class="table-book-info">
                <div class="table-book-cover-mini" style="${getBookCoverMiniStyle(book)}"></div>
                <div>
                  <strong style="color: var(--color-midnight); cursor: pointer;" onclick="openBookDetailsModal('${book.id}')">${book.title}</strong>
                  <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.7;">${book.author}</div>
                </div>
              </div>
            </td>
            <td><span class="book-availability ${statusClass}">${statusText}</span></td>
            <td>
              <div style="display: flex; gap: 0.5rem;">
                ${actionBtn}
                <button class="action-btn-sm btn-danger" onclick="toggleWishlist('${book.id}')" title="Hapus dari Wishlist"><i class="fa-solid fa-trash-can"></i></button>
              </div>
            </td>
          </tr>
        `;
      }).join("");
    }
  }
}

async function cancelReservationForUser(userId, bookId) {
  if (confirm("Apakah Anda yakin ingin membatalkan antrean reservasi untuk mahasiswa ini?")) {
    try {
      const res = await apiCall('/api/reservations/cancel', 'POST', { userId, bookId });
      showToast(res.message, "info");
      await loadStateFromServer();
      refreshUI();
    } catch (error) {
      showToast(error.message, 'danger');
    }
  }
}

// AI Curator Logic
function generateAIRecommendations() {
  const selectedGenre = document.getElementById("curator-genre").value;
  const selectedStyle = document.getElementById("curator-style").value;
  const keywords = document.getElementById("curator-keywords").value.toLowerCase().split(",").map(k => k.trim()).filter(k => k.length > 0);
  
  const resultsPanel = document.getElementById("curator-results-panel");
  const placeholder = document.getElementById("curator-placeholder");
  const outputContent = document.getElementById("curator-output-content");
  const listContainer = document.getElementById("recommendations-list");
  
  const recommendBtn = document.getElementById("curator-recommend-btn");
  const originalBtnHTML = recommendBtn.innerHTML;
  recommendBtn.disabled = true;
  recommendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menganalisis Minat...';
  
  setTimeout(() => {
    let scored = books.map(book => {
      let score = 0;
      if (selectedGenre === "any" || book.genre === selectedGenre) score += 12;
      
      keywords.forEach(keyword => {
        if (book.title.toLowerCase().includes(keyword)) score += 8;
        if (book.author.toLowerCase().includes(keyword)) score += 4;
        if (book.description.toLowerCase().includes(keyword)) score += 6;
      });
      
      if (selectedStyle === "academic") {
        if (['Philosophy', 'Science', 'Technology'].includes(book.genre)) score += 5;
      } else if (selectedStyle === "light") {
        if (book.genre === "Drama") score += 5;
      } else if (selectedStyle === "narrative") {
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
    
    recommendBtn.disabled = false;
    recommendBtn.innerHTML = originalBtnHTML;
    
    placeholder.style.display = "none";
    outputContent.style.display = "block";
    
    listContainer.innerHTML = topRecs.map(({ book, score }) => {
      const defaultReason = `Buku ini memiliki kecocokan tinggi (${Math.round(score)}%) dengan topik minat Anda.`;
      const aiReason = reasoningMap[book.id] || defaultReason;
      
      let recCoverHTML = "";
      if (book.imagePath) {
        recCoverHTML = `<img src="/${book.imagePath}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px 10px 10px 4px;">`;
      } else {
        recCoverHTML = `
          <div class="book-spine-shine"></div>
          <div class="book-spine-crease"></div>
          <div class="cover-header" style="margin-bottom: 2px;">
            <span class="cover-genre" style="font-size: 0.5rem; padding: 1px 3px;">${book.genre}</span>
          </div>
          <div class="cover-body">
            <div class="cover-title" style="font-size: 0.7rem; color: ${book.coverStyle.text}; line-height: 1.2;">${book.title}</div>
            <div class="cover-author" style="font-size: 0.5rem; color: ${book.coverStyle.text};">${book.author}</div>
          </div>
          <div class="cover-footer">
            <span class="cover-brand">AI SELECT</span>
          </div>
        `;
      }

      return `
        <div class="recommendation-card">
          <div class="book-card" onclick="openBookDetailsModal('${book.id}')">
            <div class="cover-wrapper" style="box-shadow: var(--shadow-sm);">
              <div class="book-cover ${book.imagePath ? '' : 'pattern-' + book.coverStyle.pattern}" style="${book.imagePath ? 'padding: 0; background: #e0e0e0;' : 'background: linear-gradient(135deg, ' + book.coverStyle.bgStart + ', ' + book.coverStyle.bgEnd + '); font-size: 0.6rem; padding: 0.8rem;'} border-left: 5px solid rgba(0,0,0,0.25);">
                ${recCoverHTML}
              </div>
            </div>
          </div>
          <div class="recommendation-details">
            <span class="rec-badge"><i class="fa-solid fa-circle-nodes"></i> Match: ${Math.round(score * 2.5)}%</span>
            <h4 class="rec-title">${book.title}</h4>
            <span class="rec-author">oleh ${book.author}</span>
            <p class="rec-reason"><strong>Kenapa AI memilih ini:</strong> ${aiReason}</p>
            <button class="rec-action-btn" onclick="openBookDetailsModal('${book.id}')">Lihat Detail Buku</button>
          </div>
        </div>
      `;
    }).join("");
    
    showToast("AI Curator berhasil membuat rekomendasi!", "success");
  }, 800);
}

// Database Manager
function toggleLibrarianMode() {
  const checkbox = document.getElementById("librarian-mode-checkbox");
  if (currentUser && currentUser.role === 'Pustakawan') {
    librarianModeActive = checkbox.checked;
    const activeBadge = document.getElementById("librarian-active-badge");
    const addBookBtn = document.getElementById("db-add-book-btn");
    
    if (librarianModeActive) {
      activeBadge.style.display = "inline-block";
      addBookBtn.style.display = "flex";
      showToast("Portal database Pustakawan diaktifkan.", "success");
    } else {
      activeBadge.style.display = "none";
      addBookBtn.style.display = "none";
      showToast("Portal dinonaktifkan.", "info");
    }
    renderDatabaseInventory();
  } else {
    checkbox.checked = false;
    showToast("Akses ditolak. Anda login sebagai Mahasiswa.", "danger");
  }
}

function renderDatabaseInventory() {
  const dbTbody = document.getElementById("db-inventory-tbody");
  
  dbTbody.innerHTML = books.map(book => {
    let actionButtons = `<button class="action-btn-sm" style="background-color: var(--color-midnight);" onclick="openBookDetailsModal('${book.id}')">Lihat Detail</button>`;
    
    if (librarianModeActive) {
      actionButtons = `
        <div style="display: flex; gap: 0.5rem;">
          <button class="action-btn-sm" style="background-color: var(--color-teal);" onclick="restockBook('${book.id}')" title="Tambah 1 Stok"><i class="fa-solid fa-plus-square"></i> Restock</button>
          <button class="action-btn-sm" style="background-color: var(--color-midnight);" onclick="openEditBookModal('${book.id}')" title="Edit Buku"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
          <button class="action-btn-sm btn-danger" onclick="deleteBook('${book.id}')" title="Hapus Buku"><i class="fa-solid fa-trash-can"></i> Hapus</button>
        </div>
      `;
    }
    
    return `
      <tr>
        <td style="font-family: monospace; font-size: 0.85rem; color: var(--color-teal-dark); font-weight: bold;">${book.id}</td>
        <td>
          <div class="table-book-info">
            <div class="table-book-cover-mini" style="${getBookCoverMiniStyle(book)}"></div>
            <div>
              <strong>${book.title}</strong>
              <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.7;">${book.author}</div>
            </div>
          </div>
        </td>
        <td><span style="font-size: 0.85rem; background-color: var(--color-parchment); padding: 4px 8px; border-radius: 4px;">${book.genre}</span></td>
        <td>${book.publishedYear}</td>
        <td><strong>${book.stock}</strong> / <span style="opacity:0.7;">${book.totalStock}</span></td>
        <td>${actionButtons}</td>
      </tr>
    `;
  }).join("");

  const logsTbody = document.getElementById("db-logs-tbody");
  if (activityLogs.length === 0) {
    logsTbody.innerHTML = `<tr><td colspan="3" style="text-align: center; color: var(--color-charcoal); opacity: 0.5; padding: 2rem;">Belum ada riwayat sirkulasi.</td></tr>`;
  } else {
    logsTbody.innerHTML = activityLogs.map(log => {
      const logTime = new Date(log.time);
      return `
        <tr>
          <td style="font-family: monospace; font-size: 0.8rem; white-space: nowrap;">${logTime.toLocaleString('id-ID')}</td>
          <td><strong style="color: var(--color-teal-dark);">${log.activity}</strong></td>
          <td style="font-size: 0.9rem; color: var(--color-charcoal);">${log.detail}</td>
        </tr>
      `;
    }).join("");
  }
}

// Database Operations
async function restockBook(bookId) {
  try {
    const res = await apiCall(`/api/books/${bookId}/restock`, 'POST');
    showToast(res.message, "success");
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

async function deleteBook(bookId) {
  if (confirm("Apakah Anda yakin ingin menghapus buku ini secara permanen dari katalog?")) {
    try {
      const res = await apiCall(`/api/books/${bookId}`, 'DELETE');
      showToast(res.message, "info");
      await loadStateFromServer();
      refreshUI();
    } catch (error) {
      showToast(error.message, 'danger');
    }
  }
}

// Add Book Modal Operations
function openAddBookModal() {
  document.getElementById("add-book-modal-title").innerText = "Tambah Buku Baru";
  document.getElementById("form-book-id").value = "";
  document.getElementById("form-cover-image").value = "";
  document.getElementById("add-book-modal").style.display = "flex";
}

function openEditBookModal(bookId) {
  const book = books.find(b => b.id === bookId);
  if (!book) return;

  document.getElementById("add-book-modal-title").innerText = "Edit Buku";
  document.getElementById("form-book-id").value = book.id;

  document.getElementById("form-title").value = book.title;
  document.getElementById("form-author").value = book.author;
  document.getElementById("form-genre").value = book.genre;
  document.getElementById("form-year").value = book.publishedYear;
  document.getElementById("form-stock").value = book.stock;
  document.getElementById("form-pattern").value = book.coverStyle.pattern;
  document.getElementById("form-bg-start").value = book.coverStyle.bgStart;
  document.getElementById("form-accent-color").value = book.coverStyle.accent || "#398E8E";
  document.getElementById("form-desc").value = book.description;

  // Clear cover image input
  document.getElementById("form-cover-image").value = "";

  document.getElementById("add-book-modal").style.display = "flex";
}

function closeAddBookModal() {
  document.getElementById("add-book-modal").style.display = "none";
  document.getElementById("add-book-form").reset();
}

async function handleAddBookSubmit(e) {
  e.preventDefault();
  
  const bookId = document.getElementById("form-book-id").value;
  const title = document.getElementById("form-title").value;
  const author = document.getElementById("form-author").value;
  const genre = document.getElementById("form-genre").value;
  const year = parseInt(document.getElementById("form-year").value);
  const stock = parseInt(document.getElementById("form-stock").value);
  const pattern = document.getElementById("form-pattern").value;
  const bgStart = document.getElementById("form-bg-start").value;
  const accentColor = document.getElementById("form-accent-color").value;
  const desc = document.getElementById("form-desc").value;
  
  const bgEnd = darkenColor(bgStart, 40);
  
  const coverStyle = {
    bgStart,
    bgEnd,
    text: "#FFFFFF",
    accent: accentColor,
    pattern
  };

  const formData = new FormData();
  formData.append("title", title);
  formData.append("author", author);
  formData.append("genre", genre);
  formData.append("published_year", year);
  formData.append("stock", stock);
  formData.append("description", desc);
  formData.append("cover_style", JSON.stringify(coverStyle));

  const imageFile = document.getElementById("form-cover-image").files[0];
  if (imageFile) {
    formData.append("cover_image", imageFile);
  }
  
  try {
    let res;
    if (bookId) {
      res = await apiCall(`/api/books/${bookId}/update`, 'POST', formData);
    } else {
      res = await apiCall('/api/books', 'POST', formData);
    }
    
    showToast(res.message, "success");
    await loadStateFromServer();
    refreshUI();
    closeAddBookModal();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

function darkenColor(hex, percent) {
  let num = parseInt(hex.replace("#",""), 16),
      amt = Math.round(2.55 * percent),
      R = (num >> 16) - amt,
      G = (num >> 8 & 0x00FF) - amt,
      B = (num & 0x0000FF) - amt;
  return "#" + (0x1000000 + (R<0?0:R>255?255:R)*0x10000 + (G<0?0:G>255?255:G)*0x100 + (B<0?0:B>255?255:B)).toString(16).slice(1);
}

// Notification Hub
function renderNotificationsHub() {
  const notifContainer = document.getElementById("notifications-list-container");
  
  if (notifications.length === 0) {
    notifContainer.innerHTML = `
      <div class="notif-empty">
        <i class="fa-solid fa-box-open" style="font-size: 2.5rem; color: var(--color-parchment-dark); margin-bottom: 0.8rem; display: block;"></i>
        <p>Kotak masuk Anda bersih. Tidak ada notifikasi saat ini.</p>
      </div>
    `;
    return;
  }
  
  notifContainer.innerHTML = notifications.map(notif => {
    let iconClass = "notif-icon-new-book";
    let icon = '<i class="fa-solid fa-book-open"></i>';
    let overdueClass = "";
    
    if (notif.type === "warning") {
      iconClass = "notif-icon-overdue";
      icon = '<i class="fa-solid fa-circle-exclamation"></i>';
      overdueClass = "overdue-notif";
    } else if (notif.type === "hold") {
      iconClass = "notif-icon-hold";
      icon = '<i class="fa-solid fa-business-time"></i>';
    }
    
    const notifTime = new Date(notif.time);
    const unreadIndicator = notif.unread ? "unread" : "";
    
    return `
      <div class="notif-item ${unreadIndicator} ${overdueClass}" onclick="markNotifRead('${notif.id}')">
        <div class="notif-icon-wrapper ${iconClass}">
          ${icon}
        </div>
        <div class="notif-details">
          <div class="notif-header-row">
            <span class="notif-title-text">${notif.title}</span>
            <span class="notif-time-text">${notifTime.toLocaleDateString('id-ID')} ${notifTime.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</span>
          </div>
          <p class="notif-body-text">${notif.message}</p>
        </div>
      </div>
    `;
  }).join("");
}

async function markNotifRead(notifId) {
  try {
    await apiCall(`/api/notifications/${notifId}/read`, 'POST');
    await loadStateFromServer();
    renderNotificationsHub();
    updateUnreadNotificationBadge();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

// Book Details Modal
let currentViewingBookId = null;

function openBookDetailsModal(bookId) {
  const book = books.find(b => b.id === bookId);
  if (!book) return;
  
  currentViewingBookId = bookId;
  
  document.getElementById("modal-book-title").innerText = book.title;
  document.getElementById("modal-book-author").innerText = `oleh ${book.author}`;
  document.getElementById("modal-book-description").innerText = book.description;
  document.getElementById("modal-book-genre").innerText = book.genre;
  document.getElementById("modal-book-year").innerText = book.publishedYear;
  document.getElementById("modal-book-popularity").innerHTML = `<i class="fa-solid fa-star"></i> ${book.popularity}`;
  document.getElementById("modal-book-stock").innerText = `${book.stock} / ${book.totalStock}`;

  const coverBg = document.getElementById("modal-cover-bg");
  if (book.imagePath) {
    coverBg.className = `book-cover`;
    coverBg.style.background = `#e0e0e0`;
    coverBg.style.padding = `0`;
    coverBg.innerHTML = `<img src="/${book.imagePath}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px 10px 10px 4px;">`;
  } else {
    coverBg.className = `book-cover pattern-${book.coverStyle.pattern}`;
    coverBg.style.background = `linear-gradient(135deg, ${book.coverStyle.bgStart}, ${book.coverStyle.bgEnd})`;
    coverBg.style.padding = `1.5rem`;
    coverBg.innerHTML = `
      <div class="book-spine-shine"></div>
      <div class="book-spine-crease"></div>
      <div class="cover-header">
        <span class="cover-genre" id="modal-cover-genre">${book.genre}</span>
        <span class="cover-year" id="modal-cover-year">${book.publishedYear}</span>
      </div>
      <div class="cover-body">
        <div class="cover-title" id="modal-cover-title" style="font-size: 0.95rem; color: ${book.coverStyle.text};">${book.title}</div>
        <div class="cover-author" id="modal-cover-author" style="font-size: 0.65rem; color: ${book.coverStyle.text};">${book.author}</div>
      </div>
      <div class="cover-footer">
        <span class="cover-brand">UNU NTB</span>
      </div>
    `;
  }

  const reservationCountDiv = document.getElementById("modal-reservation-group");
  
  if (book.stock === 0) {
    reservationCountDiv.style.display = "flex";
    // Simulated holds
    const userReservation = currentUser.reservations.find(r => r.bookId === bookId);
    document.getElementById("modal-book-reservations-count").innerText = userReservation ? userReservation.queuePosition : (book.id === 'unu-005' ? 2 : 1);
  } else {
    reservationCountDiv.style.display = "none";
  }

  renderModalActions(book);
  document.getElementById("book-details-modal").style.display = "flex";
}

function closeBookDetailsModal() {
  document.getElementById("book-details-modal").style.display = "none";
  currentViewingBookId = null;
}

function closeModalOnOutsideClick(e, modalId) {
  if (e.target.id === modalId) {
    if (modalId === "book-details-modal") closeBookDetailsModal();
    if (modalId === "add-book-modal") closeAddBookModal();
    if (modalId === "admin-loan-modal") closeAdminLoanModal();
    if (modalId === "add-student-modal") closeAddStudentModal();
  }
}

function renderModalActions(book) {
  const actionsContainer = document.getElementById("modal-actions-container");
  
  const isBorrowed = currentUser.borrowedBooks.some(loan => loan.bookId === book.id);
  const isInWishlist = currentUser.wishlist.includes(book.id);
  const hasReservation = currentUser.reservations.some(res => res.bookId === book.id);
  
  let html = "";
  
  if (isBorrowed) {
    html += `<button class="primary-btn" style="background-color: var(--color-midnight);" onclick="executeReturnInModal('${book.id}')">Kembalikan Buku</button>`;
  } else if (book.stock > 0) {
    html += `<button class="primary-btn" onclick="executeBorrowInModal('${book.id}')"><i class="fa-solid fa-book-bookmark"></i> Pinjam Buku</button>`;
  } else {
    if (hasReservation) {
      html += `<button class="primary-btn" style="background-color: var(--color-danger);" onclick="executeCancelReservationInModal('${book.id}')">Batalkan Reservasi</button>`;
    } else {
      html += `<button class="primary-btn" style="background-color: var(--color-amber);" onclick="executeReservationInModal('${book.id}')"><i class="fa-solid fa-clock"></i> Antre Reservasi</button>`;
    }
  }

  if (isInWishlist) {
    html += `<button class="action-btn-sm btn-danger" style="padding: 0.8rem 1.2rem; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;" onclick="executeWishlistToggleInModal('${book.id}')"><i class="fa-solid fa-heart-crack"></i> Hapus Wishlist</button>`;
  } else {
    html += `<button class="action-btn-sm" style="padding: 0.8rem 1.2rem; font-size: 0.95rem; background-color: transparent; border: 1px solid var(--color-teal); color: var(--color-teal); display: flex; align-items: center; gap: 0.4rem;" onclick="executeWishlistToggleInModal('${book.id}')"><i class="fa-solid fa-heart"></i> Tambah Wishlist</button>`;
  }

  actionsContainer.innerHTML = html;
}

// Modal Wrapper Actions
async function executeBorrowInModal(bookId) {
  await borrowBook(bookId);
  openBookDetailsModal(bookId);
}

async function executeReturnInModal(bookId) {
  await returnBook(bookId);
  openBookDetailsModal(bookId);
}

async function executeReservationInModal(bookId) {
  await joinReservationQueue(bookId);
  openBookDetailsModal(bookId);
}

async function executeCancelReservationInModal(bookId) {
  await cancelReservation(bookId);
  openBookDetailsModal(bookId);
}

async function executeWishlistToggleInModal(bookId) {
  await toggleWishlist(bookId);
  openBookDetailsModal(bookId);
}

// Transactions Callbacks
async function borrowBook(bookId) {
  try {
    const res = await apiCall('/api/loans', 'POST', { bookId });
    showToast(res.message, "success");
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

async function returnBook(bookId) {
  try {
    const res = await apiCall('/api/loans/return', 'POST', { bookId });
    showToast(res.message, "success");
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

async function joinReservationQueue(bookId) {
  try {
    const res = await apiCall('/api/reservations', 'POST', { bookId });
    showToast(res.message, "success");
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

async function cancelReservation(bookId) {
  try {
    const res = await apiCall('/api/reservations/cancel', 'POST', { bookId });
    showToast(res.message, "info");
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

async function toggleWishlist(bookId) {
  try {
    const res = await apiCall(`/api/wishlist/${bookId}/toggle`, 'POST');
    showToast(res.message, "info");
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

// DOM Loaded
document.addEventListener("DOMContentLoaded", initApp);

// Admin Circulation (Loans) Management Methods
function renderAdminLoans() {
  const tbody = document.getElementById("admin-loans-tbody");
  if (!tbody) return;

  if (allLoans.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--color-charcoal); opacity: 0.5; padding: 2rem;">Tidak ada transaksi peminjaman aktif.</td></tr>`;
    return;
  }

  tbody.innerHTML = allLoans.map(loan => {
    const borrowDate = new Date(loan.borrowDate);
    const dueDate = new Date(loan.dueDate);
    
    const diffTime = dueDate.getTime() - currentDate.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    let statusClass = "status-available";
    let statusText = "Aktif";
    
    if (diffDays < 0 || loan.status === 'overdue') {
      statusClass = "status-out";
      statusText = "Terlambat";
    }

    return `
      <tr>
        <td>
          <div style="font-weight: bold; color: var(--color-midnight);">${loan.userName}</div>
          <div style="font-size: 0.80rem; color: var(--color-charcoal); opacity: 0.85;">NIM: ${loan.userNim}</div>
          <div style="font-size: 0.75rem; color: var(--color-teal-dark); margin-top: 2px;"><i class="fa-solid fa-envelope" style="width: 14px;"></i> ${loan.userEmail || '-'}</div>
          <div style="font-size: 0.75rem; color: var(--color-charcoal); opacity: 0.7;"><i class="fa-solid fa-phone" style="width: 14px;"></i> ${loan.userPhone || '-'}</div>
        </td>
        <td>
          <div style="font-weight: bold; color: var(--color-midnight);">${loan.bookTitle}</div>
          <div style="font-size: 0.8rem; color: var(--color-charcoal); opacity: 0.8;">ID: ${loan.bookId}</div>
        </td>
        <td>${borrowDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</td>
        <td>${dueDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</td>
        <td><span class="book-availability ${statusClass}">${statusText}</span></td>
        <td>
          <div style="display: flex; gap: 0.4rem;">
            <button class="action-btn-sm" style="background-color: var(--color-midnight);" onclick="adminReturnLoan('${loan.id}')" title="Kembalikan Buku"><i class="fa-solid fa-rotate-left"></i> Kembali</button>
            <button class="action-btn-sm" style="background-color: var(--color-teal);" onclick="openEditLoanModal('${loan.id}')" title="Edit Transaksi"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
            <button class="action-btn-sm btn-danger" onclick="adminDestroyLoan('${loan.id}')" title="Hapus Catatan"><i class="fa-solid fa-trash-can"></i></button>
          </div>
        </td>
      </tr>
    `;
  }).join("");
}

function openAddLoanModal() {
  document.getElementById("admin-loan-modal-title").innerText = "Catat Peminjaman Baru";
  document.getElementById("form-loan-id").value = "";
  
  // Populate dropdowns
  populateAdminLoanDropdowns();
  
  // Set default dates
  const today = formatDateForInput(currentDate);
  const twoWeeksLater = new Date(currentDate);
  twoWeeksLater.setDate(twoWeeksLater.getDate() + 14);
  const due = formatDateForInput(twoWeeksLater);
  
  document.getElementById("form-loan-borrow-date").value = today;
  document.getElementById("form-loan-due-date").value = due;
  
  document.getElementById("form-loan-student").disabled = false;
  document.getElementById("form-loan-book").disabled = false;
  document.getElementById("form-loan-status-group").style.display = "none";
  
  document.getElementById("admin-loan-modal").style.display = "flex";
}

function openEditLoanModal(loanId) {
  const loan = allLoans.find(l => l.id === parseInt(loanId) || l.id === loanId);
  if (!loan) return;

  document.getElementById("admin-loan-modal-title").innerText = "Edit Peminjaman";
  document.getElementById("form-loan-id").value = loan.id;

  populateAdminLoanDropdowns();

  document.getElementById("form-loan-student").value = loan.userId;
  document.getElementById("form-loan-book").value = loan.bookId;
  
  document.getElementById("form-loan-borrow-date").value = formatDateForInput(new Date(loan.borrowDate));
  document.getElementById("form-loan-due-date").value = formatDateForInput(new Date(loan.dueDate));
  
  // Can edit status
  document.getElementById("form-loan-status-group").style.display = "block";
  document.getElementById("form-loan-status").value = loan.status;

  document.getElementById("admin-loan-modal").style.display = "flex";
}

function closeAdminLoanModal() {
  document.getElementById("admin-loan-modal").style.display = "none";
  document.getElementById("admin-loan-form").reset();
}

function populateAdminLoanDropdowns() {
  const studentSelect = document.getElementById("form-loan-student");
  const bookSelect = document.getElementById("form-loan-book");

  studentSelect.innerHTML = studentsList.map(s => `
    <option value="${s.id}">${s.name} (NIM: ${s.nim || '-'} | Telp: ${s.phone || '-'})</option>
  `).join("");

  bookSelect.innerHTML = books.map(b => `
    <option value="${b.id}">${b.title} (Stok: ${b.stock})</option>
  `).join("");
}

function formatDateForInput(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

async function handleAdminLoanSubmit(e) {
  e.preventDefault();
  
  const loanId = document.getElementById("form-loan-id").value;
  const userId = document.getElementById("form-loan-student").value;
  const bookId = document.getElementById("form-loan-book").value;
  const borrowDate = document.getElementById("form-loan-borrow-date").value;
  const dueDate = document.getElementById("form-loan-due-date").value;
  const status = document.getElementById("form-loan-status").value;

  const payload = { userId, bookId, borrowDate, dueDate, status };

  try {
    let res;
    if (loanId) {
      res = await apiCall(`/api/admin/loans/${loanId}/update`, 'POST', payload);
    } else {
      res = await apiCall('/api/admin/loans', 'POST', payload);
    }
    
    showToast(res.message, "success");
    await loadStateFromServer();
    refreshUI();
    closeAdminLoanModal();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

async function adminReturnLoan(loanId) {
  try {
    const res = await apiCall(`/api/admin/loans/${loanId}/return`, 'POST');
    showToast(res.message, "success");
    await loadStateFromServer();
    refreshUI();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

async function adminDestroyLoan(loanId) {
  if (confirm("Apakah Anda yakin ingin menghapus catatan transaksi peminjaman ini secara permanen?")) {
    try {
      const res = await apiCall(`/api/admin/loans/${loanId}`, 'DELETE');
      showToast(res.message, "info");
      await loadStateFromServer();
      refreshUI();
    } catch (error) {
      showToast(error.message, 'danger');
    }
  }
}

// Admin Student Management Rendering & CRUD
function renderAdminStudents() {
  const tbody = document.getElementById("admin-students-tbody");
  if (!tbody) return;

  if (studentsList.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--color-charcoal); opacity: 0.5; padding: 2rem;">Tidak ada mahasiswa terdaftar.</td></tr>`;
    return;
  }

  tbody.innerHTML = studentsList.map(s => {
    return `
      <tr>
        <td>
          <div style="font-weight: bold; color: var(--color-midnight);">${s.name}</div>
          <div style="font-size: 0.85rem; color: var(--color-charcoal); opacity: 0.85;">NIM: ${s.nim || '-'}</div>
        </td>
        <td>
          <div style="font-weight: bold; color: var(--color-teal-dark);">${s.faculty || '-'}</div>
          <div style="font-size: 0.8rem; color: var(--color-charcoal); opacity: 0.8;">${s.prodi || '-'}</div>
        </td>
        <td style="font-family: monospace; font-size: 0.9rem;">${s.email}</td>
        <td>${s.phone || '-'}</td>
        <td>
          <div style="display: flex; gap: 0.4rem;">
            <button class="action-btn-sm" style="background-color: var(--color-teal);" onclick="openEditStudentModal('${s.id}')" title="Edit Mahasiswa & Reset Password"><i class="fa-solid fa-user-pen"></i> Edit</button>
            <button class="action-btn-sm btn-danger" onclick="deleteStudent('${s.id}')" title="Hapus Akun"><i class="fa-solid fa-trash-can"></i></button>
          </div>
        </td>
      </tr>
    `;
  }).join("");
}

function openAddStudentModal() {
  document.getElementById("add-student-modal-title").innerText = "Tambah Mahasiswa Baru";
  document.getElementById("form-student-id").value = "";
  document.getElementById("form-student-name").value = "";
  document.getElementById("form-student-nim").value = "";
  document.getElementById("form-student-phone").value = "";
  document.getElementById("form-student-email").value = "";
  document.getElementById("form-student-faculty").value = "";
  document.getElementById("form-student-prodi").innerHTML = '<option value="">-- Pilih Prodi --</option>';
  
  document.getElementById("form-student-password").value = "";
  document.getElementById("form-student-password").required = true;
  document.getElementById("label-student-password").innerText = "Kata Sandi";
  document.getElementById("hint-student-password").style.display = "none";
  
  document.getElementById("add-student-modal").style.display = "flex";
}

function openEditStudentModal(studentId) {
  const student = studentsList.find(s => s.id === parseInt(studentId) || s.id === studentId);
  if (!student) return;

  document.getElementById("add-student-modal-title").innerText = "Edit Profil Mahasiswa";
  document.getElementById("form-student-id").value = student.id;
  document.getElementById("form-student-name").value = student.name;
  document.getElementById("form-student-nim").value = student.nim;
  document.getElementById("form-student-phone").value = student.phone || "";
  document.getElementById("form-student-email").value = student.email;
  
  document.getElementById("form-student-faculty").value = student.faculty || "";
  populateModalProdiDropdown(student.faculty || "");
  document.getElementById("form-student-prodi").value = student.prodi || "";

  document.getElementById("form-student-password").value = "";
  document.getElementById("form-student-password").required = false;
  document.getElementById("label-student-password").innerText = "Kata Sandi Baru (Opsional)";
  document.getElementById("hint-student-password").style.display = "block";

  document.getElementById("add-student-modal").style.display = "flex";
}

function closeAddStudentModal() {
  document.getElementById("add-student-modal").style.display = "none";
  document.getElementById("add-student-form").reset();
}

const modalFacultyProdiMapping = {
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

function handleModalFacultyChange() {
  const faculty = document.getElementById("form-student-faculty").value;
  populateModalProdiDropdown(faculty);
}

function populateModalProdiDropdown(faculty) {
  const prodiSelect = document.getElementById("form-student-prodi");
  prodiSelect.innerHTML = '<option value="">-- Pilih Prodi --</option>';
  
  if (faculty && modalFacultyProdiMapping[faculty]) {
    modalFacultyProdiMapping[faculty].forEach(p => {
      const opt = document.createElement("option");
      opt.value = p;
      opt.innerText = p;
      prodiSelect.appendChild(opt);
    });
  }
}

async function handleAdminAddStudentSubmit(e) {
  e.preventDefault();
  
  const studentId = document.getElementById("form-student-id").value;
  const name = document.getElementById("form-student-name").value;
  const nim = document.getElementById("form-student-nim").value;
  const phone = document.getElementById("form-student-phone").value;
  const email = document.getElementById("form-student-email").value;
  const faculty = document.getElementById("form-student-faculty").value;
  const prodi = document.getElementById("form-student-prodi").value;
  const password = document.getElementById("form-student-password").value;

  const payload = { name, nim, phone, email, faculty, prodi };
  if (password) {
    payload.password = password;
  }

  try {
    let res;
    if (studentId) {
      res = await apiCall(`/api/admin/students/${studentId}/update`, 'POST', payload);
    } else {
      res = await apiCall('/api/admin/students', 'POST', payload);
    }
    
    showToast(res.message, "success");
    await loadStateFromServer();
    refreshUI();
    closeAddStudentModal();
  } catch (error) {
    showToast(error.message, 'danger');
  }
}

async function deleteStudent(studentId) {
  if (confirm("Apakah Anda yakin ingin menghapus akun mahasiswa ini secara permanen?")) {
    try {
      const res = await apiCall(`/api/admin/students/${studentId}`, 'DELETE');
      showToast(res.message, "info");
      await loadStateFromServer();
      refreshUI();
    } catch (error) {
      showToast(error.message, 'danger');
    }
  }
}
