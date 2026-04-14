<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DatePickerField from '@/components/DatePickerField.vue';
import { index, store, update } from '@/routes/vehicle-refuels';
import type { Vehicle, VehicleRefuel } from '@/types';

const props = defineProps<{
    refuel?: VehicleRefuel;
    vehicles: Vehicle[];
}>();

const { t } = useI18n();

const isEditing = computed(() => !!props.refuel);

const form = useForm({
    vehicle_id: props.refuel?.vehicle_id ?? '',
    date: props.refuel?.date ?? '',
    total_price: props.refuel ? String(props.refuel.total_price) : '',
    unit_price: props.refuel ? String(props.refuel.unit_price) : '',
    liters: props.refuel ? String(props.refuel.liters) : '',
    odometer: props.refuel ? String(props.refuel.odometer) : '',
});

watch(
    () => [form.liters, form.unit_price],
    ([liters, unitPrice]) => {
        const l = parseFloat(liters);
        const u = parseFloat(unitPrice);

        if (!isNaN(l) && !isNaN(u) && l > 0 && u > 0) {
            form.total_price = (l * u).toFixed(2);
        }
    },
);

function submit(): void {
    if (isEditing.value) {
        form.put(update.url({ vehicle_refuel: props.refuel!.id }));
    } else {
        form.post(store.url());
    }
}
</script>

<template>
    <Head :title="isEditing ? t('refuels.edit') : t('refuels.add')" />

    <v-container>
        <div class="d-flex align-center ga-2 mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="router.visit(index.url())" />
            <h1 class="text-h5">{{ isEditing ? t('refuels.edit') : t('refuels.add') }}</h1>
        </div>

        <v-form @submit.prevent="submit">
            <v-select
                v-model="form.vehicle_id"
                :label="t('refuels.vehicle')"
                :error-messages="form.errors.vehicle_id"
                :items="vehicles.map(v => ({ title: `${v.brand} ${v.model} (${v.plate_number})`, value: v.id }))"
                item-title="title"
                item-value="value"
                variant="outlined"
                class="mb-2"
            />
            <DatePickerField
                v-model="form.date"
                :label="t('refuels.date')"
                :error-messages="form.errors.date"
                required
            />
            <v-text-field
                v-model="form.liters"
                :label="t('refuels.liters')"
                :error-messages="form.errors.liters"
                type="number"
                step="0.01"
                min="0"
                variant="outlined"
                class="mb-2"
            />
            <v-text-field
                v-model="form.unit_price"
                :label="t('refuels.unit_price')"
                :error-messages="form.errors.unit_price"
                type="number"
                step="0.001"
                min="0"
                variant="outlined"
                class="mb-2"
            />
            <v-text-field
                v-model="form.total_price"
                :label="t('refuels.total_price')"
                :error-messages="form.errors.total_price"
                type="number"
                step="0.01"
                min="0"
                variant="outlined"
                class="mb-2"
            />
            <v-text-field
                v-model="form.odometer"
                :label="t('refuels.odometer')"
                :error-messages="form.errors.odometer"
                type="number"
                step="1"
                min="0"
                variant="outlined"
                class="mb-4"
            />

            <div class="d-flex ga-2">
                <v-btn type="submit" color="primary" :loading="form.processing">
                    {{ t('common.save') }}
                </v-btn>
                <v-btn variant="outlined" @click="router.visit(index.url())">
                    {{ t('common.cancel') }}
                </v-btn>
            </div>
        </v-form>
    </v-container>
</template>
