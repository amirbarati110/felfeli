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
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-brand-100 text-brand-600">
                <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m5 13 4 4L19 7" />
                </svg>
            </div>

            <h1 class="mt-4 text-xl font-extrabold text-brand-900">سفارش شما ثبت شد</h1>
            <p class="mt-1 text-sm text-brand-900/55">
                شماره سفارش:
                <span class="font-bold text-brand-700">#{{ toFaDigits(order.number.replace('FS-', '')) }}</span>
            </p>

            <div class="mt-5 rounded-2xl bg-white p-4 text-right ring-1 ring-black/5">
                <p class="text-[13px] leading-relaxed text-brand-800">
                    سفارش برای <span class="font-bold">{{ store.name }}</span> ارسال شد.
                    فروشگاه برای هماهنگی نهایی
                    <template v-if="store.phone">(شماره تماس {{ toFaDigits(store.phone) }})</template>
                    با شما تماس می‌گیرد.
                </p>
            </div>

            <div class="mt-4 rounded-2xl bg-white p-4 text-right ring-1 ring-black/5">
                <ul class="divide-y divide-black/5">
                    <li v-for="(it, i) in order.items" :key="i" class="flex justify-between py-2 text-sm">
                        <span class="text-brand-900/75">{{ it.name }} × {{ toFaDigits(it.quantity) }}</span>
                        <span class="font-medium text-brand-800">{{ tomanValue(it.line_total) }} تومان</span>
                    </li>
                </ul>
                <div class="mt-2 flex justify-between border-t border-black/5 pt-2 text-sm font-extrabold text-brand-900">
                    <span>جمع کل</span><span>{{ tomanValue(order.total) }} تومان</span>
                </div>
                <p class="mt-2 text-xs text-brand-900/50">روش دریافت: {{ order.delivery_method_label }}</p>
            </div>

            <Link :href="route('menu.index')" class="mt-6 inline-block rounded-full bg-brand-500 px-6 py-3 text-sm font-extrabold text-white">
                بازگشت به منو
            </Link>
        </div>
    </ShopLayout>
</template>
