<script setup>
import { computed, reactive, ref, watch } from 'vue';
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
const filtersOpen = ref(false);
const hasFilters = computed(() => Object.values(f).some((value) => value !== '' && value !== null));
const extraFilterCount = computed(() => [f.category, f.stock, f.status].filter(Boolean).length);
watch(f, () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(route('admin.products.index'), { ...f }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
});

function clearFilters() {
    Object.assign(f, { q: '', category: '', stock: '', status: '' });
}

function destroy(id) {
    if (confirm('این محصول غیرفعال شود؟')) router.delete(route('admin.products.destroy', id), { preserveScroll: true });
}
</script>

<template>
    <Head title="محصولات" />

    <AdminLayout>
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="mb-1 text-xs font-semibold text-herb-700">مدیریت کالاها</p>
                <h1 class="text-2xl font-extrabold">محصولات</h1>
                <p class="mt-1 text-sm text-herb-700">{{ faNumber(products.total) }} کالا در این فهرست؛ قیمت و وضعیت نمایش را از همین‌جا مدیریت کنید.</p>
            </div>
            <Link :href="route('admin.products.create')" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-herb-700 px-5 text-sm font-bold text-white transition hover:bg-herb-800 sm:w-auto">+ افزودن محصول</Link>
        </div>

        <div class="mb-5 rounded-2xl border border-herb-100 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-sm font-extrabold">پیدا کردن کالا</h2>
                <button v-if="hasFilters" type="button" class="min-h-11 text-xs font-bold text-herb-700 hover:underline" @click="clearFilters">پاک کردن فیلترها</button>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <label class="block text-sm font-semibold text-herb-800">نام یا کد کالا
            <input v-model="f.q" type="search" placeholder="مثلاً سبزی یا کد کالا" class="mt-1.5 h-11 w-full rounded-xl border border-herb-100 bg-white px-3 text-base font-medium focus:border-herb-500 focus:outline-none sm:text-sm" /></label>
            <button type="button" class="flex min-h-11 items-center justify-between rounded-xl border border-herb-100 bg-paper-50 px-3 text-sm font-bold text-herb-800 sm:hidden" :aria-expanded="filtersOpen" aria-controls="product-extra-filters" @click="filtersOpen = !filtersOpen">
                فیلترهای بیشتر <span v-if="extraFilterCount">({{ faNumber(extraFilterCount) }})</span><span aria-hidden="true">{{ filtersOpen ? '−' : '+' }}</span>
            </button>
            <div id="product-extra-filters" class="gap-3" :class="filtersOpen ? 'grid sm:contents' : 'hidden sm:contents'">
            <label class="block text-xs font-semibold text-herb-800">دسته‌بندی
            <select v-model="f.category" class="mt-1.5 h-11 w-full rounded-xl border border-herb-100 bg-white px-3 text-base focus:border-herb-500 focus:outline-none sm:text-sm">
                <option value="">همه دسته‌ها</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select></label>
            <label class="block text-xs font-semibold text-herb-800">موجودی
            <select v-model="f.stock" class="mt-1.5 h-11 w-full rounded-xl border border-herb-100 bg-white px-3 text-base focus:border-herb-500 focus:outline-none sm:text-sm">
                <option value="">موجودی: همه</option><option value="in">موجود</option><option value="out">ناموجود</option>
            </select></label>
            <label class="block text-xs font-semibold text-herb-800">نمایش در فروشگاه
            <select v-model="f.status" class="mt-1.5 h-11 w-full rounded-xl border border-herb-100 bg-white px-3 text-base focus:border-herb-500 focus:outline-none sm:text-sm">
                <option value="">وضعیت: همه</option><option value="active">فعال</option><option value="inactive">غیرفعال</option>
            </select></label>
            </div>
            </div>
        </div>

        <div v-if="!products.data.length" class="rounded-2xl border border-herb-100 bg-white px-6 py-12 text-center">
            <p class="font-bold">کالایی پیدا نشد</p>
            <p class="mt-2 text-sm text-herb-700">فیلترها را پاک کنید یا محصول تازه اضافه کنید.</p>
            <button v-if="hasFilters" type="button" class="mt-4 min-h-11 rounded-xl bg-herb-50 px-4 py-2 text-sm font-bold text-herb-800" @click="clearFilters">نمایش همه محصولات</button>
        </div>

        <div v-else class="grid gap-3 md:hidden">
            <article v-for="p in products.data" :key="p.id" class="rounded-2xl border border-herb-100 bg-white p-4 shadow-sm">
                <div class="flex gap-3">
                    <img :src="p.image_url" :alt="p.name" class="h-16 w-16 shrink-0 rounded-xl bg-paper-100 object-cover" @error="(e) => (e.target.src = '/images/product-placeholder.svg')" />
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate font-bold">{{ p.name }}</h2>
                        <p class="mt-1 text-xs text-herb-700">کد {{ p.sku }} · {{ p.category ?? 'بدون دسته' }}</p>
                        <p class="mt-1 text-base font-extrabold text-herb-800">{{ tomanValue(p.price) }} <span class="text-xs font-medium">تومان</span></p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between border-t border-herb-100 pt-3 text-xs">
                    <span :class="p.in_stock ? 'text-herb-700' : 'text-anar-600'">{{ p.in_stock ? 'موجود' : 'ناموجود' }} · {{ p.is_active ? 'فعال' : 'غیرفعال' }}</span>
                    <div class="flex gap-2"><Link :href="route('admin.products.edit', p.id)" class="inline-flex min-h-11 items-center rounded-lg bg-herb-50 px-3 py-2 font-bold text-herb-800">ویرایش</Link><button type="button" class="min-h-11 rounded-lg px-2 font-bold text-anar-600" @click="destroy(p.id)">غیرفعال</button></div>
                </div>
            </article>
        </div>

        <div v-if="products.data.length" class="hidden overflow-x-auto rounded-2xl border border-herb-100 bg-white shadow-sm md:block">
            <table class="w-full min-w-[680px] text-sm">
                <thead class="border-b border-black/5 text-xs text-herb-900/65">
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
                        <td class="p-3 text-herb-900/65">{{ p.sku }}</td>
                        <td class="p-3 text-herb-900/65">{{ p.category ?? '—' }}</td>
                        <td class="p-3">{{ tomanValue(p.price) }}</td>
                        <td class="p-3">
                            <span :class="p.in_stock ? 'text-herb-600' : 'text-anar-600'">
                                {{ p.in_stock ? 'موجود' : 'ناموجود' }}<template v-if="p.stock_qty != null"> · {{ faNumber(p.stock_qty) }}</template>
                            </span>
                        </td>
                        <td class="p-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="p.is_active ? 'bg-herb-50 text-herb-700' : 'bg-black/5 text-herb-900/65'">
                                {{ p.is_active ? 'فعال' : 'غیرفعال' }}
                            </span>
                            <span v-if="p.source" class="ms-1 text-[10px] text-herb-900/65">{{ p.source }}</span>
                        </td>
                        <td class="whitespace-nowrap p-3 text-left">
                            <Link :href="route('admin.products.edit', p.id)" class="text-xs font-medium text-herb-600">ویرایش</Link>
                            <button class="ms-2 text-xs font-medium text-anar-600" @click="destroy(p.id)">غیرفعال</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="products.last_page > 1" class="mt-4 flex items-center justify-between gap-2 sm:hidden" aria-label="صفحه‌های محصولات مدیریت">
            <button type="button" :disabled="!products.prev_page_url" class="min-h-11 rounded-xl bg-white px-4 text-sm font-bold text-herb-800 ring-1 ring-herb-100 disabled:opacity-40" @click="products.prev_page_url && router.get(products.prev_page_url, {}, { preserveState: true, preserveScroll: true })">قبلی</button>
            <span class="text-sm font-semibold">{{ faNumber(products.current_page) }} از {{ faNumber(products.last_page) }}</span>
            <button type="button" :disabled="!products.next_page_url" class="min-h-11 rounded-xl bg-white px-4 text-sm font-bold text-herb-800 ring-1 ring-herb-100 disabled:opacity-40" @click="products.next_page_url && router.get(products.next_page_url, {}, { preserveState: true, preserveScroll: true })">بعدی</button>
        </div>
        <div v-if="products.last_page > 1" class="mt-4 hidden flex-wrap justify-center gap-1.5 sm:flex">
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
