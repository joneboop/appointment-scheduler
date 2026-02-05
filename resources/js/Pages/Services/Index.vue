<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  services: Array,
})
</script>

<template>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">Services</h1>

    <div class="space-y-3">
      <div
        v-for="s in services"
        :key="s.id"
        class="p-4 border rounded-lg flex items-center justify-between"
      >
        <div>
          <div class="font-medium">{{ s.name }}</div>
          <div class="text-sm text-gray-600">{{ s.duration_minutes }} minutes</div>
        </div>

        <Link
          :href="route('booking.create', s.id)"
          class="px-3 py-2 rounded-md bg-black text-white text-sm"
        >
          Book
        </Link>
      </div>

      <div v-if="services.length === 0" class="text-gray-600">
        No services found. Seed one in the database.
      </div>
    </div>
  </div>
</template>
