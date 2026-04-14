<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDateFormat } from '@/composables/useDateFormat';

const props = defineProps<{
    modelValue: string;
    label?: string;
    errorMessages?: string | string[];
    required?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const { t } = useI18n();
const { formatDate } = useDateFormat();

const menu = ref(false);

/** Convert ISO string to Date object for v-date-picker */
const pickerValue = computed<Date | null>(() => {
    if (!props.modelValue) {
return null;
}

    const [y, m, d] = props.modelValue.split('-').map(Number);

    return new Date(y, m - 1, d);
});

/** ISO string for display (formatted per locale) */
const displayValue = computed(() => formatDate(props.modelValue));

function onDateSelected(value: unknown): void {
    if (!(value instanceof Date)) {
return;
}

    const iso = [
        value.getFullYear(),
        String(value.getMonth() + 1).padStart(2, '0'),
        String(value.getDate()).padStart(2, '0'),
    ].join('-');
    emit('update:modelValue', iso);
    menu.value = false;
}
</script>

<template>
    <v-menu
        v-model="menu"
        :close-on-content-click="false"
        location="bottom"
    >
        <template #activator="{ props: menuProps }">
            <v-text-field
                v-bind="menuProps"
                :model-value="displayValue"
                :label="label"
                :error-messages="errorMessages"
                readonly
                variant="outlined"
                prepend-inner-icon="mdi-calendar"
                :clearable="!!modelValue && !required"
                class="mb-2"
                @click:clear.stop="emit('update:modelValue', '')"
            />
        </template>

        <v-card>
            <v-date-picker
                :model-value="pickerValue"
                show-adjacent-months
                @update:model-value="onDateSelected"
                :max="new Date()"
            />
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="menu = false">{{ t('common.cancel') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-menu>
</template>
