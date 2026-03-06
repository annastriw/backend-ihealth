<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBloodPressureRequest;
use App\Http\Requests\StoreBloodSugarRequest;
use App\Http\Requests\StoreCholesterolRequest;
use App\Models\BloodPressureMeasurement;
use App\Models\BloodSugarMeasurement;
use App\Models\CholesterolMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IotMeasurementController extends Controller
{
    private const CACHE_KEY_LATEST_EVENT = 'iot.latest.event';

    public function storeBloodPressure(StoreBloodPressureRequest $request)
    {
        $data = $request->validated();

        $measurement = BloodPressureMeasurement::create([
            'blood_pressure_systolic' => $data['blood_pressure_systolic'],
            'blood_pressure_diastolic' => $data['blood_pressure_diastolic'],
            'source' => 'esp32',
            'recorded_at' => now(),
        ]);

        $event = [
            'id' => 'bp-' . $measurement->id . '-' . now()->timestamp,
            'type' => 'blood-pressure',
            'payload' => [
                'blood_pressure_systolic' => $measurement->blood_pressure_systolic,
                'blood_pressure_diastolic' => $measurement->blood_pressure_diastolic,
            ],
            'recorded_at' => $measurement->recorded_at?->toDateTimeString(),
        ];

        Cache::put(self::CACHE_KEY_LATEST_EVENT, $event, now()->addMinutes(10));

        return response()->json([
            'message' => 'Blood pressure data stored successfully.',
            'data' => [
                'id' => $measurement->id,
                'blood_pressure_systolic' => $measurement->blood_pressure_systolic,
                'blood_pressure_diastolic' => $measurement->blood_pressure_diastolic,
                'source' => $measurement->source,
                'recorded_at' => $measurement->recorded_at?->toDateTimeString(),
            ],
        ], 201);
    }

    public function storeBloodSugar(StoreBloodSugarRequest $request)
    {
        $data = $request->validated();

        $measurement = BloodSugarMeasurement::create([
            'random_blood_sugar' => $data['random_blood_sugar'],
            'source' => 'esp32',
            'recorded_at' => now(),
        ]);

        $event = [
            'id' => 'bs-' . $measurement->id . '-' . now()->timestamp,
            'type' => 'blood-sugar',
            'payload' => [
                'random_blood_sugar' => $measurement->random_blood_sugar,
            ],
            'recorded_at' => $measurement->recorded_at?->toDateTimeString(),
        ];

        Cache::put(self::CACHE_KEY_LATEST_EVENT, $event, now()->addMinutes(10));

        return response()->json([
            'message' => 'Blood sugar data stored successfully.',
            'data' => [
                'id' => $measurement->id,
                'random_blood_sugar' => $measurement->random_blood_sugar,
                'source' => $measurement->source,
                'recorded_at' => $measurement->recorded_at?->toDateTimeString(),
            ],
        ], 201);
    }

    public function storeCholesterol(StoreCholesterolRequest $request)
    {
        $data = $request->validated();

        $measurement = CholesterolMeasurement::create([
            'cholesterol_level' => $data['cholesterol_level'],
            'source' => 'esp32',
            'recorded_at' => now(),
        ]);

        $event = [
            'id' => 'ch-' . $measurement->id . '-' . now()->timestamp,
            'type' => 'cholesterol',
            'payload' => [
                'cholesterol_level' => $measurement->cholesterol_level,
            ],
            'recorded_at' => $measurement->recorded_at?->toDateTimeString(),
        ];

        Cache::put(self::CACHE_KEY_LATEST_EVENT, $event, now()->addMinutes(10));

        return response()->json([
            'message' => 'Cholesterol data stored successfully.',
            'data' => [
                'id' => $measurement->id,
                'cholesterol_level' => $measurement->cholesterol_level,
                'source' => $measurement->source,
                'recorded_at' => $measurement->recorded_at?->toDateTimeString(),
            ],
        ], 201);
    }

    public function stream(Request $request): StreamedResponse
    {
        return response()->stream(function () {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', false);
            @ini_set('implicit_flush', true);

            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            ob_implicit_flush(true);

            $lastSentId = null;
            $startTime = time();
            $maxDuration = 25;

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $event = Cache::get(self::CACHE_KEY_LATEST_EVENT);

                if ($event && ($event['id'] ?? null) !== $lastSentId) {
                    echo "id: {$event['id']}\n";
                    echo "event: iot-message\n";
                    echo 'data: ' . json_encode($event) . "\n\n";

                    $lastSentId = $event['id'];
                    @ob_flush();
                    @flush();
                } else {
                    echo ": ping\n\n";
                    @ob_flush();
                    @flush();
                }

                if ((time() - $startTime) >= $maxDuration) {
                    break;
                }

                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}