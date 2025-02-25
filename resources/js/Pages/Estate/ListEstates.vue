<script setup>

import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onBeforeMount } from 'vue';
import ButtonAction from "@/Components/ButtonAction.vue";
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS required by the grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme applied to the grid
import { AgGridVue } from "ag-grid-vue3";
import { useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";
import ListEstatesAssoc from "@/Pages/Estate/ListEstatesAssoc.vue";
import Load from "@/Components/Load.vue";

import Dropdown from 'primevue/dropdown';
import 'primevue/resources/themes/aura-light-green/theme.css'
import Card from "@/Components/card.vue";
import Buttons from "@/Components/buttons.vue";

const pageTitle = "Listar Dependencias";
const autoSizeStrategy = ref(null);
const fileImport = ref();
const sendFile = ref(false);
const addOrNew = ref(true);
const props = defineProps({
    estates: Object,
    users: Object,
    validity: Object,
    edit: null
});

const columnsTable = [
    { field: 'cod_reg', headerName: 'Código de Regional', filter: true, floatingFilter: true, suppressSizeToFit: true, width: 100 },
    { field: 'cod_dep', headerName: 'Código de Dependencia', filter: true, floatingFilter: true, width: 150 },
    { field: 'dependence', headerName: 'Dependencia', filter: true, floatingFilter: true, suppressSizeToFit: true },
    { field: 'get_responsible.name', headerName: 'Responsable', filter: true, floatingFilter: true },
    { field: 'get_adviser.name', headerName: 'Responsable Control', filter: true, floatingFilter: true },
    { field: 'id', headerName: 'Acciones', cellRenderer: ButtonAction },
];

const defaultColDef = ref({
    initialWidth: 200,
    wrapHeaderText: true,
    autoHeaderHeight: true,
});

onBeforeMount(() => {
    autoSizeStrategy.value = {
        type: "fitCellContents",
    };
});

const viewForm = ref(false);
const form = useForm({
    id: 0,
    cod_reg: null,
    cod_dep: null,
    dependence: null,
    responsible_id: null,
    adviser_id: null,
});
const uploadStatus = ref(false);
const uploadStatusMoney = ref(false);
const selectorValidity = ref(0);
const loadFile = (event) => {
    fileImport.value = event.target.files[0];
}
const save = () => {

    Swal.fire({
        title: "Aviso Importante?",
        text: "Está usted Seguro!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si!",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            form.post('newEstates', {
                onSuccess: (res) => {
                    form.reset()
                    console.log(res)
                }
            });

        }
    });
}

if (props.edit > 0) {
    viewForm.value = true;
    props.estates.filter((est) => {
        if (est.id == props.edit) {
            form.id = est.id;
            form.cod_reg = est.cod_reg;
            form.cod_dep = est.cod_dep;
            form.dependence = est.dependence;
            form.responsible_id = est.responsible_id;
            form.adviser_id = est.adviser_id;
        }
    });
}
const importFile = () => {
    sendFile.value = true;
    const formData = new FormData();
    formData.append('file', fileImport.value);
    formData.append('addornew', addOrNew.value);
    axios.post('importExcelIndicatorGen', formData).then((response) => {

        uploadStatus.value = !uploadStatus.value;
        sendFile.value = false;
        if (response.status === 200) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Error al Cargar el Archivo",
            });
        }
        if (response.status === 201) {
            Swal.fire({
                icon: "success",
                title: "Excelente",
                text: "Archivo Cargado",
            });
        }
    })
}

const importFileMoney = () => {
    sendFile.value = true;
    const formData = new FormData();
    formData.append('file', fileImport.value);
    formData.append('addornew', addOrNew.value);
    axios.post('importExcelIndicatorMoney', formData).then((response) => {

        uploadStatusMoney.value = !uploadStatusMoney.value;
        sendFile.value = false;
        if (response.status === 200) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Error al Cargar el Archivo",
            });
        }
        if (response.status === 201) {
            Swal.fire({
                icon: "success",
                title: "Excelente",
                text: "Archivo Cargado",
            });
        }
    })
}
</script>

