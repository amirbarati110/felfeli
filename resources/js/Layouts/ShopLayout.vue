<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
import SearchBox from '@/Components/SearchBox.vue';
import FlashToaster from '@/Components/FlashToaster.vue';
import { faNumber, tomanValue } from '@/lib/format';

const props = defineProps({
    searchInitial: { type: String, default: '' },
    partialSearch: { type: Boolean, default: false },
    showSearch: { type: Boolean, default: true },
});

const page = usePage();
const cart = computed(() => page.props.cart ?? { count: 0, subtotal: 0 });
const currentRoute = computed(() => page.props.ziggy?.location ?? '');

const nav = [
    { label: 'خانه', route: 'menu.index', match: /\/$/, icon: 'home' },
    { label: 'سبد خرید', route: 'cart.show', match: /\/cart/, icon: 'cart' },
];
</script>

<template>
    <div class="flex min-h-screen flex-col bg-cream-100 pb-20 sm:pb-0">
        <!-- هدر -->
        <header class="sticky top-0 z-30 border-b border-black/5 bg-cream-50/90 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center gap-3 px-4 py-3">
                <Link :href="route('menu.index')" class="shrink-0">
                    <AppLogo class="hidden sm:inline-flex" />
                    <AppLogo compact class="sm:hidden" />
                </Link>

                <div v-if="showSearch" class="flex-1">
                    <SearchBox :initial="searchInitial" :partial="partialSearch" />
                </div>

                <Link
                    :href="route('cart.show')"
                    class="relative flex shrink-0 items-center gap-2 rounded-full bg-brand-500 px-3.5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-600"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 6h15l-1.5 9h-12z" /><circle cx="9" cy="20" r="1.5" /><circle cx="18" cy="20" r="1.5" /><path d="M6 6 5 3H2" />
                    </svg>
                    <span class="hidden sm:inline">سبد خرید</span>
                    <span
                        v-if="cart.count"
                        class="grid min-w-5 place-items-center rounded-full bg-lemon-400 px-1 text-xs font-extrabold text-brand-900"
                    >
                        {{ faNumber(cart.count) }}
                    </span>
                </Link>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-4">
            <slot />
        </main>

        <!-- نوار سبد چسبان (موبایل) -->
        <Transition
            enter-active-class="transition duration-200" leave-active-class="transition duration-150"
            enter-from-class="translate-y-full" leave-to-class="translate-y-full"
        >
            <Link
                v-if="cart.count && !/\/(cart|checkout)/.test(currentRoute)"
                :href="route('cart.show')"
                class="fixed inset-x-3 bottom-20 z-30 flex items-center justify-between rounded-2xl bg-brand-600 px-4 py-3 text-white shadow-xl sm:hidden"
            >
                <span class="flex items-center gap-2 text-sm font-bold">
                    <span class="grid h-6 min-w-6 place-items-center rounded-full bg-white/20 px-1 text-xs">{{ faNumber(cart.count) }}</span>
                    مشاهده سبد
                </span>
                <span class="text-sm font-extrabold">{{ tomanValue(cart.subtotal) }} تومان</span>
            </Link>
        </Transition>

        <!-- ناوبری پایین (موبایل) -->
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-black/5 bg-cream-50/95 backdrop-blur sm:hidden">
            <div class="mx-auto flex max-w-md items-stretch justify-around">
                <Link
                    v-for="item in nav" :key="item.route" :href="route(item.route)"
                    class="flex flex-1 flex-col items-center gap-1 py-2.5 text-[11px] font-medium transition"
                    :class="item.match.test(currentRoute) ? 'text-brand-600' : 'text-brand-900/50'"
                >
                    <svg v-if="item.icon === 'home'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11l8-7 8 7" /><path d="M6 10v9h12v-9" /></svg>
                    <span v-else class="relative">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z" /><circle cx="9" cy="20" r="1.5" /><circle cx="18" cy="20" r="1.5" /><path d="M6 6 5 3H2" /></svg>
                        <span v-if="cart.count" class="absolute -end-1.5 -top-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-tomato-500 px-1 text-[10px] font-bold text-white">{{ faNumber(cart.count) }}</span>
                    </span>
                    {{ item.label }}
                </Link>
            </div>
        </nav>

        <FlashToaster />
    </div>
</template>
