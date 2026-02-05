<script setup>
import { ref, watch, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
  service: Object,
})


const page = usePage()

const date = ref('')
const loading = ref(false)
const error = ref('')
const slots = ref([])
const selected = ref(null)

const form = useForm({
  service_id: props.service.id,
  customer_name: '',
  customer_email: '',
  starts_at: '',
  ends_at: '',
})

const successMessage = computed(() => page.props.flash?.success)

// fetch slots whenever date changes
watch(date, async () => {
  await fetchSlots()

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

function reserve() {
  if (!selected.value) return

  form.post('/appointments', {
    preserveScroll: true,
    onSuccess: async () => {
      // Clear selection and refresh availability so the booked slot disappears
      selected.value = null
      form.starts_at = ''
      form.ends_at = ''
      await fetchSlots()
    },
  })
}

async function fetchSlots() {
  selected.value = null
  slots.value = []
  error.value = ''

  if (!date.value) return

  loading.value = true
  try {
    const url = `/availability?service_id=${props.service.id}&date=${date.value}`
    const res = await fetch(url)
    if (!res.ok) throw new Error(`Request failed (${res.status})`)
    const data = await res.json()
    slots.value = data.slots ?? []
  } catch (e) {
    error.value = e.message || 'Failed to load availability.'
  } finally {
    loading.value = false
  }
}

function selectSlot(slot) {
  selected.value = slot
  form.starts_at = slot.start
  form.ends_at = slot.end
}

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
  @click="selectSlot(slot)"
  class="px-3 py-2 border rounded-md text-sm"
  :class="selected && selected.start === slot.start ? 'bg-black text-white' : ''"
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

<div class="mt-4">
  <div class="font-medium mb-3">Your details</div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
      <label class="block text-sm font-medium mb-1">Name</label>
      <input v-model="form.customer_name" class="border rounded-md px-3 py-2 w-full" />
      <div v-if="form.errors.customer_name" class="text-sm text-red-600 mt-1">
        {{ form.errors.customer_name }}
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Email</label>
      <input v-model="form.customer_email" class="border rounded-md px-3 py-2 w-full" />
      <div v-if="form.errors.customer_email" class="text-sm text-red-600 mt-1">
        {{ form.errors.customer_email }}
      </div>
    </div>
  </div>

  <div v-if="form.errors.starts_at" class="text-sm text-red-600 mt-2">
    {{ form.errors.starts_at }}
  </div>

  <button
    type="button"
    class="mt-4 px-4 py-2 rounded-md bg-black text-white disabled:opacity-50"
    :disabled="form.processing"
    @click="reserve"
  >
    {{ form.processing ? 'Booking…' : 'Reserve slot' }}
  </button>
</div>
<div v-if="successMessage" class="mt-4 p-3 border rounded-md">
  {{ successMessage }}
</div>
        </div>
      </div>
    </div>
  </div>
</template>
