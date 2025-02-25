<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref, onBeforeMount} from 'vue';
import {router} from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import JusitfyEstateValidity from "@/Pages/Validate/JusitfyEstateValidity.vue";
import "ag-grid-community/styles/ag-grid.css"; // Mandatory CSS required by the grid
import "ag-grid-community/styles/ag-theme-quartz.css"; // Optional Theme applied to the grid
import {AgGridVue} from "ag-grid-vue3";
import Tab from "@/Components/Tab.vue";
import Swal from "sweetalert2";
import PrimaryButton from "@/Components/buttons.vue";

// Props
const props = defineProps({
    followUp: Object,
    viability: Object,
    estates: Object,
    estatesControl: Object,
});

// Variables de estado
const pageTitle = "Validar Vigencia";
const validity = ref('');
const estateIndicators = ref([]);
const estateIndicatorsAdviser = ref([]);
const followUp = ref({});
const showModalJustifyOne = ref(false);
const cicle = ref(1);
const selectSaveJustify = ref(0);
const selectFollow = ref({});
const gridApi = ref();
const gridApiMoney = ref();
const selectIndicatorMoney = ref();
const columnTypes = ref(null);

// Constantes y variables de edición
const depEditing = [
    1010, 1011, 1012, 1013, 1023, 1032, 2020, 3030, 4040, 5050, 6060, 7070, 8080,
];
const editingGoal = ref(depEditing.includes(props.estates.id));
const editFull = ref(false);

