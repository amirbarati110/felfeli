<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { faNumber, toFaDigits } from '@/lib/format';

defineProps({
    config: { type: Object, required: true },
    failedOrders: { type: Array, default: () => [] },
    logs: { type: Array, default: () => [] },
});

const syncing = ref(false);
function syncCatalog() {
    if (syncing.value) return;
    router.post(route('admin.integration.sync'), {}, {
        preserveScroll: true,
        onStart: () => (syncing.value = true),
        onFinish: () => (syncing.value = false),
    });
}
function retryAll() {
    router.post(route('admin.integration.retryAll'), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="همگام‌سازی کالا و عکس" />

    <AdminLayout>
        <h1 class="mb-2 text-xl font-extrabold">همگام‌سازی کالا و عکس</h1>
        <p class="mb-4 text-sm text-herb-700">نام، قیمت و موجودی از باران؛ عکس‌ها از فلفلی. عکس‌هایی که دستی آپلود کرده‌اید محفوظ می‌مانند.</p>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5 lg:col-span-1">
                <h2 class="mb-3 text-sm font-bold">پیکربندی</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-herb-900/65">درایور کاتالوگ</dt><dd class="font-medium">{{ config.catalog_driver }}</dd></div>
                    <div class="flex justify-between"><dt class="text-herb-900/65">درایور سفارش</dt><dd class="font-medium">{{ config.order_driver }}</dd></div>
                    <div class="flex justify-between"><dt class="text-herb-900/65">شماره پک</dt><dd class="font-medium" dir="ltr">{{ toFaDigits(config.pack_number) }}</dd></div>
                    <div class="flex justify-between">
                        <dt class="text-herb-900/65">API حسابداری</dt>
                        <dd :class="config.api_configured ? 'text-herb-600' : 'text-amber-600'">
                            {{ config.api_configured ? 'پیکربندی‌شده' : 'در انتظار مستندات باران' }}
                        </dd>
                    </div>
                </dl>
                <div class="mt-4 space-y-2">
                    <button type="button" :disabled="syncing" class="min-h-11 w-full rounded-xl bg-herb-700 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60" @click="syncCatalog">
                        {{ syncing ? 'در حال به‌روزرسانی…' : 'به‌روزرسانی قیمت‌ها و عکس‌ها' }}
                    </button>
                    <button class="w-full rounded-full bg-white py-2.5 text-sm font-bold text-herb-700 ring-1 ring-herb-200" @click="retryAll">
                        ارسال مجدد همه‌ی سفارش‌های ناموفق
                    </button>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5 lg:col-span-2">
                <h2 class="mb-3 text-sm font-bold">سفارش‌های در انتظار / ناموفق <span class="text-herb-900/65">({{ faNumber(failedOrders.length) }})</span></h2>
                <div v-if="!failedOrders.length" class="py-6 text-center text-sm text-herb-900/65">همه‌ی سفارش‌ها همگام‌اند ✓</div>
                <table v-else class="w-full text-sm">
                    <tbody>
                        <tr v-for="o in failedOrders" :key="o.id" class="border-b border-black/5 last:border-0">
                            <td class="py-2"><Link :href="route('admin.orders.show', o.id)" class="font-medium text-herb-700">{{ o.number }}</Link></td>
                            <td class="py-2"><span :class="o.status === 'failed' ? 'text-anar-600' : 'text-amber-600'">{{ o.status }}</span></td>
                            <td class="py-2 text-xs text-herb-900/65">{{ faNumber(o.attempts) }} تلاش</td>
                            <td class="py-2 text-xs text-herb-900/65">{{ o.error }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 rounded-2xl bg-white p-4 ring-1 ring-black/5">
            <h2 class="mb-3 text-sm font-bold">لاگ اتصال (اطلاعات حساس ماسک‌شده)</h2>
            <div class="max-h-96 overflow-auto">
                <table class="w-full text-xs">
                    <tbody>
                        <tr v-for="l in logs" :key="l.id" class="border-b border-black/5 last:border-0">
                            <td class="py-1.5">
                                <span :class="l.status === 'success' ? 'text-herb-600' : 'text-anar-600'">●</span>
                            </td>
                            <td class="py-1.5 font-medium">{{ l.event }}</td>
                            <td class="py-1.5 text-herb-900/65">{{ l.direction }}</td>
                            <td class="py-1.5 text-herb-900/65">{{ l.http_status ?? '—' }}</td>
                            <td class="py-1.5 text-herb-900/65">{{ l.message }}</td>
                            <td class="py-1.5 text-herb-900/65">{{ toFaDigits(l.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
