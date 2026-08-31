<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: true });

function submit() {
    form.post(route('admin.login.store'), { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="ورود مدیریت" />

    <div class="grid min-h-screen place-items-center bg-cream-100 p-4">
        <div class="w-full max-w-sm rounded-3xl bg-white p-8 shadow-sm ring-1 ring-black/5">
            <h1 class="text-center text-lg font-extrabold text-brand-700">
                فلفلی <span class="text-brand-500">ساوه</span>
            </h1>
            <p class="mt-1 text-center text-xs text-brand-900/45">ورود به پنل مدیریت</p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">ایمیل</label>
                    <input
                        v-model="form.email" type="email" dir="ltr"
                        class="h-11 w-full rounded-xl border-0 bg-cream-100 px-4 text-sm outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-tomato-600">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">گذرواژه</label>
                    <input
                        v-model="form.password" type="password" dir="ltr"
                        class="h-11 w-full rounded-xl border-0 bg-cream-100 px-4 text-sm outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-brand-400"
                    />
                </div>
                <label class="flex items-center gap-2 text-xs text-brand-900/60">
                    <input v-model="form.remember" type="checkbox" class="rounded" />
                    مرا به خاطر بسپار
                </label>
                <button
                    type="submit" :disabled="form.processing"
                    class="w-full rounded-full bg-brand-500 py-3 text-sm font-extrabold text-white transition hover:bg-brand-600 disabled:opacity-60"
                >
                    ورود
                </button>
            </form>
        </div>
    </div>
</template>
