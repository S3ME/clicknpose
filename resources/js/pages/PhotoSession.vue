<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

interface Slot {
  x: number
  y: number
  width: number
  height: number
}

const props = defineProps<{
  template: any
  photo_path: string
}>()

// Layout & State
const layout = ref([])
const videoRef = ref<HTMLVideoElement | null>(null)
const canvasRef = ref<HTMLCanvasElement | null>(null)

const totalPhotos = ref(0)
const previews = ref<string[]>([])
const currentIndex = ref(0)
const selectedPreviewIndex = ref<number | null>(null)

const countdown = ref(3)
const sessionStarted = ref(false)
const isCapturing = ref(false)
const isRetaking = ref(false)

const allCaptured = computed(() => previews.value.every(Boolean))

// Parse layout
const parseLayout = () => {
  try {
    layout.value = JSON.parse(props.template?.layout_json || '[]')
    totalPhotos.value = layout.value.length
    previews.value = Array(totalPhotos.value).fill(null)
  } catch (err) {
    console.warn('Invalid layout_json:', err)
  }
}

// Camera
const startCamera = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true })
    if (videoRef.value) videoRef.value.srcObject = stream
  } catch (err) {
    alert('Gagal mengakses kamera. Pastikan izin kamera sudah diberikan.')
    console.error(err)
  }
}
const stopCamera = () => {
  const stream = videoRef.value?.srcObject as MediaStream
  stream?.getTracks().forEach(track => track.stop())
}

// Countdown logic
const runCountdown = () => new Promise<void>((resolve) => {
  countdown.value = 3
  const interval = setInterval(() => {
    countdown.value--
    if (countdown.value === 0) {
      clearInterval(interval)
      resolve()
    }
  }, 1000)
})

// Capture logic
const capturePhoto = (index: number) => {
  const canvas = canvasRef.value
  const ctx = canvas?.getContext('2d')
  if (!canvas || !ctx || !videoRef.value) return

  ctx.drawImage(videoRef.value, 0, 0, canvas.width, canvas.height)
  // previews.value[index] = canvas.toDataURL('image/png')
  previews.value[index] = canvas.toDataURL('image/jpeg', 0.9)
}

// Main photo session
const startPhotoSession = async () => {
  if (isCapturing.value || currentIndex.value >= totalPhotos.value) return

  sessionStarted.value = true
  isCapturing.value = true
  await startCamera()

  for (let i = currentIndex.value; i < totalPhotos.value; i++) {
    await runCountdown()
    capturePhoto(i)
    currentIndex.value++
    await new Promise(r => setTimeout(r, 500))
  }

  isCapturing.value = false
  stopCamera()
}

// Retake logic
const retakePhoto = async (index: number) => {
  selectedPreviewIndex.value = null
  isRetaking.value = true

  await startCamera()
  await runCountdown()
  capturePhoto(index)

  isRetaking.value = false
  selectedPreviewIndex.value = null
}

// Finish session
const finish = async () => {
  if (previews.value.some(p => !p)) {
    alert('Pastikan semua foto telah diambil.')
    return
  }

  try {
    const response = await axios.post('/session/finish', {
      template_id: props.template.id,
      photos: previews.value,
    })

    const sessionCode = response.data.session_code
    router.visit(`/preview/${sessionCode}`)
  } catch (error) {
    console.error(error)
    alert('Gagal menyelesaikan sesi.')
  }
}

// Init
onMounted(() => {
  if (!props.template || !props.template.layout_json) {
    alert('Invalid template or layout.')
    router.visit('/select-template')
    return
  }
  parseLayout()
  startCamera()
})

// Stop camera once complete
watch(allCaptured, (val) => {
  if (val && !isRetaking.value) stopCamera()
})
</script>

