<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    import {defineProps} from 'vue';
    defineProps({
        message: String
    });
</script>
<template>
    <AppLayout>
        Category Page
        {{ message }}
    </AppLayout>
</template>
