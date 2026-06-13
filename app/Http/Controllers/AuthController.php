<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        // Auto-login via Cloudflare Access SSO header
        $ssoEmail = $request->header('X-Auth-Email');
        if ($ssoEmail) {
            $user = \DB::table('users')->where('email', $ssoEmail)->first();
            if ($user) {
                Auth::loginUsingId($user->id);
                $request->session()->regenerate();
                
                // Log circulation activity
                \DB::table('circulation_logs')->insert([
                    'activity' => 'SSO Login',
                    'detail' => "{$user->role} {$user->name} ({$user->nim}) masuk otomatis via Cloudflare Access.",
                    'timestamp' => $this->CarbonSimDate(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return redirect('/');
            }
        }

        if (Auth::check()) {
            return redirect('/');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            
            // Log circulation activity
            $user = Auth::user();
            \DB::table('circulation_logs')->insert([
                'activity' => 'User Login',
                'detail' => "{$user->role} {$user->name} ({$user->nim}) berhasil masuk ke sistem.",
                'timestamp' => $this->CarbonSimDate(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \DB::table('circulation_logs')->insert([
                'activity' => 'User Logout',
                'detail' => "{$user->role} {$user->name} ({$user->nim}) keluar dari sistem.",
                'timestamp' => $this->CarbonSimDate(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function logoutGet(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            \DB::table('circulation_logs')->insert([
                'activity' => 'User Logout',
                'detail' => "{$user->role} {$user->name} ({$user->nim}) keluar dari sistem.",
                'timestamp' => $this->CarbonSimDate(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return redirect('/login');
    }

    public function getAuthState()
    {
        if (Auth::check()) {
            return response()->json([
                'authenticated' => true,
                'user' => Auth::user(),
            ]);
        }
        return response()->json([
            'authenticated' => false,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'nim' => ['required', 'string', 'max:50', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'faculty' => ['required', 'string'],
            'prodi' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $userId = \DB::table('users')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'nim' => $request->nim,
            'phone' => $request->phone,
            'faculty' => $request->faculty,
            'prodi' => $request->prodi,
            'role' => 'Mahasiswa',
            'wishlist' => '[]',
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'password_plain' => $request->password,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Auth::loginUsingId($userId);
        $request->session()->regenerate();

        \DB::table('circulation_logs')->insert([
            'activity' => 'Registrasi Mandiri',
            'detail' => "Mahasiswa baru {$request->name} ({$request->nim}) berhasil mendaftarkan akun secara mandiri.",
            'timestamp' => $this->CarbonSimDate(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect('/');
    }
}

