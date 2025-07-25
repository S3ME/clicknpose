<script setup lang="ts">
import { onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import QrcodeVue from 'qrcode.vue'

// Interface untuk props session
interface PhotoSession {
  session_code: string
  final_image_path: string
}

// Ambil data session dari page props
const page = usePage()
const session = page.props.session as PhotoSession

// Final image URL
const finalImageUrl = `/storage/${session.final_image_path}`

// URL file yang akan di-embed dalam QR code
// const downloadUrl = `${window.location.origin}/storage/${session.final_image_path}`
const fileName = session.final_image_path.split('/').pop() || ''
const downloadUrl = `${window.location.origin}/download/${fileName}`

// Auto print setelah mount
onMounted(() => {
  setTimeout(() => {
    window.print()
  }, 500)
})

// Navigasi ke halaman awal
function goToLandingPage() {
  router.visit('/')
}
</script>

<template>
  <div class="min-h-screen bg-white flex flex-col">
    <!-- Header logo (non-print) -->
    <div class="bg-white py-6 flex justify-center items-center shadow-md no-print">
      <img src="/storage/images/logo.png" alt="Logo" class="w-32 h-auto" />
    </div>

    <!-- Preview dan QR -->
    <div class="flex-grow bg-black text-white flex flex-col items-center justify-center px-4">
      <div class="w-full max-w-6xl flex flex-col md:flex-row justify-between items-center gap-12 md:gap-20 px-6">
        <!-- Foto hasil gabungan -->
        <div class="flex justify-center w-full md:w-1/2">
          <img
            :src="finalImageUrl"
            alt="Hasil Foto"
            class="rounded shadow-md max-h-[480px] w-auto print-only"
          />
        </div>

        <!-- QR dan tombol -->
        <div class="flex flex-col items-center w-full md:w-1/2 text-center no-print max-w-md">
          <p class="text-base font-medium mb-2">Scan QR untuk download</p>
          <QrcodeVue
            :value="downloadUrl"
            :size="160"
            class="mb-4 bg-white p-2 border border-gray-300 rounded"
          />
          <button
            @click="goToLandingPage"
            class="bg-green-600 text-white px-5 py-2 rounded text-sm hover:bg-green-700"
          >
            Kembali ke Halaman Awal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@media print {
  .no-print {
    display: none !important;
  }

  .print-only {
    width: 100vw !important;
    height: auto;
    max-height: 100vh;
    object-fit: contain;
  }

  body, html {
    background: white !important;
    margin: 0 !important;
    padding: 0 !important;
  }
}
</style>
