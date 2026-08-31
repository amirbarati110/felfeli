<script setup>
import { reactive, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { faNumber, toFaDigits, tomanValue } from '@/lib/format';

const props = defineProps({
    orders: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Object, default: () => ({}) },
    integrationOptions: { type: Object, default: () => ({}) },
});

const f = reactive({
    q: props.filters.q ?? '',
    status: props.filters.status ?? '',
    integration: props.filters.integration ?? '',
});

let t = null;
watch(f, () => {
    clearTimeout(t);
    t = setTimeout(() => router.get(route('admin.orders.index'), { ...f }, { preserveState: true, replace: true, preserveScroll: true }), 300);
});

const badge = {
    info: 'bg-sky-100 text-sky-700', warning: 'bg-amber-100 text-amber-700',
    primary: 'bg-brand-50 text-brand-700', purple: 'bg-violet-100 text-violet-700',
    success: 'bg-brand-100 text-brand-800', danger: 'bg-tomato-500/15 text-tomato-700',
};
</script>

<template>
    <Head title="سفارش‌ها" />

    <AdminLayout>
        <h1 class="mb-4 text-xl font-extrabold">سفارش‌ها <span class="text-sm font-medium text-brand-900/40">({{ faNumber(orders.total) }})</span></h1>

        <div class="mb-3 grid gap-2 sm:grid-cols-3">
            <input v-model="f.q" placeholder="شماره، نام یا موبایل…" class="h-10 rounded-xl border-0 bg-white px-3 text-sm ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400" />
            <select v-model="f.status" class="h-10 rounded-xl border-0 bg-white px-3 text-sm ring-1 ring-black/5">
                <option value="">وضعیت: همه</option>
                <option v-for="(label, val) in statusOptions" :key="val" :value="val">{{ label }}</option>
            </select>
            <select v-model="f.integration" class="h-10 rounded-xl border-0 bg-white px-3 text-sm ring-1 ring-black/5">
                <option value="">اتصال باران: همه</option>
                <option v-for="(label, val) in integrationOptions" :key="val" :value="val">{{ label }}</option>
            </select>
        </div>

        <div class="overflow-x-auto rounded-2xl bg-white ring-1 ring-black/5">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="border-b border-black/5 text-xs text-brand-900/50">
                    <tr>
                        <th class="p-3 text-right font-medium">شماره</th>
                        <th class="p-3 text-right font-medium">مشتری</th>
                        <th class="p-3 text-right font-medium">اقلام</th>
                        <th class="p-3 text-right font-medium">جمع (ت)</th>
                        <th class="p-3 text-right font-medium">دریافت</th>
                        <th class="p-3 text-right font-medium">وضعیت</th>
                        <th class="p-3 text-right font-medium">باران</th>
                        <th class="p-3 text-right font-medium">تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in orders.data" :key="o.id" class="cursor-pointer border-b border-black/5 last:border-0 hover:bg-cream-50" @click="router.get(route('admin.orders.show', o.id))">
                        <td class="p-3 font-medium text-brand-700">{{ o.number }}</td>
                        <td class="p-3">{{ o.customer }}<div class="text-xs text-brand-900/45" dir="ltr">{{ toFaDigits(o.mobile) }}</div></td>
                        <td class="p-3 text-brand-900/60">{{ faNumber(o.items_count) }}</td>
                        <td class="p-3">{{ tomanValue(o.total) }}</td>
                        <td class="p-3 text-brand-900/60">{{ o.delivery_method }}</td>
                        <td class="p-3"><span class="rounded-full px-2 py-0.5 text-xs" :class="badge[o.status_color]">{{ o.status_label }}</span></td>
                        <td class="p-3">
                            <span class="text-xs" :class="o.integration === 'synced' ? 'text-brand-600' : o.integration === 'failed' ? 'text-tomato-600' : 'text-amber-600'">
                                {{ o.integration_label }}
                            </span>
                        </td>
                        <td class="p-3 text-xs text-brand-900/45">{{ toFaDigits(o.created_at) }}</td>
                    </tr>
                    <tr v-if="!orders.data.length"><td colspan="8" class="p-6 text-center text-brand-900/40">سفارشی یافت نشد</td></tr>
                </tbody>
            </table>
        </div>

        <div v-if="orders.last_page > 1" class="mt-4 flex flex-wrap justify-center gap-1.5">
            <button
                v-for="l in orders.links" :key="l.label" :disabled="!l.url"
                class="min-w-9 rounded-lg px-3 py-1.5 text-sm disabled:opacity-30"
                :class="l.active ? 'bg-brand-500 text-white' : 'bg-white ring-1 ring-black/5'"
                @click="l.url && router.get(l.url, {}, { preserveState: true, preserveScroll: true })"
                v-html="l.label"
            />
        </div>
    </AdminLayout>
</template>
