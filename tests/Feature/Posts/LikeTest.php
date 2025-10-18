<?php

use App\Models\User;
use App\Models\Post;

test('a post can be liked by users', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $post->likedBy()->attach($user);

    $this->assertDatabaseHas('likes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);

    $likedBy = $post->likedBy()->get();
    $likedPosts = $user->likedPosts()->get();

    expect($likedBy)->toHaveCount(1);
    expect($likedBy->first()->id)->toBe($user->id);

    expect($likedPosts)->toHaveCount(1);
    expect($likedPosts->first()->id)->toBe($post->id);
});
