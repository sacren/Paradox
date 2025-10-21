<?php

namespace App\Http\Controllers;

use App\Mail\PostLikedNotification;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LikeController extends Controller
{
    public function toggle(Post $post): RedirectResponse
    {
        $user = Auth::user();
        $userId = $user->id;

        // Prevent user from liking their own post
        if ($post->user_id === $userId) {
            return back()->with('info', 'You cannot like your own post.');
        }

        $like = $post->likes()->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $message = 'Post unliked.';
        } else {
            $post->likes()->create(['user_id' => $userId]);
            $message = 'Post liked!';

            // Send email notification to post owner
            if ($post->user && $post->user->email) {
                Mail::to($post->user->email)->send(
                    new PostLikedNotification($post, $user)
                );
            }
        }

        return back()->with('success', $message);
    }
}
