<script setup>
defineProps({ compact: { type: Boolean, default: false } });
</script>

<template>
    <span class="inline-flex items-center gap-2 select-none">
        <svg viewBox="0 0 48 48" class="h-8 w-8 shrink-0" aria-hidden="true">
            <path
                d="M31 8c-9 0-16 7-16 18 0 9 6 15 14 15s14-7 14-16c0-3-.6-6-1.8-8.4C39 12 35 8 31 8z"
                fill="#2f8f4a"
            />
            <path d="M24 41c0-11-5-19-15-22 1 12 6 20 15 22z" fill="#7cc487" />
            <path d="M30 14c3-1 6 .5 8 4-4 1-7-.4-8-4z" fill="#ffd43b" />
        </svg>
        <span v-if="!compact" class="text-lg font-extrabold text-brand-700">
            فلفلی <span class="text-brand-500">ساوه</span>
        </span>
    </span>
</template>
