<template>


<Link :href='pagelink'>
<div class= "bg-green-500">
npai
</div>
</Link>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
const pagedata=usePage()

function logpagedata() {
    console.log(pagedata.component)
}

const pagelink = computed(() => {
    let page = pagedata.component
    if (page == 'Test') {
        return '/'
    }

    else {
        return '/paskaa'
    }
})
</script>