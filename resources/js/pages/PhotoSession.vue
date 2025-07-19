<script setup lang="ts">
import { ref } from 'vue'

const video = ref<HTMLVideoElement | null>(null)
const stream = ref<MediaStream | null>(null)
const countdown = ref(0)
const photo = ref<string | null>(null)
const isCaptured = ref(false)

const startCamera = async () => {
    if (!stream.value) {
        stream.value = await navigator.mediaDevices.getUserMedia({ video: true })
        if (video.value) video.value.srcObject = stream.value
    }
}

const startSession = async () => {
    await startCamera()
    photo.value = null
    isCaptured.value = false
    takePhotoWithCountdown()
}

const takePhotoWithCountdown = () => {
    countdown.value = 5
    const timer = setInterval(() => {
        countdown.value--
        if (countdown.value === 0) {
            clearInterval(timer)
            capturePhoto()
        }
    }, 1000)
}

const capturePhoto = () => {
    const videoEl = video.value
    if (!videoEl) return

    const canvas = document.createElement('canvas')
    canvas.width = videoEl.videoWidth
    canvas.height = videoEl.videoHeight
    const ctx = canvas.getContext('2d')
    if (ctx) ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height)

    photo.value = canvas.toDataURL('image/jpeg')
    isCaptured.value = true
}

const retakePhoto = () => {
    photo.value = null
    isCaptured.value = false
    takePhotoWithCountdown()
}

const uploadPhoto = async () => {
    if (!photo.value) return

    const blob = dataURItoBlob(photo.value)
    const formData = new FormData()
    formData.append('photo', blob, 'photo.jpg')

    await fetch('/api/photos/store', {
        method: 'POST',
        body: formData
    })

    alert('Photo uploaded!')
}

// helper
function dataURItoBlob(dataURI: string) {
    const byteString = atob(dataURI.split(',')[1])
    const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]
    const ab = new ArrayBuffer(byteString.length)
    const ia = new Uint8Array(ab)
    for (let i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i)
    }
    return new Blob([ab], { type: mimeString })
}
</script>

<template>
    <div class="w-full h-screen bg-black flex flex-col items-center justify-center relative">
        <video ref="video" autoplay playsinline class="w-full h-full object-cover absolute top-0 left-0" />

        <!-- Countdown -->
        <div v-if="countdown > 0" class="absolute text-white text-6xl font-bold z-10">
            {{ countdown }}
        </div>

        <!-- Buttons -->
        <div v-if="!isCaptured" class="absolute bottom-8 z-10 flex gap-4">
            <button @click="startSession" class="bg-white text-black px-6 py-3 rounded-full font-bold">Start</button>
        </div>

        <!-- Preview & Actions -->
        <div v-if="photo" class="absolute bottom-0 w-full bg-black bg-opacity-50 p-4 flex flex-col items-center gap-2 z-10">
            <img :src="photo" class="w-48 h-auto object-contain rounded border-2 border-white" />
            <div class="flex gap-4">
                <button @click="retakePhoto" class="bg-yellow-400 text-black px-4 py-2 rounded-full">Retake</button>
                <button @click="uploadPhoto" class="bg-green-500 text-white px-4 py-2 rounded-full">Save</button>
            </div>
        </div>
    </div>
</template>

<!-- 4 Photo Capture -->
<!-- <script setup lang="ts">
    import { ref } from 'vue'

    const video = ref<HTMLVideoElement | null>(null)
    const stream = ref<MediaStream | null>(null)
    const countdown = ref(0)
    const photoIndex = ref(0)
    const previews = ref<string[]>([])

    const startCamera = async () => {
        if (!stream.value) {
            stream.value = await navigator.mediaDevices.getUserMedia({ video: true })
            if (video.value) video.value.srcObject = stream.value
        }
    }

    const startSession = async () => {
        photoIndex.value = 0
        previews.value = []
        await startCamera()
        takePhotoWithCountdown()
    }

    const takePhotoWithCountdown = () => {
        countdown.value = 3
        const timer = setInterval(() => {
            countdown.value--
            if (countdown.value === 0) {
                clearInterval(timer)
                capturePhoto()
            }
        }, 1000)
    }

    const capturePhoto = () => {
    const canvas = document.createElement('canvas')
    const videoEl = video.value
    if (!videoEl) return

    canvas.width = videoEl.videoWidth
    canvas.height = videoEl.videoHeight
    const ctx = canvas.getContext('2d')
    if (ctx) ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height)

    const imageData = canvas.toDataURL('image/jpeg')
    previews.value.push(imageData)

    photoIndex.value++
        if (photoIndex.value < 4) {
            setTimeout(() => takePhotoWithCountdown(), 1000)
        } else {
            uploadPhotos()
        }
    }

    const uploadPhotos = async () => {
        const formData = new FormData()
        previews.value.forEach((photo, i) => {
            const blob = dataURItoBlob(photo)
            formData.append('photos[]', blob, `photo-${i}.jpg`)
        })

        await fetch('/api/photos/store', {
            method: 'POST',
            body: formData
        })
    }

    // helper
    function dataURItoBlob(dataURI: string) {
        const byteString = atob(dataURI.split(',')[1])
        const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]
        const ab = new ArrayBuffer(byteString.length)
        const ia = new Uint8Array(ab)
        for (let i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i)
        }
        return new Blob([ab], { type: mimeString })
    }
</script>

<template>
    <div class="w-full h-screen bg-black flex flex-col items-center justify-center relative">
        <video ref="video" autoplay playsinline class="w-full h-full object-cover absolute top-0 left-0"></video>

        <div v-if="countdown > 0" class="absolute text-white text-6xl font-bold z-10">
            {{ countdown }}
        </div>

        <div class="absolute bottom-8 z-10 flex gap-4">
            <button @click="startSession" class="bg-white text-black px-6 py-3 rounded-full font-bold">Start</button>
        </div>

        <div class="absolute bottom-0 w-full bg-black bg-opacity-50 p-4 flex justify-center gap-4">
            <img v-for="(photo, index) in previews" :key="index" :src="photo" class="w-24 h-24 object-cover rounded border-2 border-white" />
        </div>
    </div>
</template> -->
