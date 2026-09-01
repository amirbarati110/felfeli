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
        <div class="mb-4 flex items-center gap-2">
            <Link :href="route('admin.categories.index')" class="text-sm text-herb-900/45">دسته‌بندی‌ها</Link>
            <span class="text-herb-900/30">/</span>
            <h1 class="text-xl font-extrabold">{{ isEdit ? form.name : 'دسته جدید' }}</h1>
        </div>

        <form class="max-w-lg space-y-4 rounded-2xl bg-white p-5 ring-1 ring-black/5" @submit.prevent="submit">
            <div>
                <label class="mb-1.5 block text-sm font-medium">نام دسته *</label>
                <input v-model="form.name" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-sm ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-anar-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">نامک (slug) <span class="text-xs font-normal text-herb-900/40">خالی = خودکار</span></label>
                <input v-model="form.slug" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-sm ring-1 ring-black/5" />
                <p v-if="form.errors.slug" class="mt-1 text-xs text-anar-600">{{ form.errors.slug }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium">آیکون</label>
                <input v-model="form.icon" dir="ltr" placeholder="leaf" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-sm ring-1 ring-black/5" />
            </div>
            <label class="flex items-center justify-between text-sm font-medium">
                فعال
                <input v-model="form.is_active" type="checkbox" class="rounded" />
            </label>
            <button type="submit" :disabled="form.processing" class="w-full rounded-full bg-herb-500 py-3 text-sm font-extrabold text-white disabled:opacity-60">
                {{ isEdit ? 'ذخیره' : 'ساخت دسته' }}
            </button>
        </form>
    </AdminLayout>
</template>
