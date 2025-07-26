<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

interface Photo {
    id: number
    file_path: string
}

const showRecent = ref(false)
const recentPhotos = ref<Photo[]>([])

const toggleRecent = () => {
    showRecent.value = !showRecent.value
}

const goToSession = () => {
    router.visit('/select-template')
}

const enterFullscreen = () => {
    const docElm = document.documentElement
    if (docElm.requestFullscreen) {
        docElm.requestFullscreen()
    } else if ((docElm as any).webkitRequestFullscreen) {
        (docElm as any).webkitRequestFullscreen()
    } else if ((docElm as any).msRequestFullscreen) {
        (docElm as any).msRequestFullscreen()
    }
}

onMounted(() => {
    fetch('/photos/recent')
        .then(res => res.json())
        .then(data => recentPhotos.value = data)
})
</script>

<template>
    <div class="h-screen bg-top bg-cover" style="background-image: url('/storage/images/bg.jpg')">
        <!-- Tombol Start Session -->
        <div class="fixed bottom-10 w-full flex justify-center z-50">
            <button
                @click="goToSession"
                class="w-[300px] h-[80px] bg-no-repeat bg-center bg-contain"
                :style="{ backgroundImage: 'url(/storage/images/btn2.png)' }"
            >
            </button>
        </div>

        <!-- Tombol Recent -->
        <button @click="toggleRecent" class="absolute top-4 left-4 bg-white px-4 py-2 rounded shadow" style="color: #000;">
            📸 Recent
        </button>

        <!-- Tombol Fullscreen -->
        <button @click="enterFullscreen" class="absolute top-4 right-4 bg-white px-4 py-2 rounded shadow" style="color: #000;">
            🔲 Fullscreen
        </button>

        <!-- Recent Photos -->
        <div v-if="showRecent" class="absolute top-20 left-4 bg-white shadow p-4 rounded w-48 h-64 overflow-auto" style="color: #000;">
            <p class="font-semibold mb-2">Recent Photos</p>
            <ul>
                <li v-for="photo in recentPhotos" :key="photo.id">
                    <img :src="`/storage/${photo.file_path}`" class="w-full mb-2 rounded shadow" />
                </li>
            </ul>
        </div>
    </div>
</template>

<!-- <script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

interface Photo {
    id: number
    file_path: string
}

const showRecent = ref(false)
const recentPhotos = ref<Photo[]>([])

const toggleRecent = () => {
    showRecent.value = !showRecent.value
}

const goToSession = () => {
    router.visit('/select-template')
}

onMounted(() => {
    fetch('/photos/recent')
        .then(res => res.json())
        .then(data => recentPhotos.value = data)
})
</script>

<template>
    <div class="h-screen bg-top bg-cover" style="background-image: url('/storage/images/bg.jpg')">
        <div class="fixed bottom-10 w-full flex justify-center z-50">
            <button
            @click="goToSession"
            class="w-[300px] h-[80px] bg-no-repeat bg-center bg-contain"
            :style="{ backgroundImage: 'url(/storage/images/btn2.png)' }"
            >
            </button>
        </div>
        <button @click="toggleRecent" class="absolute top-4 left-4 bg-white px-4 py-2 rounded shadow" style="color: #000;">📸 Recent</button>

        <div v-if="showRecent" class="absolute top-20 left-4 bg-white shadow p-4 rounded w-48 h-64 overflow-auto" style="color: #000;">
            <p class="font-semibold mb-2">Recent Photos</p>
            <ul>
                <li v-for="photo in recentPhotos" :key="photo.id">
                    <img :src="`/storage/${photo.file_path}`" class="w-full mb-2 rounded shadow" />
                </li>
            </ul>
        </div>
    </div>
</template> -->

<!-- <template>
    <div class="h-screen bg-top bg-cover flex flex-col justify-center items-center" style="background-image: url('/storage/images/bg.jpg')">
        <h1 class="text-white text-5xl font-bold mb-6">Click n Pose</h1>
        <button @click="goToSession" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full text-lg">Start Session</button>
        <button @click="toggleRecent" class="absolute top-4 left-4 bg-white px-4 py-2 rounded shadow" style="color: #000;">📸 Recent</button>

        <div v-if="showRecent" class="absolute top-20 left-4 bg-white shadow p-4 rounded w-48 h-64 overflow-auto" style="color: #000;">
            <p class="font-semibold mb-2">Recent Photos</p>
            <ul>
                <li v-for="photo in recentPhotos" :key="photo.id">
                    <img :src="`/storage/${photo.file_path}`" class="w-full mb-2 rounded shadow" />
                </li>
            </ul>
        </div>
    </div>
</template> -->
