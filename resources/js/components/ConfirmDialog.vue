<script setup lang="ts">
import { useI18n } from 'vue-i18n';

defineProps<{
    modelValue: boolean;
    message: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    confirm: [];
}>();

const { t } = useI18n();

function cancel(): void {
    emit('update:modelValue', false);
}

function confirm(): void {
    emit('confirm');
    emit('update:modelValue', false);
}
</script>

<template>
    <v-dialog :model-value="modelValue" max-width="320" @update:model-value="$emit('update:modelValue', $event)">
        <v-card>
            <v-card-text class="pt-4">{{ message }}</v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="cancel">{{ t('common.cancel') }}</v-btn>
                <v-btn color="error" variant="tonal" @click="confirm">{{ t('common.delete') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
