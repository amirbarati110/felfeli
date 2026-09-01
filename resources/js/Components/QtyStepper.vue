<script setup>
import { toFaDigits } from '@/lib/format';

const props = defineProps({
    modelValue: { type: Number, required: true },
    min: { type: Number, default: 0 },
    max: { type: Number, default: 99 },
    size: { type: String, default: 'md' }, // sm | md
});
const emit = defineEmits(['update:modelValue', 'change']);

function set(v) {
    const next = Math.max(props.min, Math.min(props.max, v));
    emit('update:modelValue', next);
    emit('change', next);
}

const h = props.size === 'sm' ? 'h-9' : 'h-10';
</script>

<template>
    <div
        class="inline-flex select-none items-stretch overflow-hidden rounded-full bg-herb-600 text-white shadow-sm"
        :class="h"
    >
        <button
            type="button" aria-label="کاهش"
            class="grid aspect-square place-items-center text-xl leading-none transition hover:bg-white/15 active:scale-90"
            @click="set(modelValue - 1)"
        >
            <span class="-mt-0.5">−</span>
        </button>
        <span class="grid min-w-8 place-items-center px-1 text-sm font-extrabold tabular-nums">
            {{ toFaDigits(modelValue) }}
        </span>
        <button
            type="button" aria-label="افزایش" :disabled="modelValue >= max"
            class="grid aspect-square place-items-center text-xl leading-none transition hover:bg-white/15 active:scale-90 disabled:opacity-40"
            @click="set(modelValue + 1)"
        >
            <span class="-mt-0.5">+</span>
        </button>
    </div>
</template>
