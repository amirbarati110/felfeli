<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ login: '', password: '', remember: true });

function submit() {
    form.post(route('admin.login.store'), { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="ورود مدیریت" />

    <div class="grid min-h-dvh place-items-center bg-paper-100 p-4">
        <div class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 sm:p-8">
            <h1 class="text-center text-lg font-extrabold text-herb-700">
                فلفلی <span class="text-herb-500">ساوه</span>
            </h1>
            <p class="mt-1 text-center text-xs text-herb-900/45">ورود به پنل مدیریت</p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label for="admin-login" class="mb-1.5 block text-sm font-medium">نام کاربری</label>
                    <input
                        id="admin-login" v-model="form.login" type="text" dir="ltr" autocomplete="username"
                        class="h-12 w-full rounded-xl border-0 bg-paper-100 px-4 text-base outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400 sm:text-sm"
                    />
                    <p v-if="form.errors.login" class="mt-1 text-xs text-anar-600">{{ form.errors.login }}</p>
                </div>
                <div>
                    <label for="admin-password" class="mb-1.5 block text-sm font-medium">گذرواژه</label>
                    <input
                        id="admin-password" v-model="form.password" type="password" dir="ltr" autocomplete="current-password"
                        class="h-12 w-full rounded-xl border-0 bg-paper-100 px-4 text-base outline-none ring-1 ring-black/5 focus:ring-2 focus:ring-herb-400 sm:text-sm"
                    />
                </div>
                <label class="flex min-h-11 items-center gap-2 text-sm text-herb-900/60">
                    <input v-model="form.remember" type="checkbox" class="rounded" />
                    مرا به خاطر بسپار
                </label>
                <button
                    type="submit" :disabled="form.processing"
                    class="min-h-12 w-full rounded-full bg-herb-500 py-3 text-sm font-extrabold text-white transition hover:bg-herb-600 disabled:opacity-60"
                >
                    ورود
                </button>
            </form>
        </div>
    </div>
</template>
