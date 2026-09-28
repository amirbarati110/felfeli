<script setup>
import { computed, ref } from 'vue';
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
const categoryOpen = ref(false);

function pick(slug) {
    categoryOpen.value = false;
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
                <!-- انتخاب دسته در موبایل، بدون اسکرول افقیِ بلند -->
                <section class="mb-5 lg:hidden" aria-label="دسته‌بندی محصولات">
                    <button
                        type="button"
                        class="flex min-h-12 w-full items-center justify-between gap-3 rounded-xl border border-kraft-200 bg-paper-50 px-4 text-right text-sm font-bold text-herb-900 transition active:bg-paper-100"
                        :aria-expanded="categoryOpen"
                        aria-controls="mobile-categories"
                        @click="categoryOpen = !categoryOpen"
                    >
                        <span class="min-w-0 truncate">دسته‌بندی: {{ activeCategory?.name || 'همه محصولات' }}</span>
                        <svg class="h-5 w-5 shrink-0 transition-transform" :class="categoryOpen && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                    </button>
                    <div v-if="categoryOpen" id="mobile-categories" class="mt-2 grid grid-cols-2 gap-2 rounded-xl border border-kraft-200 bg-white p-2">
                        <button type="button" class="min-h-11 rounded-lg px-3 text-right text-sm font-semibold" :class="!filters.category ? 'bg-herb-700 text-white' : 'bg-paper-50 text-herb-800'" :aria-pressed="!filters.category" @click="pick(null)">همه محصولات</button>
                        <button v-for="c in categories" :key="c.slug" type="button" class="min-h-11 rounded-lg px-3 text-right text-sm font-semibold" :class="filters.category === c.slug ? 'bg-herb-700 text-white' : 'bg-paper-50 text-herb-800'" :aria-pressed="filters.category === c.slug" @click="pick(c.slug)">{{ c.name }}</button>
                    </div>
                </section>

                <div class="mb-3 flex items-baseline justify-between">
                    <h1 class="text-lg font-extrabold text-herb-900">{{ title }}</h1>
                    <span class="text-xs text-herb-900/65">{{ faNumber(products.total) }} کالا</span>
                </div>

                <!-- بدون نتیجه -->
                <div v-if="noResult" class="rounded-card border border-kraft-200/70 bg-white p-10 text-center">
                    <svg class="mx-auto h-14 w-14 text-herb-200" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M30 8c-8 0-14 6-14 15 0 8 5 13 12 13s12-6 12-14c0-8-5-14-10-14z" /><path d="M21 36c0-9-4-16-13-18 1 10 5 17 13 18z" />
                    </svg>
                    <p class="mt-3 text-sm font-semibold text-herb-900/75">چیزی پیدا نشد</p>
                    <p class="mt-1 text-xs text-herb-900/65">شاید توی این دسته‌ها باشه:</p>
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
                <div v-else class="grid grid-cols-1 gap-3 min-[360px]:grid-cols-2 sm:grid-cols-3 xl:grid-cols-4">
                    <ProductCard v-for="p in products.data" :key="p.sku" :product="p" />
                </div>

                <div v-if="products.last_page > 1" class="mt-6 flex items-center justify-between gap-2 sm:hidden" aria-label="صفحه‌های محصولات">
                    <button type="button" :disabled="!products.prev_page_url" class="min-h-11 rounded-xl border border-kraft-200 bg-white px-4 text-sm font-bold text-herb-800 disabled:opacity-40" @click="goPage(products.prev_page_url)">قبلی</button>
                    <span class="text-sm font-semibold text-herb-800">{{ faNumber(products.current_page) }} از {{ faNumber(products.last_page) }}</span>
                    <button type="button" :disabled="!products.next_page_url" class="min-h-11 rounded-xl border border-kraft-200 bg-white px-4 text-sm font-bold text-herb-800 disabled:opacity-40" @click="goPage(products.next_page_url)">بعدی</button>
                </div>
                <div v-if="products.last_page > 1" class="mt-6 hidden flex-wrap justify-center gap-1.5 sm:flex">
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
