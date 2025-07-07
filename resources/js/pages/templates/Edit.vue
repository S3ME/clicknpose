<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref } from 'vue';

// Props dari Inertia
const props = defineProps<{
    template: {
        id: number;
        name: string;
        image_path: string;
    };
}>();

const form = useForm({
    name: props.template.name,
    image: null as File | null,
});

const imagePreview = ref<string | null>(props.template.image_path ? `/storage/${props.template.image_path}` : null);

const handleImageChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('templates.update', props.template.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // optional: redirect or flash message
        },
    });
};

const breadcrumbs = [
    { title: 'Templates', href: route('templates.index') },
    { title: 'Edit Template', href: route('templates.edit', props.template.id) },
];
</script>

<template>
    <Head title="Edit Template" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall title="Edit Template" description="Update existing photobooth template" />

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="name">Template Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autocomplete="off"
                        placeholder="Nama template"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="image">Template Image</Label>
                    <Input
                        id="image"
                        type="file"
                        accept="image/*"
                        @change="handleImageChange"
                    />
                    <InputError :message="form.errors.image" />

                    <div v-if="imagePreview" class="mt-2">
                        <img :src="imagePreview" alt="Preview" class="max-w-xs rounded border" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Button Update -->
                    <Button :disabled="form.processing" class="px-5 py-2 text-sm rounded-md shadow hover:shadow-md transition">
                        Update
                    </Button>

                    <!-- Button Cancel -->
                    <Link
                        :href="route('templates.index')"
                    >
                        <button
                            class="flex items-center px-5 py-2 text-sm font-medium bg-red-500 hover:bg-red-700 border border-red-600 text-white rounded-md hover:text-black transition-all shadow-sm">
                            Cancel
                        </button>
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
