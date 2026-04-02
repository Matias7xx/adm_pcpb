<template>
  <button
    v-bind="$attrs"
    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
    :class="colorClasses"
  >
    <slot />
  </button>
</template>

<script setup>
const props = defineProps({
  color: {
    type: String,
    default: 'default',
  },
});

const colorClasses = {
  default: 'bg-gray-800 hover:bg-gray-700 active:bg-gray-900 focus:ring-indigo-500 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:focus:bg-white dark:active:bg-gray-300 dark:focus:ring-offset-gray-800',
  gold: 'bg-[#c1a85a] hover:bg-[#b09448] active:bg-[#9e8340] focus:ring-[#bea55a]',
}[props.color] ?? '';
</script>