<template>
  <div class="relative w-screen h-screen bg-black text-white">
    <!-- Live Camera -->
    <video
      ref="videoRef"
      autoplay
      playsinline
      class="absolute inset-0 w-full h-full object-cover z-0"
      v-show="!selectedPreviewIndex"
    ></video>
    <canvas ref="canvasRef" width="1280" height="720" class="hidden"></canvas>

    <!-- Countdown -->
    <div v-if="(isCapturing || isRetaking) && countdown > 0" class="absolute inset-0 flex items-center justify-center z-10">
      <div class="text-9xl font-bold text-red-600 drop-shadow-lg">{{ countdown }}</div>
    </div>

    <!-- Start Button -->
    <div v-if="!sessionStarted && !allCaptured" class="absolute inset-0 flex items-center justify-center z-10">
      <button
        @click="startPhotoSession"
        :disabled="isCapturing || allCaptured"
        class="bg-blue-600 hover:bg-blue-700 px-10 py-5 rounded-lg text-3xl font-semibold"
      >
        {{ totalPhotos === 1 ? 'Take Photo' : 'Start Session' }}
      </button>
    </div>

    <!-- Fullscreen Preview (Retake) -->
    <div
      v-if="selectedPreviewIndex !== null"
      class="absolute inset-0 bg-black flex items-center justify-center z-20"
    >
      <img :src="previews[selectedPreviewIndex]" class="max-w-full max-h-full object-contain rounded" />
      <button
        @click="retakePhoto(selectedPreviewIndex)"
        class="absolute top-4 right-4 bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded-lg text-sm font-bold"
      >
        Retake
      </button>
    </div>

    <!-- Preview Thumbnails -->
    <div class="absolute bottom-0 left-0 right-0 bg-black/70 px-4 py-3 flex flex-wrap gap-3 justify-center z-30">
      <div
        v-for="(preview, index) in previews"
        :key="index"
        class="relative w-24 h-24 border-2 border-white overflow-hidden rounded cursor-pointer"
        @click="selectedPreviewIndex = index"
      >
        <img v-if="preview" :src="preview" class="object-cover w-full h-full" />
        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Empty</div>
      </div>
    </div>

    <!-- Finish Button -->
    <div v-if="allCaptured" class="absolute bottom-0 right-0 m-6 z-40">
      <button
        @click="finish"
        class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg text-lg font-semibold"
      >
        Print & Finish
      </button>
    </div>
  </div>
</template>

<!-- <script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import Hls from 'hls.js'

interface Slot {
  x: number
  y: number
  width: number
  height: number
}

const props = defineProps<{
  template: any
  photo_path: string
}>()

const layout = ref([])
const videoRef = ref<HTMLVideoElement | null>(null)
const canvasRef = ref<HTMLCanvasElement | null>(null)
const totalPhotos = ref(0)
const previews = ref<string[]>([])
const currentIndex = ref(0)
const selectedPreviewIndex = ref<number | null>(null)

const countdown = ref(3)
const sessionStarted = ref(false)
const isCapturing = ref(false)
const isRetaking = ref(false)

const allCaptured = computed(() => previews.value.every(Boolean))

const parseLayout = () => {
  try {
    layout.value = JSON.parse(props.template?.layout_json || '[]')
    totalPhotos.value = layout.value.length
    previews.value = Array(totalPhotos.value).fill(null)
  } catch (err) {
    console.warn('Invalid layout_json:', err)
  }
}

// const startCamera = async () => {
//   if (!videoRef.value) return

//   const hlsSrc = 'https://clicknposestudio.com/hls/preview.m3u8'

//   if (Hls.isSupported()) {
//     const hls = new Hls()
//     hls.loadSource(hlsSrc)
//     hls.attachMedia(videoRef.value)
//   } else if (videoRef.value.canPlayType('application/vnd.apple.mpegurl')) {
//     videoRef.value.src = hlsSrc
//   } else {
//     alert('Browser tidak mendukung HLS playback')
//   }
// }

// const stopCamera = () => {
//   if (videoRef.value && videoRef.value.srcObject) {
//     const stream = videoRef.value.srcObject as MediaStream
//     stream.getTracks().forEach(track => track.stop())
//   }
// }

const hls = ref<Hls | null>(null)

const startCamera = async () => {
  const hlsSrc = 'https://clicknposestudio.com/hls/preview.m3u8'
  if (!videoRef.value) return

  if (Hls.isSupported()) {
    hls.value = new Hls()
    hls.value.loadSource(hlsSrc)
    hls.value.attachMedia(videoRef.value)
  } else if (videoRef.value.canPlayType('application/vnd.apple.mpegurl')) {
    videoRef.value.src = hlsSrc
  } else {
    alert('Browser tidak mendukung HLS playback.')
  }
}

const stopCamera = () => {
  // Matikan HLS stream
  if (hls.value) {
    hls.value.destroy()
    hls.value = null
  }

  // Jika masih ada stream tertinggal (opsional safeguard)
  if (videoRef.value && videoRef.value.srcObject) {
    const stream = videoRef.value.srcObject as MediaStream
    stream.getTracks().forEach(track => track.stop())
    videoRef.value.srcObject = null
  }

  // Kosongkan src
  if (videoRef.value) {
    videoRef.value.removeAttribute('src')
    videoRef.value.load()
  }
}

