<script setup lang="ts">
    import { Head, Link as InertiaLink, useForm, usePage, router } from '@inertiajs/vue3';
    import { computed, watch, ref, onBeforeUnmount } from 'vue';
    import { useDateFormatter } from '@/composables/useDateFormatter';
    import AppLayout from '@/layouts/AppLayout.vue';
    import type { BreadcrumbItem } from '@/types';

    interface User {
        id: number;
        name: string;
    }

    interface Post {
        id: number;
        title: string;
        content: string;
        created_at: string;
        user: User;
        is_liked: number;
        likes_count: number;
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface PaginatedPosts {
        data: Post[];
        links: PaginationLink[];
    }

    interface PostsType {
        posts: PaginatedPosts;
    }

    defineProps<PostsType>();

    const { formatDate } = useDateFormatter();

    const form = useForm({
        content: '',
    });

    const submitData = () => {
        form.post('/posts', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset(); // automatically reset to initial values
                router.reload();
            },
            // Idiomatic with no onError
        });
    };

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Posts',
            href: '/posts',
        },
    ];

    const page = usePage();
    const currentUser = computed(() => page.props.auth.user ?? null);
    const flashSuccess = computed(() => page.props.flash?.success ?? null);
    const localFlash = ref(flashSuccess.value ?? null);
    let flashTimer: ReturnType<typeof setTimeout> | null = null;
    const isPostOwner = (post: Post): boolean => {
        return currentUser.value?.id === post.user.id;
    };

    const deletePost = (postId: number): void => {
        if (confirm('Are you sure you want to delete this post?')) {
            router.delete(`/posts/${postId}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // Optional: show success message
                },
            });
        }
    };

    const toggleLike = (postId: number): void => {
        router.post(`/posts/${postId}/like`, {}, {
            preserveScroll: true,
            // Optional: optimistic UI update (advanced)
            // But for now, just reload or rely on Inertia's automatic re-render
        });
    };

    watch(
        () => page.props.flash?.success,
        (value) => {
            // Do nothing when flash disappears from props
            if (!value) {
                return;
            }

            // reset timer if new flash message arrives
            if (flashTimer) {
                clearTimeout(flashTimer);
                flashTimer = null;
            }

            localFlash.value = value;

            flashTimer = setTimeout(() => {
                localFlash.value = null;
            }, 12000);
        },
    );

    // clean up on unmount
    onBeforeUnmount(() => {
        if (flashTimer) {
            clearTimeout(flashTimer);
        }
    });
</script>

<template>
    <Head title="Posts" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 sm:p-6 max-w-4xl mx-auto space-y-8">
            <h1 class="text-2xl font-bold mb-6">All Posts</h1>

            <div v-if="localFlash"
                class="text-center text-sm font-medium text-green-600"
                role="alert">
                {{ localFlash }}
            </div>

            <!-- Create new post form -->
            <section class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <h2 class="text-xl font-semibold mb-4">Write a new post</h2>
                <form @submit.prevent="submitData" class="space-y-4">
                    <!-- Content -->
                    <div>
                        <label for="content" class="sr-only">
                            What's on your mind?
                        </label>
                        <textarea
                            id="content"
                            v-model="form.content"
                            @input="form.clearErrors()"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md
                                    focus:ring-blue-500 focus:border-blue-500"
                            placeholder="What's on your mind?">
                        </textarea>
                        <div v-if="form.errors.content"
                             class="text-red-600 text-sm mt-1"
                             role="alert">
                            {{ form.errors.content }}
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div>
                        <button type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md
                                       hover:bg-blue-700 disabled:opacity-50
                                       disabled:cursor-not-allowed transition">
                            {{ form.processing ? 'Posting...' : 'Create Post' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Empty state when no posts exist -->
            <div v-if="!posts.data.length" class="text-center py-8 text-gray-500">
                No posts found.
            </div>

            <div v-for="post in posts.data" :key="post.id" class="space-y-6">
                <article class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
                    <header class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6">
                        <h2 class="text-2xl font-bold leading-tight">{{ post.title }}</h2>
                        <div class="mt-1 text-blue-100">
                            By <span class="font-semibold">{{ post.user.name }}</span>
                            on <time :datetime="post.created_at">
                                {{ formatDate(post.created_at) }}
                            </time>
                        </div>
                    </header>

                    <!-- Post content -->
                    <div class="p-6">
                        <p class="text-gray-700 leading-relaxed">{{ post.content }}</p>

                        <div class="mt-4 flex items-center space-x-4">
                            <!-- Action buttons -->
                            <div v-if="isPostOwner(post)" class="space-x-2">
                                <button @click="deletePost(post.id)"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Delete
                                </button>
                            </div>

                            <!-- Like button -->
                            <div class="flex items-center space-x-4">
                                <button @click="toggleLike(post.id)"
                                     class="text-sm font-medium flex items-center space-x-1"
                                     :class="post.is_liked
                                             ? 'text-red-600 hover:text-red-800'
                                             : 'text-gray-600 hover:text-blue-600'">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-5 w-5"
                                         viewBox="0 0 20 20"
                                         fill="currentColor">
                                        <path fill-rule="evenodd"
                                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                                clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ post.is_liked ? 'Unlike' : 'Like' }}</span>
                                </button>

                                <!-- Show like count -->
                                <span class="text-sm text-gray-500">{{ post.likes_count || 0 }} likes</span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Pagination -->
            <nav v-if="posts.links.length > 3"
                 class="flex justify-center mt-12 space-x-2"
                 aria-label="Pagination">
                <template v-for="(link, index) in posts.links" :key="index">
                    <InertiaLink
                            v-if="link.url"
                            :href="link.url"
                            :aria-label="`Go to page ${link.label}`"
                            v-html="link.label"
                            :class="[ 'px-3 py-1 text-sm border rounded transition',
                                    link.active
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'text-blue-600 hover:bg-blue-50', ]">
                    </InertiaLink>
                    <span v-else
                          v-html="link.label"
                          :aria-label="`Page ${link.label}`"
                          class="px-3 py-1 text-sm text-gray-400" />
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