// Funciones
const formatMoney = (val) => {
    let number = parseFloat(val);
    if (isNaN(number)) {
        throw new Error('Invalid number');
    }
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

const loadViabilityControl = () => {
    axios.get('estateIndicatorsAdviser', {params: {validity: validity.value}})
        .then((response) => estateIndicatorsAdviser.value = response.data);

    axios.get('estateIndicators', {params: {validity: validity.value}})
        .then((response) => estateIndicators.value = response.data);

    axios.get('getFollowUp', {params: {validity: validity.value}})
        .then((response) => {
            followUp.value = response.data;
            for (let fp in followUp.value) {
                if (followUp.value[fp].cicle === 1 && followUp.value[fp].status === 'Activo') {
                    if (editingGoal.value === true) {
                        editFull.value = true;
                    }
                } else {
                    editFull.value = false;
                }
            }
        });

    axios.get('/getIndicatorsMoney', {
        params: {
            validity: validity.value,
            estate_id: props.estates.id,
        }
    }).then(response => {
        selectIndicatorMoney.value = response.data;
    });
};

const goJustify = (item) => {
    router.push('justify/indicator', item);
};

const openJustifyOne = (select) => {
    selectFollow.value = select;
    showModalJustifyOne.value = !showModalJustifyOne.value;
};

const closeJustifyOne = () => {
    loadViabilityControl();
    showModalJustifyOne.value = !showModalJustifyOne.value;
};

const onGridReady = (params) => {
    gridApi.value = params.api;
};

const onGridReadyMoney = (params) => {
    gridApiMoney.value = params.api;
};

const onBtExport = () => {
    gridApi.value.exportDataAsCsv({columnSeparator: "&"});
};

const editingGoalExec = (e) => {
    const form = {
        id: e.data.id,
        execution_goals: e.newValue,
    };
    axios.post('estateIndicatorsDep', form).then(() => {
        Swal.fire({
            title: "Actualización",
            text: "Actualización Realizada con Exito",
            icon: "success",
        });
        loadViabilityControl();
    });
};

// Configuración de columnas
const columnsTable = ref([
    {field: 'get_indicator.name_indicator', headerName: 'Indicador', filter: true, floatingFilter: true},
    {field: 'get_indicator.id', headerName: 'Cod. Indicador', filter: true, floatingFilter: true},
    {field: 'get_indicator.name_perspective', headerName: 'Perspectiva', filter: true, floatingFilter: true},
    {field: 'get_indicator.objective_strategy', headerName: 'Obj. Estrategico', filter: true, floatingFilter: true},
    {
        field: 'get_indicator.indicator_strategy',
        headerName: 'Iniciativa Estratégica',
        filter: true,
        floatingFilter: true
    },
    {field: 'goal', headerName: 'Meta', filter: true, floatingFilter: true},
    {
        field: 'execution_goals',
        headerName: 'Ejecución',
        filter: true,
        type: "editableColumn",
        floatingFilter: true,
        cellStyle: params => {
            if (editingGoal.value) {
                return {color: 'black', 'background-color': '#5D86B4'};
            }
            return null;
        },
    },
    {
        field: 'percentaje', headerName: 'Porcentaje', filter: true, floatingFilter: true, cellRenderer: (params) => {
            return `${(parseFloat(params.data.execution_goals) / parseFloat(params.data.goal) * 100).toFixed(2)}%`;
        }, cellStyle: params => {
            let per = (parseFloat(params.data.execution_goals) / parseFloat(params.data.goal) * 100);
            let expGoal = parseFloat(params.data.expected_goal) * 100;
            return styleCellColor(per, expGoal);
        }
    },
    {
        field: 'expected_goal', headerName: 'Porcentaje Esperado', filter: true, floatingFilter: true, cellRenderer: (params) => {
            return `${(parseFloat(params.data.expected_goal) * 100).toFixed(2)}%`;
        },
    },
    {field: 'physical_recursion', headerName: 'Recurso Físico', filter: true, floatingFilter: true},
    {field: 'technical_recursion', headerName: 'Recurso Técnico', filter: true, floatingFilter: true},
    {field: 'human_resource', headerName: 'Recurso Humano', filter: true, floatingFilter: true},
    {field: 'responsible_indicator', headerName: 'Responsable de Indicador', filter: true, floatingFilter: true},
    {field: 'post_responsible_indicator', headerName: 'Cargo del Responsable', filter: true, floatingFilter: true},
    {field: 'get_indicator.area', headerName: 'Responsable de Indicador', filter: true, floatingFilter: true},
]);

const columnsTableAssocMoney = [
    {field: 'siif', headerName: 'DEP SIIF', filter: true, floatingFilter: true},
    {field: 'project_id', headerName: 'Codigo Proyecto', filter: true, floatingFilter: true},
    {field: 'get_project.project', headerName: 'Proyecto', filter: true, floatingFilter: true},
    {
        field: 'open_money',
        headerName: 'Apertura',
        filter: true,
        floatingFilter: true,
        valueFormatter: p => '$' + formatMoney(p.value),
    },
    {
        field: 'commitment',
        headerName: 'Compromiso',
        filter: true,
        floatingFilter: true,
        valueFormatter: p => '$' + formatMoney(p.value),
    },
    {
        field: 'payments',
        headerName: 'Pagos',
        filter: true,
        floatingFilter: true,
        valueFormatter: p => '$' + formatMoney(p.value),
    },
    {
        field: 'commitment_percentage',
        headerName: 'Porcentaje Comprometido',
        filter: true,
        floatingFilter: true,
        cellRenderer: (params) => {
            return `${(parseFloat(params.data.commitment_percentage)*100).toFixed(2)}%`;
        }, tooltipValueGetter: (p) => "El porcentaje esperado es de 45.17%", headerTooltip: "io",
        cellStyle: params => {
            let exp = 45.17;
            let val = parseFloat(params.data.commitment_percentage)*100;
            return styleCellColor(val, exp);
        }
    },
    {
        field: 'payment_execution',
        headerName: 'Porcentaje Pago',
        filter: true,
        floatingFilter: true,
        cellRenderer: (params) => {
            return `${(parseFloat(params.data.payment_execution)*100).toFixed(2)}%`;
        },tooltipValueGetter: (p) => "El porcentaje esperado es de 10.84%",
        cellStyle: params => {
            let exp = 10.84;
            let val = parseFloat(params.data.payment_execution)*100;
            return styleCellColor(val, exp);
        }
    },
];

// Configuración antes de montar el componente
onBeforeMount(() => {
    columnTypes.value = {
        editableColumn: {
            editable: () => editFull.value,
        },
    };
    loadViabilityControl();
});


const downloadFollow = (id, relation) => {
    window.open(`export/followup/dep?id=${id}&relation=${relation}`);
}


const getRowStyle = (params) => {
    if(params.data.expected_goal === '0'){
        return { background: 'white' };
    }else{
        let expGoal = parseFloat(params.data.expected_goal) * 100;
        let execGoal = (parseFloat(params.data.execution_goals)/parseFloat(params.data.goal)) * 100;
        if(execGoal >=expGoal){
            return { background: '#00B050' };
        }else{
            return { background: "#FE5935" };
        }
    }
    /*if (params.node.rowIndex % 2 === 0) {
        return { background: 'red' };
    }*/
}

const styleCellColor = (per, exp) => {
   if(exp > 0){
       if(per > exp){
           return { background: 'orange' };
       }else{
            if(per === exp){
                return { background: 'green' };
            }else if(per >= (exp/2)){
                return { background: 'yellow' };
            }else if(per < (exp/2)){
                return { background: 'red' };
            }
       }
   }
}
</script>
<template>
    <AppLayout :title="pageTitle">
        <template #header>
            <h1 class="font-semibold text-xl text-secondary-default my-auto">Validar Vigencia - {{ estates.id }}</h1>
        </template>
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-4">
                <h1 class="text-secondary-default font-bold"><i class="fa-regular fa-calendar-days"></i> Vigencia</h1>
                <select
                    class="rounded-md"
                    name="viability" id="viability" v-model="validity">
                    <option value="" disabled selected>Seleccione el año</option>
                    <option :value="via.id" v-for="via in viability" :key="via.id">{{ via.validity }}</option>
                </select>
                <PrimaryButton @click="loadViabilityControl"
                        v-if="(Object.keys($page.props.estatesControl).length > 0 || Object.keys($page.props.estates).length > 0) && validity > 0">
                    Validar
                </PrimaryButton>
            </div>
            <div class="flex flex-wrap lg:flex-nowrap gap-3 shadow p-3 rounded-md bg-white"
                 v-if="$page.props.auth.user.role_id != 1 && Object.keys($page.props.estates).length > 0">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Información de la Propiedad</h3>
                    <p class="text-sm text-gray-500">Detalles de la propiedad seleccionada</p>
                </div>
                <dl class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-100 rounded-md px-5 py-4 grid md:grid-cols-3 gap-3 overflow-hidden">
                        <dt class="text-sm font-medium text-gray-800">Código de Regional:</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2">{{ estates.cod_reg }}</dd>
                    </div>
                    <div class="bg-gray-100 rounded-md px-5 py-4 grid md:grid-cols-3 gap-3 overflow-hidden">
                        <dt class="text-sm font-medium text-gray-800">Código de Dependencia:</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2">{{ estates.cod_dep }}</dd>
                    </div>
                    <div class="bg-gray-100 rounded-md px-5 py-4 grid md:grid-cols-3 gap-3 overflow-hidden">
                        <dt class="text-sm font-medium text-gray-800">Dependencia:</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2">{{ estates.dependence }}</dd>
                    </div>
                    <div class="bg-gray-100 rounded-md px-5 py-4 grid md:grid-cols-3 gap-3 overflow-hidden">
                        <dt class="text-sm font-medium text-gray-800">Responsable de Seguimiento:</dt>
                        <dd class="mt-1 text-sm text-gray-500 sm:col-span-2">{{ estates.get_adviser.name }} - Correo: {{
                                estates.get_adviser.email
                            }}
                        </dd>
                    </div>
                    <div v-for="(fup, index) in followUp" class="col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-gray-100 rounded-md px-5 py-4 grid md:grid-cols-3 gap-3 overflow-hidden">
                                <dt class="text-sm font-medium text-gray-800">Estado de Proceso:</dt>
                                <dd class="mt-1 text-sm text-gray-500 sm:col-span-2 font-weight-bold">
                                    <div v-if="fup.cicle == 1">
                                        En Dependencia
                                    </div>
                                    <div v-if="fup.cicle == 2">
                                        En Seguimiento
                                    </div>
                                    <div v-if="fup.cicle == 3">
                                        En Seguimiento / Dirección General
                                    </div>
                                </dd>
                            </div>
                            <div class="bg-gray-100 rounded-md px-5 py-4 grid md:grid-cols-3 gap-3 overflow-hidden">
                                <dt class="text-sm font-medium text-gray-800"></dt>
                                <dd class="mt-1 text-sm text-gray-500 sm:col-span-2">
                                    <button @click="openJustifyOne(fup)" v-if="fup.cicle == 1 && fup.status == 'Activo'"
                                            class="hover:bg-primary-default hover:text-white text-primary-default border border-primary-default font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline block">
                                        Justificar x Dependencia ({{ fup.month }})
                                    </button>
                                    <span v-if="fup.status != 'Activo'"
                                          class="bg-red-100 text-red-700 px-4 py-2 rounded">
                                      Seguimento Cerrado
                                    </span>
                                    <div class="mt-5" v-if="fup.status == 'Activo'">
                                        <button @click="downloadFollow(fup.id, 0)"
                                                class="ml-3 inline-flex items-center px-4 py-2 bg-primary-default border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <i class="fas fa-file-excel mr-2"></i>
                                            Descargar Seguimiento
                                        </button>
                                    </div>
                                    <div class="mt-5" v-else>
                                        <button @click="downloadFollow(fup.id, 1)"
                                                class="ml-3 inline-flex items-center px-4 py-2 bg-primary-default border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <i class="fas fa-file-excel mr-2"></i>
                                            Descargar Seguimiento
                                        </button>
                                    </div>
                                </dd>
                            </div>
                        </div>
                    </div>
                </dl>
            </div>
            <div v-else class="text-center bg-white mt-1">
                <span>No Tiene Asociado una dependencia</span>
            </div>
            <div class="mt-2 rounded-md shadow overflow-x-auto" v-if="$page.props.auth.user.role_id != 1 && Object.keys(estateIndicators).length > 0">
                <div class="rounded-md shadow overflow-x-auto">
                    <div v-for="(fupTwo, indexTwo) in followUp" :key="indexTwo">
                        <div :class="['w-full py-1']" >
                            <div v-if="fupTwo.cicle > 1"
                                 class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-200 p-4 bg-white rounded-md">
                                <div class="md:col-span-3 mb-2">
                                    <span class="font-bold text-lg">Justificaciones En Proceso</span>
                                </div>
                                <div class="md:col-span-3 mb-2">
                                    <span class="font-semibold">Mes de Seguimiento: </span>{{ fupTwo.month }}
                                </div>
                                <div class="p-2 border border-gray-300 rounded-md bg-white">
                                    <span class="font-semibold block">Indicadores:</span>
                                    <span>{{ fupTwo.justify_estate_indicator }}</span>
                                </div>
                                <div class="p-2 border border-gray-300 rounded-md bg-white">
                                    <span class="font-semibold block">Presupuesto:</span>
                                    <span>{{ fupTwo.justify_estate_money }}</span>
                                </div>
                                <div class="p-2 border border-gray-300 rounded-md bg-white">
                                    <span class="font-semibold block">Observación:</span>
                                    <span>{{ fupTwo.observation_control }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <Tab>
                    <template #t1>
                        <ag-grid-vue
                            :rowData="estateIndicators"
                            :columnDefs="columnsTable"
                            style=""
                            class="ag-theme-quartz h-80"
                            rowSelection="multiple"
                            @cell-edit-request="editingGoalExec"
                            :enterNavigatesVertically="true"
                            :enterNavigatesVerticallyAfterEdit="true"
                            :readOnlyEdit="true"
                            :columnTypes="columnTypes"
                            @grid-ready="onGridReady"
                            :columnHoverHighlight="true"
                        >
                        </ag-grid-vue>
                    </template>
                    <template #t2>
                        <div class="ag-grid-section px-2 mb-6">
                            <ag-grid-vue
                                :rowData="selectIndicatorMoney"
                                :columnDefs="columnsTableAssocMoney"
                                class="ag-theme-quartz h-80"
                                rowSelection="multiple"
                                @selection-changed="onSelectionChanged"
                                @grid-ready="onGridReady"
                                :columnHoverHighlight="true"
                            >
                            </ag-grid-vue>
                        </div>
                    </template>
                </Tab>
            </div>
            <div class="w-full mt-4"
                 v-if="$page.props.auth.user.role_id != 2 && Object.keys(estateIndicatorsAdviser).length > 0">
                <div class="grid grid-cols-1 gap-2">
                    <div class="bg-white mx-8 shadow overflow-x-auto sm:rounded-lg px-3">
                        <div class="container mx-1 ">
                            <div class="bg-white shadow-md rounded my-6">
                                <table class="table-auto">
                                    <thead>
                                    <tr>
                                        <th colspan="13">Control de Indicadores</th>
                                    </tr>
                                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                        <th class="py-3 px-6 text-left">Cod Regional</th>
                                        <th class="py-3 px-6 text-left">Cod Dependencia</th>
                                        <th class="py-3 px-6 text-left">Dependencia</th>
                                        <th class="py-3 px-6 text-left">Nombre de Indicador</th>
                                        <th class="py-3 px-6 text-left">Nombre de Perspectiva</th>
                                        <th class="py-3 px-6 text-left">Nombre de Objetivo Estrategico</th>
                                        <th class="py-3 px-6 text-left">Nombre de Indicador Estrategico</th>
                                        <th class="py-3 px-6 text-left">Mes</th>
                                        <th class="py-3 px-6 text-left">Meta</th>
                                        <th class="py-3 px-6 text-left">Objetivos de ejecución</th>
                                        <th class="py-3 px-6 text-left">Porcentaje ejecución</th>
                                        <th class="py-3 px-6 text-left">Estado</th>

                                    </tr>
                                    </thead>
                                    <tbody class="text-gray-600 text-sm font-light">
                                    <tr v-for="item in estateIndicatorsAdviser" :key="item.id"
                                        :class="{ 'bg-gray-100': item.status === 'Activo' }">
                                        <td class="py-3 px-6 text-left whitespace-nowrap">{{
                                                item.get_estate.cod_reg
                                            }}
                                        </td>
                                        <td class="py-3 px-6 text-left whitespace-nowrap">{{
                                                item.get_estate.cod_dep
                                            }}
                                        </td>
                                        <td class="py-3 px-6 text-left whitespace-nowrap">{{
                                                item.get_estate.dependence
                                            }}
                                        </td>
                                        <td class="py-3 px-6 text-left whitespace-nowrap">{{
                                                item.get_indicator.name_indicator
                                            }}
                                        </td>

                                        <td class="py-3 px-6 text-left whitespace-nowrap">{{
                                                item.get_indicator.name_perspective
                                            }}
                                        </td>

                                        <td class="py-3 px-6 text-left whitespace-nowrap">{{
                                                item.get_indicator.name_strategy
                                            }}
                                        </td>

                                        <td class="py-3 px-6 text-left whitespace-nowrap">{{
                                                item.get_indicator.name_indicator_strategy
                                            }}
                                        </td>
                                        <td class="py-3 px-6 text-left whitespace-nowrap">{{ item.month }}</td>
                                        <td class="py-3 px-6 text-left">{{ item.goal }}</td>
                                        <td class="py-3 px-6 text-left">{{ item.execution_goals }}</td>
                                        <td class="py-3 px-6 text-left">{{
                                                parseFloat((item.execution_goals / item.goal) * 100).toFixed(2)
                                            }}%
                                        </td>
                                        <td class="py-3 px-6 text-left">{{ item.status }}</td>

                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <Modal :show="showModalJustifyOne" maxWidth="w-full" :closeable="true">
                <JusitfyEstateValidity :viability="validity" :estates="estates" :followUp="selectFollow" :cicle="cicle"
                                       @close="closeJustifyOne"></JusitfyEstateValidity>
            </Modal>
        </div>
    </AppLayout>
</template>
