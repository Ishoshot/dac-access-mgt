<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\AccessToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Notifications\AccessTokenGenerated;
use Illuminate\Support\Facades\Notification;

class AccessTokenController extends Controller
{
    /**
     * Show all the access tokens
     */
    public function index()
    {
        try {
            $uuid = Str::uuid()->toString();

            Log::info('FETCH ALL TOKENS: START', ["uid" => $uuid]);

            $accessTokens = AccessToken::latest()->get();

            return response()->json(['success' => true, 'message' => 'Access tokens retrieved successfully', 'data' => $accessTokens], 200);
        } catch (Exception $e) {
            Log::error('FETCH ALL TOKENS: ERROR', ["uid" => $uuid, "error" => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Access tokens could not be retrieved', 'data' => $e->getMessage()], 500);
        }
    }


    /**
     * Create a new access token
     */
    public function store(Request $request)
    {
        try {
            $uuid = Str::uuid()->toString();

            Log::info('CREATE TOKEN: START', ["uid" => $uuid]);

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:access_tokens'],
            ]);

            if ($validator->fails()) {
                Log::error('CREATE TOKEN: VALIDATION', ["uid" => $uuid, "response" => ['errors' => $validator->errors()]]);
                return response()->json(['success' => false, 'message' => 'Validation failed', 'data' => ['errors' => $validator->errors()]], 422);
            }

            //merge request with token and expires_at
            $request->merge([
                'token' => Str::uuid()->toString(),
                'expires_at' => now()->addDays(7),
                'is_active' => true
            ]);


            $accessToken = AccessToken::create($request->all());

            //check if successfully created
            if (!$accessToken) {
                Log::error('CREATE TOKEN: VALIDATION', ["uid" => $uuid, "response" => 'Access token could not be created']);
                return response()->json(['success' => false, 'message' => 'Access token could not be created', 'data' => []], 500);
            }

            Notification::route('mail', $accessToken["email"])->notify(new AccessTokenGenerated($accessToken));

            return response()->json(['success' => true, 'message' => 'Access token created successfully', 'data' => $accessToken], 201);
        } catch (Exception $e) {
            Log::error('CREATE TOKEN: ERROR', ["uid" => $uuid, "error" => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Access token could not be created', 'data' => $e->getMessage()], 500);
        }
    }


    /**
     * Revoke an access token
     */
    public function revokeAccessToken(Request $request)
    {
        try {
            $uuid = Str::uuid()->toString();

            $validator = Validator::make($request->all(), [
                'token' => ['required', 'string', 'exists:access_tokens,token'],
            ]);

            if ($validator->fails()) {
                Log::error('REVOKE TOKEN: VALIDATION', ["uid" => $uuid, "response" => ['errors' => $validator->errors()]]);
                return response()->json(['success' => false, 'message' => 'Validation failed', 'data' => ['errors' => $validator->errors()]], 422);
            }

            $accessToken = AccessToken::where('token', $request->token)->first();

            $accessToken->is_active = false;

            $accessToken->revoked_at = now();

            $accessToken->save();

            return response()->json(['success' => true, 'message' => 'Access token revoked successfully', 'data' => $accessToken], 200);
        } catch (Exception $e) {
            Log::error('REVOKE TOKEN: ERROR', ["uid" => $uuid, "error" => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Access token could not be revoked', 'data' => $e->getMessage()], 500);
        }
    }


    /**
     * Extend an access token
     */
    public function extendAccessToken(Request $request)
    {
        try {
            $uuid = Str::uuid()->toString();

            $validator = Validator::make($request->all(), [
                'token' => ['required', 'string', 'exists:access_tokens,token'],
            ]);

            if ($validator->fails()) {
                Log::error('EXTEND TOKEN: VALIDATION', ["uid" => $uuid, "response" => ['errors' => $validator->errors()]]);
                return response()->json(['success' => false, 'message' => 'Validation failed', 'data' => ['errors' => $validator->errors()]], 422);
            }

            $accessToken = AccessToken::where('token', $request->token)->first();

            $accessToken->expires_at = now()->addDays(7);

            $accessToken->is_active = true;

            $accessToken->revoked_at = null;

            $accessToken->save();

            return response()->json(['success' => true, 'message' => 'Access token extended successfully', 'data' => $accessToken], 200);
        } catch (Exception $e) {
            Log::error('EXTEND TOKEN: ERROR', ["uid" => $uuid, "error" => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Access token could not be extended', 'data' => $e->getMessage()], 500);
        }
    }


    /**
     * Validate an access token
     */
    public function validateAccessToken(Request $request)
    {
        $uuid = Str::uuid()->toString();

        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string', 'exists:access_tokens,token'],
        ]);

        if ($validator->fails()) {
            Log::error('VALIDATE TOKEN: VALIDATION', ["uid" => $uuid, "response" => ['errors' => $validator->errors()]]);
            return response()->json(['success' => false, 'message' => 'Validation failed', 'data' => ['errors' => $validator->errors()]], 422);
        }

        $accessToken = AccessToken::where('token', $request->token)->first();

        if (!$accessToken) {
            Log::error('VALIDATE TOKEN: ERROR', ["uid" => $uuid, "error" => 'Access token does not exist']);
            return response()->json(['success' => false, 'message' => 'Access token does not exist', 'data' => []], 404);
        }

        if (!$accessToken->is_active) {
            Log::error('VALIDATE TOKEN: ERROR', ["uid" => $uuid, "error" => 'Access token is not active']);
            return response()->json(['success' => false, 'message' => 'Access token is not active', 'data' => $accessToken], 401);
        }

        if ($accessToken->revoked_at != null) {
            Log::error('VALIDATE TOKEN: ERROR', ["uid" => $uuid, "error" => 'Access token is revoked']);
            return response()->json(['success' => false, 'message' => 'Access token is revoked', 'data' => $accessToken], 401);
        }

        if (now() > $accessToken->expires_at) {
            Log::error('VALIDATE TOKEN: ERROR', ["uid" => $uuid, "error" => 'Access token is expired']);
            return response()->json(['success' => false, 'message' => 'Access token is expired', 'data' => $accessToken], 401);
        }

        Log::info('VALIDATE TOKEN: SUCCESS', ["uid" => $uuid, "response" => 'Access token is valid']);
        return response()->json(['success' => true, 'message' => 'Access token is valid', 'data' => $accessToken], 200);
    }
}
