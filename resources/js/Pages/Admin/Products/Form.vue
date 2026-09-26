<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    product: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
});

const isEdit = !!props.product;
const isBaran = props.product?.source === 'baran';

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
        <div class="mb-6 flex flex-wrap items-center gap-2">
            <Link :href="route('admin.products.index')" class="text-sm text-herb-900/45">محصولات</Link>
            <span class="text-herb-900/30">/</span>
            <h1 class="text-xl font-extrabold">{{ isEdit ? form.name : 'محصول جدید' }}</h1>
        </div>

        <p class="mb-5 text-sm text-herb-700">{{ isBaran ? 'نام، قیمت و موجودی این کالا از باران می‌آید؛ اینجا فقط عکس آن را دستی تغییر دهید.' : 'مشخصات، قیمت، موجودی و تصویر این کالا را دستی ویرایش کنید.' }}</p>

        <form v-if="isBaran" class="grid gap-5 lg:grid-cols-3" @submit.prevent="submit">
            <div class="rounded-2xl border border-herb-100 bg-white p-5 shadow-sm lg:col-span-2">
                <h2 class="text-base font-extrabold">عکس کالا</h2>
                <p class="mt-1 text-sm text-herb-700">عکس دستی با به‌روزرسانی باران جایگزین نمی‌شود.</p>
                <div class="my-5 flex items-center gap-4 rounded-xl bg-paper-50 p-3">
                    <img :src="product.image_url" :alt="`تصویر فعلی ${product.name}`" class="h-24 w-24 shrink-0 rounded-xl bg-white object-cover" />
                    <div class="min-w-0">
                        <p class="font-bold">{{ product.name }}</p>
                        <p class="mt-1 break-all text-xs text-herb-700" dir="ltr">{{ product.sku }}</p>
                    </div>
                </div>
                <label for="baran-product-image" class="mb-2 block text-sm font-bold">انتخاب عکس جدید</label>
                <input id="baran-product-image" type="file" accept="image/*" required class="block min-h-11 w-full text-base sm:text-sm" :aria-invalid="!!form.errors.image" aria-describedby="baran-image-help baran-image-error" @input="form.image = $event.target.files[0]" />
                <p id="baran-image-help" class="mt-2 text-xs text-herb-700">فایل تصویری تا ۴ مگابایت. پس از ذخیره، عکس جدید در فروشگاه نمایش داده می‌شود.</p>
                <p v-if="form.errors.image" id="baran-image-error" class="mt-2 text-sm text-anar-600">{{ form.errors.image }}</p>
            </div>
            <div>
                <button type="submit" :disabled="form.processing || !form.image" class="min-h-12 w-full rounded-xl bg-herb-700 px-5 py-3 text-sm font-extrabold text-white hover:bg-herb-800 disabled:opacity-60">
                    {{ form.processing ? 'در حال ذخیره…' : 'ذخیره عکس جدید' }}
                </button>
            </div>
        </form>

        <form v-else class="grid gap-5 lg:grid-cols-3" @submit.prevent="submit">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-2xl border border-herb-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-1 text-base font-extrabold">مشخصات اصلی</h2>
                    <p class="mb-5 text-xs text-herb-700">نام و کد کالا در فهرست مدیریت و هنگام ثبت سفارش استفاده می‌شوند.</p>
                    <label for="product-name" class="mb-1.5 block text-sm font-medium">نام کالا *</label>
                    <input id="product-name" v-model="form.name" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-base ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400 sm:text-sm" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-anar-600">{{ form.errors.name }}</p>

                    <label for="product-sku" class="mt-3 mb-1.5 block text-sm font-medium">کد کالا / SKU * <span class="text-xs font-normal text-herb-900/40">(مبنای اتصال به باران)</span></label>
                    <input id="product-sku" v-model="form.sku" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-base ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400 sm:text-sm" />
                    <p v-if="form.errors.sku" class="mt-1 text-xs text-anar-600">{{ form.errors.sku }}</p>

                    <label for="product-description" class="mt-3 mb-1.5 block text-sm font-medium">توضیحات</label>
                    <textarea id="product-description" v-model="form.description" rows="3" class="w-full rounded-xl border-0 bg-paper-100 p-3 text-base ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400 sm:text-sm" />
                </div>

                <div class="rounded-2xl border border-herb-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-1 text-base font-extrabold">قیمت‌گذاری</h2>
                    <p class="mb-5 text-xs text-herb-700">مبلغ را به ریال وارد کنید؛ در فروشگاه به تومان نمایش داده می‌شود.</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="product-price" class="mb-1.5 block text-sm font-medium">قیمت (ریال) *</label>
                            <input id="product-price" v-model.number="form.price" type="number" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-base ring-1 ring-black/5 sm:text-sm" />
                            <p v-if="form.errors.price" class="mt-1 text-xs text-anar-600">{{ form.errors.price }}</p>
                        </div>
                        <div>
                            <label for="product-compare-price" class="mb-1.5 block text-sm font-medium">قیمت قبل از تخفیف</label>
                            <input id="product-compare-price" v-model.number="form.compare_price" type="number" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-base ring-1 ring-black/5 sm:text-sm" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-herb-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-base font-extrabold">تصویر کالا</h2>
                    <label for="product-image" class="mb-1.5 block text-sm font-medium">آپلود عکس دستی</label>
                    <img v-if="product?.image_url" :src="product.image_url" :alt="`تصویر فعلی ${product.name}`" class="mb-2 h-20 w-20 rounded-xl object-cover" />
                    <input id="product-image" type="file" accept="image/*" class="w-full text-base sm:text-sm" @input="form.image = $event.target.files[0]" />
                    <p class="mt-2 text-xs text-herb-700">فایل تصویری تا ۴ مگابایت.</p>
                    <p v-if="form.errors.image" class="mt-1 text-xs text-anar-600">{{ form.errors.image }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-herb-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-base font-extrabold">نمایش و موجودی</h2>
                    <label for="product-category" class="mb-1.5 block text-sm font-medium">دسته‌بندی</label>
                    <select id="product-category" v-model="form.category_id" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-base ring-1 ring-black/5 sm:text-sm">
                        <option :value="null">بدون دسته</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>

                    <label class="mt-4 flex min-h-11 items-center justify-between text-sm font-medium">
                        فعال (نمایش در فروشگاه)
                        <input v-model="form.is_active" type="checkbox" class="rounded" />
                    </label>
                    <label class="mt-3 flex min-h-11 items-center justify-between text-sm font-medium">
                        موجود
                        <input v-model="form.in_stock" type="checkbox" class="rounded" />
                    </label>
                    <label class="mt-3 flex min-h-11 items-center justify-between text-sm font-medium">
                        کنترل موجودی با تعداد
                        <input v-model="form.track_stock" type="checkbox" class="rounded" />
                    </label>
                    <div v-if="form.track_stock" class="mt-2">
                        <label for="product-stock-qty" class="mb-1.5 block text-sm font-medium">تعداد موجود</label>
                        <input id="product-stock-qty" v-model.number="form.stock_qty" type="number" dir="ltr" placeholder="تعداد" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-base ring-1 ring-black/5 sm:text-sm" />
                    </div>

                    <label for="product-sort-order" class="mt-4 mb-1.5 block text-sm font-medium">ترتیب نمایش</label>
                    <input id="product-sort-order" v-model.number="form.sort_order" type="number" dir="ltr" class="h-11 w-full rounded-xl border-0 bg-paper-100 px-3 text-right text-base ring-1 ring-black/5 sm:text-sm" />
                </div>

                <button type="submit" :disabled="form.processing" class="min-h-12 w-full rounded-xl bg-herb-700 py-3 text-sm font-extrabold text-white hover:bg-herb-800 disabled:opacity-60">
                    {{ form.processing ? 'در حال ذخیره…' : isEdit ? 'ذخیره تغییرات' : 'ساخت محصول' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
