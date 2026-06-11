<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

// Import PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BackupController extends Controller
{
    /**
     * Get the status of the Google Drive credentials and destination folder.
     */
    public function getBackupStatus()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $credentialsPath = storage_path('app/google-credentials.json');
        $credentialsExist = file_exists($credentialsPath);
        $clientEmail = null;
        $projectId = null;

        if ($credentialsExist) {
            try {
                $creds = json_decode(file_get_contents($credentialsPath), true);
                $clientEmail = $creds['client_email'] ?? null;
                $projectId = $creds['project_id'] ?? null;
            } catch (Exception $e) {
                // Invalid JSON
                $credentialsExist = false;
            }
        }

        $folderId = env('GOOGLE_DRIVE_FOLDER_ID', '1k0oyuX4CWvlnt_zppvbwHUWEnsz_132i');
        $folderUrl = "https://drive.google.com/drive/folders/{$folderId}";

        return response()->json([
            'credentials_configured' => $credentialsExist,
            'client_email' => $clientEmail,
            'project_id' => $projectId,
            'folder_id' => $folderId,
            'folder_url' => $folderUrl
        ]);
    }

    /**
     * Export database to Excel and upload to Google Drive.
     */
    public function backupToGoogleDrive(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'Pustakawan') {
            return response()->json(['message' => 'Aksi ini hanya diizinkan untuk Pustakawan.'], 403);
        }

        $credentialsPath = storage_path('app/google-credentials.json');
        if (!file_exists($credentialsPath)) {
            return response()->json([
                'message' => 'File credentials.json belum diunggah. Harap letakkan file google-credentials.json di folder storage/app/.'
            ], 400);
        }

        // 1. Fetch data from all tables
        try {
            $books = DB::table('books')->orderBy('title', 'asc')->get();
            
            $users = DB::table('users')
                ->orderBy('role', 'asc')
                ->orderBy('name', 'asc')
                ->get();
                
            $loans = DB::table('loans')
                ->leftJoin('users', 'loans.user_id', '=', 'users.id')
                ->leftJoin('books', 'loans.book_id', '=', 'books.id')
                ->select(
                    'loans.id',
                    'users.name as student_name',
                    'users.nim as student_nim',
                    'books.title as book_title',
                    'loans.borrow_date',
                    'loans.due_date',
                    'loans.status'
                )
                ->orderBy('loans.borrow_date', 'desc')
                ->get();

            $reservations = DB::table('reservations')
                ->leftJoin('users', 'reservations.user_id', '=', 'users.id')
                ->leftJoin('books', 'reservations.book_id', '=', 'books.id')
                ->select(
                    'reservations.id',
                    'users.name as student_name',
                    'users.nim as student_nim',
                    'books.title as book_title',
                    'reservations.reserved_date',
                    'reservations.queue_position'
                )
                ->orderBy('reservations.reserved_date', 'asc')
                ->get();

            $logs = DB::table('circulation_logs')
                ->orderBy('timestamp', 'desc')
                ->get();
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data dari database: ' . $e->getMessage()
            ], 500);
        }

        // 2. Generate Excel file
        $tempFilePath = tempnam(sys_get_temp_dir(), 'backup_unu_ntb_');
        $filename = 'backup_perpustakaan_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        try {
            $spreadsheet = new Spreadsheet();
            
            // Common styles
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1C2D46'] // Midnight blue
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
            
            $cellStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ];

            // SHEET 1: Buku
            $sheetBooks = $spreadsheet->getActiveSheet();
            $sheetBooks->setTitle('Katalog Buku');
            $sheetBooks->setCellValue('A1', 'ID Buku');
            $sheetBooks->setCellValue('B1', 'Judul');
            $sheetBooks->setCellValue('C1', 'Penulis');
            $sheetBooks->setCellValue('D1', 'Kategori/Genre');
            $sheetBooks->setCellValue('E1', 'Stok Saat Ini');
            $sheetBooks->setCellValue('F1', 'Total Stok');
            $sheetBooks->setCellValue('G1', 'Tahun Terbit');
            $sheetBooks->setCellValue('H1', 'Popularitas');
            $sheetBooks->getStyle('A1:H1')->applyFromArray($headerStyle);
            
            $row = 2;
            foreach ($books as $b) {
                $sheetBooks->setCellValue('A' . $row, $b->id);
                $sheetBooks->setCellValue('B' . $row, $b->title);
                $sheetBooks->setCellValue('C' . $row, $b->author);
                $sheetBooks->setCellValue('D' . $row, $b->genre);
                $sheetBooks->setCellValue('E' . $row, $b->stock);
                $sheetBooks->setCellValue('F' . $row, $b->total_stock);
                $sheetBooks->setCellValue('G' . $row, $b->published_year);
                $sheetBooks->setCellValue('H' . $row, $b->popularity);
                $sheetBooks->getStyle("A{$row}:H{$row}")->applyFromArray($cellStyle);
                $row++;
            }
            foreach (range('A', 'H') as $col) {
                $sheetBooks->getColumnDimension($col)->setAutoSize(true);
            }

            // SHEET 2: Peminjaman
            $sheetLoans = $spreadsheet->createSheet();
            $sheetLoans->setTitle('Daftar Peminjaman');
            $sheetLoans->setCellValue('A1', 'ID Pinjam');
            $sheetLoans->setCellValue('B1', 'Nama Mahasiswa');
            $sheetLoans->setCellValue('C1', 'NIM');
            $sheetLoans->setCellValue('D1', 'Judul Buku');
            $sheetLoans->setCellValue('E1', 'Tanggal Pinjam');
            $sheetLoans->setCellValue('F1', 'Jatuh Tempo');
            $sheetLoans->setCellValue('G1', 'Status');
            $sheetLoans->getStyle('A1:G1')->applyFromArray($headerStyle);

            $row = 2;
            foreach ($loans as $l) {
                $sheetLoans->setCellValue('A' . $row, $l->id);
                $sheetLoans->setCellValue('B' . $row, $l->student_name);
                $sheetLoans->setCellValue('C' . $row, $l->student_nim);
                $sheetLoans->setCellValue('D' . $row, $l->book_title);
                $sheetLoans->setCellValue('E' . $row, $l->borrow_date);
                $sheetLoans->setCellValue('F' . $row, $l->due_date);
                $sheetLoans->setCellValue('G' . $row, strtoupper($l->status));
                $sheetLoans->getStyle("A{$row}:G{$row}")->applyFromArray($cellStyle);
                $row++;
            }
            foreach (range('A', 'G') as $col) {
                $sheetLoans->getColumnDimension($col)->setAutoSize(true);
            }

            // SHEET 3: Anggota (Users)
            $sheetUsers = $spreadsheet->createSheet();
            $sheetUsers->setTitle('Data Anggota');
            $sheetUsers->setCellValue('A1', 'ID Anggota');
            $sheetUsers->setCellValue('B1', 'Nama Lengkap');
            $sheetUsers->setCellValue('C1', 'Email');
            $sheetUsers->setCellValue('D1', 'NIM');
            $sheetUsers->setCellValue('E1', 'Peran');
            $sheetUsers->setCellValue('F1', 'Fakultas');
            $sheetUsers->setCellValue('G1', 'Program Studi');
            $sheetUsers->setCellValue('H1', 'Nomor Telepon');
            $sheetUsers->getStyle('A1:H1')->applyFromArray($headerStyle);

            $row = 2;
            foreach ($users as $u) {
                $sheetUsers->setCellValue('A' . $row, $u->id);
                $sheetUsers->setCellValue('B' . $row, $u->name);
                $sheetUsers->setCellValue('C' . $row, $u->email);
                $sheetUsers->setCellValue('D' . $row, $u->nim ?: '-');
                $sheetUsers->setCellValue('E' . $row, $u->role);
                $sheetUsers->setCellValue('F' . $row, $u->faculty ?: '-');
                $sheetUsers->setCellValue('G' . $row, $u->prodi ?: '-');
                $sheetUsers->setCellValue('H' . $row, $u->phone ?: '-');
                $sheetUsers->getStyle("A{$row}:H{$row}")->applyFromArray($cellStyle);
                $row++;
            }
            foreach (range('A', 'H') as $col) {
                $sheetUsers->getColumnDimension($col)->setAutoSize(true);
            }

            // SHEET 4: Reservasi
            $sheetReservations = $spreadsheet->createSheet();
            $sheetReservations->setTitle('Daftar Reservasi');
            $sheetReservations->setCellValue('A1', 'ID Reservasi');
            $sheetReservations->setCellValue('B1', 'Nama Mahasiswa');
            $sheetReservations->setCellValue('C1', 'NIM');
            $sheetReservations->setCellValue('D1', 'Judul Buku');
            $sheetReservations->setCellValue('E1', 'Tanggal Reservasi');
            $sheetReservations->setCellValue('F1', 'Posisi Antrean');
            $sheetReservations->getStyle('A1:F1')->applyFromArray($headerStyle);

            $row = 2;
            foreach ($reservations as $r) {
                $sheetReservations->setCellValue('A' . $row, $r->id);
                $sheetReservations->setCellValue('B' . $row, $r->student_name);
                $sheetReservations->setCellValue('C' . $row, $r->student_nim);
                $sheetReservations->setCellValue('D' . $row, $r->book_title);
                $sheetReservations->setCellValue('E' . $row, $r->reserved_date);
                $sheetReservations->setCellValue('F' . $row, $r->queue_position);
                $sheetReservations->getStyle("A{$row}:F{$row}")->applyFromArray($cellStyle);
                $row++;
            }
            foreach (range('A', 'F') as $col) {
                $sheetReservations->getColumnDimension($col)->setAutoSize(true);
            }

            // SHEET 5: Log Sirkulasi
            $sheetLogs = $spreadsheet->createSheet();
            $sheetLogs->setTitle('Log Sirkulasi');
            $sheetLogs->setCellValue('A1', 'ID Log');
            $sheetLogs->setCellValue('B1', 'Aktivitas');
            $sheetLogs->setCellValue('C1', 'Detail');
            $sheetLogs->setCellValue('D1', 'Waktu');
            $sheetLogs->getStyle('A1:D1')->applyFromArray($headerStyle);

            $row = 2;
            foreach ($logs as $log) {
                $sheetLogs->setCellValue('A' . $row, $log->id);
                $sheetLogs->setCellValue('B' . $row, $log->activity);
                $sheetLogs->setCellValue('C' . $row, $log->detail);
                $sheetLogs->setCellValue('D' . $row, $log->timestamp);
                $sheetLogs->getStyle("A{$row}:D{$row}")->applyFromArray($cellStyle);
                $row++;
            }
            foreach (range('A', 'D') as $col) {
                $sheetLogs->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFilePath);
        } catch (Exception $e) {
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            return response()->json([
                'message' => 'Gagal membuat file Excel: ' . $e->getMessage()
            ], 500);
        }

        // 3. Connect to Google Drive API & Upload
        try {
            $client = new \Google\Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(\Google\Service\Drive::DRIVE);
            
            $service = new \Google\Service\Drive($client);
            $folderId = env('GOOGLE_DRIVE_FOLDER_ID', '1k0oyuX4CWvlnt_zppvbwHUWEnsz_132i');
            
            $content = file_get_contents($tempFilePath);

            // Upload 1: Excel Binary (.xlsx)
            $excelMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $filename,
                'parents' => [$folderId]
            ]);
            $excelFile = $service->files->create($excelMetadata, [
                'data' => $content,
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);

            // Upload 2: Native Google Sheet (automatic conversion)
            $sheetName = str_replace('.xlsx', '', $filename);
            $sheetMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $sheetName,
                'mimeType' => 'application/vnd.google-apps.spreadsheet',
                'parents' => [$folderId]
            ]);
            $sheetFile = $service->files->create($sheetMetadata, [
                'data' => $content,
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);

            // Clean up temp file
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            // Write backup log into circulation_logs
            DB::table('circulation_logs')->insert([
                'activity' => 'Backup Database',
                'detail' => "Pustakawan melakukan backup data lengkap ke Google Drive. Nama file: {$filename} & {$sheetName} (Spreadsheet).",
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dibuat dan disimpan ke Google Drive Anda!',
                'excel_id' => $excelFile->id,
                'excel_link' => $excelFile->webViewLink,
                'sheet_id' => $sheetFile->id,
                'sheet_link' => $sheetFile->webViewLink
            ]);

        } catch (Exception $e) {
            // Clean up temp file on error
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            return response()->json([
                'message' => 'Gagal mengunggah ke Google Drive: ' . $e->getMessage()
            ], 500);
        }
    }
}
