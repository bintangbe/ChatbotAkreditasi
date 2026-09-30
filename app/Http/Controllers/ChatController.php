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
                'reply' => $replyText,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error Laravel saat menghubungi n8n: ' . $e->getMessage());

            return response()->json([
                'reply' => $fallbackMessage,
            ], 200);
        }
    }
}