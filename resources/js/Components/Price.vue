<script setup>
import { computed } from 'vue';
import { tomanValue } from '@/lib/format';

const props = defineProps({
    rial: { type: Number, required: true },
    compareRial: { type: Number, default: null },
    unit: { type: Boolean, default: true },
    size: { type: String, default: 'md' }, // sm | md | lg
});

const value = computed(() => tomanValue(props.rial));
const compare = computed(() => (props.compareRial ? tomanValue(props.compareRial) : null));

const sizeClass = {
    sm: 'text-sm',
    md: 'text-[15px]',
    lg: 'text-xl',
};
</script>

<template>
    <span class="inline-flex items-baseline gap-1 font-bold text-brand-800" :class="sizeClass[size]">
        <s v-if="compare" class="text-xs font-medium text-brand-900/40">{{ compare }}</s>
        <span>{{ value }}</span>
        <span v-if="unit" class="text-xs font-medium text-brand-900/55">تومان</span>
    </span>
</template>
