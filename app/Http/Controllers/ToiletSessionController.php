<?php

namespace App\Http\Controllers;

use App\Models\Toilet;
use App\Models\ToiletSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ToiletSessionController extends Controller
{
    private const NPC_NAMES = [
        'Budi', 'Ani', 'Siti', 'Joko', 'Rina', 'Agus', 'Dewi', 'Hadi',
        'Putri', 'Wawan', 'Lina', 'Bambang', 'Sri', 'Eko', 'Fitri',
        'Doni', 'Maya', 'Rudi', 'Yuni', 'Tono', 'Sari', 'Anton',
        'Diah', 'Hendro', 'Ratih', 'Guntur', 'Indah', 'Wahyu', 'Ayu',
    ];

    private const SERVICE_TYPES = ['pee', 'poop', 'bath'];

    public function getActiveSessions()
    {
        $sessions = ToiletSession::whereIn('toilet_id', function ($query) {
            $query->select('id')
                ->from('toilets')
                ->where('user_id', Auth::id());
        })
            ->where('is_active', true)
            ->with('toilet')
            ->get();

        return response()->json($sessions);
    }

    public function createSession(Request $request)
    {
        $validatedData = $request->validate([
            'toilet_id' => 'required|exists:toilets,id',
            'service_type' => 'nullable|in:pee,poop,bath',
            'duration' => 'nullable|integer|min:5|max:60', // in seconds
        ]);

        $toilet = Toilet::findOrFail($validatedData['toilet_id']);
        if ($toilet->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasActiveSession = ToiletSession::where('toilet_id', $toilet->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveSession) {
            return response()->json(['error' => 'Toilet is already occupied'], 400);
        }

        $serviceType = $validatedData['service_type'] ?? self::SERVICE_TYPES[array_rand(self::SERVICE_TYPES)];

        $duration = $validatedData['duration'] ?? rand(5, 60);

        // Get price based on service type
        $price = match ($serviceType) {
            'pee' => $toilet->pee_price,
            'poop' => $toilet->poop_price,
            'bath' => $toilet->bath_price,
            default => $toilet->pee_price,
        };

        // Random NPC name
        $npcName = self::NPC_NAMES[array_rand(self::NPC_NAMES)];

        $startTime = now();
        $endTime = now()->addSeconds($duration);

        $session = ToiletSession::create([
            'toilet_id' => $toilet->id,
            'npc_name' => $npcName,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_active' => true,
            'service_type' => $serviceType,
            'price' => $price,
        ]);

        return response()->json([
            'message' => 'Session created successfully',
            'session' => $session,
        ], 201);
    }

    public function endRunningSession(Request $request, $id)
    {
        $session = ToiletSession::findOrFail($id);

        $toilet = Toilet::findOrFail($session->toilet_id);
        if ($toilet->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $session->is_active) {
            return response()->json(['error' => 'Session is not active'], 400);
        }

        try {
            DB::beginTransaction();

            // Mark session as inactive
            $session->is_active = false;
            $session->save();

            // Update user balance
            $user = User::find(Auth::id());
            $user->balance += $session->price;
            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Session ended successfully',
                'session' => $session,
                'balance' => $user->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => 'Failed to end session'], 500);
        }
    }
}
