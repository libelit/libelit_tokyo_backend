<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Get the authenticated user's wallet.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get wallet from the appropriate profile based on user type
        $wallet = null;
        if ($user->isDeveloper() && $user->developerProfile) {
            $wallet = $user->developerProfile->primaryWallet;
        } elseif ($user->isLender() && $user->lenderProfile) {
            $wallet = $user->lenderProfile->primaryWallet;
        }

        if (!$wallet) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No wallet found'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new WalletResource($wallet)
        ]);
    }

    /**
     * Store a new wallet for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'xrpl_address' => 'required|string|unique:wallets,xrpl_address',
            'xrpl_public_key' => 'nullable|string',
            'label' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Get the appropriate profile based on user type
        $profile = null;
        if ($user->isDeveloper()) {
            $profile = $user->developerProfile;
        } elseif ($user->isLender()) {
            $profile = $user->lenderProfile;
        }

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'User profile not found'
            ], 404);
        }

        // Check if user already has a wallet
        if ($profile->hasWallet()) {
            return response()->json([
                'success' => false,
                'message' => 'User already has a wallet'
            ], 409);
        }

        // Create the wallet
        $wallet = $profile->wallets()->create([
            'xrpl_address' => $request->xrpl_address,
            'xrpl_public_key' => $request->xrpl_public_key,
            'label' => $request->label ?? 'Primary',
            'is_primary' => true,
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet created successfully',
            'data' => new WalletResource($wallet)
        ], 201);
    }

    /**
     * Remove the authenticated user's wallet.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get wallet from the appropriate profile based on user type
        $profile = null;
        if ($user->isDeveloper()) {
            $profile = $user->developerProfile;
        } elseif ($user->isLender()) {
            $profile = $user->lenderProfile;
        }

        if (!$profile || !$profile->hasWallet()) {
            return response()->json([
                'success' => false,
                'message' => 'No wallet found'
            ], 404);
        }

        // Soft delete all wallets
        $profile->wallets()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wallet removed successfully'
        ]);
    }
}
