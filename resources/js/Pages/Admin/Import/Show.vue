<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { faNumber, toFaDigits } from '@/lib/format';

defineProps({ record: { type: Object, required: true } });

const tone = {
    created: 'text-herb-600', updated: 'text-amber-600',
    skipped: 'text-herb-900/40', failed: 'text-anar-600',
};
</script>

<template>
    <Head :title="`درون‌ریزی ${record.filename}`" />

    <AdminLayout>
        <div class="mb-4 flex items-center gap-2">
            <Link :href="route('admin.import.index')" class="text-sm text-herb-900/45">درون‌ریزی</Link>
            <span class="text-herb-900/30">/</span>
            <h1 class="text-xl font-extrabold">{{ record.filename }}</h1>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
            <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5"><p class="text-xs text-herb-900/50">کل ردیف</p><p class="text-xl font-extrabold">{{ faNumber(record.total_rows) }}</p></div>
            <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5"><p class="text-xs text-herb-900/50">ساخته‌شده</p><p class="text-xl font-extrabold text-herb-600">{{ faNumber(record.created_count) }}</p></div>
            <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5"><p class="text-xs text-herb-900/50">به‌روز</p><p class="text-xl font-extrabold text-amber-600">{{ faNumber(record.updated_count) }}</p></div>
            <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5"><p class="text-xs text-herb-900/50">ردشده</p><p class="text-xl font-extrabold text-herb-900/50">{{ faNumber(record.skipped_count) }}</p></div>
            <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5"><p class="text-xs text-herb-900/50">خطا</p><p class="text-xl font-extrabold text-anar-600">{{ faNumber(record.failed_count) }}</p></div>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl bg-white ring-1 ring-black/5">
            <table class="w-full text-sm">
                <thead class="border-b border-black/5 text-xs text-herb-900/50">
                    <tr><th class="p-3 text-right font-medium">ردیف</th><th class="p-3 text-right font-medium">کد کالا</th><th class="p-3 text-right font-medium">نتیجه</th><th class="p-3 text-right font-medium">توضیح</th></tr>
                </thead>
                <tbody>
                    <tr v-for="(r, i) in record.report" :key="i" class="border-b border-black/5 last:border-0">
                        <td class="p-2.5 text-herb-900/50">{{ faNumber(r.row) }}</td>
                        <td class="p-2.5" dir="ltr">{{ r.sku ? toFaDigits(r.sku) : '—' }}</td>
                        <td class="p-2.5 font-medium" :class="tone[r.result]">{{ r.result }}</td>
                        <td class="p-2.5 text-xs text-herb-900/50">{{ r.reason ?? '' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
