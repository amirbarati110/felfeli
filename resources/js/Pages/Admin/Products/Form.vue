<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    product: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
});

const isEdit = !!props.product;

const form = useForm({
    sku: props.product?.sku ?? '',
    name: props.product?.name ?? '',
    category_id: props.product?.category_id ?? null,
    description: props.product?.description ?? '',
    price: props.product?.price ?? 0,
    compare_price: props.product?.compare_price ?? null,
    track_stock: props.product?.track_stock ?? false,
    stock_qty: props.product?.stock_qty ?? null,
    in_stock: props.product?.in_stock ?? true,
    is_active: props.product?.is_active ?? true,
    sort_order: props.product?.sort_order ?? 0,
    image: null,
});

function submit() {
    const opts = { forceFormData: true };
    if (isEdit) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(route('admin.products.update', props.product.id), opts);
    } else {
        form.post(route('admin.products.store'), opts);
    }
}
</script>

<template>
    <Head :title="isEdit ? 'ویرایش محصول' : 'محصول جدید'" />

    <AdminLayout>
        <div class="mb-4 flex items-center gap-2">
            <Link :href="route('admin.products.index')" class="text-sm text-brand-900/45">محصولات</Link>
            <span class="text-brand-900/30">/</span>
            <h1 class="text-xl font-extrabold">{{ isEdit ? form.name : 'محصول جدید' }}</h1>
        </div>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <label class="mb-1.5 block text-sm font-medium">نام کالا *</label>
                    <input v-model="form.name" class="h-11 w-full rounded-xl border-0 bg-cream-100 px-3 text-sm ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-tomato-600">{{ form.errors.name }}</p>

                    <label class="mt-3 mb-1.5 block text-sm font-medium">کد کالا / SKU * <span class="text-xs font-normal text-brand-900/40">(مبنای اتصال به باران)</span></label>
                    <input v-model="form.sku" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-cream-100 px-3 text-right text-sm ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400" />
                    <p v-if="form.errors.sku" class="mt-1 text-xs text-tomato-600">{{ form.errors.sku }}</p>

                    <label class="mt-3 mb-1.5 block text-sm font-medium">توضیحات</label>
                    <textarea v-model="form.description" rows="3" class="w-full rounded-xl border-0 bg-cream-100 p-3 text-sm ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400" />
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">قیمت (ریال) *</label>
                            <input v-model.number="form.price" type="number" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-cream-100 px-3 text-right text-sm ring-1 ring-black/5" />
                            <p v-if="form.errors.price" class="mt-1 text-xs text-tomato-600">{{ form.errors.price }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">قیمت قبل از تخفیف</label>
                            <input v-model.number="form.compare_price" type="number" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-cream-100 px-3 text-right text-sm ring-1 ring-black/5" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <label class="mb-1.5 block text-sm font-medium">تصویر</label>
                    <img v-if="product?.image_url" :src="product.image_url" class="mb-2 h-20 w-20 rounded-xl object-cover" />
                    <input type="file" accept="image/*" class="text-sm" @input="form.image = $event.target.files[0]" />
                    <p v-if="form.errors.image" class="mt-1 text-xs text-tomato-600">{{ form.errors.image }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-black/5">
                    <label class="mb-1.5 block text-sm font-medium">دسته‌بندی</label>
                    <select v-model="form.category_id" class="h-11 w-full rounded-xl border-0 bg-cream-100 px-3 text-sm ring-1 ring-black/5">
                        <option :value="null">بدون دسته</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>

                    <label class="mt-4 flex items-center justify-between text-sm font-medium">
                        فعال (نمایش در فروشگاه)
                        <input v-model="form.is_active" type="checkbox" class="rounded" />
                    </label>
                    <label class="mt-3 flex items-center justify-between text-sm font-medium">
                        موجود
                        <input v-model="form.in_stock" type="checkbox" class="rounded" />
                    </label>
                    <label class="mt-3 flex items-center justify-between text-sm font-medium">
                        کنترل موجودی با تعداد
                        <input v-model="form.track_stock" type="checkbox" class="rounded" />
                    </label>
                    <div v-if="form.track_stock" class="mt-2">
                        <input v-model.number="form.stock_qty" type="number" dir="ltr" placeholder="تعداد" class="h-10 w-full rounded-xl border-0 bg-cream-100 px-3 text-right text-sm ring-1 ring-black/5" />
                    </div>

                    <label class="mt-4 mb-1.5 block text-sm font-medium">ترتیب نمایش</label>
                    <input v-model.number="form.sort_order" type="number" dir="ltr" class="h-10 w-full rounded-xl border-0 bg-cream-100 px-3 text-right text-sm ring-1 ring-black/5" />
                </div>

                <button type="submit" :disabled="form.processing" class="w-full rounded-full bg-brand-500 py-3 text-sm font-extrabold text-white disabled:opacity-60">
                    {{ isEdit ? 'ذخیره تغییرات' : 'ساخت محصول' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
