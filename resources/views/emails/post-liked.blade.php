<x-mail::message>
# 👍 {{ $liker->name }} liked your post!

Hi {{ optional($post->user)->name ?? 'there' }},

Your post received a like from **{{ $liker->name }}**:

> "{{ Str::limit($post->content, 120) }}"

<x-mail::button :url="route('posts.index')">
View All Posts
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
