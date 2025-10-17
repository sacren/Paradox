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

    expect($post->likedBy)->toHaveCount(1);
    expect($post->likedBy->first()->id)->toBe($user->id);

    expect($user->likedPosts)->toHaveCount(1);
    expect($user->likedPosts->first()->id)->toBe($post->id);
});
