<script setup lang="ts">
import type { Vehicle, VehicleServiceReminder, VehicleServiceType } from '@/types';
import Form from './Form.vue';

defineProps<{ reminder: VehicleServiceReminder; vehicles: Vehicle[]; serviceTypes: VehicleServiceType[] }>();
</script>

<template>
    <Form v-bind="$props" />
</template>
