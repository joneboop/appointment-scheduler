

<template>
<h1 class="text-2xl text-red-700"> ndskamdno </h1>
<Testlinkbutton/>

<button @click='logpagedata()'>
apni
</button>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import Testlinkbutton from "../Components/Testlinkbutton.vue"
const pagedata = usePage()

function logpagedata() {
    console.log(pagedata.props.paskaa)
}

</script>