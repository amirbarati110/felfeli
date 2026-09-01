<script setup>
defineProps({
    variant: { type: String, default: 'full' }, // full | mark
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <img
        v-if="variant === 'full'"
        src="/images/brand/felfeli-logo-full.png"
        alt="فلفلی ساوه"
        class="h-9 w-auto select-none"
        :class="$props.class"
        draggable="false"
    />
    <img
        v-else
        src="/images/brand/felfeli-logo.png"
        alt="فلفلی ساوه"
        class="h-9 w-auto select-none"
        :class="$props.class"
        draggable="false"
    />
</template>
