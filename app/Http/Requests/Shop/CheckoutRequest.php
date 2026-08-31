<?php

namespace App\Http\Requests\Shop;

use App\Enums\DeliveryMethod;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'min:3', 'max:120'],
            'customer_mobile' => ['required', 'string', 'regex:/^(0|\+?98)?9\d{9}$/'],
            'delivery_method' => ['required', Rule::in(array_keys(DeliveryMethod::options()))],
            'address' => ['nullable', 'string', 'max:500', 'required_if:delivery_method,delivery'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $checkout = Setting::get('checkout', []);

        $validator->after(function (Validator $v) use ($checkout) {
            if ($this->delivery_method === 'delivery' && ! ($checkout['delivery_enabled'] ?? true)) {
                $v->errors()->add('delivery_method', 'ارسال در حال حاضر غیرفعال است.');
            }
            if ($this->delivery_method === 'pickup' && ! ($checkout['pickup_enabled'] ?? true)) {
                $v->errors()->add('delivery_method', 'دریافت حضوری در حال حاضر غیرفعال است.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'نام و نام خانوادگی الزامی است.',
            'customer_name.min' => 'نام و نام خانوادگی را کامل وارد کنید.',
            'customer_mobile.required' => 'شماره موبایل الزامی است.',
            'customer_mobile.regex' => 'شماره موبایل معتبر نیست. مثال: ۰۹۱۲۱۲۳۴۵۶۷',
            'address.required_if' => 'برای ارسال، وارد کردن آدرس الزامی است.',
            'delivery_method.required' => 'روش دریافت را انتخاب کنید.',
            'delivery_method.in' => 'روش دریافت نامعتبر است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'نام و نام خانوادگی',
            'customer_mobile' => 'شماره موبایل',
            'address' => 'آدرس',
            'note' => 'توضیحات',
        ];
    }
}
