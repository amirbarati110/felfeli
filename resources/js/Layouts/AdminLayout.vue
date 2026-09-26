<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import FlashToaster from '@/Components/FlashToaster.vue';

const page = usePage();
const open = ref(false);

const navGroups = [
    { title: 'نمای کلی', items: [
        { label: 'داشبورد', route: 'admin.dashboard', match: /\/admin\/?$/ },
        { label: 'سفارش‌ها', route: 'admin.orders.index', match: /\/admin\/orders/ },
    ] },
    { title: 'مدیریت فروشگاه', items: [
        { label: 'محصولات', route: 'admin.products.index', match: /\/admin\/products/ },
        { label: 'دسته‌بندی‌ها', route: 'admin.categories.index', match: /\/admin\/categories/ },
        { label: 'درون‌ریزی کالا', route: 'admin.import.index', match: /\/admin\/import/ },
    ] },
    { title: 'سیستم', items: [
        { label: 'اتصال باران', route: 'admin.integration.index', match: /\/admin\/integration/ },
        { label: 'تنظیمات', route: 'admin.settings.edit', match: /\/admin\/settings/ },
    ] },
];

const loc = () => page.props.ziggy?.location ?? '';
const currentTitle = () => navGroups.flatMap((group) => group.items).find((item) => item.match.test(loc()))?.label ?? 'مدیریت فروشگاه';
</script>

<template>
    <div class="min-h-screen bg-[#f6f8f6] text-herb-900">
        <div class="flex">
            <!-- سایدبار -->
            <aside
                class="fixed inset-y-0 z-40 flex w-64 shrink-0 flex-col border-e border-herb-100 bg-white shadow-xl transition-transform lg:static lg:translate-x-0 lg:shadow-none"
                :class="open ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
            >
                <div class="flex h-20 items-center gap-2 border-b border-herb-100 px-5">
                    <span class="text-base font-extrabold text-herb-700">فلفلی <span class="text-herb-500">ساوه</span></span>
                    <span class="rounded-full bg-paper-100 px-2 py-0.5 text-[11px] font-bold text-herb-900/50">مدیریت</span>
                </div>
                <nav class="min-h-0 flex-1 space-y-5 overflow-y-auto p-3" aria-label="منوی مدیریت">
                    <div v-for="group in navGroups" :key="group.title">
                        <p class="px-3.5 pb-2 text-[11px] font-bold text-herb-700">{{ group.title }}</p>
                        <div class="space-y-1">
                            <Link
                                v-for="item in group.items" :key="item.route" :href="route(item.route)"
                                class="flex min-h-11 items-center justify-between rounded-xl px-3.5 text-sm font-semibold transition"
                                :class="item.match.test(loc()) ? 'bg-herb-50 text-herb-800 ring-1 ring-herb-100' : 'text-herb-800 hover:bg-paper-100'"
                                :aria-current="item.match.test(loc()) ? 'page' : undefined"
                                @click="open = false"
                            >
                                {{ item.label }}<span v-if="item.match.test(loc())" class="h-1.5 w-1.5 rounded-full bg-herb-600" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </nav>
                <div class="border-t border-herb-100 p-3">
                    <a :href="route('menu.index')" target="_blank" class="block rounded-xl px-3.5 py-2 text-xs font-medium text-herb-900/50 hover:bg-paper-100">
                        مشاهده فروشگاه ↗
                    </a>
                    <button type="button"
                        class="mt-1 block min-h-11 w-full rounded-xl px-3.5 py-2 text-right text-xs font-medium text-anar-600 hover:bg-anar-500/10"
                        @click="router.post(route('admin.logout'))"
                    >
                        خروج
                    </button>
                </div>
            </aside>

            <button v-if="open" type="button" class="fixed inset-0 z-30 bg-black/30 lg:hidden" aria-label="بستن منو" @click="open = false" />

            <!-- محتوا -->
            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-herb-100 bg-white/95 px-4 backdrop-blur sm:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" class="grid h-11 w-11 place-items-center rounded-xl text-herb-800 hover:bg-herb-50 lg:hidden" aria-label="باز کردن منوی مدیریت" :aria-expanded="open" @click="open = true">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                        </button>
                        <span class="text-sm font-extrabold text-herb-800">{{ currentTitle() }}</span>
                    </div>
                    <div class="rounded-full bg-herb-50 px-3 py-1.5 text-xs font-semibold text-herb-800">{{ page.props.auth?.user?.name }}</div>
                </header>

                <main class="mx-auto max-w-6xl p-4 sm:p-8">
                    <slot />
                </main>
            </div>
        </div>

        <FlashToaster />
    </div>
</template>
