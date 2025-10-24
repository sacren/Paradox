<?php

use App\Mail\PostLikedNotification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('sends a notification email when a user likes a post', function () {
    Mail::fake();

    $postOwner = User::factory()->create();
    $liker = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $postOwner->id]);

    actingAs($liker)
        ->post(route('posts.like', $post))
        ->assertRedirect()
        ->assertSessionHas('success', 'Post liked!');

    assertDatabaseHas('likes', [
        'user_id' => $liker->id,
        'post_id' => $post->id,
    ]);

    Mail::assertQueued(PostLikedNotification::class, function (PostLikedNotification $mail) use ($post, $liker, $postOwner) {
        return $mail->post->id === $post->id
            && $mail->liker->id === $liker->id
            && $mail->hasTo($postOwner->email);
    });
});

it('does not send email when unliking (starting from liked state)', function () {
    Mail::fake();

    $postOwner = User::factory()->create();
    $liker = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $postOwner->id]);

    // Manually create like (simulate prior state)
    $post->likes()->create(['user_id' => $liker->id]);

    actingAs($liker)
        ->post(route('posts.like', $post))
        ->assertRedirect()
        ->assertSessionHas('success', 'Post unliked.');

    assertDatabaseMissing('likes', [
        'user_id' => $liker->id,
        'post_id' => $post->id,
    ]);

    Mail::assertNothingQueued(); // Because we never faked before the like
});

it('does not send email when post owner likes their own post', function () {
    Mail::fake();

    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    actingAs($user)
        ->post(route('posts.like', $post));

    Mail::assertNothingQueued();
});
