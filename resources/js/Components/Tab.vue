<script setup>

import Card from "./card.vue";

import {ref} from "vue";
const tab = ref(1);
</script>
<template>
    <Card class="w-full flex flex-col">
        <!-- Tabs -->
        <div class="flex border-b">
            <label for="tab1" :class="[tab == 1 ? 'border-b-4 border-primary-700 bg-primary-default text-white transition-all' : 'hover:border-b-4 text-secondary-default hover:bg-secondary-200 hover:border-secondary-default hover:border-secpndary-default ', 'tab-label py-2 px-4 cursor-pointer  ']" @click="tab = 1">Indicadores de Gestión</label>
            <label for="tab1" :class="[tab == 2 ? 'border-b-4 border-primary-700 bg-primary-default text-white transition-all' : 'hover:border-b-4 text-secondary-default hover:bg-secondary-200 hover:border-secondary-default hover:border-secpndary-default ', 'tab-label py-2 px-4 cursor-pointer ']" @click="tab = 2">Indicadores de Presupuesto</label>
        </div>
        <div id="content1" :class="[tab == 1 ? '' : 'hidden', 'tab-content p-4 flex-1']">
            <slot name="t1"/>
        </div>
        <div id="content2" :class="[tab == 2 ? '' : 'hidden', 'tab-content p-4 flex-1']">
            <slot name="t2"/>
        </div>

    </Card>
</template>
<style scoped>
.tab-label {
    transition: all 0.3s ease;
}
</style>
