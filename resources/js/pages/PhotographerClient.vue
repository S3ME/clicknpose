<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { io } from 'socket.io-client'

const sessionId = 'main-session'
let stream: MediaStream
let video: HTMLVideoElement
let canvas: HTMLCanvasElement
let intervalId: number

// const socket = io('https://clicknposestudio.com', {
//   path: '/socket.io',
//   transports: ['websocket']
// })

const socket = new WebSocket("wss://clicknposestudio.com/socket");

onMounted(async () => {
  console.log('[PhotographerClient] Connecting to socket...')
  socket.emit('join-session', { sessionId })
  console.log('[PhotographerClient] Sent join-session:', sessionId)

  socket.on('connect', () => {
    console.log('[PhotographerClient] ✅ Connected to socket:', socket.id)
  })

  socket.on('disconnect', () => {
    console.log('[PhotographerClient] ❌ Disconnected from socket')
  })

  try {
    stream = await navigator.mediaDevices.getUserMedia({ video: true })
    console.log('[PhotographerClient] ✅ Camera access granted')
  } catch (err) {
    console.error('[PhotographerClient] ❌ Error accessing camera:', err)
    return
  }

  video = document.createElement('video')
  canvas = document.createElement('canvas')
  video.srcObject = stream
  await video.play()

  canvas.width = 640
  canvas.height = 480

  intervalId = window.setInterval(() => {
    const ctx = canvas.getContext('2d')
    if (ctx) {
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
      const dataURL = canvas.toDataURL('image/jpeg', 0.6)
      const base64 = dataURL.replace(/^data:image\/jpeg;base64,/, '')
      socket.emit('preview-frame', { sessionId, data: base64 })
      console.log('[PhotographerClient] 🖼️ Sent preview-frame')
    }
  }, 150)
})

onUnmounted(() => {
  console.log('[PhotographerClient] Cleaning up...')
  clearInterval(intervalId)
  stream.getTracks().forEach(track => track.stop())
  socket.disconnect()
})
</script>

<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-2">📷 Photographer Client</h1>
    <p class="text-green-600">🟢 Connected to session: {{ sessionId }}</p>
    <p class="text-sm text-gray-500">Camera is streaming silently in the background...</p>
  </div>
</template>

<!-- <script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { io } from 'socket.io-client'

const sessionId = 'main-session'
let stream: MediaStream
let video: HTMLVideoElement
let canvas: HTMLCanvasElement
let intervalId: number

const socket = io('https://clicknposestudio.com', {
  path: '/socket.io',
  transports: ['websocket']
})

onMounted(async () => {
  stream = await navigator.mediaDevices.getUserMedia({ video: true })
  video = document.createElement('video')
  canvas = document.createElement('canvas')
  video.srcObject = stream
  await video.play()

  canvas.width = 640
  canvas.height = 480

  socket.emit('join-session', sessionId)

  intervalId = window.setInterval(() => {
    const ctx = canvas.getContext('2d')
    if (ctx) {
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
      const dataURL = canvas.toDataURL('image/jpeg', 0.6)
      const base64 = dataURL.replace(/^data:image\/jpeg;base64,/, '')
      socket.emit('preview-frame', { sessionId, data: base64 })
    }
  }, 150)
})

onUnmounted(() => {
  clearInterval(intervalId)
  stream.getTracks().forEach(track => track.stop())
  socket.disconnect()
})
</script>

<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-2">📷 Photographer Client</h1>
    <p class="text-green-600">🟢 Connected to session: {{ sessionId }}</p>
  </div>
</template> -->

<!-- <script setup lang="ts">
import { io } from "socket.io-client"

const urlParams = new URLSearchParams(window.location.search)
const roomId = urlParams.get("room") || "default"

const socket = io("https://clicknposestudio.com:3001", {
  transports: ["websocket"],
  secure: true,
})

socket.on("connect", () => {
  console.log("🟢 Socket connected:", socket.id)
  socket.emit("join-room", { role: "photographer", roomId })
})
</script> -->
