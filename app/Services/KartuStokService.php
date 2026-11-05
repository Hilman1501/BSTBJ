<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Client untuk API Kartu Stok WMS (POST /api/bstbj).
 * Menambah stok Barang Jadi + membuat header X-Signature (HMAC SHA-256).
 *
 * Simpan di: app/Services/KartuStokService.php
 */
class KartuStokService
{
    // Unit yang diterima KS. Selain ini akan ditolak (HTTP 422).
    public const ALLOWED_UNITS = ['specialty', 'genchem', 'powder', 'pac'];

    /**
     * Kirim beberapa item sekaligus (mis. satu item per batch pada tiket Manual).
     * Semua item divalidasi dulu; kalau ada yang salah, TIDAK ADA yang dikirim.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array>  response 'data' dari KS untuk tiap item
     *
     * @throws RuntimeException
     */
    public function addMany(array $items): array
    {
        if (empty($items)) {
            throw new RuntimeException('Tidak ada data yang dikirim ke Kartu Stok.');
        }

        foreach ($items as $n => $item) {
            $this->validateItem($item, $n + 1);
        }
        if (!config('services.ks.enabled', true)) {
            Log::warning('KS_ENABLED=false: pengiriman ke Kartu Stok dilewati.', ['items' => $items]);
            return [];
        }

        $results = [];
        foreach ($items as $n => $item) {
            try {
                $results[] = $this->send($item);
            } catch (RuntimeException $e) {
                $done = count($results);
                $suffix = $done > 0
                    ? " ({$done} dari " . count($items) . " item sudah terkirim ke KS, harap cek manual.)"
                    : '';
                throw new RuntimeException($e->getMessage() . $suffix, 0, $e);
            }
        }

        return $results;
    }

    /**
     * Kirim satu request ke KS.
     */
    public function send(array $payload): array
    {
        $baseUrl = rtrim((string) config('services.ks.base_url'), '/');
        $secret = (string) config('services.ks.secret');

        if ($baseUrl === '' || $secret === '') {
            throw new RuntimeException(
                'Konfigurasi Kartu Stok belum lengkap (KS_BASE_URL / secret di .env).'
            );
        }

        $payload = array_filter($payload, fn ($v) => $v !== null && $v !== '');

        $json = json_encode($payload);
        $signature = hash_hmac('sha256', $json, $secret);

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->withHeaders(['X-Signature' => $signature])
                ->withBody($json, 'application/json')
                ->post($baseUrl . '/api/bstbj');
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak bisa terhubung ke Kartu Stok: ' . $e->getMessage(), 0, $e);
        }

        if ($response->successful() && $response->json('success') === true) {
            return $response->json('data') ?? [];
        }

        throw new RuntimeException($this->errorMessage($response));
    }

    protected function validateItem(array $item, int $no): void
    {
        $label = "Item #{$no}";

        if (empty($item['spk_number'])) {
            throw new RuntimeException("{$label}: SPK number kosong.");
        }
        if (empty($item['material_code'])) {
            throw new RuntimeException("{$label}: material kosong.");
        }
        if (empty($item['s_loc'])) {
            throw new RuntimeException("{$label}: Sloc belum dipilih.");
        }
        if (!is_numeric($item['qty'] ?? null) || (float) $item['qty'] <= 0) {
            throw new RuntimeException("{$label}: Qty Confirm harus diisi dan lebih dari 0.");
        }
        if (!in_array($item['unit'] ?? null, self::ALLOWED_UNITS, true)) {
            throw new RuntimeException(
                "{$label}: unit '" . ($item['unit'] ?? '-') . "' tidak diterima KS. "
                . 'Yang diperbolehkan: ' . implode(', ', self::ALLOWED_UNITS) . '.'
            );
        }
    }

    protected function errorMessage($response): string
    {
        $status = $response->status();

        if ($status === 401) {
            return 'KS menolak signature (401). Cek secret di .env, harus sama dengan milik KS.';
        }

        if ($status === 422) {
            $errors = collect($response->json('errors') ?? [])->flatten()->implode(' ');
            return 'Validasi KS gagal: ' . ($errors ?: $response->json('message', 'data tidak valid.'));
        }

        return 'KS error (' . $status . '): ' . ($response->json('message') ?? 'tidak ada pesan.');
    }
}