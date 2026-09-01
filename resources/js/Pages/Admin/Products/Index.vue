<script setup>
import { reactive, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { faNumber, tomanValue } from '@/lib/format';

const props = defineProps({
    products: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const f = reactive({
    q: props.filters.q ?? '',
    category: props.filters.category ?? '',
    stock: props.filters.stock ?? '',
    status: props.filters.status ?? '',
});

let t = null;
watch(f, () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(route('admin.products.index'), { ...f }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
});

function destroy(id) {
    if (confirm('این محصول غیرفعال شود؟')) router.delete(route('admin.products.destroy', id), { preserveScroll: true });
}
</script>

<template>
    <Head title="محصولات" />

    <AdminLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-extrabold">محصولات <span class="text-sm font-medium text-herb-900/40">({{ faNumber(products.total) }})</span></h1>
            <Link :href="route('admin.products.create')" class="rounded-full bg-herb-500 px-4 py-2 text-sm font-bold text-white">افزودن محصول</Link>
        </div>

        <div class="mb-3 grid gap-2 sm:grid-cols-4">
            <input v-model="f.q" placeholder="جستجو نام یا کد کالا…" class="h-10 rounded-xl border-0 bg-white px-3 text-sm ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400" />
            <select v-model="f.category" class="h-10 rounded-xl border-0 bg-white px-3 text-sm ring-1 ring-black/5">
                <option value="">همه دسته‌ها</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <select v-model="f.stock" class="h-10 rounded-xl border-0 bg-white px-3 text-sm ring-1 ring-black/5">
                <option value="">موجودی: همه</option><option value="in">موجود</option><option value="out">ناموجود</option>
            </select>
            <select v-model="f.status" class="h-10 rounded-xl border-0 bg-white px-3 text-sm ring-1 ring-black/5">
                <option value="">وضعیت: همه</option><option value="active">فعال</option><option value="inactive">غیرفعال</option>
            </select>
        </div>

        <div class="overflow-x-auto rounded-2xl bg-white ring-1 ring-black/5">
            <table class="w-full min-w-[680px] text-sm">
                <thead class="border-b border-black/5 text-xs text-herb-900/50">
                    <tr>
                        <th class="p-3 text-right font-medium">کالا</th>
                        <th class="p-3 text-right font-medium">کد</th>
                        <th class="p-3 text-right font-medium">دسته</th>
                        <th class="p-3 text-right font-medium">قیمت (ت)</th>
                        <th class="p-3 text-right font-medium">موجودی</th>
                        <th class="p-3 text-right font-medium">وضعیت</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in products.data" :key="p.id" class="border-b border-black/5 last:border-0">
                        <td class="flex items-center gap-2 p-3">
                            <img :src="p.image_url" class="h-9 w-9 rounded-lg object-cover" @error="(e)=>e.target.src='/images/product-placeholder.svg'" />
                            <span class="font-medium">{{ p.name }}</span>
                        </td>
                        <td class="p-3 text-herb-900/60">{{ p.sku }}</td>
                        <td class="p-3 text-herb-900/60">{{ p.category ?? '—' }}</td>
                        <td class="p-3">{{ tomanValue(p.price) }}</td>
                        <td class="p-3">
                            <span :class="p.in_stock ? 'text-herb-600' : 'text-anar-600'">
                                {{ p.in_stock ? 'موجود' : 'ناموجود' }}<template v-if="p.stock_qty != null"> · {{ faNumber(p.stock_qty) }}</template>
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="p.is_active ? 'bg-herb-50 text-herb-700' : 'bg-black/5 text-herb-900/40'">
                                {{ p.is_active ? 'فعال' : 'غیرفعال' }}
                            </span>
                            <span v-if="p.source" class="ms-1 text-[10px] text-herb-900/30">{{ p.source }}</span>
                        </td>
                        <td class="whitespace-nowrap p-3 text-left">
                            <Link :href="route('admin.products.edit', p.id)" class="text-xs font-medium text-herb-600">ویرایش</Link>
                            <button class="ms-2 text-xs font-medium text-anar-600" @click="destroy(p.id)">غیرفعال</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="products.last_page > 1" class="mt-4 flex flex-wrap justify-center gap-1.5">
            <button
                v-for="l in products.links" :key="l.label" :disabled="!l.url"
                class="min-w-9 rounded-lg px-3 py-1.5 text-sm disabled:opacity-30"
                :class="l.active ? 'bg-herb-500 text-white' : 'bg-white ring-1 ring-black/5'"
                @click="l.url && router.get(l.url, {}, { preserveState: true, preserveScroll: true })"
                v-html="l.label"
            />
        </div>
    </AdminLayout>
</template>
