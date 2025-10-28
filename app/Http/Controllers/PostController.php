<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function store(Request $request)
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

        $request->validate([
            'title' => 'required|max:255',
            'description' => 'required|max:255',
        ]);

        $image = $request->file('photo_url')->store('posts', 'public');
        $imageUrl = asset('storage/' . $image);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'photo_url' => $imageUrl,
            'date' => now(),
            'author' => $user->id,
        ]);

        $post->load('user:id,name,photo_profile');

        return response()->json([
            'data' => [
                'success' => true,
                'post' => $post,
            ]
        ]);
    }

    public function all()
    {
        $posts = Post::with('user:id,name')->orderBy('date', 'desc')->get();

        return response()->json([
            'data' => [
                'success' => true,
                'posts' => $posts,
            ]
        ]);
    }

    public function get($id)
    {
        $post = Post::with('user:id,name')->find($id);

        if (!$post) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Post not found',
                ]
            ], 404);
        }

        return response()->json([
            'data' => [
                'success' => true,
                'post' => $post,
            ]
        ]);
    }

    public function destroy(Request $request, $id)
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

        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Post not found',
                ]
            ]);
        }

        $post->delete();

        return response()->json([
            'data' => [
                'success' => true,
                'message' => 'Post deleted successfully',
            ]
        ]);
    }

    public function update(Request $request, $id)
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

        $post = Post::find($id);

        $updateData = [
            'title' => $request->title ?? $post->title,
            'description' => $request->description ?? $post->description,
        ];

        if ($request->hasFile('photo_url')) {
            $image = $request->file('photo_url')->store('posts', 'public');
            $imageUrl = asset('storage/' . $image);
            $updateData['photo_url'] = $imageUrl;
        }

        $post->update($updateData);
        $post->refresh();

        return response()->json([
            'data' => [
                'success' => true,
                'post' => $post,
            ]
        ]);
    }
}
