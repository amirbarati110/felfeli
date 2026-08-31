<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import QtyStepper from '@/Components/QtyStepper.vue';
import { tomanValue } from '@/lib/format';

const props = defineProps({
    items: { type: Array, default: () => [] },
    subtotal: { type: Number, default: 0 },
    removed: { type: Array, default: () => [] },
    adjusted: { type: Array, default: () => [] },
});

const isEmpty = computed(() => props.items.length === 0);
const opts = { preserveScroll: true, preserveState: true };

function setQty(sku, qty) {
    router.patch(route('cart.update', sku), { qty }, opts);
}
function removeItem(sku) {
    router.delete(route('cart.remove', sku), opts);
}
</script>

<template>
    <Head title="سبد خرید" />

    <ShopLayout :show-search="false">
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-4 text-xl font-extrabold text-brand-900">سبد خرید</h1>

            <div v-if="removed.length" class="mb-3 rounded-xl bg-tomato-500/10 px-4 py-3 text-sm text-tomato-600">
                این کالاها ناموجود شدند و حذف شدند: {{ removed.join('، ') }}
            </div>
            <div v-if="adjusted.length" class="mb-3 rounded-xl bg-lemon-400/20 px-4 py-3 text-sm text-brand-800">
                تعداد این کالاها بر اساس موجودی اصلاح شد: {{ adjusted.join('، ') }}
            </div>

            <div v-if="isEmpty" class="rounded-2xl bg-white p-10 text-center ring-1 ring-black/5">
                <p class="text-sm font-medium text-brand-900/60">سبد خرید خالی است.</p>
                <Link :href="route('menu.index')" class="mt-4 inline-block rounded-full bg-brand-500 px-5 py-2.5 text-sm font-bold text-white">
                    رفتن به منو
                </Link>
            </div>

            <template v-else>
                <ul class="space-y-2">
                    <li
                        v-for="item in items" :key="item.sku"
                        class="flex items-center gap-3 rounded-2xl bg-white p-3 ring-1 ring-black/5"
                    >
                        <img
                            :src="item.image_url" :alt="item.name"
                            class="h-16 w-16 shrink-0 rounded-xl object-cover"
                            @error="(e) => (e.target.src = '/images/product-placeholder.svg')"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-1 text-sm font-medium text-brand-900">{{ item.name }}</p>
                            <p class="mt-0.5 text-xs text-brand-900/50">
                                {{ tomanValue(item.unit_price) }} تومان
                            </p>
                            <div class="mt-2 flex items-center gap-3">
                                <QtyStepper :model-value="item.quantity" size="sm" @change="(q) => setQty(item.sku, q)" />
                                <button class="text-xs font-medium text-tomato-600" @click="removeItem(item.sku)">حذف</button>
                            </div>
                        </div>
                        <div class="shrink-0 text-sm font-bold text-brand-800">
                            {{ tomanValue(item.line_total) }}
                            <span class="text-[11px] font-medium text-brand-900/45">تومان</span>
                        </div>
                    </li>
                </ul>

                <div class="mt-4 rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-brand-900/60">جمع سبد</span>
                        <span class="font-extrabold text-brand-900">{{ tomanValue(subtotal) }} تومان</span>
                    </div>
                    <Link
                        :href="route('checkout.show')"
                        class="mt-4 flex w-full items-center justify-center rounded-full bg-brand-500 py-3.5 text-sm font-extrabold text-white transition hover:bg-brand-600"
                    >
                        ادامه و ثبت سفارش
                    </Link>
                </div>
            </template>
        </div>
    </ShopLayout>
</template>
