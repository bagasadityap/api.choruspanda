<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class XummController extends Controller
{
    public function createPayload()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key'    => env('XUMM_API_KEY'),
                'X-API-Secret' => env('XUMM_API_SECRET'),
            ])->post(env('XUMM_API_BASE') . '/platform/payload', [
                'txjson' => [
                    'TransactionType' => 'SignIn',
                ]
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to create payload',
                    'detail' => $response->json()
                ], 500);
            }

            $data = $response->json();

            return response()->json([
                'uuid' => $data['uuid'],
                'qr' => $data['refs']['qr_png'],
                'next' => $data['next']['always'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception when creating payload',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $uuid = $request->query('uuid');

        try {
            $response = Http::withHeaders([
                'X-API-Key'    => env('XUMM_API_KEY'),
                'X-API-Secret' => env('XUMM_API_SECRET'),
            ])->get(env('XUMM_API_BASE') . "/platform/payload/{$uuid}");

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'detail' => $response->json()
                ], 500);
            }

            $data = $response->json();

            if ($data['meta']['resolved'] === true) {
                if ($data['meta']['signed'] === true) {
                    return response()->json([
                        'status' => 'success',
                        'wallet_address' => $data['response']['account'] ?? null,
                    ]);
                } else {
                    return response()->json([
                        'status' => 'cancelled',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'pending',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

