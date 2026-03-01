<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import {
  Chart,
  LineElement,
  PointElement,
  LineController,
  LinearScale,
  CategoryScale,
  Tooltip,
  Legend,
} from 'chart.js';

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  options: {
    type: Object,
    default: () => ({}),
  },
});

const root = ref(null);

let chart;

Chart.register(
  LineElement,
  PointElement,
  LineController,
  LinearScale,
  CategoryScale,
  Tooltip,
  Legend
);

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      display: false,
    },
    x: {
      display: true,
    },
  },
  plugins: {
    legend: {
      display: false,
    },
  },
};

const mergedOptions = computed(() => {
  return Object.keys(props.options).length > 0 ? props.options : defaultOptions;
});

onMounted(() => {
  chart = new Chart(root.value, {
    type: 'line',
    data: props.data,
    options: mergedOptions.value,
  });
});

const chartData = computed(() => props.data);

watch(chartData, data => {
  if (chart) {
    chart.data = data;
    chart.update();
  }
});
</script>

<template>
  <canvas ref="root" />
</template>
