<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue';
    defineProps({
        message: String
    });
</script>
<template>
    <AppLayout>
        {{ message }}
    </AppLayout>
</template>
