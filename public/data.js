// Seed data for Perpustakaan UNU NTB Website
const initialBooks = [
  {
    id: "unu-001",
    title: "Bumi Manusia",
    author: "Pramoedya Ananta Toer",
    genre: "Historical Fiction",
    description: "Roman sejarah luar biasa yang berlatar belakang kebangkitan nasional Indonesia pada awal abad ke-20. Mengisahkan perjuangan Minke melawan ketidakadilan kolonial.",
    stock: 2,
    totalStock: 3,
    publishedYear: 1980,
    popularity: 98,
    coverStyle: {
      bgStart: "#4A2E2B",
      bgEnd: "#2D1B19",
      text: "#E9D5C4",
      accent: "#C19A6B",
      pattern: "border"
    }
  },
  {
    id: "unu-002",
    title: "Laskar Pelangi",
    author: "Andrea Hirata",
    genre: "Drama",
    description: "Kisah inspiratif tentang perjuangan sepuluh anak di Belitung dari keluarga miskin untuk mendapatkan pendidikan yang layak di sekolah dasar Muhammadiyah yang terancam ditutup.",
    stock: 5,
    totalStock: 5,
    publishedYear: 2005,
    popularity: 95,
    coverStyle: {
      bgStart: "#398E8E",
      bgEnd: "#1D5F5F",
      text: "#F4F4F2",
      accent: "#E2B842",
      pattern: "stripes"
    }
  },
  {
    id: "unu-003",
    title: "Saman",
    author: "Ayu Utami",
    genre: "Modern Literature",
    description: "Sebuah novel penting dalam sastra Indonesia modern yang mengeksplorasi tema seksualitas, politik, persahabatan, dan kebebasan berekspresi di masa akhir Orde Baru.",
    stock: 0,
    totalStock: 1,
    publishedYear: 1998,
    popularity: 88,
    coverStyle: {
      bgStart: "#2D4263",
      bgEnd: "#1C2D46",
      text: "#F4F4F2",
      accent: "#D96B27",
      pattern: "circle"
    }
  },
  {
    id: "unu-004",
    title: "Madilog",
    author: "Tan Malaka",
    genre: "Philosophy",
    description: "Karya magnum opus Tan Malaka yang memadukan Materialisme, Dialektika, dan Logika sebagai jalan keluar dari cara berpikir mistis menuju pemikiran ilmiah modern bagi bangsa Indonesia.",
    stock: 1,
    totalStock: 2,
    publishedYear: 1943,
    popularity: 90,
    coverStyle: {
      bgStart: "#1E2522",
      bgEnd: "#0C100E",
      text: "#E5E5E2",
      accent: "#C15C3D",
      pattern: "grid"
    }
  },
  {
    id: "unu-005",
    title: "Laut Bercerita",
    author: "Leila S. Chudori",
    genre: "Historical Fiction",
    description: "Sebuah novel yang menceritakan tentang hilangnya para aktivis mahasiswa di masa Orde Baru dan bagaimana keluarga serta sahabat yang ditinggalkan bertahan menghadapi kehilangan.",
    stock: 0,
    totalStock: 2,
    publishedYear: 2017,
    popularity: 97,
    coverStyle: {
      bgStart: "#1A3A3A",
      bgEnd: "#0A1F1F",
      text: "#F4F4F2",
      accent: "#8AAEA5",
      pattern: "waves"
    }
  },
  {
    id: "unu-006",
    title: "Cantik itu Luka",
    author: "Eka Kurniawan",
    genre: "Magical Realism",
    description: "Mahakarya yang menggabungkan sejarah perjuangan kemerdekaan dengan dongeng lokal, tragedi keluarga, dan realisme magis, dimulai dari kebangkitan seorang pelacur legendaris dari kubur.",
    stock: 3,
    totalStock: 3,
    publishedYear: 2002,
    popularity: 92,
    coverStyle: {
      bgStart: "#722F37",
      bgEnd: "#4A151B",
      text: "#F7D6D0",
      accent: "#D4AF37",
      pattern: "floral"
    }
  },
  {
    id: "unu-007",
    title: "Introduction to Algorithms",
    author: "Thomas H. Cormen",
    genre: "Technology",
    description: "Buku panduan komprehensif mengenai algoritma komputer, mencakup analisis efisiensi, desain algoritma, struktur data, dan topik-topik lanjutan untuk pengembang profesional.",
    stock: 2,
    totalStock: 2,
    publishedYear: 2009,
    popularity: 85,
    coverStyle: {
      bgStart: "#2E3A4E",
      bgEnd: "#1A2533",
      text: "#FFFFFF",
      accent: "#398E8E",
      pattern: "tech"
    }
  },
  {
    id: "unu-008",
    title: "Principles of Data Science",
    author: "Sinan Ozdemir",
    genre: "Technology",
    description: "Membahas konsep-konsep inti statistika, pemrosesan data, pemodelan machine learning, dan teknik evaluasi untuk membangun karir yang kuat di bidang sains data.",
    stock: 1,
    totalStock: 1,
    publishedYear: 2016,
    popularity: 80,
    coverStyle: {
      bgStart: "#1E3D59",
      bgEnd: "#172F44",
      text: "#F5F0E1",
      accent: "#FF6E40",
      pattern: "tech"
    }
  },
  {
    id: "unu-009",
    title: "Philosophy of Science",
    author: "Samir Okasha",
    genre: "Philosophy",
    description: "Pengantar singkat namun mendalam mengenai sifat, metode, dan batas-batas pengetahuan ilmiah, mendiskusikan debat utama antara realisme dan antirealisme.",
    stock: 2,
    totalStock: 2,
    publishedYear: 2002,
    popularity: 78,
    coverStyle: {
      bgStart: "#424B54",
      bgEnd: "#2E353B",
      text: "#E6C280",
      accent: "#E6C280",
      pattern: "border"
    }
  },
  {
    id: "unu-010",
    title: "A Brief History of Time",
    author: "Stephen Hawking",
    genre: "Science",
    description: "Karya populer fisikawan teoritis terkemuka yang menjelaskan konsep lubang hitam, waktu, ruang angkasa, dan asal-usul alam semesta kepada pembaca awam dengan bahasa yang mudah dicerna.",
    stock: 2,
    totalStock: 2,
    publishedYear: 1988,
    popularity: 94,
    coverStyle: {
      bgStart: "#131826",
      bgEnd: "#090C14",
      text: "#E5F0FF",
      accent: "#76A2E8",
      pattern: "stars"
    }
  },
  {
    id: "unu-011",
    title: "Cosmos",
    author: "Carl Sagan",
    genre: "Science",
    description: "Petualangan ilmiah kosmis yang mengeksplorasi hubungan antara evolusi kosmos, peradaban manusia, dan penjelajahan ruang angkasa dalam prosa sastra yang indah.",
    stock: 4,
    totalStock: 4,
    publishedYear: 1980,
    popularity: 93,
    coverStyle: {
      bgStart: "#2E1C47",
      bgEnd: "#170C25",
      text: "#FFDF9F",
      accent: "#FFDF9F",
      pattern: "stars"
    }
  }
];

const initialUser = {
  name: "Fadhil Rahmatullah",
  nim: "2024102004",
  role: "Mahasiswa",
  prodi: "Teknik Informatika",
  borrowedBooks: [
    {
      bookId: "unu-001",
      borrowDate: "2026-06-05T10:00:00Z",
      dueDate: "2026-06-19T10:00:00Z",
      status: "active"
    },
    {
      bookId: "unu-004",
      borrowDate: "2026-05-20T09:00:00Z",
      dueDate: "2026-06-03T09:00:00Z", // Simulation will mark this as overdue!
      status: "overdue"
    }
  ],
  wishlist: ["unu-002", "unu-007"],
  reservations: [
    {
      bookId: "unu-003",
      reservedDate: "2026-06-10T14:30:00Z",
      queuePosition: 1
    }
  ]
};

// Expose to window object for ease of vanilla JS architecture
window.UNU_DATA = {
  initialBooks,
  initialUser
};
