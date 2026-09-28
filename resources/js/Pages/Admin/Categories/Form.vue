<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ category: { type: Object, default: null } });
const isEdit = !!props.category;

const form = useForm({
    name: props.category?.name ?? '',
    slug: props.category?.slug ?? '',
    icon: props.category?.icon ?? '',
    is_active: props.category?.is_active ?? true,
    sort_order: props.category?.sort_order ?? 0,
});

function submit() {
    isEdit
        ? form.put(route('admin.categories.update', props.category.id))
        : form.post(route('admin.categories.store'));
}
</script>

<template>
    <Head :title="isEdit ? 'ویرایش دسته' : 'دسته جدید'" />

    <AdminLayout>
        <div class="mb-6 flex flex-wrap items-center gap-2">
            <Link :href="route('admin.categories.index')" class="text-sm text-herb-900/65">دسته‌بندی‌ها</Link>
            <span class="text-herb-900/65">/</span>
            <h1 class="text-xl font-extrabold">{{ isEdit ? form.name : 'دسته جدید' }}</h1>
        </div>

        <form class="max-w-xl space-y-5 rounded-2xl border border-herb-100 bg-white p-5 shadow-sm sm:p-7" @submit.prevent="submit">
            <div class="border-b border-herb-100 pb-5">
                <h2 class="text-base font-extrabold">مشخصات دسته</h2>
                <p class="mt-1 text-sm text-herb-700">دسته‌ها کمک می‌کنند مشتری محصولات و قیمت‌ها را سریع‌تر پیدا کند.</p>
            </div>
            <div>
                <label for="category-name" class="mb-1.5 block text-sm font-medium">نام دسته *</label>
                <input id="category-name" v-model="form.name" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-base ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400 sm:text-sm" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-anar-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label for="category-slug" class="mb-1.5 block text-sm font-medium">نامک (slug) <span class="text-xs font-normal text-herb-900/65">خالی = خودکار</span></label>
                <input id="category-slug" v-model="form.slug" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-base ring-1 ring-black/5 sm:text-sm" />
                <p v-if="form.errors.slug" class="mt-1 text-xs text-anar-600">{{ form.errors.slug }}</p>
            </div>
            <div>
                <label for="category-icon" class="mb-1.5 block text-sm font-medium">آیکون</label>
                <input id="category-icon" v-model="form.icon" dir="ltr" placeholder="leaf" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-base ring-1 ring-black/5 sm:text-sm" />
            </div>
            <label class="flex min-h-12 items-center justify-between rounded-xl bg-herb-50 px-4 text-sm font-semibold text-herb-800">
                نمایش این دسته در منو
                <input v-model="form.is_active" type="checkbox" class="rounded" />
            </label>
            <button type="submit" :disabled="form.processing" class="min-h-12 w-full rounded-xl bg-herb-700 py-3 text-sm font-extrabold text-white hover:bg-herb-800 disabled:opacity-60">
                {{ isEdit ? 'ذخیره' : 'ساخت دسته' }}
            </button>
        </form>
    </AdminLayout>
</template>
