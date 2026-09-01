// سبد خرید سمت کلاینت با به‌روزرسانیِ خوش‌بینانه (optimistic).
// هدف: افزودن/کاست/حذف بدون حسِ رفرش — UI فوری عوض می‌شود و سرور در
// پس‌زمینه همگام می‌شود. قیمت و جمعِ معتبر همیشه از پاسخِ اشتراکیِ سرور می‌آید.

import { reactive, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const overrides = reactive({}); // sku => qty (لغو موقت تا رسیدن پاسخ سرور)
const timers = {};

function serverLines(page) {
    return page.props.cart?.lines ?? {};
}

export function useCart(extraReload = []) {
    const page = usePage();
    const only = ['cart', 'flash', ...extraReload];

    const lines = computed(() => ({ ...serverLines(page), ...overrides }));

    const count = computed(() =>
        Object.values(lines.value).reduce((s, q) => s + (q > 0 ? Number(q) : 0), 0),
    );

    const subtotal = computed(() => page.props.cart?.subtotal ?? 0);

    function qtyOf(sku) {
        return sku in overrides ? overrides[sku] : Number(serverLines(page)[sku] ?? 0);
    }

    function flush(sku) {
        const qty = overrides[sku];
        const onDisk = Number(serverLines(page)[sku] ?? 0);
        const opts = {
            preserveScroll: true,
            preserveState: true,
            only,
            replace: true,
            onFinish: () => {
                delete overrides[sku];
            },
        };

        if (qty <= 0) {
            if (onDisk > 0) router.delete(route('cart.remove', sku), opts);
            else delete overrides[sku];
        } else if (onDisk === 0) {
            router.post(route('cart.add'), { sku, qty }, opts);
        } else {
            router.patch(route('cart.update', sku), { qty }, opts);
        }
    }

    function schedule(sku, qty) {
        overrides[sku] = Math.max(0, Math.min(99, qty));
        clearTimeout(timers[sku]);
        timers[sku] = setTimeout(() => flush(sku), 260);
    }

    return {
        lines,
        count,
        subtotal,
        qtyOf,
        add: (sku) => schedule(sku, qtyOf(sku) + 1),
        setQty: (sku, qty) => schedule(sku, qty),
        remove: (sku) => schedule(sku, 0),
    };
}
