<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
        ? `جستجو: ${props.filters.q}`
        : props.activeCategory
          ? props.activeCategory.name
          : 'منوی محصولات',
);

function pickCategory(slug) {
    const data = {};
    if (slug) data.category = slug;
    if (props.filters.q) data.q = props.filters.q;
    router.get(route('menu.index'), data, {
        preserveScroll: true,
        preserveState: true,
        only: ['products', 'filters', 'activeCategory', 'noResult'],
    });
}

function goToPage(url) {
    if (url) router.get(url, {}, { preserveScroll: true, preserveState: true, only: ['products', 'filters'] });
}
</script>

<template>
    <Head :title="title" />

    <ShopLayout :search-initial="filters.q" partial-search>
        <div class="flex gap-6">
            <!-- سایدبار دسته‌ها (دسکتاپ) -->
            <aside class="hidden w-56 shrink-0 lg:block">
                <div class="sticky top-20 rounded-2xl bg-white p-2 ring-1 ring-black/5">
                    <p class="px-3 py-2 text-xs font-bold text-brand-900/50">دسته‌بندی‌ها</p>
                    <button
                        class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition"
                        :class="!filters.category ? 'bg-brand-50 text-brand-700' : 'text-brand-900/70 hover:bg-cream-100'"
                        @click="pickCategory(null)"
                    >
                        همه محصولات
                    </button>
                    <button
                        v-for="c in categories" :key="c.slug"
                        class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-medium transition"
                        :class="filters.category === c.slug ? 'bg-brand-50 text-brand-700' : 'text-brand-900/70 hover:bg-cream-100'"
                        @click="pickCategory(c.slug)"
                    >
                        <span>{{ c.name }}</span>
                        <span class="text-xs text-brand-900/35">{{ faNumber(c.count) }}</span>
                    </button>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <!-- چیپس دسته‌ها (موبایل/تبلت) -->
                <div class="no-scrollbar -mx-4 mb-4 flex gap-2 overflow-x-auto px-4 lg:hidden">
                    <button
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition"
                        :class="!filters.category ? 'bg-brand-500 text-white' : 'bg-white text-brand-900/70 ring-1 ring-black/5'"
                        @click="pickCategory(null)"
                    >
                        همه
                    </button>
                    <button
                        v-for="c in categories" :key="c.slug"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-medium transition"
                        :class="filters.category === c.slug ? 'bg-brand-500 text-white' : 'bg-white text-brand-900/70 ring-1 ring-black/5'"
                        @click="pickCategory(c.slug)"
                    >
                        {{ c.name }}
                    </button>
                </div>

                <div class="mb-3 flex items-baseline justify-between">
                    <h1 class="text-lg font-extrabold text-brand-900">{{ title }}</h1>
                    <span class="text-xs text-brand-900/45">{{ faNumber(products.total) }} کالا</span>
                </div>

                <!-- بدون نتیجه -->
                <div v-if="noResult" class="rounded-2xl bg-white p-8 text-center ring-1 ring-black/5">
                    <p class="text-sm font-medium text-brand-900/70">چیزی پیدا نشد 🤔</p>
                    <p class="mt-1 text-xs text-brand-900/45">شاید بین دسته‌ها باشد:</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <button
                            v-for="c in categories.slice(0, 6)" :key="c.slug"
                            class="rounded-full bg-cream-100 px-3 py-1.5 text-xs font-medium text-brand-700"
                            @click="pickCategory(c.slug)"
                        >
                            {{ c.name }}
                        </button>
                    </div>
                </div>

                <!-- گرید محصولات -->
                <div v-else class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                    <ProductCard v-for="p in products.data" :key="p.sku" :product="p" />
                </div>

                <!-- صفحه‌بندی -->
                <div v-if="products.last_page > 1" class="mt-6 flex flex-wrap justify-center gap-1.5">
                    <button
                        v-for="link in products.links" :key="link.label"
                        :disabled="!link.url"
                        class="min-w-9 rounded-lg px-3 py-1.5 text-sm font-medium transition disabled:opacity-30"
                        :class="link.active ? 'bg-brand-500 text-white' : 'bg-white text-brand-900/70 ring-1 ring-black/5 hover:bg-cream-50'"
                        v-html="link.label"
                        @click="goToPage(link.url)"
                    />
                </div>
            </div>
        </div>
    </ShopLayout>
</template>
