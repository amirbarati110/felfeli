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
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-extrabold">دسته‌بندی‌ها</h1>
            <Link :href="route('admin.categories.create')" class="rounded-full bg-herb-500 px-4 py-2 text-sm font-bold text-white">افزودن دسته</Link>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-black/5">
            <table class="w-full text-sm">
                <tbody>
                    <tr v-for="(c, i) in categories" :key="c.id" class="border-b border-black/5 last:border-0">
                        <td class="w-16 p-3">
                            <div class="flex flex-col text-herb-900/30">
                                <button :disabled="i === 0" class="hover:text-herb-600 disabled:opacity-20" @click="move(c, -1)">▲</button>
                                <button :disabled="i === categories.length - 1" class="hover:text-herb-600 disabled:opacity-20" @click="move(c, 1)">▼</button>
                            </div>
                        </td>
                        <td class="p-3 font-medium">{{ c.name }}</td>
                        <td class="p-3 text-herb-900/50">{{ c.slug }}</td>
                        <td class="p-3 text-herb-900/50">{{ faNumber(c.products_count) }} محصول</td>
                        <td class="p-3">
                            <button
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="c.is_active ? 'bg-herb-50 text-herb-700' : 'bg-black/5 text-herb-900/40'"
                                @click="toggle(c)"
                            >
                                {{ c.is_active ? 'فعال' : 'غیرفعال' }}
                            </button>
                        </td>
                        <td class="p-3 text-left">
                            <Link :href="route('admin.categories.edit', c.id)" class="text-xs font-medium text-herb-600">ویرایش</Link>
                            <button class="ms-2 text-xs font-medium text-anar-600" @click="destroy(c.id)">حذف</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