<template>
    <AppLayout :title="pageTitle">
        <template #header>
            <h2 class="font-semibold text-xl text-secondary-default my-auto">
                {{ pageTitle }}
            </h2>
        </template>
        <div class="flex flex-col gap-4 flex-1">
            <div class="flex justify-end gap-3 flex-col md:flex-row">
                <Buttons icon="fa-solid fa-plus" @click="viewForm = !viewForm">
                    Nueva Dependencia
                </Buttons>

                <Buttons icon="fa-solid fa-file-import" @click="uploadStatus = !uploadStatus" variant="secondary">
                    Cargar Indicadores de Gestión a Dependencia
                </Buttons>

                <Buttons icon="fa-solid fa-file-import" @click="uploadStatusMoney = !uploadStatusMoney" variant="secondary">
                    Cargar Indicadores de Presupuesto a Dependencia
                </Buttons>

            </div>
            <Card v-if="viewForm" class="w-full">
                <div class="p-3 text-secondary-default">
                    <h2 class="text-xl font-semibold mb-4 ">Crear Elemento</h2>
                    <form id="myForm" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-3">
                        <div class="flex flex-col">
                            <label for="cod_reg" class="text-sm font-semibold mb-1">Cód.Regional</label>
                            <input type="text" id="cod_reg" name="cod_reg"
                                class="rounded py-2 px-3 focus:outline-none focus:border-blue-500"
                                v-model="form.cod_reg">
                        </div>
                        <div class="flex flex-col md:col-span-2">
                            <label for="cod_dep" class="text-sm font-semibold mb-1">Cód. Dependencia</label>
                            <input type="text" id="cod_dep" name="cod_dep"
                                class="border rounded py-2 px-3 focus:outline-none focus:border-blue-500"
                                v-model="form.cod_dep">
                        </div>
                        <div class="flex flex-col md:col-span-4">
                            <label for="dependence" class="text-sm font-semibold mb-1">Dependencia</label>
                            <input type="text" id="dependence" name="dependence"
                                class="border rounded py-2 px-3 focus:outline-none focus:border-blue-500"
                                v-model="form.dependence">
                        </div>
                        <div class="flex flex-col md:col-span-3">
                            <label for="responsible_id" class="text-sm font-semibold mb-1">ID del Responsable</label>
                            <Dropdown v-model="form.responsible_id" :options="users" optionValue="id" filter
                                optionLabel="name" placeholder="Seleccionar Usuario" class="w-full md:w-14rem bg-white">
                                <template #option="slotProps">
                                    {{ slotProps.option.name + ' / ' + slotProps.option.email }}
                                </template>
                            </Dropdown>
                        </div>
                        <div class="flex flex-col md:col-span-2">
                            <label for="adviser_id" class="text-sm font-semibold mb-1">ID del Asesor</label>
                            <Dropdown v-model="form.adviser_id" :options="users" optionValue="id" filter
                                optionLabel="name" placeholder="Seleccionar Usuario" class="w-full md:w-14rem bg-white">
                                <template #option="slotProps">
                                    {{ slotProps.option.name + ' / ' + slotProps.option.email }}
                                </template>
                            </Dropdown>

                        </div>
                        <Buttons type="button" @click="save" class="col-span-full w-full">
                            Crear / Actualizar
                        </Buttons>
                    </form>
                </div>
            </Card>
            <Card class="w-full p-3 flex-1">   
                <ag-grid-vue
                    :rowData="$page.props.estates"
                    :columnDefs="columnsTable"
                    :autoSizeStrategy="autoSizeStrategy"
                    class="ag-theme-quartz h-full"
                >
                </ag-grid-vue>
            </Card>
        </div>
        <Modal :show="uploadStatus" maxWidth="w-full" :closeable="true">
            <div class="modal-container max-w-md mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg">
                <div class="modal-header mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Carga Masiva de Indicadores de Gestión a
                        Dependencia</h1>
                </div>
                <div class="modal-body mb-6">
                    <input type="file" @change="loadFile"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors duration-300"
                        accept="">
                </div>
                <div class="modal-footer flex justify-end mb-6">
                    <button @click="importFile" v-if="!sendFile"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-300">
                        Cargar Archivo
                    </button>
                    <Load v-else></Load>
                </div>
                <div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                            class="form-checkbox custom-checkbox h-6 w-6 text-blue-500 rounded-md border-gray-300 focus:ring-blue-500"
                            v-model="addOrNew">
                        <span class="ml-2 custom-label text-gray-700" v-if="addOrNew">Adicionar Nuevos</span>
                        <span class="ml-2 custom-label text-gray-700" v-else>Inactivar y Nuevos Registros</span>
                    </label>
                </div>
                <div class="modal-actions flex justify-between items-center">
                    <a href="/format/Assoc_Indicator_Generico.xlsx"
                        class="text-blue-600 hover:underline hover:text-blue-800 transition-colors duration-300">
                        Descargar Plantilla XLSX
                    </a>
                    <button @click="uploadStatus = !uploadStatus"
                        class="text-red-600 hover:underline hover:text-red-800 transition-colors duration-300">
                        Cerrar
                    </button>
                </div>
            </div>

        </Modal>

        <Modal :show="uploadStatusMoney" maxWidth="w-full" :closeable="true">
            <div class="modal-container max-w-md mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg">
                <div class="modal-header mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Carga Masiva de Indicadores de Presupuesto a
                        Dependencia
                    </h1>
                </div>
                <div class="modal-body mb-6">
                    <input type="file" @change="loadFile"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors duration-300"
                        accept="">
                </div>
                <div class="modal-footer flex justify-end mb-6">
                    <button @click="importFileMoney" v-if="!sendFile"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-300">
                        Cargar Archivo
                    </button>
                    <Load v-else></Load>
                </div>
                <div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                            class="form-checkbox custom-checkbox h-6 w-6 text-blue-500 rounded-md border-gray-300 focus:ring-blue-500"
                            v-model="addOrNew">
                        <span class="ml-2 custom-label text-gray-700" v-if="addOrNew">Adicionar Nuevos</span>
                        <span class="ml-2 custom-label text-gray-700" v-else>Inactivar y Nuevos Registros</span>
                    </label>
                </div>
                <div class="modal-actions flex justify-between items-center">
                    <a href="/format/Indicator_Money_General.xlsx"
                        class="text-blue-600 hover:underline hover:text-blue-800 transition-colors duration-300">
                        Descargar Plantilla XLSX
                    </a>
                    <button @click="uploadStatusMoney = !uploadStatusMoney"
                        class="text-red-600 hover:underline hover:text-red-800 transition-colors duration-300">
                        Cerrar
                    </button>
                </div>
            </div>

        </Modal>
    </AppLayout>
</template>
<style scoped></style>
