<script setup lang="ts">

import { router } from '@inertiajs/vue3';
import type { ChartOptions } from 'chart.js';
import {
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip
} from 'chart.js';
import { ref, watch } from 'vue';
import { Line } from 'vue-chartjs';
import { useI18n } from 'vue-i18n';
import DatePickerField from '@/components/DatePickerField.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { show as reports } from '@/routes/reports';
import type { Vehicle } from '@/types/models';
import type { FuelCosts } from '@/types/reports';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
)

const { t } = useI18n();
const { formatDate } = useDateFormat();

const props = defineProps<{
    fuel_costs: FuelCosts;
    vehicles: Vehicle[];
    filters: {
        vehicle_id: string | null;
        from: string;
        to: string;
    };
}>();

const vehicleId = ref(props.filters.vehicle_id);
const from = ref(props.filters.from);
const to = ref(props.filters.to);

const options: ChartOptions<'line'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
        },
    },
    scales: {
        x: {
            display: true,
            grid: {
                display: true,
            },
        },
        y: {
            display: true,
            beginAtZero: true,
            grid: {
                display: true,
            },
        },
    },
};

watch([vehicleId, from, to], () => {
    router.get(reports.url(), {
        vehicle_id: vehicleId.value,
        from: from.value,
        to: to.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
});
</script>

<template>
    <div class="pa-4">
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-select
                            v-model="vehicleId"
                            :items="[
                                { id: null, plate_number: t('reports.all_vehicles') },
                                ...vehicles
                            ]"
                            item-title="plate_number"
                            item-value="id"
                            :label="t('reports.vehicle')"
                            density="comfortable"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DatePickerField
                            v-model="from"
                            :label="t('reports.from')"
                            type="date"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DatePickerField
                            v-model="to"
                            :label="t('reports.to')"
                            type="date"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <v-card>
            <v-card-title class="my-4"><div class="bg-primary pa-3 text-center rounded">{{t('reports.fuel_costs.title')}}</div></v-card-title>
            <v-card-text style="height: 400px;">
                <Line :data="props.fuel_costs" :options="options" />
            </v-card-text>
        </v-card>
    </div>
</template>
