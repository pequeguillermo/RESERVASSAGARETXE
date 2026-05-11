<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    reservations: {
        type: Array,
        required: true
    },
    settings: {
        type: Object,
        default: () => ({})
    },
    schedules: {
        type: Array,
        default: () => []
    },
    special_schedules: {
        type: Array,
        default: () => []
    },
    special_schedules: {
        type: Array,
        default: () => []
    }
});

const activeTab = ref('libro'); // 'libro', 'club', 'horario', 'excepciones', 'emails'
const emailSubTab = ref('plantillas'); // 'plantillas', 'cabecera', 'pie'

// ---- ESTADO DE RESERVAS ----
const formattedReservations = computed(() => {
    return props.reservations.map(res => {
        const dateObj = new Date(res.date);
        return {
            ...res,
            formattedDate: dateObj.toLocaleDateString('es-ES', { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
            }),
            formattedTime: dateObj.toLocaleTimeString('es-ES', { 
                hour: '2-digit', minute: '2-digit' 
            }),
            hasCancellations: res.client_stats && res.client_stats.total_cancellations > 0
        };
    });
});

const editingReservation = ref(null);
const editForm = useForm({
    date: '',
    time: '',
    people: 1,
});

const startEdit = (reservation) => {
    const dateObj = new Date(reservation.date);
    editingReservation.value = reservation.id;
    editForm.date = dateObj.toISOString().split('T')[0];
    editForm.time = dateObj.toTimeString().split(':')[0] + ':' + dateObj.toTimeString().split(':')[1];
    editForm.people = reservation.people;
};

const saveEdit = (id) => {
    const finalDate = editForm.date + ' ' + editForm.time + ':00';
    editForm.transform((data) => ({
        ...data,
        date: finalDate,
    })).put(route('reservations.update', id), {
        preserveScroll: true,
        onSuccess: () => {
            editingReservation.value = null;
        }
    });
};

const cancelEdit = () => {
    editingReservation.value = null;
};

const cancelReservation = (id) => {
    if (confirm('¿Estás seguro de que quieres cancelar esta reserva? Se enviará un email al cliente.')) {
        router.post(route('reservations.cancel', id), {}, { preserveScroll: true });
    }
};

// ---- ESTADO DE HORARIOS Y EMAILS ----
const scheduleForm = useForm({
    schedules: props.schedules.map(s => ({
        id: s.id,
        day_of_week: s.day_of_week,
        open_time: s.open_time || '',
        close_time: s.close_time || '',
        open_time_2: s.open_time_2 || '',
        close_time_2: s.close_time_2 || '',
        is_closed: s.is_closed
    }))
});

const specialForm = useForm({
    date: '',
    open_time: '',
    close_time: '',
    open_time_2: '',
    close_time_2: '',
    is_closed: false
});

const emailForm = useForm({
    from_name: props.settings.from_name || 'Sagaretxe',
    from_email: props.settings.from_email || 'reservas@sagaretxe.com',
    email_header: props.settings.email_header || '',
    email_footer: props.settings.email_footer || '',
    subject_confirmation: props.settings.subject_confirmation || 'Confirmación de tu reserva',
    subject_cancellation: props.settings.subject_cancellation || 'Cancelación de tu reserva',
    subject_reminder: props.settings.subject_reminder || 'Recordatorio de tu reserva',
    subject_feedback: props.settings.subject_feedback || '¿Qué tal tu experiencia en Sagaretxe?',
    email_confirmation: props.settings.email_confirmation || 'Hola [nombre],\nTu reserva para el día [fecha] a las [hora] para [comensales] personas está confirmada.',
    email_cancellation: props.settings.email_cancellation || 'Hola [nombre],\nTu reserva ha sido cancelada.',
    email_reminder_24h: props.settings.email_reminder_24h || 'Hola [nombre],\nTe recordamos tu reserva mañana. Si no vas a asistir, por favor cancela respondiendo a este correo.',
    email_feedback_24h: props.settings.email_feedback_24h || 'Hola [nombre],\n¿Qué tal estuvo tu experiencia? Déjanos una reseña en Google.',
    url_confirm_redirect: props.settings.url_confirm_redirect || '',
    url_cancel_redirect: props.settings.url_cancel_redirect || ''
});