const runCountdown = () => new Promise<void>((resolve) => {
  countdown.value = 5
  const interval = setInterval(() => {
    countdown.value--
    if (countdown.value === 0) {
      clearInterval(interval)
      resolve()
    }
  }, 1000)
})

const capturePhoto = (index: number) => {
  const canvas = canvasRef.value
  const ctx = canvas?.getContext('2d')
  if (!canvas || !ctx || !videoRef.value) return
  ctx.drawImage(videoRef.value, 0, 0, canvas.width, canvas.height)
  previews.value[index] = canvas.toDataURL('image/jpeg', 0.9)
}

const startPhotoSession = async () => {
  if (isCapturing.value || currentIndex.value >= totalPhotos.value) return

  sessionStarted.value = true
  isCapturing.value = true
  await startCamera()

  for (let i = currentIndex.value; i < totalPhotos.value; i++) {
    await runCountdown()
    capturePhoto(i)
    currentIndex.value++
    await new Promise(r => setTimeout(r, 500))
  }

  isCapturing.value = false
  stopCamera()
}

const retakePhoto = async (index: number) => {
  selectedPreviewIndex.value = null
  isRetaking.value = true

  await startCamera()
  await runCountdown()
  capturePhoto(index)

  isRetaking.value = false
  selectedPreviewIndex.value = null
}

const finish = async () => {
  if (previews.value.some(p => !p)) {
    alert('Pastikan semua foto telah diambil.')
    return
  }

  try {
    const response = await axios.post('/session/finish', {
      template_id: props.template.id,
      photos: previews.value,
    })

    const sessionCode = response.data.session_code
    router.visit(`/preview/${sessionCode}`)
  } catch (error) {
    console.error(error)
    alert('Gagal menyelesaikan sesi.')
  }
}

onMounted(() => {
  if (!props.template || !props.template.layout_json) {
    alert('Invalid template or layout.')
    router.visit('/select-template')
    return
  }
  parseLayout()
  startCamera()
})

watch(allCaptured, (val) => {
  if (val && !isRetaking.value) stopCamera()
})
</script> -->

<!-- <template>
  <div class="relative w-screen h-screen bg-black text-white">
    <video
      ref="videoRef"
      autoplay
      playsinline
      class="absolute inset-0 w-full h-full object-cover z-0"
      v-show="!selectedPreviewIndex"
    ></video>
    <canvas ref="canvasRef" width="1280" height="720" class="hidden"></canvas>

    <div v-if="(isCapturing || isRetaking) && countdown > 0" class="absolute inset-0 flex items-center justify-center z-10">
      <div class="text-9xl font-bold text-red-600 drop-shadow-lg">{{ countdown }}</div>
    </div>

    <div v-if="!sessionStarted && !allCaptured" class="absolute inset-0 flex items-center justify-center z-10">
      <button
        @click="startPhotoSession"
        :disabled="isCapturing || allCaptured"
        class="bg-blue-600 hover:bg-blue-700 px-10 py-5 rounded-lg text-3xl font-semibold"
      >
        {{ totalPhotos === 1 ? 'Take Photo' : 'Start Session' }}
      </button>
    </div>

    <div
      v-if="selectedPreviewIndex !== null"
      class="absolute inset-0 bg-black flex items-center justify-center z-20"
    >
      <img :src="previews[selectedPreviewIndex]" class="max-w-full max-h-full object-contain rounded" />
      <button
        @click="retakePhoto(selectedPreviewIndex)"
        class="absolute top-4 right-4 bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded-lg text-sm font-bold"
      >
        Retake
      </button>
    </div>

    <div class="absolute bottom-0 left-0 right-0 bg-black/70 px-4 py-3 flex flex-wrap gap-3 justify-center z-30">
      <div
        v-for="(preview, index) in previews"
        :key="index"
        class="relative w-24 h-24 border-2 border-white overflow-hidden rounded cursor-pointer"
        @click="selectedPreviewIndex = index"
      >
        <img v-if="preview" :src="preview" class="object-cover w-full h-full" />
        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Empty</div>
      </div>
    </div>

    <div v-if="allCaptured" class="absolute bottom-0 right-0 m-6 z-40">
      <button
        @click="finish"
        class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg text-lg font-semibold"
      >
        Print & Finish
      </button>
    </div>
  </div>
</template> -->