<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  service: Object,
})

const date = ref('')
const loading = ref(false)
const error = ref('')
const slots = ref([])
const selected = ref(null)

// fetch slots whenever date changes
watch(date, async (newDate) => {
  selected.value = null
  slots.value = []
  error.value = ''

  if (!newDate) return

  loading.value = true
  try {
    const url = `/availability?service_id=${props.service.id}&date=${newDate}`
    const res = await fetch(url)

    if (!res.ok) {
      const text = await res.text()
      throw new Error(`Request failed (${res.status}): ${text}`)
    }

    const data = await res.json()
    slots.value = data.slots ?? []
  } catch (e) {
    error.value = e.message || 'Failed to load availability.'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-semibold">Book: {{ service.name }}</h1>
    <p class="text-gray-600 mb-6">{{ service.duration_minutes }} minutes</p>

    <label class="block text-sm font-medium mb-1">Choose date</label>
    <input
      type="date"
      v-model="date"
      class="border rounded-md px-3 py-2 w-64"
    />

    <div class="mt-6">
      <div v-if="loading" class="text-gray-600">Loading slots…</div>
      <div v-else-if="error" class="text-red-600">{{ error }}</div>
      <div v-else-if="date && slots.length === 0" class="text-gray-600">
        No slots available for this date.
      </div>

      <div v-if="slots.length > 0" class="mt-4">
        <div class="text-sm font-medium mb-2">Available times</div>

        <div class="flex flex-wrap gap-2">
          <button
            v-for="slot in slots"
            :key="slot.start"
            @click="selected = slot"
            class="px-3 py-2 border rounded-md text-sm"
            :class="selected?.start === slot.start ? 'bg-black text-white' : ''"
            type="button"
          >
            {{ new Date(slot.start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
          </button>
        </div>

        <div v-if="selected" class="mt-6 p-4 border rounded-lg">
          <div class="font-medium">Selected</div>
          <div class="text-gray-700">
            {{ new Date(selected.start).toLocaleString() }} →
            {{ new Date(selected.end).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
          </div>

          <div class="text-sm text-gray-600 mt-2">
            Next we’ll submit this selection to create an appointment.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
