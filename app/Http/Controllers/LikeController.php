<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Post $post): RedirectResponse
    {
        $userId = Auth::id();

        $like = $post->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $message = 'Post unliked.';
        } else {
            $post->likes()->create(['user_id' => $userId]);
            $message = 'Post liked!';
        }

        return back()->with('success', $message);
    }
}
