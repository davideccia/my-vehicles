<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { destroy, edit, index } from '@/routes/vehicles';
import type { Vehicle } from '@/types';

const props = defineProps<{
    vehicle: Vehicle;
}>();

const { t } = useI18n();
const { formatDate } = useDateFormat();

const showConfirm = ref(false);

function doDelete(): void {
    router.delete(destroy.url(props.vehicle));
}
</script>

<template>
    <Head :title="vehicle.plate_number" />

    <ConfirmDialog
        v-model="showConfirm"
        :message="t('vehicles.delete') + ' ' + vehicle.plate_number + '?'"
        @confirm="doDelete"
    />

    <v-container>
        <div class="d-flex align-center ga-2 mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="router.visit(index.url())" />
            <h1 class="text-h5 text-uppercase">{{ vehicle.plate_number }}</h1>
        </div>

        <v-card class="mb-4">
            <v-list>
                <v-list-item :subtitle="t('vehicles.brand')" :title="vehicle.brand" />
                <v-divider />
                <v-list-item :subtitle="t('vehicles.model')" :title="vehicle.model" />
                <v-divider />
                <v-list-item :subtitle="t('vehicles.year')" :title="String(vehicle.year)" />
                <template v-if="vehicle.purchase_date">
                    <v-divider />
                    <v-list-item :subtitle="t('vehicles.purchase_date')" :title="formatDate(vehicle.purchase_date)" />
                </template>
            </v-list>
        </v-card>

        <div class="d-flex ga-2">
            <v-btn variant="outlined" @click="router.visit(edit.url(vehicle))">
                {{ t('vehicles.edit') }}
            </v-btn>
            <v-btn color="error" variant="outlined" @click="showConfirm = true">
                {{ t('vehicles.delete') }}
            </v-btn>
        </div>
    </v-container>
</template>
