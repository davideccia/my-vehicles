<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { create, destroy, edit, index } from '@/routes/vehicle-service-reminders';
import { index as servicesIndex } from '@/routes/vehicle-services';
import type { Vehicle, VehicleServiceReminder } from '@/types';

defineProps<{
    reminders: VehicleServiceReminder[];
    vehicles: Vehicle[];
    selectedVehicleId: string | null;
}>();

const { t } = useI18n();
const { formatDate } = useDateFormat();

const showConfirm = ref(false);
const pendingReminder = ref<VehicleServiceReminder | null>(null);

function promptDelete(reminder: VehicleServiceReminder): void {
    pendingReminder.value = reminder;
    showConfirm.value = true;
}

function doDelete(): void {
    if (pendingReminder.value) {
        router.delete(destroy.url({ vehicle_service_reminder: pendingReminder.value.id }));
    }
}

function onVehicleFilter(value: string | null): void {
    router.get(index.url(), { vehicle_id: value || undefined }, { preserveState: true, replace: true });
}
</script>

<template>
    <Head :title="t('reminders.title')" />

    <ConfirmDialog
        v-model="showConfirm"
        :message="t('reminders.delete') + '?'"
        @confirm="doDelete"
    />

    <v-container>
        <div class="d-flex align-center ga-2 mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="router.visit(servicesIndex.url())" />
            <h1 class="text-h5">{{ t('reminders.title') }}</h1>
        </div>

        <v-select
            :label="t('reminders.vehicle')"
            :model-value="selectedVehicleId"
            :items="[{ title: t('reminders.all_vehicles'), value: null }, ...vehicles.map(v => ({ title: `${v.brand} ${v.model} (${v.plate_number})`, value: v.id }))]"
            item-title="title"
            item-value="value"
            variant="outlined"
            class="mb-2"
            clearable
            @update:model-value="onVehicleFilter"
        />

        <v-alert
            v-if="reminders.some(r => r.is_overdue)"
            type="error"
            variant="tonal"
            class="mb-4"
        >
            {{ t('reminders.overdue_alert') }}
        </v-alert>

        <v-btn color="primary" block class="mb-4" prepend-icon="mdi-plus" @click="router.visit(create.url())">
            {{ t('common.add') }}
        </v-btn>

        <v-alert v-if="reminders.length === 0" type="info" variant="tonal">
            {{ t('reminders.no_reminders') }}
        </v-alert>

        <v-card v-for="reminder in reminders" :key="reminder.id" class="mb-3 d-flex" variant="tonal" :color="reminder.is_overdue ? 'error' : undefined" rounded="lg">
            <div class="flex-grow-1" style="min-width: 0;">
                <v-card-title class="d-flex align-center ga-2 text-wrap">
                    <v-icon v-if="reminder.vehicle_service_type?.icon">{{ reminder.vehicle_service_type.icon }}</v-icon>
                    {{ reminder.vehicle_service_type?.label }}
                </v-card-title>
                <v-card-subtitle v-if="reminder.vehicle" class="text-wrap">
                    <div class="text-caption text-white pb-1">({{ t('reminders.every') + ' ' + reminder.every }} km)</div>
                </v-card-subtitle>
                <v-card-subtitle v-if="reminder.vehicle" class="text-white text-wrap">
                    {{ reminder.vehicle.brand }} {{ reminder.vehicle.model }} ({{ reminder.vehicle.plate_number }})
                </v-card-subtitle>
                <v-card-text>
                    <div class="text-caption text-medium-emphasis mb-1"></div>
                    <template v-if="reminder.latest_vehicle_service">
                        <v-row density="comfortable">
                            <v-col cols="6">
                                <div class="text-caption text-medium-emphasis">{{ t('reminders.last_service') }}</div>
                                <div class="font-weight-medium">{{ formatDate(reminder.latest_vehicle_service.date) }}</div>
                            </v-col>
                            <v-col cols="6">
                                <div class="text-caption text-medium-emphasis">{{ t('reminders.total_paid') }}</div>
                                <div class="font-weight-medium">€ {{ reminder.latest_vehicle_service.total_paid }}</div>
                            </v-col>
                            <v-divider/>
                            <v-col cols="6">
                                <div class="text-caption text-medium-emphasis">{{ t('reminders.current_vehicle_odometer') }}</div>
                                <div class="font-weight-medium">{{ reminder.current_vehicle_odometer }} km</div>
                            </v-col>
                            <v-col cols="6">
                                <div class="text-caption text-medium-emphasis">{{ t('reminders.last_vehicle_service_odometer') }}</div>
                                <div class="font-weight-medium">{{ reminder.last_vehicle_service_odometer }} km</div>
                            </v-col>
                            <v-col cols="6">
                                <div class="text-caption text-medium-emphasis">{{ t('reminders.recommended_vehicle_service_odometer') }}</div>
                                <div class="font-weight-medium">{{ reminder.recommended_vehicle_service_odometer }} km</div>
                            </v-col>
                            <v-col cols="6" class="pa-4">
                                <v-chip variant="elevated" label :color="reminder.is_overdue ? 'error' : undefined" size="small">
                                    <div class="font-weight-bold">{{ reminder.overdue_odometer_diff }} km</div>
                                </v-chip>
                            </v-col>
                        </v-row>
                    </template>
                    <div v-else class="text-caption font-italic text-medium-emphasis">{{ t('reminders.no_service_yet') }}</div>
                </v-card-text>
            </div>
            <div class="d-flex flex-column align-center justify-center pa-2 border-s">
                <div class="pb-2">
                    <v-btn icon="mdi-pencil" size="small" variant="outlined" @click="router.visit(edit.url({ vehicle_service_reminder: reminder.id }))" />
                </div>
                <div class="pt-2">
                    <v-btn icon="mdi-delete" size="small" variant="elevated" color="error" @click="promptDelete(reminder)" />
                </div>
            </div>
        </v-card>
    </v-container>
</template>
