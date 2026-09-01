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
const compare = computed(() => (props.compareRial && props.compareRial > props.rial ? tomanValue(props.compareRial) : null));

const sizeClass = { sm: 'text-base', md: 'text-lg', lg: 'text-2xl' };
</script>

<template>
    <span class="inline-flex flex-col items-start leading-none">
        <s v-if="compare" class="mb-0.5 text-[11px] font-medium text-anar-500/70">{{ compare }}</s>
        <span class="price-tag" :class="sizeClass[size]">
            {{ value }}<span v-if="unit" class="unit">تومان</span>
        </span>
    </span>
</template>
