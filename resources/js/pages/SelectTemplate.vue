<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    templates: Array,
    photo_path: String
})

const selectedTemplateId = ref(null)
const isReadyToProceed = computed(() => selectedTemplateId.value !== null)

const continueToSession = () => {
    if (!isReadyToProceed.value) return

    // Kirim sebagai URL query manual
    const query = new URLSearchParams({
        template_id: selectedTemplateId.value,
        photo_path: props.photo_path,
    }).toString()

    router.visit(`/photo-session?${query}`, {
        method: 'get',
    })
}
</script>

<template>
    <div class="p-6 max-w-5xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">Choose Layout</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                v-for="template in templates"
                :key="template.id"
                class="border p-2 rounded cursor-pointer hover:border-blue-500 transition-all duration-200"
                :class="{ 'border-4 border-blue-600': selectedTemplateId === template.id }"
                @click="selectedTemplateId = template.id"
            >
                <img
                    :src="`/storage/${template.image_path}`"
                    class="w-full h-auto object-contain"
                    :alt="template.name || 'Template'"
                />
            </div>
        </div>

        <div class="mt-6 text-center">
            <button
                @click="continueToSession"
                class="px-6 py-2 rounded bg-blue-600 text-white font-semibold disabled:bg-gray-400"
                :disabled="!isReadyToProceed"
            >
                Ready to take a picture?
            </button>
        </div>
    </div>
</template>
