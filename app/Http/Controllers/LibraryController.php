<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LibraryController extends Controller
{
    public function getState()
    {
        $user = Auth::user();
        $simDate = $this->CarbonSimDate();
        
        // Check for overdue items automatically on state retrieval
        $this->checkOverdueLoansForUser($user, $simDate);

        // Fetch user data details
        $userData = null;
        if ($user) {
            $borrowed = DB::table('loans')
                ->where('user_id', $user->id)
                ->where('status', '!=', 'returned')
                ->get();
                
            $reservations = DB::table('reservations')
                ->where('user_id', $user->id)
                ->orderBy('queue_position', 'asc')
                ->get();

            $wishlist = json_decode($user->wishlist ?: '[]', true);

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nim' => $user->nim,
                'role' => $user->role,
                'prodi' => $user->prodi,
                'borrowedBooks' => $borrowed->map(function ($loan) {
                    return [
                        'id' => $loan->id,
                        'bookId' => $loan->book_id,
                        'borrowDate' => $loan->borrow_date,
                        'dueDate' => $loan->due_date,
                        'status' => $loan->status
                    ];
                }),
                'reservations' => $reservations->map(function ($res) {
                    return [
                        'id' => $res->id,
                        'bookId' => $res->book_id,
                        'reservedDate' => $res->reserved_date,
                        'queuePosition' => $res->queue_position
                    ];
                }),
                'wishlist' => $wishlist
            ];
        }

        // Fetch Books
        $books = DB::table('books')->get()->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'genre' => $book->genre,
                'classNumber' => $book->class_number ?? '',
                'isbn' => $book->isbn ?? '',
                'description' => $book->description,
                'stock' => $book->stock,
                'totalStock' => $book->total_stock,
                'publishedYear' => $book->published_year,
                'popularity' => $book->popularity,
                'coverStyle' => json_decode($book->cover_style),
                'imagePath' => $book->image_path
            ];
        });

        // Fetch notifications
        $notifications = [];
        if ($user) {
            $query = DB::table('notifications');
            if ($user->role !== 'Pustakawan') {
                $query->where('notifications.user_id', $user->id);
            }
            $notifications = $query
                ->leftJoin('users', 'notifications.user_id', '=', 'users.id')
                ->select('notifications.*', 'users.name as userName')
                ->orderBy('notifications.created_at', 'desc')
                ->get()->map(function ($n) use ($user) {
                    $title = $n->title;
                    if ($user->role === 'Pustakawan' && $n->userName) {
                        $title = $n->title . " (" . $n->userName . ")";
                    }
                    return [
                        'id' => $n->id,
                        'title' => $title,
                        'message' => $n->message,
                        'type' => $n->type,
                        'related_id' => $n->related_id,
                        'unread' => !$n->is_read,
                        'time' => $n->created_at
                    ];
                })->all();

            if ($user->role === 'Pustakawan') {
                $outOfStock = DB::table('books')->where('stock', 0)->get();
                foreach ($outOfStock as $b) {
                    array_unshift($notifications, [
                        'id' => 'restock-' . $b->id,
                        'title' => "Buku Kehabisan Stok (Perlu Restock)",
                        'message' => "Buku '{$b->title}' karya {$b->author} saat ini kehabisan stok (0). Klik notifikasi ini untuk mengedit / melakukan restock.",
                        'type' => 'warning',
                        'related_id' => $b->id,
                        'unread' => true,
                        'time' => now()->toIso8601String()
                    ]);
                }
            }
        }

        // Fetch Circulation Logs
        $logs = DB::table('circulation_logs')
            ->orderBy('timestamp', 'desc')
            ->get()->map(function ($l) {
                return [
                    'time' => $l->timestamp,
                    'activity' => $l->activity,
                    'detail' => $l->detail
                ];
            });

        $allLoans = [];
        $allReservations = [];
        $students = [];
        if ($user && $user->role === 'Pustakawan') {
            $allLoans = DB::table('loans')
                ->join('users', 'loans.user_id', '=', 'users.id')
                ->join('books', 'loans.book_id', '=', 'books.id')
                ->select(
                    'loans.id as id',
                    'loans.user_id as userId',
                    'users.name as userName',
                    'users.nim as userNim',
                    'users.email as userEmail',
                    'users.phone as userPhone',
                    'loans.book_id as bookId',
                    'books.title as bookTitle',
                    'loans.borrow_date as borrowDate',
                    'loans.due_date as dueDate',
                    'loans.status as status'
                )
                ->where('loans.status', '!=', 'returned')
                ->orderBy('loans.borrow_date', 'desc')
                ->get();

            $allReservations = DB::table('reservations')
                ->join('users', 'reservations.user_id', '=', 'users.id')
                ->join('books', 'reservations.book_id', '=', 'books.id')
                ->select(
                    'reservations.id as id',
                    'reservations.user_id as userId',
                    'users.name as userName',
                    'users.nim as userNim',
                    'reservations.book_id as bookId',
                    'books.title as bookTitle',
                    'reservations.reserved_date as reservedDate',
                    'reservations.queue_position as queuePosition'
                )
                ->orderBy('reservations.reserved_date', 'asc')
                ->get();

            $students = DB::table('users')
                ->where('role', 'Mahasiswa')
                ->select('id', 'name', 'nim', 'email', 'phone', 'faculty', 'prodi', 'password_plain')
                ->get();
        }

        return response()->json([
            'user' => $userData,
            'books' => $books,
            'notifications' => $notifications,
            'logs' => $logs,
            'sim_date' => $simDate->toIso8601String(),
            'all_loans' => $allLoans,
            'all_reservations' => $allReservations,
            'students' => $students
        ]);
    }

    public function borrowBook(Request $request)
    {
        $user = Auth::user();
        $bookId = $request->input('bookId');
        $simDate = $this->CarbonSimDate();

        $book = DB::table('books')->where('id', $bookId)->first();

        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan.'], 404);
        }

        if ($book->stock <= 0) {
            return response()->json(['message' => 'Stok buku kosong.'], 400);
        }

        // Check if already borrowed
        $alreadyBorrowed = DB::table('loans')
            ->where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->where('status', '!=', 'returned')
            ->exists();

        if ($alreadyBorrowed) {
            return response()->json(['message' => 'Anda sedang meminjam buku ini.'], 400);
        }

        // Decrement stock
        DB::table('books')->where('id', $bookId)->decrement('stock');

        // Create loan record (14 days period) with pending status
        $dueDate = (clone $simDate)->addDays(14);
        $loanId = DB::table('loans')->insertGetId([
            'book_id' => $bookId,
            'user_id' => $user->id,
            'borrow_date' => $simDate,
            'due_date' => $dueDate,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notify admin (all librarians) about pending loan request
        $adminIds = DB::table('users')->where('role', 'Pustakawan')->pluck('id');
        foreach ($adminIds as $adminId) {
            DB::table('notifications')->insert([
                'user_id' => $adminId,
                'title' => 'Permintaan Pinjam Instan',
                'message' => "Mahasiswa {$user->name} meminta pinjaman buku '{$book->title}'.",
                'type' => 'hold', // indicates pending approval
                'related_id' => $loanId,
                'is_read' => false,
                'created_at' => $simDate,
                'updated_at' => $simDate,
            ]);
        }

        // Remove from wishlist if it was there
        $wishlist = json_decode($user->wishlist ?: '[]', true);
        if (($key = array_search($bookId, $wishlist)) !== false) {
            unset($wishlist[$key]);
            DB::table('users')->where('id', $user->id)->update(['wishlist' => json_encode(array_values($wishlist))]);
        }

        // Log and Notify
        DB::table('circulation_logs')->insert([
            'activity' => 'Peminjaman Buku',
            'detail' => "Mahasiswa {$user->name} meminjam '{$book->title}'. Kembali pada: {$dueDate->locale('id')->isoFormat('D MMM YYYY')}.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('notifications')->insert([
            'user_id' => $user->id,
            'title' => 'Peminjaman Berhasil',
            'message' => "Anda berhasil meminjam '{$book->title}'. Tanggal Jatuh Tempo: {$dueDate->locale('id')->isoFormat('D MMM YYYY')}.",
            'type' => 'info',
            'is_read' => false,
            'created_at' => $simDate,
            'updated_at' => $simDate
        ]);

        return response()->json(['message' => 'Peminjaman berhasil diproses.']);
    }

    public function approveLoan(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Hanya pustakawan yang dapat menyetujui peminjaman.'], 403);
        }

        $loanId = $request->input('loanId');
        $simDate = $this->CarbonSimDate();

        $loan = DB::table('loans')->where('id', $loanId)->first();
        if (!$loan) {
            return response()->json(['message' => 'Data peminjaman tidak ditemukan.'], 404);
        }

        if ($loan->status === 'pending') {
            // Persetujuan Peminjaman
            // Change status to active
            DB::table('loans')->where('id', $loanId)->update([
                'status' => 'active',
                'updated_at' => now()
            ]);

            $book = DB::table('books')->where('id', $loan->book_id)->first();
            $student = DB::table('users')->where('id', $loan->user_id)->first();

            // Check if this was a reservation request
            $reservation = DB::table('reservations')
                ->where('user_id', $loan->user_id)
                ->where('book_id', $loan->book_id)
                ->first();

            if ($reservation) {
                // Decrement stock for the reservation since it wasn't done during request
                DB::table('books')->where('id', $loan->book_id)->decrement('stock');
                
                // Delete reservation and update queue positions for others
                DB::table('reservations')->where('id', $reservation->id)->delete();
                DB::table('reservations')
                    ->where('book_id', $loan->book_id)
                    ->where('queue_position', '>', $reservation->queue_position)
                    ->decrement('queue_position');
            }

            // Create log entry for approval
            DB::table('circulation_logs')->insert([
                'activity' => 'Persetujuan Peminjaman',
                'detail' => "Pustakawan {$user->name} menyetujui peminjaman buku '{$book->title}' untuk mahasiswa {$student->name}.",
                'timestamp' => $simDate,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Mark the hold/pending notification as read
            DB::table('notifications')
                ->where('related_id', $loanId)
                ->where('type', 'hold')
                ->update(['is_read' => true]);

            // Send a success notification to the student
            DB::table('notifications')->insert([
                'user_id' => $loan->user_id,
                'title' => 'Persetujuan Peminjaman',
                'message' => "Permintaan pinjam instan untuk buku '{$book->title}' telah disetujui. Tanggal Jatuh Tempo: " . \Carbon\Carbon::parse($loan->due_date)->locale('id')->isoFormat('D MMM YYYY') . ".",
                'type' => 'info',
                'is_read' => false,
                'created_at' => $simDate,
                'updated_at' => $simDate
            ]);

            return response()->json(['message' => 'Peminjaman berhasil disetujui.']);

        } elseif ($loan->status === 'pending_return') {
            // Persetujuan Pengembalian
            // Change status to returned
            DB::table('loans')->where('id', $loanId)->update([
                'status' => 'returned',
                'updated_at' => now()
            ]);

            $book = DB::table('books')->where('id', $loan->book_id)->first();
            $student = DB::table('users')->where('id', $loan->user_id)->first();

            // Create log entry for return approval
            DB::table('circulation_logs')->insert([
                'activity' => 'Persetujuan Pengembalian',
                'detail' => "Pustakawan {$user->name} menyetujui pengembalian buku '{$book->title}' oleh mahasiswa {$student->name}.",
                'timestamp' => $simDate,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Mark the hold/pending notification as read
            DB::table('notifications')
                ->where('related_id', $loanId)
                ->where('type', 'hold')
                ->update(['is_read' => true]);

            // Send a success notification to the student
            DB::table('notifications')->insert([
                'user_id' => $loan->user_id,
                'title' => 'Pengembalian Berhasil',
                'message' => "Buku '{$book->title}' telah berhasil dikembalikan ke perpustakaan.",
                'type' => 'info',
                'is_read' => false,
                'created_at' => $simDate,
                'updated_at' => $simDate
            ]);

            // Queue logic: Check if another student has reserved this book
            $nextReservation = DB::table('reservations')
                ->where('book_id', $loan->book_id)
                ->orderBy('queue_position', 'asc')
                ->first();

            if ($nextReservation) {
                // Allocate the book to this user!
                DB::table('reservations')->where('id', $nextReservation->id)->delete();
                
                // Adjust other queue positions for this book
                DB::table('reservations')
                    ->where('book_id', $loan->book_id)
                    ->decrement('queue_position');

                $nextUser = DB::table('users')->where('id', $nextReservation->user_id)->first();
                $dueDate = (clone $simDate)->addDays(14);
                
                DB::table('loans')->insert([
                    'book_id' => $loan->book_id,
                    'user_id' => $nextUser->id,
                    'borrow_date' => $simDate,
                    'due_date' => $dueDate,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('circulation_logs')->insert([
                    'activity' => 'Pemberian Antrean',
                    'detail' => "Buku '{$book->title}' dialokasikan dari daftar reservasi ke Mahasiswa {$nextUser->name}.",
                    'timestamp' => $simDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('notifications')->insert([
                    'user_id' => $nextUser->id,
                    'title' => 'Reservasi Siap & Dipinjam',
                    'message' => "Buku '{$book->title}' yang Anda antre sekarang tersedia dan otomatis dipinjamkan ke akun Anda. Jatuh tempo: {$dueDate->locale('id')->isoFormat('D MMM YYYY')}.",
                    'type' => 'hold',
                    'is_read' => false,
                    'created_at' => $simDate,
                    'updated_at' => $simDate
                ]);
            } else {
                // Increment stock
                DB::table('books')->where('id', $loan->book_id)->increment('stock');
            }

            return response()->json(['message' => 'Pengembalian berhasil disetujui.']);
        }

        return response()->json(['message' => 'Status peminjaman tidak valid untuk disetujui.'], 400);
    }

    public function returnBook(Request $request)
    {
        $user = Auth::user();
        $bookId = $request->input('bookId');
        $simDate = $this->CarbonSimDate();

        $loan = DB::table('loans')
            ->where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->where('status', '!=', 'returned')
            ->first();

        if (!$loan) {
            return response()->json(['message' => 'Transaksi peminjaman tidak aktif.'], 404);
        }

        if ($loan->status === 'pending_return') {
            return response()->json(['message' => 'Pengembalian buku ini sedang diproses.'], 400);
        }

        $book = DB::table('books')->where('id', $bookId)->first();

        // Update loan status to pending_return
        DB::table('loans')->where('id', $loan->id)->update([
            'status' => 'pending_return',
            'updated_at' => now()
        ]);

        DB::table('circulation_logs')->insert([
            'activity' => 'Permintaan Pengembalian',
            'detail' => "Mahasiswa {$user->name} mengajukan pengembalian buku '{$book->title}'.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Notify student that it is being processed
        DB::table('notifications')->insert([
            'user_id' => $user->id,
            'title' => 'Pengembalian Diproses',
            'message' => "Pengembalian buku '{$book->title}' sedang diproses. Menunggu persetujuan pustakawan.",
            'type' => 'info',
            'is_read' => false,
            'created_at' => $simDate,
            'updated_at' => $simDate
        ]);

        // Notify admins (all librarians) about pending return request
        $adminIds = DB::table('users')->where('role', 'Pustakawan')->pluck('id');
        foreach ($adminIds as $adminId) {
            DB::table('notifications')->insert([
                'user_id' => $adminId,
                'title' => 'Permintaan Pengembalian Buku',
                'message' => "Mahasiswa {$user->name} ingin mengembalikan buku '{$book->title}'.",
                'type' => 'hold', // indicates action required
                'related_id' => $loan->id,
                'is_read' => false,
                'created_at' => $simDate,
                'updated_at' => $simDate,
            ]);
        }

        return response()->json(['message' => 'Permintaan pengembalian buku berhasil diajukan.']);
    }

    public function joinReservation(Request $request)
    {
        $user = Auth::user();
        $bookId = $request->input('bookId');
        $simDate = $this->CarbonSimDate();

        $book = DB::table('books')->where('id', $bookId)->first();
        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan.'], 404);
        }

        $alreadyReserved = DB::table('reservations')
            ->where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->exists();

        if ($alreadyReserved) {
            return response()->json(['message' => 'Anda sudah berada dalam antrean buku ini.'], 400);
        }

        // Create a pending loan record so it shows up in Book Loan Administration
        $dueDate = (clone $simDate)->addDays(14);
        $loanId = DB::table('loans')->insertGetId([
            'book_id' => $bookId,
            'user_id' => $user->id,
            'borrow_date' => $simDate,
            'due_date' => $dueDate,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Get max queue position
        $maxQueue = DB::table('reservations')->where('book_id', $bookId)->max('queue_position') ?: 0;
        $newQueuePosition = $maxQueue + 1;

        DB::table('reservations')->insert([
            'book_id' => $bookId,
            'user_id' => $user->id,
            'reserved_date' => $simDate,
            'queue_position' => $newQueuePosition,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('circulation_logs')->insert([
            'activity' => 'Reservasi Buku',
            'detail' => "Mahasiswa {$user->name} meminta izin reservasi/pinjaman untuk '{$book->title}'.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Send notification to the student
        DB::table('notifications')->insert([
            'user_id' => $user->id,
            'title' => 'Permintaan Reservasi Diproses',
            'message' => "Permintaan reservasi Anda untuk buku '{$book->title}' sedang diproses. Menunggu persetujuan pustakawan.",
            'type' => 'hold',
            'is_read' => false,
            'created_at' => $simDate,
            'updated_at' => $simDate
        ]);

        // Send notification to all admins (Pustakawan) for approval
        $adminIds = DB::table('users')->where('role', 'Pustakawan')->pluck('id');
        foreach ($adminIds as $adminId) {
            DB::table('notifications')->insert([
                'user_id' => $adminId,
                'title' => 'Permintaan Antrean Reservasi',
                'message' => "Mahasiswa {$user->name} meminta izin antrean/pinjaman untuk buku '{$book->title}'.",
                'type' => 'hold',
                'related_id' => $loanId,
                'is_read' => false,
                'created_at' => $simDate,
                'updated_at' => $simDate
            ]);
        }

        return response()->json(['message' => 'Permintaan antrean reservasi berhasil dikirim ke pustakawan.']);
    }

    public function cancelReservation(Request $request)
    {
        $user = Auth::user();
        $bookId = $request->input('bookId');
        $simDate = $this->CarbonSimDate();

        $targetUserId = $user->id;
        if ($user->role === 'Pustakawan' && $request->has('userId')) {
            $targetUserId = $request->input('userId');
        }

        $reservation = DB::table('reservations')
            ->where('user_id', $targetUserId)
            ->where('book_id', $bookId)
            ->first();

        if (!$reservation) {
            return response()->json(['message' => 'Reservasi tidak ditemukan.'], 404);
        }

        $student = DB::table('users')->where('id', $targetUserId)->first();

        DB::table('reservations')->where('id', $reservation->id)->delete();

        // Adjust other queues
        DB::table('reservations')
            ->where('book_id', $bookId)
            ->where('queue_position', '>', $reservation->queue_position)
            ->decrement('queue_position');

        $book = DB::table('books')->where('id', $bookId)->first();

        DB::table('circulation_logs')->insert([
            'activity' => 'Batal Reservasi',
            'detail' => "Batal reservasi untuk '{$book->title}' (Mahasiswa: {$student->name}).",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Reservasi dibatalkan.']);
    }

    public function toggleWishlist(Request $request, $bookId)
    {
        $user = Auth::user();
        $wishlist = json_decode($user->wishlist ?: '[]', true);

        if (($key = array_search($bookId, $wishlist)) !== false) {
            unset($wishlist[$key]);
            $msg = 'Buku dihapus dari wishlist.';
        } else {
            $wishlist[] = $bookId;
            $msg = 'Buku ditambahkan ke wishlist.';
        }

        DB::table('users')->where('id', $user->id)->update([
            'wishlist' => json_encode(array_values($wishlist)),
            'updated_at' => now()
        ]);

        return response()->json(['message' => $msg]);
    }

    public function storeBook(Request $request)
    {
        // Check Librarian Role
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $request->validate([
            'id' => 'nullable|string',
            'title' => 'required|string',
            'author' => 'required|string',
            'genre' => 'required|string',
            'class_number' => 'nullable|string',
            'isbn' => 'nullable|string',
            'published_year' => 'required|integer',
            'stock' => 'required|integer',
            'description' => 'required|string'
        ]);

        $bookId = $request->input('id');
        if (empty($bookId)) {
            $class = trim($request->input('class_number') ?: '000');
            if (empty($class) || $class === '-') {
                $class = '000';
            }
            $count = DB::table('books')->where('id', 'like', $class . '-%')->count();
            $bookId = $class . '-' . ($count + 1);
            
            while (DB::table('books')->where('id', $bookId)->exists()) {
                $count++;
                $bookId = $class . '-' . ($count + 1);
            }
        } else {
            $bookId = trim($bookId);
            if (DB::table('books')->where('id', $bookId)->exists()) {
                return response()->json(['message' => "ID Buku '{$bookId}' sudah digunakan oleh buku lain."], 422);
            }
        }

        $imagePath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Ensure public/assets/covers directory exists
            $file->move(public_path('assets/covers'), $filename);
            $imagePath = 'assets/covers/' . $filename;
        }

        $coverStyleVal = $request->input('cover_style');
        if (is_string($coverStyleVal)) {
            $coverStyle = json_decode($coverStyleVal, true);
        } else {
            $coverStyle = $coverStyleVal;
        }

        if (!$coverStyle) {
            $coverStyle = [
                'bgStart' => '#2D4263',
                'bgEnd' => '#1C2D46',
                'text' => '#FFFFFF',
                'accent' => '#398E8E',
                'pattern' => 'border'
            ];
        }

        DB::table('books')->insert([
            'id' => $bookId,
            'title' => $request->input('title'),
            'author' => $request->input('author'),
            'genre' => $request->input('genre'),
            'class_number' => $request->input('class_number'),
            'isbn' => $request->input('isbn'),
            'published_year' => $request->input('published_year'),
            'stock' => $request->input('stock'),
            'total_stock' => $request->input('stock'),
            'description' => $request->input('description'),
            'popularity' => 50,
            'cover_style' => json_encode($coverStyle),
            'image_path' => $imagePath,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Katalog Buku Baru',
            'detail' => "Buku baru '{$request->input('title')}' ditambahkan ke database dengan ID: {$bookId}.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Send notification to everyone (using simple user insert or notification for all)
        DB::table('notifications')->insert([
            'user_id' => $user->id,
            'title' => 'Koleksi Baru Perpustakaan',
            'message' => "Buku baru berjudul '{$request->input('title')}' oleh {$request->input('author')} telah ditambahkan ke katalog. Silakan pinjam sekarang.",
            'type' => 'info',
            'is_read' => false,
            'created_at' => $simDate,
            'updated_at' => $simDate
        ]);

        return response()->json(['message' => 'Buku baru berhasil disimpan.']);
    }

    public function restockBook($bookId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $book = DB::table('books')->where('id', $bookId)->first();
        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan.'], 404);
        }

        // Increment total stock anyway because a new physical copy was added
        DB::table('books')->where('id', $bookId)->update([
            'total_stock' => $book->total_stock + 1,
            'updated_at' => now()
        ]);

        $simDate = $this->CarbonSimDate();

        // Queue logic: Check if another student has reserved this book
        $nextReservation = DB::table('reservations')
            ->where('book_id', $bookId)
            ->orderBy('queue_position', 'asc')
            ->first();

        if ($nextReservation) {
            // Allocate the book to this user (don't increment available stock, since it gets checked out immediately)
            DB::table('reservations')->where('id', $nextReservation->id)->delete();
            DB::table('reservations')->where('book_id', $bookId)->decrement('queue_position');

            $nextUser = DB::table('users')->where('id', $nextReservation->user_id)->first();
            $dueDate = (clone $simDate)->addDays(14);

            DB::table('loans')->insert([
                'book_id' => $bookId,
                'user_id' => $nextUser->id,
                'borrow_date' => $simDate,
                'due_date' => $dueDate,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('circulation_logs')->insert([
                'activity' => 'Pemberian Antrean',
                'detail' => "Buku '{$book->title}' dialokasikan dari daftar reservasi ke Mahasiswa {$nextUser->name}.",
                'timestamp' => $simDate,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('notifications')->insert([
                'user_id' => $nextUser->id,
                'title' => 'Reservasi Siap & Dipinjam',
                'message' => "Buku '{$book->title}' yang Anda antre sekarang tersedia dan otomatis dipinjamkan ke akun Anda. Jatuh tempo: {$dueDate->locale('id')->isoFormat('D MMM YYYY')}.",
                'type' => 'hold',
                'is_read' => false,
                'created_at' => $simDate,
                'updated_at' => $simDate
            ]);

            $msg = "Stok buku berhasil ditambah dan langsung dialokasikan ke antrean teratas.";
        } else {
            // No reservation, increment available stock
            DB::table('books')->where('id', $bookId)->increment('stock');
            $msg = "Stok buku berhasil ditambah.";
        }

        DB::table('circulation_logs')->insert([
            'activity' => 'Penambahan Stok',
            'detail' => "Pustakawan menambah 1 salinan buku '{$book->title}' ke dalam database.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => $msg]);
    }

    public function destroyBook($bookId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        // Check active loans
        $activeLoans = DB::table('loans')
            ->where('book_id', $bookId)
            ->where('status', '!=', 'returned')
            ->exists();

        if ($activeLoans) {
            return response()->json(['message' => 'Gagal menghapus! Buku sedang dipinjam oleh anggota.'], 400);
        }

        $book = DB::table('books')->where('id', $bookId)->first();
        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan.'], 404);
        }

        DB::table('books')->where('id', $bookId)->delete();

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Penghapusan Buku',
            'detail' => "Pustakawan menghapus buku '{$book->title}' secara permanen.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Buku berhasil dihapus dari katalog.']);
    }

    public function markNotificationRead($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $query = DB::table('notifications')->where('id', $id);
        if ($user->role !== 'Pustakawan') {
            $query->where('user_id', $user->id);
        }

        $query->update(['is_read' => true, 'updated_at' => now()]);

        return response()->json(['message' => 'Notifikasi ditandai telah dibaca.']);
    }

    public function simulateTime(Request $request)
    {
        $days = (int) $request->input('days');
        
        // Increase offset in session
        $offset = session('sim_date_offset', 0);
        $newOffset = $offset + $days;
        session(['sim_date_offset' => $newOffset]);

        $simDate = $this->CarbonSimDate();

        // Write log
        DB::table('circulation_logs')->insert([
            'activity' => 'Simulasi Waktu',
            'detail' => "Waktu berjalan maju sebanyak {$days} hari.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Evaluate overdue conditions instantly
        $user = Auth::user();
        if ($user) {
            $this->checkOverdueLoansForUser($user, $simDate);
        }

        return response()->json([
            'message' => "Simulasi waktu berjalan maju {$days} hari.",
            'sim_date' => $simDate->toIso8601String()
        ]);
    }

    private function checkOverdueLoansForUser($user, $simDate)
    {
        if (!$user) return;

        $loans = DB::table('loans')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        foreach ($loans as $loan) {
            $dueDate = Carbon::parse($loan->due_date);
            $timeDiff = $dueDate->getTimestamp() - $simDate->getTimestamp();

            if ($timeDiff < 0) {
                // Mark as overdue
                DB::table('loans')->where('id', $loan->id)->update(['status' => 'overdue', 'updated_at' => now()]);

                $book = DB::table('books')->where('id', $loan->book_id)->first();
                $title = $book ? $book->title : "Buku";

                // Add alert notification
                DB::table('notifications')->insert([
                    'user_id' => $user->id,
                    'title' => 'PEMBERITAHUAN JATUH TEMPO',
                    'message' => "Peminjaman buku '{$title}' telah melewati batas tanggal pengembalian (" . $dueDate->locale('id')->isoFormat('D MMM YYYY') . "). Segera kembalikan ke pustakawan untuk menghindari sanksi administratif tambahan.",
                    'type' => 'warning',
                    'is_read' => false,
                    'created_at' => $simDate,
                    'updated_at' => $simDate
                ]);

                DB::table('circulation_logs')->insert([
                    'activity' => 'Sanksi Keterlambatan',
                    'detail' => "Buku '{$title}' milik {$user->name} melewati tenggat waktu kembali.",
                    'timestamp' => $simDate,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            // Check for approaching deadline within 2 days
            elseif ($timeDiff > 0 && $timeDiff <= 2 * 24 * 60 * 60) {
                $book = DB::table('books')->where('id', $loan->book_id)->first();
                $title = $book ? $book->title : "Buku";
                $alertMsg = "Peminjaman buku '{$title}' akan jatuh tempo dalam waktu dekat.";

                $alreadyNotified = DB::table('notifications')
                    ->where('user_id', $user->id)
                    ->where('message', 'like', "%{$alertMsg}%")
                    ->exists();

                if (!$alreadyNotified) {
                    DB::table('notifications')->insert([
                        'user_id' => $user->id,
                        'title' => 'Pengingat Jatuh Tempo',
                        'message' => "Peminjaman buku '{$title}' akan jatuh tempo pada " . $dueDate->locale('id')->isoFormat('D MMM YYYY') . ". Mohon kembalikan tepat waktu.",
                        'type' => 'info',
                        'is_read' => false,
                        'created_at' => $simDate,
                        'updated_at' => $simDate
                    ]);
                }
            }
        }
    }

    public function adminCreateLoan(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $request->validate([
            'userId' => 'required',
            'bookId' => 'required',
            'borrowDate' => 'required|date',
            'dueDate' => 'required|date'
        ]);

        $studentId = $request->input('userId');
        $bookId = $request->input('bookId');
        $borrowDate = Carbon::parse($request->input('borrowDate'));
        $dueDate = Carbon::parse($request->input('dueDate'));

        $student = DB::table('users')->where('id', $studentId)->first();
        if (!$student) {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan.'], 404);
        }

        $book = DB::table('books')->where('id', $bookId)->first();
        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan.'], 404);
        }

        if ($book->stock <= 0) {
            return response()->json(['message' => 'Stok buku kosong.'], 400);
        }

        // Check if already borrowing
        $alreadyBorrowed = DB::table('loans')
            ->where('user_id', $studentId)
            ->where('book_id', $bookId)
            ->where('status', '!=', 'returned')
            ->exists();

        if ($alreadyBorrowed) {
            return response()->json(['message' => 'Mahasiswa ini sedang meminjam buku ini.'], 400);
        }

        // Decrement stock
        DB::table('books')->where('id', $bookId)->decrement('stock');

        // Insert loan
        $simDate = $this->CarbonSimDate();
        DB::table('loans')->insert([
            'book_id' => $bookId,
            'user_id' => $studentId,
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Remove from wishlist if it was there
        $wishlist = json_decode($student->wishlist ?: '[]', true);
        if (($key = array_search($bookId, $wishlist)) !== false) {
            unset($wishlist[$key]);
            DB::table('users')->where('id', $studentId)->update(['wishlist' => json_encode(array_values($wishlist))]);
        }

        // Log and Notify
        DB::table('circulation_logs')->insert([
            'activity' => 'Peminjaman Admin',
            'detail' => "Pustakawan mencatatkan peminjaman '{$book->title}' untuk Mahasiswa {$student->name}.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('notifications')->insert([
            'user_id' => $studentId,
            'title' => 'Buku Dipinjamkan oleh Pustakawan',
            'message' => "Pustakawan telah mendaftarkan peminjaman '{$book->title}' untuk Anda. Tanggal Jatuh Tempo: {$dueDate->locale('id')->isoFormat('D MMM YYYY')}.",
            'type' => 'info',
            'is_read' => false,
            'created_at' => $simDate,
            'updated_at' => $simDate
        ]);

        return response()->json(['message' => 'Peminjaman berhasil didaftarkan.']);
    }

    public function adminUpdateLoan(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $request->validate([
            'userId' => 'required',
            'bookId' => 'required',
            'borrowDate' => 'required|date',
            'dueDate' => 'required|date',
            'status' => 'required|string'
        ]);

        $loan = DB::table('loans')->where('id', $id)->first();
        if (!$loan) {
            return response()->json(['message' => 'Transaksi peminjaman tidak ditemukan.'], 404);
        }

        $studentId = $request->input('userId');
        $bookId = $request->input('bookId');
        $borrowDate = Carbon::parse($request->input('borrowDate'));
        $dueDate = Carbon::parse($request->input('dueDate'));
        $status = $request->input('status');

        $student = DB::table('users')->where('id', $studentId)->first();
        if (!$student) {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan.'], 404);
        }

        $book = DB::table('books')->where('id', $bookId)->first();
        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan.'], 404);
        }

        // Adjust stock if book or status changes
        if ($status === 'returned' && $loan->status !== 'returned') {
            return $this->adminReturnLoan($id);
        }

        if ($loan->book_id !== $bookId) {
            // Restore old book stock
            DB::table('books')->where('id', $loan->book_id)->increment('stock');
            // Decrement new book stock
            DB::table('books')->where('id', $bookId)->decrement('stock');
        }

        DB::table('loans')->where('id', $id)->update([
            'user_id' => $studentId,
            'book_id' => $bookId,
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'status' => $status,
            'updated_at' => now()
        ]);

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Edit Peminjaman',
            'detail' => "Pustakawan mengedit detail peminjaman ID: {$id} ('{$book->title}' untuk {$student->name}).",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Detail peminjaman berhasil diperbarui.']);
    }

    public function adminReturnLoan($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $loan = DB::table('loans')->where('id', $id)->first();
        if (!$loan || $loan->status === 'returned') {
            return response()->json(['message' => 'Transaksi tidak aktif.'], 404);
        }

        $book = DB::table('books')->where('id', $loan->book_id)->first();
        $borrower = DB::table('users')->where('id', $loan->user_id)->first();

        $simDate = $this->CarbonSimDate();

        // Update status to returned
        DB::table('loans')->where('id', $id)->update([
            'status' => 'returned',
            'updated_at' => now()
        ]);

        // Log and Notify
        DB::table('circulation_logs')->insert([
            'activity' => 'Pengembalian Admin',
            'detail' => "Pustakawan mengembalikan '{$book->title}' atas nama {$borrower->name}.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('notifications')->insert([
            'user_id' => $borrower->id,
            'title' => 'Buku Dikembalikan oleh Pustakawan',
            'message' => "Buku '{$book->title}' Anda telah dikembalikan oleh pustakawan.",
            'type' => 'info',
            'is_read' => false,
            'created_at' => $simDate,
            'updated_at' => $simDate
        ]);

        // Queue logic
        $nextReservation = DB::table('reservations')
            ->where('book_id', $loan->book_id)
            ->orderBy('queue_position', 'asc')
            ->first();

        if ($nextReservation) {
            DB::table('reservations')->where('id', $nextReservation->id)->delete();
            DB::table('reservations')->where('book_id', $loan->book_id)->decrement('queue_position');

            $nextUser = DB::table('users')->where('id', $nextReservation->user_id)->first();
            $dueDate = (clone $simDate)->addDays(14);

            DB::table('loans')->insert([
                'book_id' => $loan->book_id,
                'user_id' => $nextUser->id,
                'borrow_date' => $simDate,
                'due_date' => $dueDate,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('circulation_logs')->insert([
                'activity' => 'Pemberian Antrean',
                'detail' => "Buku '{$book->title}' dialokasikan dari daftar reservasi ke Mahasiswa {$nextUser->name}.",
                'timestamp' => $simDate,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('notifications')->insert([
                'user_id' => $nextUser->id,
                'title' => 'Reservasi Siap & Dipinjam',
                'message' => "Buku '{$book->title}' yang Anda antre sekarang tersedia dan otomatis dipinjamkan ke akun Anda. Jatuh tempo: {$dueDate->locale('id')->isoFormat('D MMM YYYY')}.",
                'type' => 'hold',
                'is_read' => false,
                'created_at' => $simDate,
                'updated_at' => $simDate
            ]);
        } else {
            DB::table('books')->where('id', $loan->book_id)->increment('stock');
        }

        return response()->json(['message' => 'Buku berhasil dikembalikan.']);
    }

    public function adminDestroyLoan($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $loan = DB::table('loans')->where('id', $id)->first();
        if (!$loan) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        // If active, restore book stock
        if ($loan->status !== 'returned') {
            DB::table('books')->where('id', $loan->book_id)->increment('stock');
        }

        DB::table('loans')->where('id', $id)->delete();

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Hapus Peminjaman',
            'detail' => "Pustakawan menghapus transaksi peminjaman ID: {$id} secara permanen.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Transaksi peminjaman berhasil dihapus.']);
    }

    public function updateBook(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $request->validate([
            'id' => 'required|string',
            'title' => 'required|string',
            'author' => 'required|string',
            'genre' => 'required|string',
            'class_number' => 'nullable|string',
            'isbn' => 'nullable|string',
            'published_year' => 'required|integer',
            'stock' => 'required|integer',
            'description' => 'required|string'
        ]);

        $book = DB::table('books')->where('id', $id)->first();
        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan.'], 404);
        }

        $newId = trim($request->input('id'));
        if (empty($newId)) {
            return response()->json(['message' => 'ID Buku tidak boleh kosong.'], 422);
        }

        if ($newId !== $id) {
            $exists = DB::table('books')->where('id', $newId)->exists();
            if ($exists) {
                return response()->json(['message' => "ID Buku '{$newId}' sudah digunakan oleh buku lain."], 422);
            }
        }

        $imagePath = $book->image_path;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/covers'), $filename);
            $imagePath = 'assets/covers/' . $filename;
        }

        $coverStyleVal = $request->input('cover_style');
        if (is_string($coverStyleVal)) {
            $coverStyle = json_decode($coverStyleVal, true);
        } else {
            $coverStyle = $coverStyleVal;
        }

        if (!$coverStyle) {
            $coverStyle = json_decode($book->cover_style, true);
        }

        // Adjust total_stock based on change in stock
        $stockDiff = $request->input('stock') - $book->stock;
        $newTotalStock = $book->total_stock + $stockDiff;
        if ($newTotalStock < 0) {
            $newTotalStock = 0;
        }

        // Disable foreign keys temporarily for SQLite
        DB::statement('PRAGMA foreign_keys = OFF;');

        DB::transaction(function () use ($id, $newId, $request, $newTotalStock, $imagePath, $coverStyle) {
            if ($newId !== $id) {
                // 1. Update loans
                DB::table('loans')->where('book_id', $id)->update(['book_id' => $newId]);
                
                // 2. Update reservations
                DB::table('reservations')->where('book_id', $id)->update(['book_id' => $newId]);
                
                // 3. Update wishlists in users table
                $users = DB::table('users')->where('wishlist', 'like', '%' . $id . '%')->get();
                foreach ($users as $user) {
                    $wishlist = json_decode($user->wishlist, true);
                    if (is_array($wishlist)) {
                        $key = array_search($id, $wishlist);
                        if ($key !== false) {
                            $wishlist[$key] = $newId;
                            DB::table('users')->where('id', $user->id)->update(['wishlist' => json_encode(array_values($wishlist))]);
                        }
                    }
                }
            }
            
            // 4. Update the book details and ID
            DB::table('books')->where('id', $id)->update([
                'id' => $newId,
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'genre' => $request->input('genre'),
                'class_number' => $request->input('class_number'),
                'isbn' => $request->input('isbn'),
                'published_year' => $request->input('published_year'),
                'stock' => $request->input('stock'),
                'total_stock' => $newTotalStock,
                'description' => $request->input('description'),
                'cover_style' => json_encode($coverStyle),
                'image_path' => $imagePath,
                'updated_at' => now()
            ]);
        });

        // Re-enable foreign keys
        DB::statement('PRAGMA foreign_keys = ON;');

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Edit Katalog Buku',
            'detail' => "Pustakawan memperbarui detail buku '{$request->input('title')}' (ID: {$id}).",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Detail buku berhasil diperbarui.']);
    }

    public function adminCreateStudent(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'nim' => 'required|string|unique:users,nim',
            'phone' => 'nullable|string',
            'faculty' => 'required|string',
            'prodi' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        DB::table('users')->insert([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'nim' => $request->input('nim'),
            'phone' => $request->input('phone'),
            'faculty' => $request->input('faculty'),
            'prodi' => $request->input('prodi'),
            'role' => 'Mahasiswa',
            'wishlist' => '[]',
            'password' => \Illuminate\Support\Facades\Hash::make($request->input('password')),
            'password_plain' => $request->input('password'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Registrasi Anggota',
            'detail' => "Pustakawan mendaftarkan Mahasiswa baru: {$request->input('name')} (NIM: {$request->input('nim')}).",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Akun mahasiswa berhasil ditambahkan.']);
    }

    public function adminUpdateStudent(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'nim' => 'required|string|unique:users,nim,' . $id,
            'phone' => 'nullable|string',
            'faculty' => 'required|string',
            'prodi' => 'required|string',
            'password' => 'nullable|string|min:6'
        ]);

        $updateData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'nim' => $request->input('nim'),
            'phone' => $request->input('phone'),
            'faculty' => $request->input('faculty'),
            'prodi' => $request->input('prodi'),
            'updated_at' => now()
        ];

        if ($request->filled('password')) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($request->input('password'));
            $updateData['password_plain'] = $request->input('password');
        }

        DB::table('users')->where('id', $id)->update($updateData);

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Update Anggota',
            'detail' => "Pustakawan memperbarui data Mahasiswa: {$request->input('name')} (NIM: {$request->input('nim')}).",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Data mahasiswa berhasil diperbarui.']);
    }

    public function adminDestroyStudent($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $student = DB::table('users')->where('id', $id)->first();
        if (!$student) {
            return response()->json(['message' => 'Mahasiswa tidak ditemukan.'], 404);
        }

        // Check if they have active loans
        $activeLoans = DB::table('loans')
            ->where('user_id', $id)
            ->where('status', '!=', 'returned')
            ->exists();

        if ($activeLoans) {
            return response()->json(['message' => 'Gagal menghapus! Mahasiswa sedang meminjam buku.'], 400);
        }

        DB::table('users')->where('id', $id)->delete();

        // Delete their reservations
        DB::table('reservations')->where('user_id', $id)->delete();

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Hapus Anggota',
            'detail' => "Pustakawan menghapus akun Mahasiswa: {$student->name} (NIM: {$student->nim}) secara permanen.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Akun mahasiswa berhasil dihapus.']);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:6',
            'confirmPassword' => 'required|same:newPassword'
        ], [
            'currentPassword.required' => 'Kata sandi saat ini harus diisi.',
            'newPassword.required' => 'Kata sandi baru harus diisi.',
            'newPassword.min' => 'Kata sandi baru minimal 6 karakter.',
            'confirmPassword.required' => 'Konfirmasi kata sandi harus diisi.',
            'confirmPassword.same' => 'Konfirmasi kata sandi tidak cocok dengan kata sandi baru.'
        ]);

        $currentPassword = $request->input('currentPassword');
        $newPassword = $request->input('newPassword');

        // Check if current password is correct
        if (!Hash::check($currentPassword, $user->password)) {
            return response()->json(['message' => 'Kata sandi saat ini salah.'], 400);
        }

        // Update password
        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($newPassword),
            'password_plain' => $newPassword,
            'updated_at' => now()
        ]);

        $simDate = $this->CarbonSimDate();

        DB::table('circulation_logs')->insert([
            'activity' => 'Ubah Kata Sandi',
            'detail' => "Pengguna {$user->name} ({$user->role}) mengubah kata sandi miliknya.",
            'timestamp' => $simDate,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Kata sandi berhasil diperbarui.']);
    }
}
