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

function submit() {
    form.transform((data) => ({
        ...data,
        address: data.delivery_method === 'delivery' ? data.address : '',
    })).post(route('checkout.store'));
}
</script>

<template>
    <Head title="تکمیل سفارش" />

    <ShopLayout :show-search="false">
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-4 text-xl font-extrabold text-brand-900">تکمیل اطلاعات</h1>

            <p class="mb-4 rounded-xl bg-brand-50 px-4 py-3 text-[13px] leading-relaxed text-brand-700">
                {{ checkout.notice }}
            </p>

            <div v-if="form.errors.cart" class="mb-4 rounded-xl bg-tomato-500/10 px-4 py-3 text-sm text-tomato-600">
                {{ form.errors.cart }}
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <label class="mb-1.5 block text-sm font-medium text-brand-900">
                        نام و نام خانوادگی <span class="text-tomato-500">*</span>
                    </label>
                    <input
                        v-model="form.customer_name" type="text" placeholder="مثال: محمد احمدی"
                        class="h-12 w-full rounded-xl border-0 bg-cream-100 px-4 text-sm outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400"
                    />
                    <p v-if="form.errors.customer_name" class="mt-1 text-xs text-tomato-600">{{ form.errors.customer_name }}</p>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <label class="mb-1.5 block text-sm font-medium text-brand-900">
                        شماره موبایل <span class="text-tomato-500">*</span>
                    </label>
                    <input
                        v-model="form.customer_mobile" type="tel" inputmode="numeric" dir="ltr" placeholder="۰۹۱۲ ۱۲۳ ۴۵۶۷"
                        class="h-12 w-full rounded-xl border-0 bg-cream-100 px-4 text-right text-sm outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400"
                    />
                    <p v-if="form.errors.customer_mobile" class="mt-1 text-xs text-tomato-600">{{ form.errors.customer_mobile }}</p>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <label class="mb-2 block text-sm font-medium text-brand-900">روش دریافت سفارش</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-if="checkout.delivery_enabled" type="button"
                            class="rounded-xl border px-3 py-3 text-sm font-medium transition"
                            :class="form.delivery_method === 'delivery' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-black/10 text-brand-900/60'"
                            @click="form.delivery_method = 'delivery'"
                        >
                            🚚 ارسال در ساوه
                        </button>
                        <button
                            v-if="checkout.pickup_enabled" type="button"
                            class="rounded-xl border px-3 py-3 text-sm font-medium transition"
                            :class="form.delivery_method === 'pickup' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-black/10 text-brand-900/60'"
                            @click="form.delivery_method = 'pickup'"
                        >
                            🏪 دریافت حضوری
                        </button>
                    </div>
                    <p v-if="form.errors.delivery_method" class="mt-1 text-xs text-tomato-600">{{ form.errors.delivery_method }}</p>

                    <div v-if="form.delivery_method === 'delivery'" class="mt-3">
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">
                            آدرس <span class="text-tomato-500">*</span>
                        </label>
                        <textarea
                            v-model="form.address" rows="3" placeholder="نشانی دقیق برای ارسال در ساوه"
                            class="w-full rounded-xl border-0 bg-cream-100 p-4 text-sm outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400"
                        />
                        <p v-if="form.errors.address" class="mt-1 text-xs text-tomato-600">{{ form.errors.address }}</p>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <label class="mb-1.5 block text-sm font-medium text-brand-900">توضیحات سفارش (اختیاری)</label>
                    <textarea
                        v-model="form.note" rows="2"
                        class="w-full rounded-xl border-0 bg-cream-100 p-4 text-sm outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400"
                    />
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <div class="flex justify-between text-sm text-brand-900/60">
                        <span>جمع کالاها</span><span>{{ tomanValue(subtotal) }} تومان</span>
                    </div>
                    <div v-if="form.delivery_method === 'delivery'" class="mt-1 flex justify-between text-sm text-brand-900/60">
                        <span>هزینه ارسال</span>
                        <span>{{ deliveryFee ? tomanValue(deliveryFee) + ' تومان' : 'هماهنگی با فروشگاه' }}</span>
                    </div>
                    <div class="mt-2 flex justify-between border-t border-black/5 pt-2 text-sm font-extrabold text-brand-900">
                        <span>جمع کل</span><span>{{ tomanValue(total) }} تومان</span>
                    </div>
                </div>

                <button
                    type="submit" :disabled="form.processing"
                    class="flex w-full items-center justify-center rounded-full bg-brand-500 py-4 text-sm font-extrabold text-white transition hover:bg-brand-600 disabled:opacity-60"
                >
                    {{ form.processing ? 'در حال ثبت…' : 'ثبت و ارسال سفارش' }}
                </button>

                <Link :href="route('cart.show')" class="block text-center text-xs font-medium text-brand-900/45">
                    بازگشت به سبد
                </Link>
            </form>
        </div>
    </ShopLayout>
</template>
