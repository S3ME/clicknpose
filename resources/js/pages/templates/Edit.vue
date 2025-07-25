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

// Inertia form setup
const form = useForm({
    name: props.template.name,
    image: null as File | null,
});

// Image preview state
const imagePreview = ref<string | null>(
    props.template.image_path ? `/storage/${props.template.image_path}` : null
);

// Handle file change
const handleImageChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;

    if (file.type !== 'image/png') {
        alert('Hanya file PNG yang diperbolehkan.');
        (e.target as HTMLInputElement).value = '';
        return;
    }

    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
};

// Form submission
const submit = () => {
    // form.transform(() => ({
    //     name: form.name,
    //     image: form.image,
    // })).put(route('templates.update', props.template.id), {
    //     forceFormData: form.image !== null,
    //     preserveScroll: true,
    // });
    form.transform(data => ({
        ...data,
        _method: 'put',
        templateId: props.template.id,
    }))
        .post(route('templates.update', props.template.id), {
        preserveScroll: true,
        forceFormData: form.image !== null,
    })
};

// Breadcrumbs
const breadcrumbs = [
    { title: 'Templates', href: route('templates.index') },
    { title: 'Edit Template', href: route('templates.edit', props.template.id) },
];
</script>

<template>
    <Head title="Edit Template" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 rounded-xl">
            <HeadingSmall title="Edit Template" description="Update existing photobooth template" />

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Nama Template -->
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

                <!-- Gambar Template -->
                <div class="grid gap-2">
                    <Label for="image">Template Image (PNG only)</Label>
                    <Input
                        id="image"
                        type="file"
                        accept="image/png"
                        @change="handleImageChange"
                    />
                    <InputError :message="form.errors.image" />

                    <div v-if="imagePreview" class="mt-2">
                        <img :src="imagePreview" alt="Preview" class="max-w-xs rounded border" />
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing" class="px-5 py-2 text-sm rounded-md shadow hover:shadow-md transition">
                        Update
                    </Button>

                    <Link :href="route('templates.index')">
                        <button
                            type="button"
                            class="flex items-center px-5 py-2 text-sm font-medium bg-red-500 hover:bg-red-700 border border-red-600 text-white rounded-md hover:text-black transition-all shadow-sm">
                            Cancel
                        </button>
                    </Link>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
