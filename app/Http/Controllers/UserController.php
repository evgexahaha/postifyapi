<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return response()->json([
            'data' => [
                'success' => true,
                'user' => $user,
            ]
        ]);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'invalid username or password',
                ]
            ]);
        }

        $user->api_token = Str::random(16);
        $user->save();

        return response()->json([
            'data' => [
                'success' => true,
                'name' => $user->name,
                'api_token' => $user->api_token,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

//        if (!$token) {
//            return response()->json([
//                'data' => [
//                    'success' => false,
//                    'message' => 'Token not provided',
//                ]
//            ], 401);
//        }

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'User not found',
                ]
            ]);
        }

        $user->api_token = null;
        $user->save();

        return response()->json([
            'data' => [
                'success' => true,
                'message' => 'Logged out successfully',
            ]
        ]);
    }

    public function profile(Request $request)
    {
        $token = $request->bearerToken();
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'User not authenticated',
                ]
            ], 401);
        }

        $userPosts = Post::where('author', $user->id)->orderBy('date', 'desc')->get();

        if (!$userPosts) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'No posts found for this user',
                ]
            ]);
        }

        return response()->json([
            'data' => [
                'success' => true,
                'id' => $user->id,
                'photo_profile' => $user->photo_profile,
                'desc_profile' => $user->desc_profile,
                'userPosts' => $userPosts,
            ]
        ]);
    }

    public function uploadPhotoProfile(Request $request)
    {
        $token = $request->bearerToken();
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'User not authenticated',
                ]
            ], 401);
        }

        $image = $request->file('photo_profile')->store('profile', 'public');
        $imageUrl = asset('storage/' . $image);

        $user->photo_profile = $imageUrl;
        $user->save();

        return response()->json([
            'data' => [
                'success' => true,
                'photo_profile' => $imageUrl,
            ]
        ]);
    }

    public function uploadDescProfile(Request $request)
    {
        $token = $request->bearerToken();
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'User not authenticated',
                ]
            ], 401);
        }

        $user->desc_profile = $request->desc_profile;
        $user->save();

        return response()->json([
            'data' => [
                'success' => true,
                'desc_profile' => $user->desc_profile,
            ]
        ]);
    }

    public function anyUser($name)
    {
        $user = User::where('name', $name)->first();

        return response()->json([
            'data' => [
                'success' => true,
                'id' => $user->id,
                'name' => $user->name,
                'photo_profile' => $user->photo_profile,
                'desc_profile' => $user->desc_profile,
                'created_at' => $user->created_at,
            ]
        ]);
    }
}
