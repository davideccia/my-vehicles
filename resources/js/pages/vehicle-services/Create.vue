<script setup lang="ts">
import type { Vehicle, VehicleServiceType } from '@/types';
import Form from './Form.vue';

defineProps<{ vehicles: Vehicle[]; serviceTypes: VehicleServiceType[] }>();
</script>

<template>
    <Form v-bind="$props" />
</template>
