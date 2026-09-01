<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { toFaDigits } from '@/lib/format';

const props = defineProps({
    store: { type: Object, required: true },
});

const digits = (s) => (s ? String(s).replace(/^0/, '') : '');

const links = computed(() => {
    const out = [];
    if (props.store.phone) {
        out.push({ label: 'تماس', href: `tel:${props.store.phone}`, icon: 'phone' });
        out.push({ label: 'واتساپ', href: `https://wa.me/98${digits(props.store.phone)}`, icon: 'whatsapp' });
    }
    if (props.store.instagram) out.push({ label: 'اینستاگرام', href: props.store.instagram, icon: 'instagram' });
    if (props.store.location_link) out.push({ label: 'نقشه فروشگاه', href: props.store.location_link, icon: 'pin' });
    return out;
});
</script>

<template>
    <Head :title="store.name" />

    <div class="bg-paper-grain relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-5 py-12">
        <!-- امضای طرح: شاخه‌ی سبزیِ خطی که از گوشه بیرون می‌زند -->
        <svg class="pointer-events-none absolute -end-16 -top-10 h-72 w-72 text-herb-200/60" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M100 190C100 120 70 60 20 40M100 150c-30-4-52-24-60-56M100 120c22-2 40-16 46-40M100 96c-24-2-42-18-46-44M100 74c18 0 34-12 40-32" stroke-linecap="round" />
        </svg>
        <svg class="pointer-events-none absolute -bottom-16 -start-16 h-64 w-64 rotate-180 text-zaffron-300/50" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M100 190C100 120 70 60 20 40M100 150c-30-4-52-24-60-56M100 120c22-2 40-16 46-40" stroke-linecap="round" />
        </svg>

        <div class="relative w-full max-w-sm text-center">
            <img
                src="/images/brand/felfeli-logo-full.png" alt="فلفلی ساوه"
                class="mx-auto h-28 w-auto drop-shadow-sm"
            />

            <p class="mt-6 text-[15px] font-medium leading-relaxed text-herb-800">
                {{ store.about || 'سبزی تازه، خشک و سرخ‌شده، ادویه، ترشی و روغن — مستقیم از فلفلی ساوه.' }}
            </p>

            <Link
                :href="route('menu.index')"
                class="group mt-8 flex w-full items-center justify-center gap-2.5 rounded-2xl bg-herb-600 py-4 text-lg font-extrabold text-white shadow-xl shadow-herb-600/25 transition hover:bg-herb-700 active:scale-[.98]"
            >
                مشاهده منو
                <svg class="h-5 w-5 transition group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 5 6 12l7 7M6 12h13" />
                </svg>
            </Link>

            <div v-if="links.length" class="mt-6 flex flex-wrap justify-center gap-2">
                <a
                    v-for="l in links" :key="l.label" :href="l.href" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-full border border-kraft-200 bg-white/70 px-3.5 py-2 text-xs font-semibold text-herb-700 backdrop-blur transition hover:border-herb-300 hover:bg-white"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path v-if="l.icon === 'phone'" d="M6 3h3l2 5-2 1c1 3 3 5 6 6l1-2 5 2v3a2 2 0 0 1-2 2A17 17 0 0 1 4 5a2 2 0 0 1 2-2z" />
                        <template v-else-if="l.icon === 'whatsapp'"><path d="M12 3a9 9 0 0 0-7.7 13.6L3 21l4.6-1.2A9 9 0 1 0 12 3z" /><path d="M8.5 8.5c-.3 1 0 2.2.8 3.2a8 8 0 0 0 3.5 3c1 .4 2 .6 2.7-.2" /></template>
                        <template v-else-if="l.icon === 'instagram'"><rect x="3.5" y="3.5" width="17" height="17" rx="5" /><circle cx="12" cy="12" r="4" /><circle cx="17" cy="7" r="1" fill="currentColor" /></template>
                        <template v-else><path d="M12 21s-6.5-5.7-6.5-11A6.5 6.5 0 0 1 18.5 10c0 5.3-6.5 11-6.5 11z" /><circle cx="12" cy="10" r="2.3" /></template>
                    </svg>
                    {{ l.label }}
                </a>
            </div>

            <div v-if="store.work_time" class="mt-6 rounded-2xl border border-kraft-200 bg-white/60 p-4 text-xs leading-relaxed text-herb-900/65 backdrop-blur">
                <p class="mb-1.5 flex items-center justify-center gap-1.5 font-bold text-herb-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                    ساعت کاری
                </p>
                <p class="whitespace-pre-line">{{ toFaDigits(store.work_time) }}</p>
            </div>
        </div>
    </div>
</template>
