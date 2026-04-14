<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import IconPickerField from '@/components/IconPickerField.vue';
import { index, store, update } from '@/routes/vehicle-service-types';
import type { VehicleServiceType } from '@/types';

const props = defineProps<{
    serviceType?: VehicleServiceType;
}>();

const { t } = useI18n();

const isEditing = computed(() => !!props.serviceType);

const form = useForm({
    icon: props.serviceType?.icon ?? '',
    label: props.serviceType?.label ?? '',
});

function submit(): void {
    if (isEditing.value) {
        form.put(update.url({ vehicle_service_type: props.serviceType!.id }));
    } else {
        form.post(store.url());
    }
}
</script>

<template>
    <Head :title="isEditing ? t('service_types.edit') : t('service_types.add')" />

    <v-container>
        <div class="d-flex align-center ga-2 mb-4">
            <v-btn icon="mdi-arrow-left" variant="text" @click="router.visit(index.url())" />
            <h1 class="text-h5">{{ isEditing ? t('service_types.edit') : t('service_types.add') }}</h1>
        </div>

        <v-form @submit.prevent="submit">
            <v-text-field
                v-model="form.label"
                :label="t('service_types.label')"
                :error-messages="form.errors.label"
                variant="outlined"
                class="mb-2"
            />
            <IconPickerField
                v-model="form.icon"
                :label="t('service_types.icon')"
                :error-messages="form.errors.icon"
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
