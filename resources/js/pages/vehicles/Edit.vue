<script setup lang="ts">
import type { Vehicle } from '@/types';
import Form from './Form.vue';

defineProps<{ vehicle: Vehicle }>();
</script>

<template>
    <Form v-bind="$props" />
</template>
