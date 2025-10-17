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

    // Disambiguate with table name
    $likedUserIds = $post->likedBy()->select('users.id')->pluck('id');
    $likedPostIds = $user->likedPosts()->select('posts.id')->pluck('id');

    expect($likedUserIds)->toContain($user->id);
    expect($likedPostIds)->toContain($post->id);
});
