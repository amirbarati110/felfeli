<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { faNumber, toFaDigits, tomanValue } from '@/lib/format';

const props = defineProps({
    order: { type: Object, required: true },
    payloadPreview: { type: Object, required: true },
    statusOptions: { type: Object, default: () => ({}) },
});

const statusForm = useForm({ status: props.order.status });
const showPayload = ref(false);

function saveStatus() {
    statusForm.patch(route('admin.orders.status', props.order.id), { preserveScroll: true });
}
function retrySync() {
    router.post(route('admin.orders.retry', props.order.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="`سفارش ${order.order_number}`" />

    <AdminLayout>
        <div class="mb-4 flex items-center gap-2">
            <Link :href="route('admin.orders.index')" class="text-sm text-herb-900/45">سفارش‌ها</Link>
            <span class="text-herb-900/30">/</span>
            <h1 class="text-xl font-extrabold">{{ order.order_number }}</h1>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <h2 class="mb-3 text-sm font-bold">اقلام</h2>
                    <table class="w-full text-sm">
                        <tbody>
                            <tr v-for="(it, i) in order.items" :key="i" class="border-b border-black/5 last:border-0">
                                <td class="py-2">{{ it.product_name }}<span class="ms-1 text-xs text-herb-900/40" dir="ltr">#{{ it.sku }}</span></td>
                                <td class="py-2 text-herb-900/60">× {{ faNumber(it.quantity) }}</td>
                                <td class="py-2 text-left">{{ tomanValue(it.line_total) }} ت</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-3 space-y-1 border-t border-black/5 pt-3 text-sm">
                        <div class="flex justify-between text-herb-900/60"><span>جمع کالاها</span><span>{{ tomanValue(order.subtotal) }} ت</span></div>
                        <div class="flex justify-between text-herb-900/60"><span>هزینه ارسال</span><span>{{ tomanValue(order.delivery_fee) }} ت</span></div>
                        <div class="flex justify-between font-extrabold"><span>جمع کل</span><span>{{ tomanValue(order.total) }} ت</span></div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold">اتصال باران</h2>
                        <button class="text-xs font-medium text-herb-600" @click="showPayload = !showPayload">
                            {{ showPayload ? 'بستن' : 'نمایش payload' }}
                        </button>
                    </div>
                    <p class="mt-2 text-sm">
                        وضعیت:
                        <span :class="order.integration_status === 'synced' ? 'text-herb-600' : order.integration_status === 'failed' ? 'text-anar-600' : 'text-amber-600'">
                            {{ order.integration_label }}
                        </span>
                        <span class="text-xs text-herb-900/40"> · {{ faNumber(order.integration_attempts) }} تلاش</span>
                        <span v-if="order.integration_reference" class="text-xs text-herb-900/40"> · سند {{ toFaDigits(order.integration_reference) }}</span>
                    </p>
                    <p v-if="order.integration_last_error" class="mt-1 text-xs text-anar-600">{{ order.integration_last_error }}</p>
                    <button
                        v-if="order.integration_status !== 'synced'"
                        class="mt-3 rounded-full bg-herb-500 px-4 py-1.5 text-xs font-bold text-white"
                        @click="retrySync"
                    >
                        ارسال مجدد به باران
                    </button>

                    <pre v-if="showPayload" class="mt-3 max-h-72 overflow-auto rounded-xl bg-herb-900 p-3 text-[11px] leading-relaxed text-herb-100" dir="ltr">{{ JSON.stringify(payloadPreview, null, 2) }}</pre>

                    <div v-if="order.logs.length" class="mt-3 space-y-1 border-t border-black/5 pt-3">
                        <div v-for="(l, i) in order.logs" :key="i" class="flex items-center gap-2 text-xs">
                            <span :class="l.status === 'success' ? 'text-herb-600' : 'text-anar-600'">●</span>
                            <span class="text-herb-900/60">{{ l.event }}</span>
                            <span class="text-herb-900/40">{{ toFaDigits(l.created_at) }}</span>
                            <span v-if="l.message" class="text-herb-900/50">— {{ l.message }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <h2 class="mb-2 text-sm font-bold">مشتری</h2>
                    <p class="text-sm">{{ order.customer_name }}</p>
                    <p class="text-sm text-herb-900/60" dir="ltr">{{ toFaDigits(order.customer_mobile) }}</p>
                    <p class="mt-2 text-xs text-herb-900/50">روش: {{ order.delivery_method }}</p>
                    <p v-if="order.address" class="mt-1 text-sm text-herb-900/70">{{ order.address }}</p>
                    <p v-if="order.note" class="mt-2 rounded-lg bg-paper-100 p-2 text-xs text-herb-900/60">یادداشت: {{ order.note }}</p>
                    <p class="mt-2 text-xs text-herb-900/40">ثبت: {{ toFaDigits(order.created_at) }}</p>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <h2 class="mb-2 text-sm font-bold">وضعیت سفارش</h2>
                    <select v-model="statusForm.status" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-sm ring-1 ring-black/5">
                        <option v-for="(label, val) in statusOptions" :key="val" :value="val">{{ label }}</option>
                    </select>
                    <button class="mt-2 w-full rounded-full bg-herb-500 py-2.5 text-sm font-bold text-white disabled:opacity-60" :disabled="statusForm.processing" @click="saveStatus">
                        ثبت وضعیت
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
