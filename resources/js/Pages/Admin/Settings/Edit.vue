<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    store: { type: Object, default: () => ({}) },
    checkout: { type: Object, default: () => ({}) },
});

const form = useForm({
    store: {
        name: props.store.name ?? '',
        phone: props.store.phone ?? '',
        about: props.store.about ?? '',
        work_time: props.store.work_time ?? '',
        instagram: props.store.instagram ?? '',
        location_link: props.store.location_link ?? '',
    },
    checkout: {
        delivery_fee: props.checkout.delivery_fee ?? 0,
        min_order_total: props.checkout.min_order_total ?? 0,
        notice: props.checkout.notice ?? '',
        delivery_enabled: props.checkout.delivery_enabled ?? true,
        pickup_enabled: props.checkout.pickup_enabled ?? true,
    },
});

const input = 'h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-sm ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400';
</script>

<template>
    <Head title="تنظیمات" />

    <AdminLayout>
        <h1 class="mb-4 text-xl font-extrabold">تنظیمات</h1>

        <form class="grid gap-4 lg:grid-cols-2" @submit.prevent="form.put(route('admin.settings.update'))">
            <div class="rounded-2xl bg-white p-5 ring-1 ring-black/5">
                <h2 class="mb-3 text-sm font-bold">اطلاعات فروشگاه</h2>
                <div class="space-y-3">
                    <div><label class="mb-1 block text-sm font-medium">نام فروشگاه *</label><input v-model="form.store.name" :class="input" /></div>
                    <div><label class="mb-1 block text-sm font-medium">تلفن</label><input v-model="form.store.phone" dir="ltr" :class="[input, 'text-right']" /></div>
                    <div><label class="mb-1 block text-sm font-medium">درباره</label><textarea v-model="form.store.about" rows="2" class="w-full rounded-xl border-0 bg-paper-100 p-3 text-sm ring-1 ring-black/5" /></div>
                    <div><label class="mb-1 block text-sm font-medium">ساعت کاری</label><textarea v-model="form.store.work_time" rows="3" class="w-full rounded-xl border-0 bg-paper-100 p-3 text-sm ring-1 ring-black/5" /></div>
                    <div><label class="mb-1 block text-sm font-medium">اینستاگرام</label><input v-model="form.store.instagram" dir="ltr" :class="[input, 'text-right']" /></div>
                    <div><label class="mb-1 block text-sm font-medium">لینک نقشه</label><input v-model="form.store.location_link" dir="ltr" :class="[input, 'text-right']" /></div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-5 ring-1 ring-black/5">
                <h2 class="mb-3 text-sm font-bold">ثبت سفارش</h2>
                <div class="space-y-3">
                    <div><label class="mb-1 block text-sm font-medium">هزینه ارسال در ساوه (ریال)</label><input v-model.number="form.checkout.delivery_fee" type="number" dir="ltr" :class="[input, 'text-right']" /></div>
                    <div><label class="mb-1 block text-sm font-medium">حداقل مبلغ سفارش (ریال)</label><input v-model.number="form.checkout.min_order_total" type="number" dir="ltr" :class="[input, 'text-right']" /></div>
                    <div><label class="mb-1 block text-sm font-medium">پیام صفحه ثبت سفارش</label><textarea v-model="form.checkout.notice" rows="3" class="w-full rounded-xl border-0 bg-paper-100 p-3 text-sm ring-1 ring-black/5" /></div>
                    <label class="flex items-center justify-between text-sm font-medium">ارسال در ساوه فعال<input v-model="form.checkout.delivery_enabled" type="checkbox" class="rounded" /></label>
                    <label class="flex items-center justify-between text-sm font-medium">دریافت حضوری فعال<input v-model="form.checkout.pickup_enabled" type="checkbox" class="rounded" /></label>
                </div>
            </div>

            <div class="lg:col-span-2">
                <button type="submit" :disabled="form.processing" class="rounded-full bg-herb-500 px-6 py-3 text-sm font-extrabold text-white disabled:opacity-60">
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