const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

const saveSchedules = () => {
    scheduleForm.post(route('settings.schedules'), {
        preserveScroll: true,
    });
};

const saveSpecial = () => {
    specialForm.post(route('settings.special.store'), {
        preserveScroll: true,
        onSuccess: () => {
            specialForm.reset();
        }
    });
};

const deleteSpecial = (id) => {
    if (confirm('¿Eliminar esta fecha especial?')) {
        router.delete(route('settings.special.destroy', id), { preserveScroll: true });
    }
};

const saveEmails = () => {
    emailForm.post(route('settings.update'), {
        preserveScroll: true,
    });
};

</script>

<template>
    <Head title="Gestión de Reservas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-3xl font-extrabold leading-tight text-gray-900 tracking-tight">
                    Centro de Reservas
                </h2>
                <div v-if="activeTab === 'libro'" class="bg-blue-600 text-white text-sm font-bold px-4 py-2 rounded-full shadow-md flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    {{ reservations.length }} Totales
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-2xl rounded-3xl border border-gray-100 flex flex-col md:flex-row min-h-[700px]">
                    
                    <!-- Sidebar de Navegación Premium -->
                    <div class="w-full md:w-72 bg-gradient-to-b from-gray-50 to-white border-r border-gray-200 p-6 flex flex-col">
                        <div class="mb-8">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 ml-2">Panel de Gestión</h3>
                            <nav class="space-y-2">
                                <button @click="activeTab = 'libro'" 
                                    class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 font-bold text-sm"
                                    :class="activeTab === 'libro' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                                    <svg class="w-5 h-5 mr-3" :class="activeTab === 'libro' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    Libro de Reservas
                                </button>


                                
                                <button @click="activeTab = 'horario'" 
                                    class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 font-bold text-sm"
                                    :class="activeTab === 'horario' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                                    <svg class="w-5 h-5 mr-3" :class="activeTab === 'horario' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Horario General
                                </button>
                                
                                <button @click="activeTab = 'excepciones'" 
                                    class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 font-bold text-sm"
                                    :class="activeTab === 'excepciones' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                                    <svg class="w-5 h-5 mr-3" :class="activeTab === 'excepciones' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Excepciones de Horario
                                </button>
                                
                                <button @click="activeTab = 'emails'" 
                                    class="w-full flex items-center px-4 py-3 rounded-xl transition-all duration-200 font-bold text-sm"
                                    :class="activeTab === 'emails' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                                    <svg class="w-5 h-5 mr-3" :class="activeTab === 'emails' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    Plantillas de Mail
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Contenido Principal -->
                    <div class="flex-1 bg-white">
                        
                        <!-- TAB: LIBRO DE RESERVAS -->
                        <div v-show="activeTab === 'libro'" class="h-full flex flex-col">
                            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white">
                                <div>
                                    <h3 class="text-xl font-extrabold text-gray-900">Listado de Próximas Reservas</h3>
                                    <p class="text-sm text-gray-500 mt-1">Gestiona las asistencias y modificaciones de los clientes.</p>
                                </div>
                            </div>
                            
                            <div class="flex-1 overflow-x-auto p-4">
                                <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha y Hora</th>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cliente / Stats</th>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pax</th>
                                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="reservation in formattedReservations" :key="reservation.id" 
                                                class="transition-colors duration-150"
                                                :class="reservation.hasCancellations ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-blue-50'">
                                                
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div v-if="editingReservation !== reservation.id">
                                                        <div class="text-sm font-bold text-gray-900 capitalize">{{ reservation.formattedDate }}</div>
                                                        <div class="text-sm text-gray-500 flex items-center mt-1">
                                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            {{ reservation.formattedTime }}
                                                        </div>
                                                    </div>
                                                    <div v-else class="flex flex-col space-y-2">
                                                        <input type="date" v-model="editForm.date" class="rounded border-gray-300 shadow-sm text-sm w-full" />
                                                        <input type="time" v-model="editForm.time" class="rounded border-gray-300 shadow-sm text-sm w-full" />
                                                    </div>
                                                </td>

                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-semibold" :class="reservation.hasCancellations ? 'text-red-700' : 'text-gray-900'">
                                                        {{ reservation.name }} 
                                                        <span v-if="reservation.member" class="ml-2 text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">Socio</span>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                                        {{ reservation.phone }}
                                                    </div>
                                                    <div v-if="reservation.client_stats" class="mt-2 text-xs">
                                                        <span class="font-semibold" :class="reservation.hasCancellations ? 'text-red-600' : 'text-gray-600'">
                                                            Total Reservas: {{ reservation.client_stats.total_reservations }}
                                                        </span>
                                                        <span v-if="reservation.client_stats.total_cancellations > 0" class="ml-2 font-bold text-red-600 bg-red-100 border border-red-200 px-2 py-0.5 rounded shadow-sm">
                                                            Canceladas: {{ reservation.client_stats.total_cancellations }}
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div v-if="editingReservation !== reservation.id">
                                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                                            {{ reservation.people }}
                                                        </span>
                                                    </div>
                                                    <div v-else>
                                                        <input type="number" min="1" v-model="editForm.people" class="rounded border-gray-300 shadow-sm text-sm w-16 text-center" />
                                                    </div>
                                                </td>
                                                
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span v-if="reservation.status === 'confirmed' || reservation.status === 'confirmada'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200 shadow-sm">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5 my-auto"></span> Confirmada
                                                    </span>
                                                    <span v-else-if="reservation.status === 'pendiente'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-orange-100 text-orange-800 border border-orange-200 shadow-sm">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-600 mr-1.5 my-auto"></span> Pendiente
                                                    </span>
                                                    <span v-else-if="reservation.status === 'cancelada 24'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 border border-red-200 shadow-sm">
                                                        Cancelada (24h)
                                                    </span>
                                                    <span v-else-if="reservation.status === 'cancelled' || reservation.status === 'cancelada'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-gray-800 line-through border border-gray-200 shadow-sm">
                                                        Cancelada
                                                    </span>
                                                    <span v-else class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200 shadow-sm capitalize">
                                                        {{ reservation.status }}
                                                    </span>
                                                </td>

                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div v-if="editingReservation !== reservation.id && reservation.status === 'confirmed'" class="flex justify-end space-x-3">
                                                        <button @click="startEdit(reservation)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition-colors" title="Editar Reserva">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                        </button>
                                                        <button @click="cancelReservation(reservation.id)" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Cancelar Reserva">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </div>
                                                    <div v-else-if="editingReservation === reservation.id" class="flex flex-col space-y-2 justify-end">
                                                        <button @click="saveEdit(reservation.id)" class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded shadow-sm font-bold flex items-center justify-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Guardar
                                                        </button>
                                                        <button @click="cancelEdit" class="bg-gray-400 hover:bg-gray-500 text-white text-xs px-3 py-1.5 rounded shadow-sm font-bold">Cancelar</button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="reservations.length === 0">
                                                <td colspan="5" class="px-6 py-16 text-center text-gray-500 text-lg font-medium">
                                                    <div class="flex justify-center mb-4 text-gray-300">
                                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    </div>
                                                    No hay ninguna reserva registrada en el sistema.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>



                        <!-- TAB: HORARIO GENERAL -->
                        <div v-show="activeTab === 'horario'" class="p-8 h-full">
                            <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Horario General de Apertura</h3>
                            <p class="text-gray-500 mb-8">Establece el horario semanal por defecto del restaurante.</p>
                            
                            <form @submit.prevent="saveSchedules" class="max-w-3xl space-y-3">
                                <div v-for="(schedule, index) in scheduleForm.schedules" :key="index" 
                                    class="flex flex-col lg:flex-row lg:items-center gap-4 p-4 rounded-2xl border transition-all"
                                    :class="schedule.is_closed ? 'bg-red-50 border-red-100' : 'bg-white border-gray-200 shadow-sm'">
                                    
                                    <div class="flex items-center w-full lg:w-56 flex-shrink-0 justify-between lg:justify-start">
                                        <div class="w-24 font-bold text-gray-800 text-lg">{{ days[schedule.day_of_week] }}</div>
                                        
                                        <div class="flex items-center">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="schedule.is_closed" class="sr-only peer">
                                                <div class="w-11 h-6 bg-blue-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                                                <span class="ml-3 text-sm font-semibold text-gray-700 w-16">{{ schedule.is_closed ? 'Cerrado' : 'Abierto' }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 w-full min-w-0">
                                        <template v-if="!schedule.is_closed">
                                            <div class="flex flex-col sm:flex-row items-center gap-3 w-full">
                                                <!-- Turno 1 -->
                                                <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 flex-1 w-full sm:w-auto">
                                                    <span class="text-xs font-bold text-gray-500 w-16 flex-shrink-0">T. Mañana</span>
                                                    <input type="time" v-model="schedule.open_time" class="w-full min-w-0 rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1 h-9" :max="schedule.close_time">
                                                    <span class="text-gray-400 font-bold flex-shrink-0">-</span>
                                                    <input type="time" v-model="schedule.close_time" class="w-full min-w-0 rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1 h-9" :min="schedule.open_time" :max="schedule.open_time_2">
                                                </div>
                                                <!-- Turno 2 -->
                                                <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 flex-1 w-full sm:w-auto">
                                                    <span class="text-xs font-bold text-gray-500 w-16 flex-shrink-0">T. Tarde</span>
                                                    <input type="time" v-model="schedule.open_time_2" class="w-full min-w-0 rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1 h-9" :min="schedule.close_time" :max="schedule.close_time_2">
                                                    <span class="text-gray-400 font-bold flex-shrink-0">-</span>
                                                    <input type="time" v-model="schedule.close_time_2" class="w-full min-w-0 rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1 h-9" :min="schedule.open_time_2">
                                                </div>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <span class="text-red-500 font-bold bg-red-100 px-4 py-3 rounded-lg text-sm w-full block text-center">Día Libre Completo</span>
                                        </template>
                                    </div>
                                </div>
                                
                                <div class="pt-6">
                                    <button type="submit" :disabled="scheduleForm.processing" class="bg-gray-900 hover:bg-black text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-transform hover:-translate-y-1 w-full sm:w-auto">
                                        {{ scheduleForm.processing ? 'Guardando...' : 'Guardar Horario General' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB: EXCEPCIONES -->
                        <div v-show="activeTab === 'excepciones'" class="p-8 h-full">
                            <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Excepciones y Fechas Especiales</h3>
                            <p class="text-gray-500 mb-8">Sobreescribe el horario general para días concretos (festivos, descansos extra, etc).</p>
                            
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-100 mb-10 shadow-sm">
                                <h4 class="font-bold text-blue-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Registrar Nueva Fecha Especial
                                </h4>
                                <form @submit.prevent="saveSpecial" class="flex flex-col md:flex-row gap-5 items-end">
                                    <div class="w-full md:w-auto flex-1">
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Día Exacto</label>
                                        <input type="date" v-model="specialForm.date" required class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 h-11">
                                    </div>
                                    
                                    <div class="w-full md:w-auto flex items-center h-11 px-2">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" v-model="specialForm.is_closed" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                                            <span class="ml-3 text-sm font-bold" :class="specialForm.is_closed ? 'text-red-600' : 'text-gray-700'">Cerrar Día Completo</span>
                                        </label>
                                    </div>

                                    <div v-if="!specialForm.is_closed" class="w-full grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Apertura (Mañana)</label>
                                            <input type="time" v-model="specialForm.open_time" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 h-11" :max="specialForm.close_time">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Cierre (Mañana)</label>
                                            <input type="time" v-model="specialForm.close_time" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 h-11" :min="specialForm.open_time" :max="specialForm.open_time_2">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Apertura (Tarde)</label>
                                            <input type="time" v-model="specialForm.open_time_2" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 h-11" :min="specialForm.close_time" :max="specialForm.close_time_2">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 mb-1">Cierre (Tarde)</label>
                                            <input type="time" v-model="specialForm.close_time_2" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 h-11" :min="specialForm.open_time_2">
                                        </div>
                                    </div>
                                    
                                    <div class="w-full md:w-auto ml-auto">
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold h-11 px-6 rounded-xl shadow-md transition-colors w-full mt-4 md:mt-0">Añadir Excepción</button>
                                    </div>
                                </form>
                            </div>

                            <h4 class="font-bold text-gray-800 mb-4 text-lg">Próximas Excepciones Registradas</h4>
                            <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Horario Aplicado</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="special in special_schedules" :key="special.id" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-extrabold text-gray-900">{{ special.date }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span v-if="special.is_closed" class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">Cerrado Completamente</span>
                                                <span v-else class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Horario Modificado</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                                <div v-if="!special.is_closed" class="flex flex-col space-y-1">
                                                    <span v-if="special.open_time" class="bg-gray-100 px-3 py-1 rounded-lg text-xs">{{ special.open_time }} <span class="text-gray-400 mx-1">a</span> {{ special.close_time }}</span>
                                                    <span v-if="special.open_time_2" class="bg-gray-100 px-3 py-1 rounded-lg text-xs">{{ special.open_time_2 }} <span class="text-gray-400 mx-1">a</span> {{ special.close_time_2 }}</span>
                                                </div>
                                                <span v-else class="text-gray-400">-</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <button @click="deleteSpecial(special.id)" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors inline-flex" title="Eliminar Excepción">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="special_schedules.length === 0">
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">No hay fechas excepcionales configuradas en el calendario.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB: EMAILS -->
                        <div v-show="activeTab === 'emails'" class="p-8 h-full">
                            <div class="flex flex-col xl:flex-row xl:justify-between xl:items-center gap-6 mb-8">
                                <div>
                                    <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Configuración de Correos</h3>
                                    <p class="text-gray-500">Configura la cabecera, plantillas, pie de página, remitente y asunto.</p>
                                </div>
                                <div class="bg-gray-100/80 backdrop-blur-sm p-1.5 rounded-2xl shadow-sm flex flex-wrap sm:flex-nowrap gap-1 sm:space-x-1 border border-gray-200">
                                    <button @click="emailSubTab = 'plantillas'" :class="emailSubTab === 'plantillas' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'" class="flex-1 sm:flex-none whitespace-nowrap px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-200">Plantillas</button>
                                    <button @click="emailSubTab = 'cabecera'" :class="emailSubTab === 'cabecera' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'" class="flex-1 sm:flex-none whitespace-nowrap px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-200">Cabecera</button>
                                    <button @click="emailSubTab = 'pie'" :class="emailSubTab === 'pie' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'" class="flex-1 sm:flex-none whitespace-nowrap px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-200">Pie de Página</button>
                                    <button @click="emailSubTab = 'redirecciones'" :class="emailSubTab === 'redirecciones' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'" class="flex-1 sm:flex-none whitespace-nowrap px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-200">Redirecciones</button>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 mb-8 bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <span class="text-sm font-bold text-gray-700 w-full mb-2">Shortcodes Disponibles:</span>
                                <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[nombre]</span>
                                <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[fecha]</span>
                                <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[hora]</span>
                                <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[comensales]</span>
                                <span class="bg-white text-pink-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[confirmar]</span>
                                <span class="bg-white text-pink-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[cancelar]</span>
                            </div>
                            
                            <form @submit.prevent="saveEmails" class="space-y-6">
                                
                                <div v-show="emailSubTab === 'plantillas'" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 bg-indigo-50 p-6 rounded-2xl border border-indigo-100 mb-2">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Remitente (Desde - Nombre)</label>
                                            <input type="text" v-model="emailForm.from_name" placeholder="Ej: Reservas Sagaretxe" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 h-11">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Email Remitente</label>
                                            <input type="email" v-model="emailForm.from_email" placeholder="Ej: reservas@sagaretxe.com" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 h-11">
                                        </div>
                                    </div>

                                    <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-shadow">
                                        <label class="flex items-center text-sm font-extrabold text-gray-800 mb-3"><span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center mr-2">1</span> Confirmación de Reserva</label>
                                        <input type="text" v-model="emailForm.subject_confirmation" placeholder="Asunto del correo" class="w-full mb-3 rounded-lg border-gray-300 text-sm h-10 shadow-sm focus:ring-indigo-500">
                                        <textarea v-model="emailForm.email_confirmation" rows="5" class="w-full border-0 bg-gray-50 rounded-xl p-4 text-sm font-mono focus:ring-0 text-gray-700"></textarea>
                                    </div>
                                    
                                    <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-shadow">
                                        <label class="flex items-center text-sm font-extrabold text-gray-800 mb-3"><span class="w-6 h-6 rounded-full bg-red-100 text-red-700 flex items-center justify-center mr-2">2</span> Cancelación de Reserva</label>
                                        <input type="text" v-model="emailForm.subject_cancellation" placeholder="Asunto del correo" class="w-full mb-3 rounded-lg border-gray-300 text-sm h-10 shadow-sm focus:ring-indigo-500">
                                        <textarea v-model="emailForm.email_cancellation" rows="5" class="w-full border-0 bg-gray-50 rounded-xl p-4 text-sm font-mono focus:ring-0 text-gray-700"></textarea>
                                    </div>
                                    
                                    <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-shadow">
                                        <label class="flex items-center text-sm font-extrabold text-gray-800 mb-3"><span class="w-6 h-6 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mr-2">3</span> Recordatorio (24h antes)</label>
                                        <input type="text" v-model="emailForm.subject_reminder" placeholder="Asunto del correo" class="w-full mb-3 rounded-lg border-gray-300 text-sm h-10 shadow-sm focus:ring-indigo-500">
                                        <textarea v-model="emailForm.email_reminder_24h" rows="5" class="w-full border-0 bg-gray-50 rounded-xl p-4 text-sm font-mono focus:ring-0 text-gray-700"></textarea>
                                    </div>
                                    
                                    <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-shadow">
                                        <label class="flex items-center text-sm font-extrabold text-gray-800 mb-3"><span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center mr-2">4</span> Feedback Google (24h después)</label>
                                        <input type="text" v-model="emailForm.subject_feedback" placeholder="Asunto del correo" class="w-full mb-3 rounded-lg border-gray-300 text-sm h-10 shadow-sm focus:ring-indigo-500">
                                        <textarea v-model="emailForm.email_feedback_24h" rows="5" class="w-full border-0 bg-gray-50 rounded-xl p-4 text-sm font-mono focus:ring-0 text-gray-700"></textarea>
                                    </div>
                                </div>

                                <div v-show="emailSubTab === 'cabecera'">
                                    <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500">
                                        <label class="block text-sm font-extrabold text-gray-800 mb-3">Cabecera HTML (Header)</label>
                                        <textarea v-model="emailForm.email_header" rows="12" placeholder="Introduce el HTML de la cabecera (incluyendo logo, estilos iniciales...)" class="w-full border-gray-300 rounded-xl p-4 text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </div>
                                </div>

                                <div v-show="emailSubTab === 'pie'">
                                    <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500">
                                        <label class="block text-sm font-extrabold text-gray-800 mb-3">Pie de Página HTML (Footer)</label>
                                        <textarea v-model="emailForm.email_footer" rows="12" placeholder="Introduce el HTML del pie de página (redes sociales, información legal...)" class="w-full border-gray-300 rounded-xl p-4 text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    </div>
                                </div>

                                <div v-show="emailSubTab === 'redirecciones'" class="space-y-6">
                                    <div class="bg-indigo-50 border border-indigo-100 p-5 rounded-2xl shadow-sm">
                                        <h4 class="font-extrabold text-indigo-900 mb-2">Páginas de Destino (Redirecciones)</h4>
                                        <p class="text-sm text-indigo-700 mb-4">Cuando un cliente haga clic en "Confirmar" o "Cancelar" en su correo, la App Central validará la acción y le redirigirá automáticamente a estas URLs. Puedes diseñar estas páginas en tu WordPress.</p>
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 mb-1">URL tras Confirmar Reserva</label>
                                                <input type="url" v-model="emailForm.url_confirm_redirect" placeholder="Ej: https://misitio.com/reserva-confirmada" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 h-11 bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 mb-1">URL tras Cancelar Reserva</label>
                                                <input type="url" v-model="emailForm.url_cancel_redirect" placeholder="Ej: https://misitio.com/reserva-cancelada" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 h-11 bg-white">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="pt-4 flex justify-end">
                                    <button type="submit" :disabled="emailForm.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-transform hover:-translate-y-1 w-full sm:w-auto">
                                        {{ emailForm.processing ? 'Guardando...' : 'Guardar Configuración de Correos' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
