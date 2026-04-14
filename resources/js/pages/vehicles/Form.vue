<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import DatePickerField from '@/components/DatePickerField.vue';
import { index, store, update } from '@/routes/vehicles';
import type { Vehicle } from '@/types';

const props = defineProps<{
    vehicle?: Vehicle;
}>();

const { t } = useI18n();

const isEditing = computed(() => !!props.vehicle);

const form = useForm({
    plate_number: props.vehicle?.plate_number ?? '',
    brand: props.vehicle?.brand ?? '',
    model: props.vehicle?.model ?? '',
    year: props.vehicle?.year ?? new Date().getFullYear(),
    purchase_date: props.vehicle?.purchase_date ?? '',
});

function submit(): void {
    if (isEditing.value) {
        form.put(update.url(props.vehicle!));
    } else {
        form.post(store.url());
    }
}
</script>

<template>
    <Head :title="isEditing ? t('vehicles.edit') : t('vehicles.add')" />

    <v-container>
        <div class="d-flex align-center ga-2 mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="router.visit(index.url())" />
            <h1 class="text-h5">{{ isEditing ? t('vehicles.edit') : t('vehicles.add') }}</h1>
        </div>

        <v-form @submit.prevent="submit">
            <v-text-field
                v-model="form.plate_number"
                :label="t('vehicles.plate')"
                :error-messages="form.errors.plate_number"
                class="mb-2"
                variant="outlined"
            />
            <v-text-field
                v-model="form.brand"
                :label="t('vehicles.brand')"
                :error-messages="form.errors.brand"
                variant="outlined"
                class="mb-2"
            />
            <v-text-field
                v-model="form.model"
                :label="t('vehicles.model')"
                :error-messages="form.errors.model"
                variant="outlined"
                class="mb-2"
            />
            <v-text-field
                v-model.number="form.year"
                :label="t('vehicles.year')"
                :error-messages="form.errors.year"
                type="number"
                :min="1900"
                :max="new Date().getFullYear() + 1"
                variant="outlined"
                class="mb-2"
            />
            <DatePickerField
                v-model="form.purchase_date"
                :label="t('vehicles.purchase_date')"
                :error-messages="form.errors.purchase_date"
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
