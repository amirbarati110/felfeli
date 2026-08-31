// نمایش قیمت — داده‌ی خام همیشه «ریال» است (خروجی باران و دیتابیس).
// طبق طرح، به کاربر «تومان» نشان داده می‌شود.

const FA_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

export function toFaDigits(input) {
    return String(input).replace(/[0-9]/g, (d) => FA_DIGITS[+d]);
}

export function groupDigits(n) {
    return Math.round(n).toLocaleString('en-US');
}

/** ریال → رشته‌ی «۱۲٬۳۴۵ تومان» */
export function toman(rial, { suffix = 'تومان', fa = true } = {}) {
    const t = Math.round((Number(rial) || 0) / 10);
    const grouped = groupDigits(t).replace(/,/g, '٬');
    const out = suffix ? `${grouped} ${suffix}` : grouped;
    return fa ? toFaDigits(out) : out;
}

/** فقط عدد تومان بدون واحد */
export function tomanValue(rial) {
    return toFaDigits(groupDigits((Number(rial) || 0) / 10).replace(/,/g, '٬'));
}

export function faNumber(n) {
    return toFaDigits(groupDigits(n).replace(/,/g, '٬'));
}
