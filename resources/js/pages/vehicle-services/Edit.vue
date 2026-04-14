<script setup lang="ts">
import type { Vehicle, VehicleService, VehicleServiceType } from '@/types';
import Form from './Form.vue';

defineProps<{ service: VehicleService; vehicles: Vehicle[]; serviceTypes: VehicleServiceType[] }>();
</script>

<template>
    <Form v-bind="$props" />
</template>
