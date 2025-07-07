<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    templates: Array<{
        id: number;
        name: string;
        image_path: string;
        image_url?: string;
    }>;
}>();

const confirmDelete = (id: number) => {
    if (confirm('Yakin ingin menghapus template ini?')) {
        router.delete(route('templates.destroy', id), {
        preserveScroll: true,
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Templates',
        href: '/office/templates',
    },
];

const imageError = ref(false);
</script>

<template>
    <Head title="Templates" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall title="Templates Manager" description="Manage photobooth templates for print layouts" />

            <div class="flex justify-end">
                <Link :href="route('templates.create')">
                    <Button class="bg-green-500 hover:bg-green-700 border border-green-600 text-white">
                        <Plus class="mr-2" :size="18" /> Add Template
                    </Button>
                </Link>
            </div>

            <div v-if="templates.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="template in templates" :key="template.id" class="relative bg-white border rounded-lg shadow p-4" >
                    <h3 class="text-base font-semibold text-black flex justify-center mb-5">{{ template.name }}</h3>
                    <img
                        v-if="template.image_path && !imageError"
                        :src="`/storage/${template.image_path}`"
                        @error="imageError = true"
                    />

                    <svg
                        v-else
                        class="w-full h-40"
                        viewBox="0 0 200 200"
                        xmlns="http://www.w3.org/2000/svg"
                        >
                        <rect width="100%" height="100%" fill="#f8f8f8" stroke="#ccc" stroke-width="2"/>
                        <text x="100" y="105" fill="#999" font-size="16" text-anchor="middle">Image Not Found</text>
                    </svg>

                    <div class="mt-4 flex justify-between items-center w-full">
                        <!-- Button Edit -->
                        <Link :href="route('templates.edit', template.id)">
                            <button
                            class="flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 border border-yellow-500 rounded-lg px-4 py-2 text-sm shadow transition-all"
                            >
                            <Pencil class="w-4 h-4" />
                            Edit
                            </button>
                        </Link>

                        <!-- Button Delete -->
                        <button
                            class="flex items-center gap-2 bg-red-500 hover:bg-red-600 border border-red-600 text-white rounded-lg px-4 py-2 text-sm shadow transition-all"
                            @click="confirmDelete(template.id)"
                        >
                            <Trash2 class="w-4 h-4" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center text-muted-foreground">
                Templates not found.
            </div>
        </div>
    </AppLayout>
</template>
