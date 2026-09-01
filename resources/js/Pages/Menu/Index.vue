<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { faNumber } from '@/lib/format';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    products: { type: Object, required: true },
    filters: { type: Object, required: true },
    activeCategory: { type: Object, default: null },
    noResult: { type: Boolean, default: false },
});

const title = computed(() =>
    props.filters.q
        ? `«${props.filters.q}»`
        : props.activeCategory
          ? props.activeCategory.name
          : 'همه محصولات',
);

function pick(slug) {
    const data = {};
    if (slug) data.category = slug;
    if (props.filters.q) data.q = props.filters.q;
    router.get(route('menu.index'), data, {
        preserveScroll: true,
        preserveState: true,
        only: ['products', 'filters', 'activeCategory', 'noResult'],
    });
}

function goPage(url) {
    if (url) router.get(url, {}, { preserveScroll: true, preserveState: true, only: ['products', 'filters'] });
}
</script>

<template>
    <Head :title="title === 'همه محصولات' ? 'منو' : title" />

    <ShopLayout :search-initial="filters.q" partial-search>
        <div class="flex gap-6">
            <!-- سایدبار دسته‌ها (دسکتاپ) -->
            <aside class="hidden w-52 shrink-0 lg:block">
                <div class="sticky top-[4.5rem] rounded-card border border-kraft-200/70 bg-white p-1.5">
                    <button
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-semibold transition"
                        :class="!filters.category ? 'bg-herb-600 text-white' : 'text-herb-900/70 hover:bg-paper-100'"
                        @click="pick(null)"
                    >
                        همه محصولات
                        <span class="text-xs opacity-60">{{ faNumber(products.total) }}</span>
                    </button>
                    <button
                        v-for="c in categories" :key="c.slug"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition"
                        :class="filters.category === c.slug ? 'bg-herb-600 text-white' : 'text-herb-900/65 hover:bg-paper-100'"
                        @click="pick(c.slug)"
                    >
                        <span class="truncate">{{ c.name }}</span>
                        <span class="text-xs opacity-55">{{ faNumber(c.count) }}</span>
                    </button>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <!-- چیپس دسته‌ها (موبایل/تبلت) -->
                <div class="no-scrollbar -mx-4 mb-4 flex gap-2 overflow-x-auto px-4 lg:hidden">
                    <button
                        class="shrink-0 rounded-full border px-4 py-1.5 text-sm font-semibold transition"
                        :class="!filters.category ? 'border-herb-600 bg-herb-600 text-white' : 'border-kraft-200 bg-white text-herb-900/70'"
                        @click="pick(null)"
                    >
                        همه
                    </button>
                    <button
                        v-for="c in categories" :key="c.slug"
                        class="shrink-0 rounded-full border px-4 py-1.5 text-sm font-medium transition"
                        :class="filters.category === c.slug ? 'border-herb-600 bg-herb-600 text-white' : 'border-kraft-200 bg-white text-herb-900/70'"
                        @click="pick(c.slug)"
                    >
                        {{ c.name }}
                    </button>
                </div>

                <div class="mb-3 flex items-baseline justify-between">
                    <h1 class="text-lg font-extrabold text-herb-900">{{ title }}</h1>
                    <span class="text-xs text-herb-900/45">{{ faNumber(products.total) }} کالا</span>
                </div>

                <!-- بدون نتیجه -->
                <div v-if="noResult" class="rounded-card border border-kraft-200/70 bg-white p-10 text-center">
                    <svg class="mx-auto h-14 w-14 text-herb-200" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M30 8c-8 0-14 6-14 15 0 8 5 13 12 13s12-6 12-14c0-8-5-14-10-14z" /><path d="M21 36c0-9-4-16-13-18 1 10 5 17 13 18z" />
                    </svg>
                    <p class="mt-3 text-sm font-semibold text-herb-900/75">چیزی پیدا نشد</p>
                    <p class="mt-1 text-xs text-herb-900/45">شاید توی این دسته‌ها باشه:</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <button
                            v-for="c in categories.slice(0, 6)" :key="c.slug"
                            class="rounded-full bg-paper-100 px-3 py-1.5 text-xs font-medium text-herb-700"
                            @click="pick(c.slug)"
                        >
                            {{ c.name }}
                        </button>
                    </div>
                </div>

                <!-- گرید محصولات -->
                <div v-else class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                    <ProductCard v-for="p in products.data" :key="p.sku" :product="p" />
                </div>

                <div v-if="products.last_page > 1" class="mt-6 flex flex-wrap justify-center gap-1.5">
                    <button
                        v-for="link in products.links" :key="link.label"
                        :disabled="!link.url"
                        class="min-w-9 rounded-lg border px-3 py-1.5 text-sm font-medium transition disabled:opacity-30"
                        :class="link.active ? 'border-herb-600 bg-herb-600 text-white' : 'border-kraft-200 bg-white text-herb-900/70 hover:bg-paper-50'"
                        v-html="link.label"
                        @click="goPage(link.url)"
                    />
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
