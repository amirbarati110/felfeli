<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { faNumber } from '@/lib/format';

const props = defineProps({ categories: { type: Array, default: () => [] } });

function toggle(c) {
    router.put(route('admin.categories.update', c.id), { name: c.name, slug: c.slug, icon: c.icon, is_active: !c.is_active, sort_order: c.sort_order }, { preserveScroll: true });
}
function destroy(id) {
    if (confirm('این دسته حذف شود؟')) router.delete(route('admin.categories.destroy', id), { preserveScroll: true });
}
function move(c, dir) {
    const list = [...props.categories];
    const i = list.findIndex((x) => x.id === c.id);
    const j = i + dir;
    if (j < 0 || j >= list.length) return;
    [list[i], list[j]] = [list[j], list[i]];
    router.post(route('admin.categories.reorder'), { ids: list.map((x) => x.id) }, { preserveScroll: true });
}
</script>

<template>
    <Head title="دسته‌بندی‌ها" />

    <AdminLayout>
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="mb-1 text-xs font-semibold text-herb-700">ساختار منوی فروشگاه</p>
                <h1 class="text-2xl font-extrabold">دسته‌بندی‌ها</h1>
                <p class="mt-1 text-sm text-herb-700">{{ faNumber(categories.length) }} دسته؛ ترتیب این فهرست، ترتیب نمایش در منو است.</p>
            </div>
            <Link :href="route('admin.categories.create')" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-herb-700 px-5 text-sm font-bold text-white transition hover:bg-herb-800 sm:w-auto">+ افزودن دسته</Link>
        </div>

        <div v-if="!categories.length" class="rounded-2xl border border-herb-100 bg-white px-6 py-12 text-center">
            <p class="font-bold">هنوز دسته‌ای ساخته نشده است.</p>
            <Link :href="route('admin.categories.create')" class="mt-4 inline-block text-sm font-bold text-herb-700 underline">ساخت اولین دسته</Link>
        </div>

        <div v-else class="space-y-3">
            <article v-for="(c, i) in categories" :key="c.id" class="flex flex-wrap items-center gap-3 rounded-2xl border border-herb-100 bg-white p-4 shadow-sm sm:flex-nowrap sm:gap-5">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-herb-50 text-sm font-extrabold text-herb-800">{{ faNumber(i + 1) }}</div>
                <div class="min-w-0 flex-1">
                    <h2 class="font-bold text-herb-900">{{ c.name }}</h2>
                    <p class="mt-1 text-xs text-herb-700">{{ c.slug }} · {{ faNumber(c.products_count) }} محصول</p>
                </div>
                <button type="button" class="min-h-11 rounded-full px-3 text-xs font-bold" :class="c.is_active ? 'bg-herb-50 text-herb-800' : 'bg-paper-100 text-herb-700'" :aria-label="`${c.is_active ? 'غیرفعال کردن' : 'فعال کردن'} دسته ${c.name}`" @click="toggle(c)">{{ c.is_active ? 'فعال' : 'غیرفعال' }}</button>
                <div class="flex items-center gap-1">
                    <button type="button" :disabled="i === 0" class="grid h-11 w-11 place-items-center rounded-lg border border-herb-100 text-herb-800 hover:bg-herb-50 disabled:opacity-30" :aria-label="`بالا بردن دسته ${c.name}`" @click="move(c, -1)">↑</button>
                    <button type="button" :disabled="i === categories.length - 1" class="grid h-11 w-11 place-items-center rounded-lg border border-herb-100 text-herb-800 hover:bg-herb-50 disabled:opacity-30" :aria-label="`پایین بردن دسته ${c.name}`" @click="move(c, 1)">↓</button>
                </div>
                <div class="flex items-center gap-2 border-t border-herb-100 pt-3 sm:border-t-0 sm:pt-0">
                    <Link :href="route('admin.categories.edit', c.id)" class="inline-flex min-h-11 items-center rounded-lg bg-herb-50 px-3 text-xs font-bold text-herb-800">ویرایش</Link>
                    <button type="button" class="min-h-11 rounded-lg px-2 text-xs font-bold text-anar-600" @click="destroy(c.id)">حذف</button>
                </div>
            </article>
        </div>
    </AdminLayout>
</template>
