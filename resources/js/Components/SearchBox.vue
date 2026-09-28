<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { toman } from '@/lib/format';

const props = defineProps({
    initial: { type: String, default: '' },
    partial: { type: Boolean, default: false },
});

const term = ref(props.initial);
const suggestions = ref([]);
const open = ref(false);
const loading = ref(false);
const activeIndex = ref(-1);
let searchTimer = null;
let suggestionTimer = null;
let request = null;
let suppressNextWatch = false;

watch(
    () => props.initial,
    (v) => {
        if (v !== term.value) term.value = v;
    },
);

watch(term, (value) => {
    if (suppressNextWatch) {
        suppressNextWatch = false;
        return;
    }

    clearTimeout(searchTimer);
    clearTimeout(suggestionTimer);
    request?.abort();

    const query = value.trim();
    activeIndex.value = -1;

    if (query.length < 2) {
        suggestions.value = [];
        loading.value = false;
        open.value = false;
        if (!query) searchTimer = setTimeout(() => submit(''), 320);
        return;
    }

    open.value = true;
    loading.value = true;
    suggestionTimer = setTimeout(() => loadSuggestions(query), 180);
    searchTimer = setTimeout(() => submit(query), 420);
});

async function loadSuggestions(query) {
    request = new AbortController();

    try {
        const url = new URL(route('menu.suggestions'), window.location.origin);
        url.searchParams.set('q', query);
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            signal: request.signal,
        });
        if (!response.ok) throw new Error('Search failed');

        const payload = await response.json();
        if (term.value.trim() === query) {
            suggestions.value = payload.data ?? [];
            open.value = true;
        }
    } catch (error) {
        if (error.name !== 'AbortError') suggestions.value = [];
    } finally {
        if (term.value.trim() === query) loading.value = false;
    }
}

function submit(value) {
    const data = value.trim() ? { q: value.trim() } : {};
    router.get(route('menu.index'), data, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: props.partial ? ['products', 'filters', 'noResult', 'activeCategory'] : [],
    });
}

function choose(product) {
    clearTimeout(searchTimer);
    clearTimeout(suggestionTimer);
    request?.abort();
    suppressNextWatch = true;
    term.value = product.name;
    open.value = false;
    router.get(route('menu.index'), { q: product.name }, { preserveState: true, replace: true });
}

function submitFromForm() {
    open.value = false;
    submit(term.value);
}

function onKeydown(event) {
    if (!open.value || !suggestions.value.length) return;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        activeIndex.value = (activeIndex.value + 1) % suggestions.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        activeIndex.value = (activeIndex.value - 1 + suggestions.value.length) % suggestions.value.length;
    } else if (event.key === 'Enter' && activeIndex.value >= 0) {
        event.preventDefault();
        choose(suggestions.value[activeIndex.value]);
    } else if (event.key === 'Escape') {
        open.value = false;
    }
}

function closeAfterFocusLeaves() {
    window.setTimeout(() => (open.value = false), 120);
}

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
    clearTimeout(suggestionTimer);
    request?.abort();
});
</script>

<template>
    <form class="relative w-full" role="search" @submit.prevent="submitFromForm" @focusout="closeAfterFocusLeaves">
        <svg
            class="pointer-events-none absolute end-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-herb-900/35"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        >
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
        </svg>
        <label for="shop-search" class="sr-only">جستجو در محصولات</label>
        <input
            id="shop-search"
            v-model="term"
            type="search"
            enterkeyhint="search"
            placeholder="چی لازم داری؟"
            role="combobox"
            autocomplete="off"
            aria-autocomplete="list"
            aria-controls="shop-search-results"
            :aria-expanded="open"
            :aria-activedescendant="activeIndex >= 0 ? `shop-search-result-${activeIndex}` : undefined"
            class="h-12 w-full rounded-full border border-kraft-200 bg-white pe-11 text-base text-herb-900 shadow-sm outline-none transition placeholder:text-herb-900/40 focus:border-herb-400 focus:ring-2 focus:ring-herb-300/50 sm:h-11 sm:text-sm"
            :class="term ? 'ps-11' : 'ps-4'"
            @focus="term.trim().length >= 2 && (open = true)"
            @keydown="onKeydown"
        />
        <button
            v-if="term"
            type="button"
            class="absolute start-1.5 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full text-herb-900/60 transition hover:bg-herb-50 hover:text-herb-900"
            aria-label="پاک کردن"
            @click="term = ''"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18" /></svg>
        </button>

        <div
            v-if="open && term.trim().length >= 2"
            id="shop-search-results"
            role="listbox"
            class="absolute inset-x-0 top-[calc(100%+0.5rem)] z-50 max-h-[min(26rem,65vh)] overflow-y-auto rounded-2xl border border-kraft-200 bg-white p-2 shadow-2xl shadow-herb-900/15"
            :aria-busy="loading"
        >
            <p v-if="loading" class="px-3 py-4 text-center text-sm text-herb-900/55">در حال جست‌وجو…</p>
            <template v-else-if="suggestions.length">
                <button
                    v-for="(product, index) in suggestions"
                    :id="`shop-search-result-${index}`"
                    :key="product.sku"
                    type="button"
                    role="option"
                    :aria-selected="activeIndex === index"
                    class="flex min-h-14 w-full items-center gap-3 rounded-xl p-2 text-right transition hover:bg-herb-50 focus:bg-herb-50 focus:outline-none"
                    :class="activeIndex === index && 'bg-herb-50'"
                    @mouseenter="activeIndex = index"
                    @mousedown.prevent="choose(product)"
                >
                    <img
                        :src="product.image_url"
                        alt=""
                        class="h-11 w-11 shrink-0 rounded-lg bg-paper-100 object-cover"
                        @error="(event) => (event.target.src = '/images/product-placeholder.svg')"
                    />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-bold text-herb-900">{{ product.name }}</span>
                        <span class="mt-0.5 block text-xs font-semibold text-herb-700">{{ toman(product.price) }}</span>
                    </span>
                </button>
                <button
                    type="submit"
                    class="mt-1 min-h-11 w-full rounded-xl bg-herb-700 px-4 text-sm font-bold text-white transition hover:bg-herb-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-herb-600"
                >
                    نمایش همه نتایج «{{ term.trim() }}»
                </button>
            </template>
            <p v-else class="px-3 py-4 text-center text-sm text-herb-900/55">کالایی با این عبارت پیدا نشد.</p>
        </div>
    </form>
</template>
