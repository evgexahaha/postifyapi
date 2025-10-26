<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Post not found'
                ]
            ]);
        }

        $comments = Comment::where('post_id', $post->id)->get();

        $comments->load('user:id,name,photo_profile');

        return response()->json([
            'data' => [
                'success' => true,
                'comments' => $comments
            ]
        ]);
    }

    public function store(Request $request, $id)
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

        $comment = Comment::create([
            'comment' => $request->comment,
            'post_id' => $id,
            'user_id' => $user->id,
        ]);

        $comment->load('user:id,name,photo_profile');

        return response()->json([
            'data' => [
                'success' => true,
                'comment' => $comment,
            ]
        ]);
    }

    public function destroy(Request $request, $id) {
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

        $comment = Comment::find($id);

        $comment->delete();

        return response()->json([
            'data' => [
                'success' => true,
                'message' => 'Comment deleted'
            ]
        ]);
    }
}
