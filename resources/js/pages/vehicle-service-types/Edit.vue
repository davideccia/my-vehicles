<script setup lang="ts">
import type { VehicleServiceType } from '@/types';
import Form from './Form.vue';

defineProps<{ serviceType: VehicleServiceType }>();
</script>

<template>
    <Form v-bind="$props" />
</template>
