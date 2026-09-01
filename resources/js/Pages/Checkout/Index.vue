<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { tomanValue } from '@/lib/format';

const props = defineProps({
    items: { type: Array, default: () => [] },
    subtotal: { type: Number, default: 0 },
    checkout: { type: Object, required: true },
});

const form = useForm({
    customer_name: '',
    customer_mobile: '',
    delivery_method: props.checkout.delivery_enabled ? 'delivery' : 'pickup',
    address: '',
    note: '',
});

const deliveryFee = computed(() => (form.delivery_method === 'delivery' ? props.checkout.delivery_fee : 0));
const total = computed(() => props.subtotal + deliveryFee.value);

const field = 'h-12 w-full rounded-xl border border-kraft-200 bg-paper-50 px-4 text-sm outline-none transition focus:border-herb-400 focus:ring-2 focus:ring-herb-300/50';

function submit() {
    form.transform((d) => ({ ...d, address: d.delivery_method === 'delivery' ? d.address : '' }))
        .post(route('checkout.store'));
}
</script>

<template>
    <Head title="تکمیل سفارش" />

    <ShopLayout :show-search="false">
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-4 text-xl font-extrabold text-herb-900">تکمیل اطلاعات</h1>

            <p class="mb-4 flex gap-2 rounded-xl bg-herb-50 px-4 py-3 text-[13px] leading-relaxed text-herb-700">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 8h.01M11 12h1v4h1" stroke-linecap="round" /></svg>
                {{ checkout.notice }}
            </p>

            <div v-if="form.errors.cart" class="mb-4 rounded-xl bg-anar-500/10 px-4 py-3 text-sm text-anar-600">
                {{ form.errors.cart }}
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="rounded-card border border-kraft-200/70 bg-white p-4">
                    <label class="mb-1.5 block text-sm font-semibold text-herb-900">نام و نام خانوادگی <span class="text-anar-500">*</span></label>
                    <input v-model="form.customer_name" type="text" placeholder="مثال: محمد احمدی" :class="field" />
                    <p v-if="form.errors.customer_name" class="mt-1 text-xs text-anar-600">{{ form.errors.customer_name }}</p>
                </div>

                <div class="rounded-card border border-kraft-200/70 bg-white p-4">
                    <label class="mb-1.5 block text-sm font-semibold text-herb-900">شماره موبایل <span class="text-anar-500">*</span></label>
                    <input v-model="form.customer_mobile" type="tel" inputmode="numeric" dir="ltr" placeholder="۰۹۱۲ ۱۲۳ ۴۵۶۷" :class="[field, 'text-right']" />
                    <p v-if="form.errors.customer_mobile" class="mt-1 text-xs text-anar-600">{{ form.errors.customer_mobile }}</p>
                </div>

                <div class="rounded-card border border-kraft-200/70 bg-white p-4">
                    <label class="mb-2 block text-sm font-semibold text-herb-900">روش دریافت سفارش</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-if="checkout.delivery_enabled" type="button"
                            class="rounded-xl border-2 px-3 py-3 text-sm font-semibold transition"
                            :class="form.delivery_method === 'delivery' ? 'border-herb-500 bg-herb-50 text-herb-700' : 'border-kraft-200 text-herb-900/55'"
                            @click="form.delivery_method = 'delivery'"
                        >
                            ارسال در ساوه
                        </button>
                        <button
                            v-if="checkout.pickup_enabled" type="button"
                            class="rounded-xl border-2 px-3 py-3 text-sm font-semibold transition"
                            :class="form.delivery_method === 'pickup' ? 'border-herb-500 bg-herb-50 text-herb-700' : 'border-kraft-200 text-herb-900/55'"
                            @click="form.delivery_method = 'pickup'"
                        >
                            دریافت حضوری
                        </button>
                    </div>
                    <p v-if="form.errors.delivery_method" class="mt-1 text-xs text-anar-600">{{ form.errors.delivery_method }}</p>

                    <div v-if="form.delivery_method === 'delivery'" class="mt-3">
                        <label class="mb-1.5 block text-sm font-semibold text-herb-900">آدرس <span class="text-anar-500">*</span></label>
                        <textarea v-model="form.address" rows="3" placeholder="نشانی دقیق برای ارسال در ساوه" class="w-full rounded-xl border border-kraft-200 bg-paper-50 p-4 text-sm outline-none focus:border-herb-400 focus:ring-2 focus:ring-herb-300/50" />
                        <p v-if="form.errors.address" class="mt-1 text-xs text-anar-600">{{ form.errors.address }}</p>
                    </div>
                </div>

                <div class="rounded-card border border-kraft-200/70 bg-white p-4">
                    <label class="mb-1.5 block text-sm font-semibold text-herb-900">توضیحات سفارش <span class="font-normal text-herb-900/40">(اختیاری)</span></label>
                    <textarea v-model="form.note" rows="2" class="w-full rounded-xl border border-kraft-200 bg-paper-50 p-4 text-sm outline-none focus:border-herb-400 focus:ring-2 focus:ring-herb-300/50" />
                </div>

                <div class="rounded-card border border-kraft-200/70 bg-white p-4">
                    <div class="flex justify-between text-sm text-herb-900/60">
                        <span>جمع کالاها</span><span>{{ tomanValue(subtotal) }} تومان</span>
                    </div>
                    <div v-if="form.delivery_method === 'delivery'" class="mt-1 flex justify-between text-sm text-herb-900/60">
                        <span>هزینه ارسال</span>
                        <span>{{ deliveryFee ? tomanValue(deliveryFee) + ' تومان' : 'هماهنگی با فروشگاه' }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between border-t border-kraft-200/70 pt-2">
                        <span class="text-sm font-bold text-herb-900">جمع کل</span>
                        <span class="price-tag text-lg">{{ tomanValue(total) }}<span class="unit">تومان</span></span>
                    </div>
                </div>

                <button
                    type="submit" :disabled="form.processing"
                    class="flex w-full items-center justify-center rounded-full bg-herb-600 py-4 text-sm font-extrabold text-white transition hover:bg-herb-700 disabled:opacity-60"
                >
                    {{ form.processing ? 'در حال ثبت…' : 'ثبت و ارسال سفارش' }}
                </button>

                <Link :href="route('cart.show')" class="block text-center text-xs font-medium text-herb-900/45">بازگشت به سبد</Link>
            </form>
        </div>
    </ShopLayout>
</template>
