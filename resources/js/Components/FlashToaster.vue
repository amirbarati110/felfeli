<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const toast = ref(null);
let timer = null;

watch(
    () => page.props.flash,
    (flash) => {
        const msg = flash?.success || flash?.error;
        if (!msg) return;
        toast.value = { type: flash.success ? 'success' : 'error', msg };
        clearTimeout(timer);
        timer = setTimeout(() => (toast.value = null), 2600);
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-200" leave-active-class="transition duration-200"
        enter-from-class="translate-y-3 opacity-0" leave-to-class="translate-y-3 opacity-0"
    >
        <div
            v-if="toast"
            class="fixed inset-x-0 bottom-24 z-50 mx-auto w-fit max-w-[92%] rounded-full px-4 py-2.5 text-sm font-medium text-white shadow-lg sm:bottom-6"
            :class="toast.type === 'success' ? 'bg-herb-600' : 'bg-anar-500'"
        >
            {{ toast.msg }}
        </div>
    </Transition>
</template>
