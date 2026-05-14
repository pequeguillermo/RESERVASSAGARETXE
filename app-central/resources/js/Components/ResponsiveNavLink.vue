<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-3 border-l-4 border-white text-start text-base font-extrabold text-white bg-blue-800 focus:outline-none focus:text-white focus:bg-blue-700 focus:border-white transition duration-150 ease-in-out shadow-sm'
        : 'block w-full ps-3 pe-4 py-3 border-l-4 border-transparent text-start text-base font-medium text-blue-100 hover:text-white hover:bg-blue-800/50 hover:border-blue-300 focus:outline-none focus:text-white focus:bg-blue-800/50 focus:border-blue-300 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
