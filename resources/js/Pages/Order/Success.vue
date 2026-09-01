<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { toFaDigits, tomanValue } from '@/lib/format';

defineProps({
    order: { type: Object, required: true },
    store: { type: Object, required: true },
});
</script>

<template>
    <Head title="سفارش ثبت شد" />

    <ShopLayout :show-search="false">
        <div class="mx-auto max-w-lg text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-herb-100 text-herb-600">
                <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7" /></svg>
            </div>

            <h1 class="mt-4 text-xl font-extrabold text-herb-900">سفارش شما ثبت شد</h1>
            <p class="mt-1 text-sm text-herb-900/55">
                شماره سفارش:
                <span class="font-bold text-herb-700">#{{ toFaDigits(order.number.replace('FS-', '')) }}</span>
            </p>

            <div class="mt-5 rounded-card border border-kraft-200/70 bg-white p-4 text-right text-[13px] leading-relaxed text-herb-800">
                سفارش برای <span class="font-bold">{{ store.name }}</span> ارسال شد.
                فروشگاه برای هماهنگی نهایی
                <template v-if="store.phone">(شماره تماس {{ toFaDigits(store.phone) }})</template>
                با شما تماس می‌گیرد.
            </div>

            <div class="mt-4 rounded-card border border-kraft-200/70 bg-white p-4 text-right">
                <ul class="divide-y divide-kraft-200/70">
                    <li v-for="(it, i) in order.items" :key="i" class="flex justify-between py-2 text-sm">
                        <span class="text-herb-900/75">{{ it.name }} × {{ toFaDigits(it.quantity) }}</span>
                        <span class="font-medium text-herb-800">{{ tomanValue(it.line_total) }} تومان</span>
                    </li>
                </ul>
                <div class="mt-2 flex items-center justify-between border-t border-kraft-200/70 pt-2">
                    <span class="text-sm font-bold text-herb-900">جمع کل</span>
                    <span class="price-tag text-lg">{{ tomanValue(order.total) }}<span class="unit">تومان</span></span>
                </div>
                <p class="mt-2 text-xs text-herb-900/50">روش دریافت: {{ order.delivery_method_label }}</p>
            </div>

            <Link :href="route('menu.index')" class="mt-6 inline-block rounded-full bg-herb-600 px-6 py-3 text-sm font-extrabold text-white">
                بازگشت به منو
            </Link>
        </div>
    </ShopLayout>
</template>
