<script setup lang="ts">
import type { Vehicle, VehicleRefuel } from '@/types';
import Form from './Form.vue';

defineProps<{ refuel: VehicleRefuel; vehicles: Vehicle[] }>();
</script>

<template>
    <Form v-bind="$props" />
</template>
