<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import DatePickerField from '@/components/DatePickerField.vue';
import { index, store, update } from '@/routes/vehicle-services';
import type { Vehicle, VehicleService, VehicleServiceType } from '@/types';

const props = defineProps<{
    service?: VehicleService;
    vehicles: Vehicle[];
    serviceTypes: VehicleServiceType[];
}>();

const { t } = useI18n();

const isEditing = computed(() => !!props.service);

const form = useForm({
    vehicle_id: props.service?.vehicle_id ?? '',
    vehicle_service_type_id: props.service?.vehicle_service_type_id ?? '',
    date: props.service?.date ?? '',
    total_paid: props.service ? String(props.service.total_paid) : '',
    odometer: props.service ? String(props.service.odometer) : '',
    location: props.service?.location ?? '',
    notes: props.service?.notes ?? '',
});

function submit(): void {
    if (isEditing.value) {
        form.put(update.url({ vehicle_service: props.service!.id }));
    } else {
        form.post(store.url());
    }
}
</script>

<template>
    <Head :title="isEditing ? t('services.edit') : t('services.add')" />

    <v-container>
        <div class="d-flex align-center ga-2 mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="router.visit(index.url())" />
            <h1 class="text-h5">{{ isEditing ? t('services.edit') : t('services.add') }}</h1>
        </div>

        <v-form @submit.prevent="submit">
            <v-select
                v-model="form.vehicle_id"
                :label="t('services.vehicle')"
                :error-messages="form.errors.vehicle_id"
                :items="vehicles.map(v => ({ title: `${v.brand} ${v.model} (${v.plate_number})`, value: v.id }))"
                item-title="title"
                item-value="value"
                variant="outlined"
                class="mb-2"
            />
            <v-select
                v-model="form.vehicle_service_type_id"
                :label="t('services.type')"
                :error-messages="form.errors.vehicle_service_type_id"
                :items="serviceTypes.map((st: { label: string; id: string; }) => ({ title: st.label, value: st.id }))"
                item-title="title"
                item-value="value"
                variant="outlined"
                class="mb-2"
            />
            <DatePickerField
                v-model="form.date"
                :label="t('services.date')"
                :error-messages="form.errors.date"
                required
            />
            <v-text-field
                v-model="form.total_paid"
                :label="t('services.total_paid')"
                :error-messages="form.errors.total_paid"
                type="number"
                step="0.01"
                min="0"
                variant="outlined"
                class="mb-2"
            />
            <v-text-field
                v-model="form.odometer"
                :label="t('services.odometer')"
                :error-messages="form.errors.odometer"
                type="number"
                step="1"
                min="0"
                variant="outlined"
                class="mb-2"
            />
            <v-text-field
                v-model="form.location"
                :label="t('services.location')"
                :error-messages="form.errors.location"
                variant="outlined"
                class="mb-2"
            />
            <v-textarea
                v-model="form.notes"
                :label="t('services.notes')"
                :error-messages="form.errors.notes"
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
