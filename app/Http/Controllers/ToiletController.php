<?php

namespace App\Http\Controllers;

use App\Models\Toilet;
use App\Models\ToiletSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ToiletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $toilets = Toilet::where('user_id', $user->id)
            ->with(['activeSessions' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        // Count total NPCs served (completed sessions)
        $npcsServed = ToiletSession::whereIn('toilet_id', function ($query) use ($user) {
            $query->select('id')
                ->from('toilets')
                ->where('user_id', $user->id);
        })
            ->where('is_active', false)
            ->count();

        return view('game.index', compact('user', 'toilets', 'npcsServed'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $toiletCount = Toilet::where('user_id', $user->id)->count();

        // Check max toilet limit
        if ($toiletCount >= 4) {
            return response()->json(['error' => 'Maximum toilet limit reached (4)'], 400);
        }

        // First toilet is free, others cost exponentially more
        // Toilet 1: Free, Toilet 2: 50k, Toilet 3: 200k, Toilet 4: 800k
        $cost = $toiletCount == 0 ? 0 : 50000 * (4 ** ($toiletCount - 1));

        if ($cost > 0 && $user->balance < $cost) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        try {
            DB::beginTransaction();

            if ($cost > 0) {
                $user->balance -= $cost;
                $user->save();
            }

            $toilet = Toilet::create([
                'user_id' => $user->id,
                'level' => 1,
                'pee_price' => 2000,
                'poop_price' => 3000,
                'bath_price' => 5000,
                'is_clean' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Toilet created successfully',
                'toilet' => $toilet,
                'balance' => $user->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => 'Failed to create toilet'], 500);
        }
    }

    public function getUpgradeCost(Toilet $toilet)
    {
        // Check ownership
        if ($toilet->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // More expensive upgrade: Level 1->2: 10k, 2->3: 40k, 3->4: 160k, etc
        $cost = 10000 * (4 ** ($toilet->level - 1));

        return response()->json(['cost' => $cost]);
    }

    public function upgrade(Toilet $toilet)
    {
        // Check ownership
        if ($toilet->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // More expensive upgrade: Level 1->2: 10k, 2->3: 40k, 3->4: 160k, etc
        $cost = 10000 * (4 ** ($toilet->level - 1));
        $user = Auth::user();

        if ($user->balance < $cost) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        try {
            DB::beginTransaction();

            $user->balance -= $cost;
            $user->save();

            // Increase price exponentially by level
            $toilet->level += 1;
            $toilet->pee_price = 2000 * (2 ** ($toilet->level - 1));
            $toilet->poop_price = 3000 * (2 ** ($toilet->level - 1));
            $toilet->bath_price = 5000 * (2 ** ($toilet->level - 1));
            $toilet->save();

            DB::commit();

            return response()->json([
                'message' => 'Toilet upgraded successfully',
                'toilet' => $toilet,
                'balance' => $user->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => 'Failed to upgrade toilet'], 500);
        }
    }

    public function resetProgress()
    {
        try {
            DB::beginTransaction();

            // delete semua history
            ToiletSession::whereIn('toilet_id', function ($query) {
                $query->select('id')
                    ->from('toilets')
                    ->where('user_id', Auth::id());
            })->delete();

            // delete semua toilets
            Toilet::where('user_id', Auth::id())->delete();

            // Reset balance
            User::where('id', Auth::id())->update(['balance' => 0]);

            DB::commit();

            return response()->json([
                'message' => 'Progress reset successfully',
                'balance' => 0,
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => 'Failed to reset progress'], 500);
        }
    }
}
