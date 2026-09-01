<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { faNumber, toFaDigits } from '@/lib/format';

defineProps({ imports: { type: Array, default: () => [] } });

const form = useForm({ file: null });

function submit() {
    form.post(route('admin.import.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="درون‌ریزی اکسل" />

    <AdminLayout>
        <h1 class="mb-4 text-xl font-extrabold">درون‌ریزی محصولات</h1>

        <div class="grid gap-4 lg:grid-cols-2">
            <form class="rounded-2xl bg-white p-5 ring-1 ring-black/5" @submit.prevent="submit">
                <h2 class="text-sm font-bold">بارگذاری فایل CSV</h2>
                <p class="mt-1 text-xs leading-relaxed text-herb-900/50">
                    فایل با کدگذاری UTF-8. ستون‌های الزامی: «کد کالا»، «نام»، «قیمت». دسته نبود ⇒ حدس خودکار.
                    upsert بر مبنای کد کالا انجام می‌شود.
                </p>
                <a :href="route('admin.import.template')" class="mt-2 inline-block text-xs font-medium text-herb-600">دانلود قالب نمونه ↓</a>

                <input type="file" accept=".csv,text/csv" class="mt-4 block w-full text-sm" @input="form.file = $event.target.files[0]" />
                <p v-if="form.errors.file" class="mt-1 text-xs text-anar-600">{{ form.errors.file }}</p>

                <button type="submit" :disabled="form.processing || !form.file" class="mt-4 rounded-full bg-herb-500 px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50">
                    {{ form.processing ? 'در حال پردازش…' : 'شروع درون‌ریزی' }}
                </button>
            </form>

            <div class="rounded-2xl bg-white p-5 ring-1 ring-black/5">
                <h2 class="mb-3 text-sm font-bold">تاریخچه</h2>
                <table class="w-full text-sm">
                    <tbody>
                        <tr v-for="im in imports" :key="im.id" class="border-b border-black/5 last:border-0">
                            <td class="py-2">
                                <Link :href="route('admin.import.show', im.id)" class="font-medium text-herb-700">{{ im.filename }}</Link>
                                <div class="text-xs text-herb-900/40">{{ toFaDigits(im.created_at) }}</div>
                            </td>
                            <td class="py-2 text-xs">
                                <span class="text-herb-600">+{{ faNumber(im.created_count) }}</span>
                                <span class="ms-1 text-amber-600">~{{ faNumber(im.updated_count) }}</span>
                                <span class="ms-1 text-herb-900/40">رد {{ faNumber(im.skipped_count) }}</span>
                                <span v-if="im.failed_count" class="ms-1 text-anar-600">خطا {{ faNumber(im.failed_count) }}</span>
                            </td>
                        </tr>
                        <tr v-if="!imports.length"><td class="py-6 text-center text-herb-900/40">تاکنون درون‌ریزی‌ای انجام نشده</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
