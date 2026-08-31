<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import FlashToaster from '@/Components/FlashToaster.vue';

const page = usePage();
const open = ref(false);

const nav = [
    { label: 'داشبورد', route: 'admin.dashboard', match: /\/admin$/ },
    { label: 'سفارش‌ها', route: 'admin.orders.index', match: /\/admin\/orders/ },
    { label: 'محصولات', route: 'admin.products.index', match: /\/admin\/products/ },
    { label: 'دسته‌بندی‌ها', route: 'admin.categories.index', match: /\/admin\/categories/ },
    { label: 'درون‌ریزی اکسل', route: 'admin.import.index', match: /\/admin\/import/ },
    { label: 'اتصال باران', route: 'admin.integration.index', match: /\/admin\/integration/ },
    { label: 'تنظیمات', route: 'admin.settings.edit', match: /\/admin\/settings/ },
];

const loc = () => page.props.ziggy?.location ?? '';
</script>

<template>
    <div class="min-h-screen bg-cream-100 text-brand-900">
        <div class="flex">
            <!-- سایدبار -->
            <aside
                class="fixed inset-y-0 z-40 w-60 shrink-0 border-e border-black/5 bg-white transition-transform lg:static lg:translate-x-0"
                :class="open ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
            >
                <div class="flex h-16 items-center gap-2 border-b border-black/5 px-5">
                    <span class="text-base font-extrabold text-brand-700">فلفلی <span class="text-brand-500">ساوه</span></span>
                    <span class="rounded-full bg-cream-100 px-2 py-0.5 text-[11px] font-bold text-brand-900/50">مدیریت</span>
                </div>
                <nav class="space-y-1 p-3">
                    <Link
                        v-for="item in nav" :key="item.route" :href="route(item.route)"
                        class="block rounded-xl px-3.5 py-2.5 text-sm font-medium transition"
                        :class="item.match.test(loc()) ? 'bg-brand-50 text-brand-700' : 'text-brand-900/65 hover:bg-cream-100'"
                        @click="open = false"
                    >
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="absolute inset-x-0 bottom-0 border-t border-black/5 p-3">
                    <a :href="route('menu.index')" target="_blank" class="block rounded-xl px-3.5 py-2 text-xs font-medium text-brand-900/50 hover:bg-cream-100">
                        مشاهده فروشگاه ↗
                    </a>
                    <button
                        class="mt-1 block w-full rounded-xl px-3.5 py-2 text-right text-xs font-medium text-tomato-600 hover:bg-tomato-500/10"
                        @click="router.post(route('admin.logout'))"
                    >
                        خروج
                    </button>
                </div>
            </aside>

            <div v-if="open" class="fixed inset-0 z-30 bg-black/20 lg:hidden" @click="open = false" />

            <!-- محتوا -->
            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-black/5 bg-cream-50/90 px-5 backdrop-blur">
                    <button class="rounded-lg p-2 text-brand-900/60 lg:hidden" @click="open = true">☰</button>
                    <div class="text-sm font-medium text-brand-900/60">
                        {{ page.props.auth?.user?.name }}
                    </div>
                </header>

                <main class="mx-auto max-w-6xl p-5">
                    <slot />
                </main>
            </div>
        </div>

        <FlashToaster />
    </div>
</template>
