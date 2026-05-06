<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SensorDataController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // ── Validasi input ──
        $validated = $request->validate([
            'device_id'   => 'required|string|max:50',
            'flame_raw'   => 'required|integer|min:0|max:4095',
            'fire'        => 'required|boolean',

            'voltage'     => 'nullable|numeric|min:0|max:200',
            'current'     => 'nullable|numeric|min:0|max:100',
            'power'       => 'nullable|numeric|min:0',
            'energy'      => 'nullable|numeric|min:0',
            'deviasi_pct' => 'nullable|numeric',
            'voltage_ok'  => 'nullable|boolean',
        ]);

        $record = SensorData::create($validated);

        if ($validated['fire']) {
            Log::warning('FIRE DETECTED', [
                'device_id' => $validated['device_id'],
                'flame_raw' => $validated['flame_raw'],
                'recorded_at' => $record->created_at,
            ]);
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Data sensor berhasil disimpan',
            'id'          => $record->id,
            'recorded_at' => $record->created_at->toIso8601String(),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'nullable|string|max:50',
            'limit'     => 'nullable|integer|min:1|max:500',
            'fire_only' => 'nullable|boolean',
        ]);

        $query = SensorData::query()->latest();

        if ($request->filled('device_id')) {
            $query->device($request->device_id);
        }

        if ($request->boolean('fire_only')) {
            $query->fireOnly();
        }

        $limit = $request->integer('limit', 48); 
        $data  = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'count'   => $data->count(),
            'data'    => $data,
        ]);
    }
    
    public function latest(Request $request): JsonResponse
    {
        $deviceId = $request->query('device_id', 'esp32-flame-01');

        $record = SensorData::device($deviceId)->latest()->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data untuk device ini',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $record,
        ]);
    }
}