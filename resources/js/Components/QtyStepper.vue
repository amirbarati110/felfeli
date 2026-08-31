<script setup>
import { toFaDigits } from '@/lib/format';

const props = defineProps({
    modelValue: { type: Number, required: true },
    min: { type: Number, default: 0 },
    max: { type: Number, default: 99 },
    loading: { type: Boolean, default: false },
    size: { type: String, default: 'md' },
});
const emit = defineEmits(['update:modelValue', 'change']);

function set(v) {
    const next = Math.max(props.min, Math.min(props.max, v));
    emit('update:modelValue', next);
    emit('change', next);
}

const sz = props.size === 'sm' ? 'h-8 text-sm' : 'h-10 text-base';
</script>

<template>
    <div
        class="inline-flex items-center rounded-full bg-brand-500 text-white shadow-sm ring-1 ring-brand-600/20 transition"
        :class="[sz, loading && 'opacity-60']"
    >
        <button
            type="button"
            class="grid aspect-square h-full place-items-center rounded-full text-lg leading-none transition active:scale-90 hover:bg-white/10"
            :disabled="loading"
            aria-label="کاهش"
            @click="set(modelValue - 1)"
        >
            −
        </button>
        <span class="min-w-8 text-center font-bold tabular-nums">{{ toFaDigits(modelValue) }}</span>
        <button
            type="button"
            class="grid aspect-square h-full place-items-center rounded-full text-lg leading-none transition active:scale-90 hover:bg-white/10"
            :disabled="loading || modelValue >= max"
            aria-label="افزایش"
            @click="set(modelValue + 1)"
        >
            +
        </button>
    </div>
</template>
