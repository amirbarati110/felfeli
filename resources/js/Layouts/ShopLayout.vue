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
const store = computed(() => page.props.store ?? {});
const isMenu = computed(() => /\/menu(?:\?|$)/.test(loc.value));
</script>

<template>
    <div class="flex min-h-dvh flex-col bg-white sm:pb-0" :class="cart.count.value && !onCartFlow ? 'pb-[calc(10rem+env(safe-area-inset-bottom))]' : 'pb-[calc(5rem+env(safe-area-inset-bottom))]'">
        <header class="sticky top-0 z-30 border-b border-kraft-200 bg-white/95 shadow-[0_3px_18px_-14px_rgba(18,51,34,0.3)] backdrop-blur-md">
            <div class="hidden border-b border-kraft-200/60 bg-herb-800 py-1.5 text-center text-xs font-medium text-white sm:block">
                محصولات فلفلی ساوه، با قیمت روشن و سفارش آسان
            </div>
            <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-3 px-4 py-3 sm:flex-nowrap sm:gap-6">
                <Link :href="route('menu.index')" class="order-1 shrink-0 rounded-lg focus-visible:outline-2 sm:order-none" aria-label="فلفلی ساوه، صفحه محصولات">
                    <AppLogo class="h-10 sm:h-11" />
                </Link>

                <nav class="order-2 hidden items-center gap-1 lg:order-none lg:flex" aria-label="ناوبری فروشگاه">
                    <Link :href="route('home')" class="rounded-xl px-3 py-2 text-sm font-semibold text-herb-800 transition hover:bg-herb-50">خانه</Link>
                    <Link :href="route('menu.index')" class="rounded-xl px-3 py-2 text-sm font-semibold transition" :class="isMenu ? 'bg-herb-50 text-herb-700' : 'text-herb-800 hover:bg-herb-50'">محصولات</Link>
                </nav>

                <div v-if="showSearch" class="order-3 w-full min-w-0 sm:order-none sm:flex-1">
                    <SearchBox :initial="searchInitial" :partial="partialSearch" />
                </div>

                <Link
                    :href="route('cart.show')"
                    class="relative order-2 ms-auto flex min-h-11 shrink-0 items-center gap-2 rounded-full bg-herb-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-herb-800 sm:order-none sm:ms-0"
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

        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6">
            <slot />
        </main>

        <footer class="mt-12 border-t border-kraft-200 bg-paper-50 text-herb-900">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-3">
                <div>
                    <AppLogo class="h-12" />
                    <p class="mt-3 max-w-xs text-sm leading-7 text-herb-800">{{ store.about || 'سبزی، ادویه، ترشی و محصولات تازه فلفلی ساوه؛ قیمت‌ها را ببینید و برای سفارش انتخاب کنید.' }}</p>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold">دسترسی سریع</h2>
                    <div class="mt-3 flex flex-col items-start gap-2 text-sm text-herb-700">
                        <Link :href="route('menu.index')" class="hover:text-herb-900 hover:underline">محصولات و قیمت‌ها</Link>
                        <Link :href="route('cart.show')" class="hover:text-herb-900 hover:underline">سبد سفارش</Link>
                    </div>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold">ارتباط با فروشگاه</h2>
                    <a v-if="store.phone" :href="`tel:${store.phone}`" dir="ltr" class="mt-3 inline-block text-sm font-bold text-herb-700 hover:underline">{{ store.phone }}</a>
                    <p v-else class="mt-3 text-sm text-herb-700">بعد از ثبت سفارش با شما تماس می‌گیریم.</p>
                    <p v-if="store.work_time" class="mt-2 whitespace-pre-line text-xs leading-6 text-herb-700">{{ store.work_time }}</p>
                </div>
            </div>
            <div class="border-t border-kraft-200/70 px-4 py-3 text-center text-xs text-herb-700">فلفلی ساوه · مشاهده محصولات و ثبت سفارش بدون پرداخت آنلاین</div>
        </footer>

        <!-- نوار سبدِ چسبان (موبایل) -->
        <Transition
            enter-active-class="transition duration-250" enter-from-class="translate-y-full"
            leave-active-class="transition duration-200" leave-to-class="translate-y-full"
        >
            <Link
                v-if="cart.count.value && !onCartFlow"
                :href="route('cart.show')"
                class="fixed inset-x-3 bottom-[calc(4.25rem+env(safe-area-inset-bottom))] z-30 flex min-h-12 items-center justify-between rounded-2xl bg-herb-700 px-4 py-3 text-white shadow-xl shadow-herb-900/20 sm:hidden"
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
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-kraft-200/70 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur-md sm:hidden" aria-label="ناوبری موبایل">
            <div class="mx-auto flex max-w-md items-stretch">
                <Link
                    :href="route('menu.index')"
                    class="flex min-h-16 flex-1 flex-col items-center justify-center gap-1 py-2 text-xs font-semibold transition"
                    :class="isMenu && !onCartFlow ? 'text-herb-700' : 'text-herb-900/65'"
                    :aria-current="isMenu && !onCartFlow ? 'page' : undefined"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M4 12h16M4 17h10" /></svg>
                    محصولات
                </Link>
                <Link
                    :href="route('cart.show')"
                    class="flex min-h-16 flex-1 flex-col items-center justify-center gap-1 py-2 text-xs font-semibold transition"
                    :class="onCartFlow ? 'text-herb-700' : 'text-herb-900/65'"
                    :aria-current="onCartFlow ? 'page' : undefined"
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
