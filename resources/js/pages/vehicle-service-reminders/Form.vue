<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { index, store, update } from '@/routes/vehicle-service-reminders';
import type { Vehicle, VehicleServiceReminder, VehicleServiceType } from '@/types';

const props = defineProps<{
    reminder?: VehicleServiceReminder;
    vehicles: Vehicle[];
    serviceTypes: VehicleServiceType[];
}>();

const { t } = useI18n();

const isEditing = computed(() => !!props.reminder);

const form = useForm({
    vehicle_id: props.reminder?.vehicle_id ?? '',
    vehicle_service_type_id: props.reminder?.vehicle_service_type_id ?? '',
    every: props.reminder ? String(props.reminder.every) : '',
});

function submit(): void {
    if (isEditing.value) {
        form.put(update.url({ vehicle_service_reminder: props.reminder!.id }));
    } else {
        form.post(store.url());
    }
}
</script>

<template>
    <Head :title="isEditing ? t('reminders.edit') : t('reminders.add')" />

    <v-container>
        <div class="d-flex align-center ga-2 mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="router.visit(index.url())" />
            <h1 class="text-h5">{{ isEditing ? t('reminders.edit') : t('reminders.add') }}</h1>
        </div>

        <v-form @submit.prevent="submit">
            <v-select
                v-model="form.vehicle_id"
                :label="t('reminders.vehicle')"
                :error-messages="form.errors.vehicle_id"
                :items="vehicles.map(v => ({ title: `${v.brand} ${v.model} (${v.plate_number})`, value: v.id }))"
                item-title="title"
                item-value="value"
                variant="outlined"
                class="mb-2"
            />
            <v-select
                v-model="form.vehicle_service_type_id"
                :label="t('reminders.type')"
                :error-messages="form.errors.vehicle_service_type_id"
                :items="serviceTypes.map(st => ({ title: st.label, value: st.id, icon: st.icon }))"
                item-title="title"
                item-value="value"
                variant="outlined"
                class="mb-2"
            >
                <template #item="{ item, props: itemProps }">
                    <v-list-item v-bind="itemProps">
                        <template #prepend>
                            <v-icon class="mr-2">{{ item.raw?.icon }}</v-icon>
                        </template>
                    </v-list-item>
                </template>
            </v-select>
            <v-text-field
                v-model="form.every"
                :label="t('reminders.every')"
                :error-messages="form.errors.every"
                type="number"
                step="1"
                min="1"
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
