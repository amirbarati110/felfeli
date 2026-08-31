<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    initial: { type: String, default: '' },
    partial: { type: Boolean, default: false }, // در صفحه‌ی منو: partial reload
});

const term = ref(props.initial);
let timer = null;

watch(
    () => props.initial,
    (v) => {
        if (v !== term.value) term.value = v;
    },
);

watch(term, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => submit(value), 350);
});

function submit(value) {
    const data = value.trim() ? { q: value.trim() } : {};
    router.get(route('menu.index'), data, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: props.partial ? ['products', 'filters', 'noResult', 'activeCategory'] : [],
    });
}
</script>

<template>
    <form class="relative w-full" role="search" @submit.prevent="submit(term)">
        <svg
            class="pointer-events-none absolute end-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-brand-900/35"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        >
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
        </svg>
        <input
            v-model="term"
            type="search"
            enterkeyhint="search"
            placeholder="چی لازم داری؟"
            class="h-12 w-full rounded-full border-0 bg-white ps-4 pe-11 text-sm text-brand-900 shadow-sm ring-1 ring-black/5 outline-none transition placeholder:text-brand-900/35 focus:ring-2 focus:ring-brand-400"
        />
        <button
            v-if="term"
            type="button"
            class="absolute start-3 top-1/2 -translate-y-1/2 rounded-full p-1 text-brand-900/40 hover:text-brand-900/70"
            aria-label="پاک کردن"
            @click="term = ''"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </form>
</template>
