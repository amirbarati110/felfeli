<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { faNumber, tomanValue } from '@/lib/format';

defineProps({
    stats: { type: Object, required: true },
    recentOrders: { type: Array, default: () => [] },
});

const cards = [
    { key: 'orders_today', label: 'سفارش امروز', tone: 'brand' },
    { key: 'orders_new', label: 'سفارش جدید', tone: 'lemon' },
    { key: 'sync_failed', label: 'خطای ارسال به باران', tone: 'tomato' },
    { key: 'sync_pending', label: 'در صف ارسال', tone: 'brand' },
    { key: 'products_total', label: 'کل محصولات', tone: 'brand' },
    { key: 'products_out', label: 'ناموجود', tone: 'tomato' },
];
</script>

<template>
    <Head title="داشبورد مدیریت" />

    <AdminLayout>
        <h1 class="mb-5 text-xl font-extrabold">داشبورد</h1>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div v-for="c in cards" :key="c.key" class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                <p class="text-xs text-brand-900/50">{{ c.label }}</p>
                <p
                    class="mt-1 text-2xl font-extrabold"
                    :class="{
                        'text-brand-700': c.tone === 'brand',
                        'text-lemon-600': c.tone === 'lemon',
                        'text-tomato-600': c.tone === 'tomato',
                    }"
                >
                    {{ faNumber(stats[c.key]) }}
                </p>
            </div>
        </div>

        <div class="mt-6 rounded-2xl bg-white ring-1 ring-black/5">
            <div class="flex items-center justify-between border-b border-black/5 p-4">
                <h2 class="text-sm font-bold">آخرین سفارش‌ها</h2>
                <Link :href="route('admin.orders.index')" class="text-xs font-medium text-brand-600">همه</Link>
            </div>
            <table class="w-full text-sm">
                <tbody>
                    <tr v-for="o in recentOrders" :key="o.id" class="border-b border-black/5 last:border-0">
                        <td class="p-3 font-medium">
                            <Link :href="route('admin.orders.show', o.id)" class="text-brand-700">{{ o.number }}</Link>
                        </td>
                        <td class="p-3 text-brand-900/70">{{ o.customer }}</td>
                        <td class="p-3 text-brand-900/70">{{ tomanValue(o.total) }} ت</td>
                        <td class="p-3"><span class="rounded-full bg-cream-100 px-2 py-0.5 text-xs">{{ o.status_label }}</span></td>
                        <td class="p-3 text-xs text-brand-900/45">{{ o.created_at }}</td>
                    </tr>
                    <tr v-if="!recentOrders.length"><td class="p-6 text-center text-sm text-brand-900/40">هنوز سفارشی ثبت نشده</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
