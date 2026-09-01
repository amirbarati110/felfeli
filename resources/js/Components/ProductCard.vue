<script setup>
import { computed } from 'vue';
import Price from '@/Components/Price.vue';
import QtyStepper from '@/Components/QtyStepper.vue';
import { useCart } from '@/lib/cart';

const props = defineProps({
    product: { type: Object, required: true },
});

const cart = useCart();
const qty = computed(() => cart.qtyOf(props.product.sku));
const inCart = computed(() => qty.value > 0);
</script>

<template>
    <article
        class="group relative flex flex-col overflow-hidden rounded-card bg-white ring-1 ring-kraft-200/70 transition duration-200 hover:ring-herb-300 hover:shadow-[0_6px_24px_-12px_rgba(18,51,34,0.25)]"
        :class="!product.in_stock && 'opacity-80'"
    >
        <div class="relative aspect-square overflow-hidden bg-paper-200">
            <img
                :src="product.image_url"
                :alt="product.name"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]"
                :class="!product.in_stock && 'grayscale'"
                @error="(e) => (e.target.src = '/images/product-placeholder.svg')"
            />
            <span
                v-if="!product.in_stock"
                class="absolute end-0 top-3 rounded-s-full bg-anar-500 py-1 ps-3 pe-2.5 text-[11px] font-bold text-white shadow"
            >
                ناموجود
            </span>
        </div>

        <div class="flex flex-1 flex-col gap-2.5 p-3">
            <h3 class="line-clamp-2 min-h-[2.6em] text-[13px] font-semibold leading-snug text-herb-900">
                {{ product.name }}
            </h3>

            <div class="mt-auto flex items-end justify-between gap-2">
                <div class="min-w-0">
                    <Price :rial="product.price" :compare-rial="product.compare_price" size="sm" />
                </div>

                <QtyStepper
                    v-if="inCart"
                    :model-value="qty"
                    size="sm"
                    @change="(q) => cart.setQty(product.sku, q)"
                />
                <button
                    v-else
                    type="button"
                    :disabled="!product.in_stock"
                    class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-zaffron-400 text-xl leading-none text-herb-900 shadow-sm transition hover:bg-zaffron-500 active:scale-90 disabled:cursor-not-allowed disabled:bg-kraft-200 disabled:text-herb-900/30"
                    aria-label="افزودن به سبد"
                    @click="cart.add(product.sku)"
                >
                    <span class="-mt-0.5">+</span>
                </button>
            </div>
        </div>
    </article>
</template>
