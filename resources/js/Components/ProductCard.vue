<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Price from '@/Components/Price.vue';
import QtyStepper from '@/Components/QtyStepper.vue';

const props = defineProps({
    product: { type: Object, required: true },
});

const page = usePage();
const busy = ref(false);

const qty = computed(() => Number(page.props.cart?.lines?.[props.product.sku] ?? 0));
const inCart = computed(() => qty.value > 0);

const opts = {
    preserveScroll: true,
    preserveState: true,
    only: ['cart', 'flash'],
    onStart: () => (busy.value = true),
    onFinish: () => (busy.value = false),
};

function add() {
    if (!props.product.in_stock) return;
    router.post(route('cart.add'), { sku: props.product.sku }, opts);
}

function setQty(next) {
    router.patch(route('cart.update', props.product.sku), { qty: next }, opts);
}
</script>

<template>
    <article
        class="group relative flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-black/5 transition hover:ring-brand-300"
        :class="!product.in_stock && 'opacity-70'"
    >
        <div class="relative aspect-square overflow-hidden bg-cream-100">
            <img
                :src="product.image_url"
                :alt="product.name"
                loading="lazy"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                @error="(e) => (e.target.src = '/images/product-placeholder.svg')"
            />
            <span
                v-if="!product.in_stock"
                class="absolute end-2 top-2 rounded-full bg-brand-900/75 px-2 py-0.5 text-[11px] font-medium text-white backdrop-blur"
            >
                ناموجود
            </span>
        </div>

        <div class="flex flex-1 flex-col gap-2 p-3">
            <h3 class="line-clamp-2 min-h-[2.5em] text-[13px] font-medium leading-snug text-brand-900">
                {{ product.name }}
            </h3>

            <div class="mt-auto flex items-center justify-between gap-2">
                <Price :rial="product.price" :compare-rial="product.compare_price" size="sm" />

                <QtyStepper
                    v-if="inCart"
                    :model-value="qty"
                    :loading="busy"
                    size="sm"
                    @change="setQty"
                />
                <button
                    v-else
                    type="button"
                    :disabled="!product.in_stock || busy"
                    class="grid h-8 w-8 place-items-center rounded-full bg-brand-500 text-lg leading-none text-white shadow-sm transition active:scale-90 hover:bg-brand-600 disabled:cursor-not-allowed disabled:bg-brand-200"
                    aria-label="افزودن به سبد"
                    @click="add"
                >
                    +
                </button>
            </div>
        </div>
    </article>
</template>
