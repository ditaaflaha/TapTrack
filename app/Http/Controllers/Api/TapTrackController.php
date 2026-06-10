<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\CanteenTransaction;

class TapTrackController extends Controller
{
    public function handleTap(Request $request)
    {
        // 1. Tangkap data UID RFID dan Mode dari ESP32
        $uid = $request->input('no_rfid');
        $mode = $request->input('mode'); // Nilainya: 'akses' atau 'kantin'

        // 2. Cari data karyawan berdasarkan nomor kartu RFID
        $employee = Employee::where('no_rfid', $uid)->first();

        // Jika kartu tidak terdaftar di database website
        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'invalid',
                'reason' => 'Kartu tidak terdaftar'
            ], 404);
        }

        // ==========================================
        // Skenario A: Mode Akses Pintu & Absensi
        // ==========================================
        if ($mode == 'akses') {
            $hariIni = now()->format('Y-m-d');
            $waktuSekarang = now()->format('H:i:s');

            // Cek apakah hari ini karyawan tersebut sudah pernah tap_in
            $attendance = Attendance::where('employee_id', $employee->id)
                                    ->where('date', $hariIni)
                                    ->first();

            if (!$attendance) {
                // Jika belum pernah tap hari ini, otomatis tercatat sebagai Tap In
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $hariIni,
                    'tap_in' => $waktuSekarang,
                    'status' => 'Tepat Waktu'
                ]);
                $infoRespon = 'Tap In Berhasil';
            } else {
                // Jika sudah pernah tap_in, maka otomatis memperbarui kolom tap_out (pulang)
                $attendance->tap_out = $waktuSekarang;
                $attendance->save();
                $infoRespon = 'Tap Out Berhasil';
            }

            return response()->json([
                'status' => 'success',
                'message' => 'valid', // Kata kunci 'valid' dibaca ESP32 untuk memicu relay pintu
                'nama' => $employee->name,
                'info' => $infoRespon
            ], 200);
        }

        // ==========================================
        // Skenario B: Mode Transaksi Kantin (Flat 12K)
        // ==========================================
        if ($mode == 'kantin') {
            $tarifFlat = 12000;
            $hariIni = now()->format('Y-m-d');

            // Cek apakah karyawan sudah pernah jajan di kantin hari ini
            $transaction = CanteenTransaction::where('employee_id', $employee->id)
                                             ->where('transaction_date', $hariIni)
                                             ->first();

            if ($transaction) {
                // Jika sudah ada record hari ini, naikkan jumlah tap dan update total uangnya
                $transaction->tap_count += 1;
                $transaction->total_amount = $transaction->tap_count * $tarifFlat;
                $transaction->save();
                $infoRespon = 'Tap Jajan ke-' . $transaction->tap_count;
            } else {
                // Jika jajan pertama kali hari ini, buat baris data baru
                CanteenTransaction::create([
                    'employee_id' => $employee->id,
                    'transaction_date' => $hariIni,
                    'tap_count' => 1,
                    'total_amount' => $tarifFlat
                ]);
                $infoRespon = 'Transaksi Pertama Sukses';
            }

            return response()->json([
                'status' => 'success',
                'message' => 'valid', // Memberi sinyal sukses ke alat kantin
                'nama' => $employee->name,
                'info' => $infoRespon
            ], 200);
        }
    }
}