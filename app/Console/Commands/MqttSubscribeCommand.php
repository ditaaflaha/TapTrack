<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\EmployeeActivity;
use App\Models\CanteenTransaction;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Carbon\Carbon;

class MqttSubscribeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to wildcard MQTT topics for TapTrack nodes and process them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = env('MQTT_HOST', 'broker.hivemq.com');
        $port = (int) env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'taptrack_laravel_' . uniqid());
        $username = env('MQTT_USERNAME');
        $password = env('MQTT_PASSWORD');

        $this->info("Connecting to MQTT broker at {$host}:{$port}...");

        try {
            $mqtt = new MqttClient($host, $port, $clientId);

            $settings = (new ConnectionSettings())
                ->setKeepAliveInterval(60);

            if (!empty($username)) {
                $settings->setUsername($username);
            }
            if (!empty($password)) {
                $settings->setPassword($password);
            }

            $mqtt->connect($settings, true);
            $this->info("Connected successfully. Subscribing to wildcard topic: taptrack/+/tap");

            // Subscribe to wildcard topic 'taptrack/+/tap'
            // where '+' will match node names like 'absensi', 'akses_pintu', or 'kantin'
            $mqtt->subscribe('taptrack/+/tap', function ($topic, $message) use ($mqtt) {
                $this->info("Received message on [{$topic}]: {$message}");

                $parts = explode('/', $topic);
                $node = $parts[1] ?? ''; // 'absensi', 'akses_pintu', or 'kantin'
                
                $data = json_decode($message, true);
                if (!$data || !isset($data['uid'])) {
                    $this->error("Invalid payload format: {$message}");
                    return;
                }

                $uid = strtoupper(trim($data['uid']));
                $employee = Employee::where('no_rfid', $uid)->first();

                // Response Topic
                $commandTopic = "taptrack/{$node}/command";

                if (!$employee) {
                    $this->warn("Access Denied for node [{$node}]: RFID {$uid} is not registered.");
                    
                    // Publish error status back to the node
                    $responsePayload = json_encode([
                        'status' => 'error',
                        'message' => 'invalid',
                        'uid' => $uid
                    ]);
                    $mqtt->publish($commandTopic, $responsePayload, 0);
                    return;
                }

                $this->info("Employee identified: {$employee->name} for node [{$node}]");

                $hariIni = Carbon::today()->format('Y-m-d');
                $waktuSekarang = Carbon::now()->format('H:i:s');
                $scannedAt = Carbon::now();

                // ==========================================
                // ROUTING LOGIC BASED ON NODE TYPE
                // ==========================================

                // Node 1: ABSENSI (Dual RFID)
                if ($node === 'absensi') {
                    $reader = strtolower(trim($data['reader'] ?? 'masuk')); // 'masuk' or 'keluar'

                    // Get today's attendance record
                    $attendance = Attendance::where('employee_id', $employee->id)
                                            ->where('date', $hariIni)
                                            ->first();

                    if ($reader === 'masuk') {
                        // Blokir jika sudah absen masuk hari ini
                        if ($attendance && $attendance->tap_in) {
                            $this->warn("Absensi masuk ditolak untuk {$employee->name}: sudah absen masuk hari ini.");
                            $responsePayload = json_encode([
                                'status' => 'error',
                                'message' => 'already_in',
                                'name' => $employee->name,
                                'uid' => $uid,
                            ]);
                            $mqtt->publish($commandTopic, $responsePayload, 0);
                            return;
                        }

                        // Log activity
                        EmployeeActivity::create([
                            'employee_id' => $employee->id,
                            'activity_type' => 'tap_in',
                            'scanned_at' => $scannedAt,
                            'is_manual' => false,
                            'notes' => 'Tapped via Absensi RFID Masuk',
                        ]);

                        Attendance::create([
                            'employee_id' => $employee->id,
                            'date' => $hariIni,
                            'tap_in' => $waktuSekarang,
                            'status' => Carbon::now()->format('H:i') > '08:00' ? 'Terlambat' : 'Tepat Waktu',
                            'is_manual' => false,
                        ]);

                    } else { // keluar
                        // Blokir jika belum absen masuk
                        if (!$attendance || !$attendance->tap_in) {
                            $this->warn("Absensi pulang ditolak untuk {$employee->name}: belum absen masuk.");
                            $responsePayload = json_encode([
                                'status' => 'error',
                                'message' => 'not_in_yet',
                                'name' => $employee->name,
                                'uid' => $uid,
                            ]);
                            $mqtt->publish($commandTopic, $responsePayload, 0);
                            return;
                        }

                        // Blokir jika sudah absen pulang hari ini
                        if ($attendance->tap_out) {
                            $this->warn("Absensi pulang ditolak untuk {$employee->name}: sudah absen pulang hari ini.");
                            $responsePayload = json_encode([
                                'status' => 'error',
                                'message' => 'already_out',
                                'name' => $employee->name,
                                'uid' => $uid,
                            ]);
                            $mqtt->publish($commandTopic, $responsePayload, 0);
                            return;
                        }

                        // Log activity
                        EmployeeActivity::create([
                            'employee_id' => $employee->id,
                            'activity_type' => 'tap_out',
                            'scanned_at' => $scannedAt,
                            'is_manual' => false,
                            'notes' => 'Tapped via Absensi RFID Keluar',
                        ]);

                        $attendance->tap_out = $waktuSekarang;
                        $attendance->save();
                    }

                    // Success response payload
                    $responsePayload = json_encode([
                        'status' => 'success',
                        'message' => 'valid',
                        'name' => $employee->name,
                        'uid' => $uid,
                        'reader' => $reader
                    ]);
                    $mqtt->publish($commandTopic, $responsePayload, 0);
                    $this->info("Absensi success published to {$commandTopic}");
                }

                // Node 2: AKSES PINTU (2 ESP32 Terpisah: Masuk & Keluar)
                elseif ($node === 'akses_pintu') {
                    // Gunakan direction dari ESP (bukan toggle otomatis)
                    // ESP Masuk  → mengirim direction "masuk"  → tap_in  → relay_ch 1
                    // ESP Keluar → mengirim direction "keluar" → tap_out → relay_ch 2
                    $directionRaw = strtolower(trim($data['direction'] ?? 'masuk'));
                    $nextDirection = ($directionRaw === 'keluar') ? 'tap_out' : 'tap_in';
                    $relayCh      = ($nextDirection === 'tap_in') ? 1 : 2;

                    // Ambil data absensi hari ini
                    $attendance = Attendance::where('employee_id', $employee->id)
                                            ->where('date', $hariIni)
                                            ->first();

                    // Validasi: tidak bisa keluar kalau belum masuk
                    if ($nextDirection === 'tap_out' && (!$attendance || !$attendance->tap_in)) {
                        $this->warn("Akses keluar ditolak untuk {$employee->name}: belum tap masuk hari ini.");
                        $responsePayload = json_encode([
                            'status'  => 'error',
                            'message' => 'not_in_yet',
                            'name'    => $employee->name,
                            'uid'     => $uid,
                            'direction' => 'keluar',
                        ]);
                        $mqtt->publish($commandTopic, $responsePayload, 0);
                        return;
                    }

                    // Log aktivitas
                    EmployeeActivity::create([
                        'employee_id'   => $employee->id,
                        'activity_type' => $nextDirection,
                        'scanned_at'    => $scannedAt,
                        'is_manual'     => false,
                        'notes'         => 'Tapped via Akses Pintu RFID (' . ($nextDirection === 'tap_in' ? 'Pintu Masuk' : 'Pintu Keluar') . ')',
                    ]);

                    // Update Attendance
                    if ($nextDirection === 'tap_in') {
                        if (!$attendance) {
                            Attendance::create([
                                'employee_id' => $employee->id,
                                'date'        => $hariIni,
                                'tap_in'      => $waktuSekarang,
                                'status'      => Carbon::now()->format('H:i') > '08:00' ? 'Terlambat' : 'Tepat Waktu',
                                'is_manual'   => false,
                            ]);
                        }
                    } else { // tap_out
                        if ($attendance) {
                            $attendance->tap_out = $waktuSekarang;
                            $attendance->save();
                        }
                    }

                    // Kirim response ke ESP (dengan direction yang sesuai agar relay yang benar terbuka)
                    $responsePayload = json_encode([
                        'status'    => 'success',
                        'message'   => 'valid',
                        'name'      => $employee->name,
                        'uid'       => $uid,
                        'direction' => $directionRaw,  // kembalikan direction asli dari ESP
                        'relay_ch'  => $relayCh
                    ]);
                    $mqtt->publish($commandTopic, $responsePayload, 0);
                    $this->info("Akses Pintu success [{$directionRaw}] published to {$commandTopic}");
                }

                // Node 3: KANTIN (Flat 12K Transaction)
                elseif ($node === 'kantin') {
                    $tarifFlat = 12000;

                    $transaction = CanteenTransaction::where('employee_id', $employee->id)
                                                     ->where('transaction_date', $hariIni)
                                                     ->first();

                    if ($transaction) {
                        $transaction->tap_count += 1;
                        $transaction->total_amount = $transaction->tap_count * $tarifFlat;
                        $transaction->save();
                        $infoRespon = 'Tap Jajan ke-' . $transaction->tap_count;
                    } else {
                        CanteenTransaction::create([
                            'employee_id' => $employee->id,
                            'transaction_date' => $hariIni,
                            'tap_count' => 1,
                            'total_amount' => $tarifFlat
                        ]);
                        $infoRespon = 'Transaksi Pertama Sukses';
                    }

                    // Success response payload
                    $responsePayload = json_encode([
                        'status' => 'success',
                        'message' => 'valid',
                        'name' => $employee->name,
                        'uid' => $uid,
                        'info' => $infoRespon
                    ]);
                    $mqtt->publish($commandTopic, $responsePayload, 0);
                    $this->info("Kantin success published to {$commandTopic}");
                }

            }, 0);

            $mqtt->loop(true);

        } catch (\Exception $e) {
            $this->error("MQTT Error: " . $e->getMessage());
        }
    }
}
