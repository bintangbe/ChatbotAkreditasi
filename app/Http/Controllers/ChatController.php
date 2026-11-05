<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function queryPinecone()
    {
        // Mengambil API key dari file .env
        $apiKey = env('PINECONE_API_KEY');
        $host = env('PINECONE_HOST');

        // Contoh memanggil REST API Pinecone menggunakan Laravel HTTP Client
        $response = Http::withHeaders([
            'Api-Key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post("https://{$host}/query", [
            'topK' => 3,
            'vector' => [/* nilai vektor Anda */],
            'namespace' => 'chatbot-akreditasi'
        ]);

        return $response->json();
    }
    public function sendMessage(Request $request)
    {
        $pesanUser = $request->input('chatInput');

        // Pesan default kalau terjadi kegagalan apapun
        $fallbackMessage = 'Maaf, sistem sedang mengalami gangguan. Silakan coba beberapa saat lagi.';

        try {
            // Tembak API n8n
            $response = Http::timeout(30)->post(env('N8N_WEBHOOK_URL'), [
                'chatInput' => $pesanUser,
                'sessionId' => session()->getId(),
            ]);

            $rawBody = $response->body();

            // Catat respons mentah ke log (untuk debugging developer, tidak tampil ke user)
            Log::info('Respons mentah n8n: ' . $rawBody);

            // Kalau n8n merespons dengan status error (4xx/5xx)
            if ($response->failed()) {
                Log::error('n8n merespons dengan status gagal: ' . $response->status() . ' | Body: ' . $rawBody);

                return response()->json([
                    'reply' => $fallbackMessage,
                ], 200);
            }

            $jawabanN8n = json_decode($rawBody, true);
            $replyText = $fallbackMessage;

            if (is_array($jawabanN8n)) {
                if (isset($jawabanN8n['output'])) {
                    $replyText = $jawabanN8n['output'];
                } elseif (isset($jawabanN8n[0]['output'])) {
                    $replyText = $jawabanN8n[0]['output'];
                } elseif (isset($jawabanN8n['text'])) {
                    $replyText = $jawabanN8n['text'];
                } else {
                    // JSON valid tapi strukturnya tidak dikenali
                    Log::warning('Struktur respons n8n tidak dikenali: ' . $rawBody);
                }
            } else {
                // Bukan JSON sama sekali
                Log::warning('Respons n8n bukan JSON valid: ' . $rawBody);
            }

            return response()->json([
                'reply'   => $replyText,
                'sources' => $this->extractSources($replyText),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error Laravel saat menghubungi n8n: ' . $e->getMessage());

            return response()->json([
                'reply' => $fallbackMessage,
            ], 200);
        }
    }

    /**
     * Sajikan PDF dokumen akreditasi untuk pratinjau halaman (pdf.js di browser).
     *
     * Browser tidak boleh mengambil file Google Drive langsung (diblokir CORS),
     * jadi Laravel mengunduhnya sekali, menyimpannya di storage/app/dokumen,
     * lalu menyajikannya dari domain sendiri.
     *
     * Keamanan: hanya ID file yang terdaftar di config/dokumen.php yang dilayani,
     * sehingga endpoint ini tidak bisa dipakai sebagai proxy ke file lain.
     */
    public function document(string $fileId)
    {
        abort_unless(in_array($fileId, config('dokumen.drive', []), true), 404);

        $dir  = storage_path('app/dokumen');
        $path = "{$dir}/{$fileId}.pdf";

        if (!is_file($path) || filesize($path) < 1024) {
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $tmp = $path . '.tmp';

            try {
                $response = Http::timeout(120)
                    ->sink($tmp)
                    ->get('https://drive.usercontent.google.com/download', [
                        'id'      => $fileId,
                        'export'  => 'download',
                        'confirm' => 't', // lewati halaman peringatan "tidak bisa scan virus" untuk file besar
                    ]);

                // Pastikan yang terunduh benar-benar PDF, bukan halaman HTML login/error Drive
                $head = is_file($tmp) ? file_get_contents($tmp, false, null, 0, 4) : '';

                if ($response->failed() || $head !== '%PDF') {
                    Log::error("Gagal mengunduh PDF Drive {$fileId} | status: {$response->status()}");
                    @unlink($tmp);
                    abort(502, 'Dokumen tidak dapat diambil.');
                }

                rename($tmp, $path);
            } catch (\Illuminate\Http\Exceptions\HttpResponseException | \Symfony\Component\HttpKernel\Exception\HttpException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error("Error mengunduh PDF Drive {$fileId}: " . $e->getMessage());
                @unlink($tmp);
                abort(502, 'Dokumen tidak dapat diambil.');
            }
        }

        // response()->file mendukung HTTP Range, jadi pdf.js bisa memuat per bagian
        return response()->file($path, [
            'Content-Type'  => 'application/pdf',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Cari rujukan dokumen di teks jawaban, misalnya:
     *   1_7_Standar_Pengelolaan_Pembelajaran (tahun standar 2020, Halaman: 9)
     * lalu ubah menjadi link Google Drive + gambar pratinjau.
     * Link HANYA dibuat dari daftar di config/dokumen.php, bukan dari teks AI.
     */
    private function extractSources(string $text): array
    {
        $drive = config('dokumen.drive', []);
        $pattern = '/\b([A-Za-z0-9]+(?:_[A-Za-z0-9]+)+)(?:\.pdf)?\s*\([^)]*?Halaman:?\s*(\d+)[^)]*\)/u';

        if (!preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $sources = [];

        foreach ($matches as $m) {
            $doc  = $m[1];
            $page = (int) $m[2];

            // Dokumen belum didaftarkan -> jangan dibuatkan link
            if (!isset($drive[$doc]) || $page < 1) {
                continue;
            }

            $key = $doc . '#' . $page;
            if (isset($sources[$key])) {
                continue; // hindari referensi dobel
            }

            $fileId = $drive[$doc];

            // "1_7_Standar_Pengelolaan_Pembelajaran" -> "Standar Pengelolaan Pembelajaran"
            $label = str_replace('_', ' ', preg_replace('/^\d+(?:_\d+)*_/', '', $doc));

            $sources[$key] = [
                'doc'      => $doc,
                'label'    => $label,
                'page'     => $page,
                // Klik: buka dokumen di Google Drive pada halaman terkait
                'url'      => "https://drive.google.com/file/d/{$fileId}/view#page={$page}",
                // Hover: gambar halaman (hasil tools/render_previews.py)
                'preview'  => asset("previews/{$doc}/p{$page}.jpg"),
                // Cadangan kalau gambar halaman belum ada: thumbnail Drive (halaman 1)
                'fallback' => "https://drive.google.com/thumbnail?id={$fileId}&sz=w600",
            ];
        }

        return array_values($sources);
    }
}