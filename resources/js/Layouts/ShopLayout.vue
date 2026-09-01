<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
import SearchBox from '@/Components/SearchBox.vue';
import FlashToaster from '@/Components/FlashToaster.vue';
import { useCart } from '@/lib/cart';
import { faNumber, tomanValue } from '@/lib/format';

const props = defineProps({
    searchInitial: { type: String, default: '' },
    partialSearch: { type: Boolean, default: false },
    showSearch: { type: Boolean, default: true },
});

const page = usePage();
const cart = useCart();
const loc = computed(() => page.props.ziggy?.location ?? '');
const onCartFlow = computed(() => /\/(cart|checkout)/.test(loc.value));
</script>

<template>
    <div class="flex min-h-screen flex-col bg-paper-100 pb-[4.5rem] sm:pb-0">
        <header class="sticky top-0 z-30 border-b border-kraft-200/70 bg-paper-50/85 backdrop-blur-md">
            <div class="mx-auto flex max-w-6xl items-center gap-3 px-4 py-2.5">
                <Link :href="route('menu.index')" class="shrink-0" aria-label="فلفلی ساوه">
                    <AppLogo class="h-8 sm:h-9" />
                </Link>

                <div v-if="showSearch" class="min-w-0 flex-1">
                    <SearchBox :initial="searchInitial" :partial="partialSearch" />
                </div>

                <Link
                    :href="route('cart.show')"
                    class="relative flex shrink-0 items-center gap-2 rounded-full bg-herb-600 px-3 py-2.5 text-sm font-bold text-white transition hover:bg-herb-700"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 7h13l-1.4 9.5a2 2 0 0 1-2 1.7H9.4a2 2 0 0 1-2-1.7L6 4H3" /><circle cx="10" cy="20" r="1" /><circle cx="17" cy="20" r="1" />
                    </svg>
                    <span class="hidden sm:inline">سبد</span>
                    <Transition
                        enter-active-class="transition duration-150" enter-from-class="scale-0"
                        leave-active-class="transition duration-150" leave-to-class="scale-0"
                    >
                        <span
                            v-if="cart.count.value"
                            class="grid min-w-5 place-items-center rounded-full bg-zaffron-400 px-1 text-xs font-extrabold text-herb-900"
                        >
                            {{ faNumber(cart.count.value) }}
                        </span>
                    </Transition>
                </Link>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-4">
            <slot />
        </main>

        <!-- نوار سبدِ چسبان (موبایل) -->
        <Transition
            enter-active-class="transition duration-250" enter-from-class="translate-y-full"
            leave-active-class="transition duration-200" leave-to-class="translate-y-full"
        >
            <Link
                v-if="cart.count.value && !onCartFlow"
                :href="route('cart.show')"
                class="fixed inset-x-3 bottom-[4.25rem] z-30 flex items-center justify-between rounded-2xl bg-herb-700 px-4 py-3 text-white shadow-xl shadow-herb-900/20 sm:hidden"
            >
                <span class="flex items-center gap-2 text-sm font-bold">
                    <span class="grid h-6 min-w-6 place-items-center rounded-full bg-white/20 px-1 text-xs">{{ faNumber(cart.count.value) }}</span>
                    مشاهده سبد
                </span>
                <span class="flex items-baseline gap-1 text-sm font-extrabold">
                    {{ tomanValue(cart.subtotal.value) }}<span class="text-[11px] opacity-70">تومان</span>
                </span>
            </Link>
        </Transition>

        <!-- ناوبری پایین (موبایل) -->
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-kraft-200/70 bg-paper-50/95 backdrop-blur-md sm:hidden">
            <div class="mx-auto flex max-w-md items-stretch">
                <Link
                    :href="route('menu.index')"
                    class="flex flex-1 flex-col items-center gap-1 py-2.5 text-[11px] font-semibold transition"
                    :class="/\/menu|localhost:\d+\/?$|8010\/?$/.test(loc) && !onCartFlow ? 'text-herb-700' : 'text-herb-900/45'"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h16M4 17h10" /></svg>
                    منو
                </Link>
                <Link
                    :href="route('cart.show')"
                    class="flex flex-1 flex-col items-center gap-1 py-2.5 text-[11px] font-semibold transition"
                    :class="onCartFlow ? 'text-herb-700' : 'text-herb-900/45'"
                >
                    <span class="relative">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h13l-1.4 9.5a2 2 0 0 1-2 1.7H9.4a2 2 0 0 1-2-1.7L6 4H3" /><circle cx="10" cy="20" r="1" /><circle cx="17" cy="20" r="1" /></svg>
                        <span v-if="cart.count.value" class="absolute -end-2 -top-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-anar-500 px-1 text-[10px] font-bold text-white">{{ faNumber(cart.count.value) }}</span>
                    </span>
                    سبد خرید
                </Link>
            </div>
        </nav>

        <FlashToaster />
    </div>
</template>
