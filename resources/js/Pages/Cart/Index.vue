<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import QtyStepper from '@/Components/QtyStepper.vue';
import { useCart } from '@/lib/cart';
import { tomanValue } from '@/lib/format';

const props = defineProps({
    items: { type: Array, default: () => [] },
    subtotal: { type: Number, default: 0 },
    removed: { type: Array, default: () => [] },
    adjusted: { type: Array, default: () => [] },
});

const cart = useCart(['items', 'subtotal', 'removed', 'adjusted']);
const isEmpty = computed(() => props.items.length === 0);
</script>

<template>
    <Head title="سبد خرید" />

    <ShopLayout :show-search="false">
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-4 text-xl font-extrabold text-herb-900">سبد خرید</h1>

            <div v-if="removed.length" class="mb-3 rounded-xl bg-anar-500/10 px-4 py-3 text-sm text-anar-600">
                این کالاها ناموجود شدند و از سبد حذف شدند: {{ removed.join('، ') }}
            </div>
            <div v-if="adjusted.length" class="mb-3 rounded-xl bg-zaffron-300/25 px-4 py-3 text-sm text-herb-800">
                تعداد این کالاها بر اساس موجودی اصلاح شد: {{ adjusted.join('، ') }}
            </div>

            <div v-if="isEmpty" class="rounded-card border border-kraft-200/70 bg-white p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-herb-200" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 14h28l-3 20a4 4 0 0 1-4 3H17a4 4 0 0 1-4-3z" /><path d="M18 20v10M30 20v10M10 14 8 7H3" />
                </svg>
                <p class="mt-4 text-sm font-medium text-herb-900/65">سبد خرید خالی است.</p>
                <Link :href="route('menu.index')" class="mt-4 inline-block rounded-full bg-herb-600 px-5 py-2.5 text-sm font-bold text-white">
                    رفتن به منو
                </Link>
            </div>

            <template v-else>
                <ul class="space-y-2">
                    <li
                        v-for="item in items" :key="item.sku"
                        class="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-3 rounded-card border border-kraft-200/70 bg-white p-3 sm:grid-cols-[4.5rem_minmax(0,1fr)_auto] sm:items-center"
                    >
                        <img
                            :src="item.image_url" :alt="item.name"
                            class="h-[4.5rem] w-[4.5rem] rounded-lg object-cover"
                            @error="(e) => (e.target.src = '/images/product-placeholder.svg')"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold leading-6 text-herb-900">{{ item.name }}</p>
                            <p class="mt-0.5 text-xs text-herb-700">قیمت هر عدد: {{ tomanValue(item.unit_price) }} تومان</p>
                        </div>
                        <div class="col-span-2 flex flex-wrap items-center justify-between gap-2 border-t border-kraft-200/70 pt-3 sm:col-span-1 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
                            <div class="flex items-center gap-2">
                                <QtyStepper :model-value="item.quantity" size="sm" @change="(q) => cart.setQty(item.sku, q)" />
                                <button type="button" class="min-h-11 min-w-11 rounded-lg px-2 text-sm font-bold text-anar-600" @click="cart.remove(item.sku)">حذف</button>
                            </div>
                            <div class="price-tag shrink-0 text-base">
                                {{ tomanValue(item.line_total) }}<span class="unit">تومان</span>
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="mt-4 rounded-card border border-kraft-200/70 bg-white p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-herb-900/65">جمع سبد</span>
                        <span class="price-tag text-lg">{{ tomanValue(subtotal) }}<span class="unit">تومان</span></span>
                    </div>
                    <Link
                        :href="route('checkout.show')"
                        class="mt-4 flex w-full items-center justify-center rounded-full bg-herb-600 py-3.5 text-sm font-extrabold text-white transition hover:bg-herb-700"
                    >
                        ادامه و ثبت سفارش
                    </Link>
                </div>
            </template>
        </div>
    </ShopLayout>
</template>
