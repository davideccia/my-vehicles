<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import allIcons from '@/data/mdi-icons.json';

defineProps<{
    modelValue: string;
    label?: string;
    errorMessages?: string | string[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const { t } = useI18n();

const dialog = ref(false);
const search = ref('');

const ITEMS_PER_ROW = 5;
const ITEM_HEIGHT = 72;

const filteredIcons = computed(() => {
    const q = search.value.trim().toLowerCase();

    if (!q) {
return allIcons as string[];
}

    return (allIcons as string[]).filter((icon) => icon.includes(q));
});

const rows = computed(() => {
    const icons = filteredIcons.value;
    const result: string[][] = [];

    for (let i = 0; i < icons.length; i += ITEMS_PER_ROW) {
        result.push(icons.slice(i, i + ITEMS_PER_ROW));
    }

    return result;
});

function selectIcon(icon: string): void {
    emit('update:modelValue', icon);
    dialog.value = false;
    search.value = '';
}

function openDialog(): void {
    search.value = '';
    dialog.value = true;
}
</script>

<template>
    <div class="mb-2">
        <v-text-field
            :model-value="modelValue"
            :label="label"
            :error-messages="errorMessages"
            readonly
            variant="outlined"
            @click="openDialog"
        >
            <template v-if="modelValue" #prepend-inner>
                <v-icon class="mr-1">{{ modelValue }}</v-icon>
            </template>
            <template #append-inner>
                <v-icon>mdi-chevron-down</v-icon>
            </template>
        </v-text-field>

        <v-dialog v-model="dialog" max-width="480" scrollable>
            <v-card>
                <v-card-title>{{ label ?? t('service_types.icon') }}</v-card-title>

                <v-divider />

                <v-card-text style="padding: 0;">
                    <div class="pa-3 pb-0">
                        <v-text-field
                            v-model="search"
                            :placeholder="t('common.search')"
                            prepend-inner-icon="mdi-magnify"
                            variant="outlined"
                            density="compact"
                            hide-details
                            autofocus
                            clearable
                        />
                    </div>

                    <div class="pa-2 text-caption text-medium-emphasis">
                        {{ filteredIcons.length }} {{ t('service_types.icons_found') }}
                    </div>

                    <v-virtual-scroll
                        :items="rows"
                        :item-height="ITEM_HEIGHT"
                        height="360"
                    >
                        <template #default="{ item: row }">
                            <div class="d-flex">
                                <v-btn
                                    v-for="icon in row"
                                    :key="icon"
                                    :title="icon"
                                    :color="modelValue === icon ? 'primary' : undefined"
                                    :variant="modelValue === icon ? 'tonal' : 'text'"
                                    icon
                                    size="56"
                                    style="flex: 1; max-width: 20%;"
                                    @click="selectIcon(icon)"
                                >
                                    <v-icon size="24">{{ icon }}</v-icon>
                                </v-btn>
                            </div>
                        </template>
                    </v-virtual-scroll>
                </v-card-text>

                <v-divider />

                <v-card-actions>
                    <v-btn variant="text" @click="dialog = false">
                        {{ t('common.cancel') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
