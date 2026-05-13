<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

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
    }
});

const activeTab = ref('libro'); // 'libro', 'club', 'horario', 'excepciones', 'emails'
const emailSubTab = ref('plantillas'); // 'plantillas', 'cabecera', 'pie'

// ---- ESTADO DE RESERVAS ----
const formattedReservations = computed(() => {
    const now = new Date();
    return props.reservations.map(res => {
        const dateObj = new Date(res.date);
        
        let finalStatus = res.status;
        let isPast = dateObj < now;
        
        if (isPast && res.status === 'confirmada') {
            finalStatus = 'realizada';
        }

        return {
            ...res,
            computedStatus: finalStatus,
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
    status: '',
});

const startEdit = (reservation) => {
    const dateObj = new Date(reservation.date);
    editingReservation.value = reservation.id;
    editForm.date = dateObj.toISOString().split('T')[0];
    editForm.time = dateObj.toTimeString().split(':')[0] + ':' + dateObj.toTimeString().split(':')[1];
    editForm.people = reservation.people;
    editForm.status = reservation.status;
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

// ---- NUEVA RESERVA MANUAL ----
const isNewReservationModalOpen = ref(false);
const newReservationForm = useForm({
    name: '',
    phone: '',
    email: '',
    date: '',
    time: '',
    people: 0,
    adults: 0,
    children: 0,
    allergies: false,
    celiac: false,
    strollers: false,
    reduced_mobility: false,
    wheelchairs: false,
    notes: ''
});

watch([() => newReservationForm.adults, () => newReservationForm.children], ([adults, children]) => {
    newReservationForm.people = (parseInt(adults) || 0) + (parseInt(children) || 0);
});

const openNewReservationModal = () => {
    newReservationForm.reset();
    isNewReservationModalOpen.value = true;
};

const closeNewReservationModal = () => {
    isNewReservationModalOpen.value = false;
    newReservationForm.reset();
};

const submitNewReservation = () => {
    const finalDate = newReservationForm.date + ' ' + newReservationForm.time + ':00';
    newReservationForm.transform((data) => ({
        ...data,
        date: finalDate,
    })).post(route('reservations.store'), {
        preserveScroll: true,
        onSuccess: () => closeNewReservationModal(),
    });
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
    is_closed: false,
    is_permanent: false,
    close_morning: false,
    close_afternoon: false,
    max_diners: ''
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
    url_cancel_redirect: props.settings.url_cancel_redirect || '',
    admin_email: props.settings.admin_email || '',
    subject_admin_reservation: props.settings.subject_admin_reservation || '🔔 Nueva Reserva Recibida',
    email_admin_reservation: props.settings.email_admin_reservation || 'Se ha recibido una nueva reserva:\n\nCliente: [nombre]\nFecha: [fecha]\nHora: [hora]\nComensales: [comensales]\nTeléfono: [telefono]\nEmail: [email]'
});

const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

const saveSchedules = () => {
    scheduleForm.post(route('settings.schedules'), {
        preserveScroll: true,
    });
};

const saveSpecial = () => {
    const maxDiners = prompt(`¿Quieres poner un máximo de reserva para esta excepción?\nPor ejemplo, si pones 4, solo se podrá reservar hasta un máximo de 4 comensales y luego se cerrará.\n\nSi no quieres límite, déjalo en blanco o pon 0:`);
    
    if (maxDiners !== null) {
        specialForm.max_diners = maxDiners ? parseInt(maxDiners) : 0;
        specialForm.post(route('settings.special.store'), {
            preserveScroll: true,
            onSuccess: () => {
                specialForm.reset();
            }
        });
    }
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

const quickClose = (dayOffset, shift) => {
    const targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + dayOffset);
    // Asegurarse de que el timezone no cambie la fecha local
    const offset = targetDate.getTimezoneOffset() * 60000;
    const localDate = new Date(targetDate.getTime() - offset);
    const dateStr = localDate.toISOString().split('T')[0];
    
    const maxDiners = prompt(`Vas a cerrar el turno de ${shift === 'morning' ? 'mañana' : 'tarde'} para el ${dateStr}.\n\nSi deseas dejar unas plazas libres (por ejemplo, 4), introduce el número. Si quieres cerrar completamente, déjalo en blanco o pon 0:`);
    
    if (maxDiners !== null) { // User didn't cancel
        router.post(route('settings.special.quick-close'), {
            date: dateStr,
            shift: shift,
            max_diners: maxDiners ? parseInt(maxDiners) : 0
        }, {
            preserveScroll: true
        });
    }
};

const formatSpanishDate = (dateString) => {
    if (!dateString) return '';
    const dateObj = new Date(dateString);
    return dateObj.toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric' 
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
                
                <div class="bg-white overflow-hidden shadow-2xl rounded-3xl border border-gray-100 flex flex-col min-h-[700px]">
                    
                    <!-- Navegación Premium Horizontal -->
                    <div class="w-full bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 p-4 sm:px-6 sm:py-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest sm:mb-0 ml-2 whitespace-nowrap">Panel de Gestión</h3>
                            <nav class="flex flex-wrap sm:flex-nowrap gap-2 w-full sm:w-auto overflow-x-auto pb-1 hide-scrollbar">
                                <button @click="activeTab = 'libro'" 
                                    class="flex-1 sm:flex-none flex items-center justify-center px-4 py-2.5 rounded-xl transition-all duration-200 font-bold text-sm whitespace-nowrap"
                                    :class="activeTab === 'libro' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                                    <svg class="w-5 h-5 mr-2" :class="activeTab === 'libro' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    Libro de Reservas
                                </button>
                                
                                <button @click="activeTab = 'horario'" 
                                    class="flex-1 sm:flex-none flex items-center justify-center px-4 py-2.5 rounded-xl transition-all duration-200 font-bold text-sm whitespace-nowrap"
                                    :class="activeTab === 'horario' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                                    <svg class="w-5 h-5 mr-2" :class="activeTab === 'horario' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Horario General
                                </button>
                                
                                <button @click="activeTab = 'excepciones'" 
                                    class="flex-1 sm:flex-none flex items-center justify-center px-4 py-2.5 rounded-xl transition-all duration-200 font-bold text-sm whitespace-nowrap"
                                    :class="activeTab === 'excepciones' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                                    <svg class="w-5 h-5 mr-2" :class="activeTab === 'excepciones' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    Excepciones
                                </button>
                                
                                <button v-if="$page.props.auth.user.role === 'superadmin'" @click="activeTab = 'emails'" 
                                    class="flex-1 sm:flex-none flex items-center justify-center px-4 py-2.5 rounded-xl transition-all duration-200 font-bold text-sm whitespace-nowrap"
                                    :class="activeTab === 'emails' ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                                    <svg class="w-5 h-5 mr-2" :class="activeTab === 'emails' ? 'text-white' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    Plantillas de Mail
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Contenido Principal -->
                    <div class="flex-1 bg-white">
                        
                        <!-- TAB: LIBRO DE RESERVAS -->
                        <div v-show="activeTab === 'libro'" class="h-full flex flex-col">
                            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white flex-wrap gap-4">
                                <div>
                                    <h3 class="text-xl font-extrabold text-gray-900">Listado de Próximas Reservas</h3>
                                    <p class="text-sm text-gray-500 mt-1">Gestiona las asistencias y modificaciones de los clientes.</p>
                                    <button @click="openNewReservationModal" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded-lg shadow transition-colors">
                                        + Nueva Reserva Manual
                                    </button>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-2 text-xs">
                                    <div class="flex gap-2">
                                        <button @click="quickClose(0, 'morning')" class="bg-red-50 hover:bg-red-100 text-red-700 font-bold py-1.5 px-3 rounded shadow-sm border border-red-200 transition-colors">
                                            Cerrar Hoy (Mañana)
                                        </button>
                                        <button @click="quickClose(0, 'afternoon')" class="bg-red-50 hover:bg-red-100 text-red-700 font-bold py-1.5 px-3 rounded shadow-sm border border-red-200 transition-colors">
                                            Cerrar Hoy (Tarde)
                                        </button>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="quickClose(1, 'morning')" class="bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold py-1.5 px-3 rounded shadow-sm border border-orange-200 transition-colors">
                                            Cerrar Mañana (Mañana)
                                        </button>
                                        <button @click="quickClose(1, 'afternoon')" class="bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold py-1.5 px-3 rounded shadow-sm border border-orange-200 transition-colors">
                                            Cerrar Mañana (Tarde)
                                        </button>
                                    </div>
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
                                                    <div v-if="editingReservation !== reservation.id">
                                                        <span v-if="reservation.computedStatus === 'confirmada' || reservation.computedStatus === 'confirmed'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200 shadow-sm">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5 my-auto"></span> Confirmada
                                                        </span>
                                                        <span v-else-if="reservation.computedStatus === 'pendiente'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-orange-100 text-orange-800 border border-orange-200 shadow-sm">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-600 mr-1.5 my-auto"></span> Pendiente
                                                        </span>
                                                        <span v-else-if="reservation.computedStatus === 'cancelada_mail'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 border border-red-200 shadow-sm">
                                                            Cancelada (Mail)
                                                        </span>
                                                        <span v-else-if="reservation.computedStatus === 'cancelada_tlf'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 border border-red-200 shadow-sm">
                                                            Cancelada (Tlf)
                                                        </span>
                                                        <span v-else-if="reservation.computedStatus === 'realizada'" class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200 shadow-sm">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mr-1.5 my-auto"></span> Realizada
                                                        </span>
                                                        <span v-else class="px-3 py-1.5 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-gray-800 line-through border border-gray-200 shadow-sm capitalize">
                                                            {{ reservation.computedStatus }}
                                                        </span>
                                                    </div>
                                                    <div v-else>
                                                        <select v-model="editForm.status" class="rounded border-gray-300 shadow-sm text-sm w-full py-1">
                                                            <option value="pendiente">Pendiente</option>
                                                            <option value="confirmada">Confirmada</option>
                                                            <option value="cancelada_mail">Cancelada Mail</option>
                                                            <option value="cancelada_tlf">Cancelada Tlf</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div v-if="editingReservation !== reservation.id && reservation.computedStatus !== 'realizada'" class="flex justify-end space-x-3">
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
                            
                            <form @submit.prevent="saveSchedules" class="w-full space-y-3">
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
                                <form @submit.prevent="saveSpecial" class="bg-white/50 p-6 rounded-2xl border border-blue-50 shadow-inner">
                                    <div class="flex flex-col lg:flex-row gap-6 items-center">
                                        <!-- Selector de Fecha -->
                                        <div class="w-full lg:w-48 flex-shrink-0">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Día Exacto</label>
                                            <input type="date" v-model="specialForm.date" required class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 h-12 font-bold text-gray-800">
                                        </div>
                                        
                                        <!-- Botones Deslizantes (Grid) -->
                                        <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            
                                            <!-- Cerrar Día Completo -->
                                            <label class="flex items-center justify-between px-4 py-3 rounded-xl border bg-white shadow-sm cursor-pointer transition-colors" :class="specialForm.is_closed ? 'border-red-400 bg-red-50 hover:bg-red-50' : 'border-gray-200 hover:bg-gray-50'">
                                                <span class="text-sm font-bold" :class="specialForm.is_closed ? 'text-red-700' : 'text-gray-700'">Cerrar Día Completo</span>
                                                <div class="relative inline-flex items-center ml-2">
                                                    <input type="checkbox" v-model="specialForm.is_closed" class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                                                </div>
                                            </label>

                                            <!-- Cerrar Mañana -->
                                            <label class="flex items-center justify-between px-4 py-3 rounded-xl border bg-white shadow-sm cursor-pointer transition-colors" :class="specialForm.close_morning || specialForm.is_closed ? 'border-orange-300 bg-orange-50 hover:bg-orange-50' : 'border-gray-200 hover:bg-gray-50'">
                                                <span class="text-sm font-bold" :class="specialForm.close_morning || specialForm.is_closed ? 'text-orange-700' : 'text-gray-700'">Cerrar Mañanas</span>
                                                <div class="relative inline-flex items-center ml-2" :class="{'opacity-50 pointer-events-none': specialForm.is_closed}">
                                                    <input type="checkbox" v-model="specialForm.close_morning" class="sr-only peer" :disabled="specialForm.is_closed">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                                </div>
                                            </label>

                                            <!-- Cerrar Tarde -->
                                            <label class="flex items-center justify-between px-4 py-3 rounded-xl border bg-white shadow-sm cursor-pointer transition-colors" :class="specialForm.close_afternoon || specialForm.is_closed ? 'border-orange-300 bg-orange-50 hover:bg-orange-50' : 'border-gray-200 hover:bg-gray-50'">
                                                <span class="text-sm font-bold" :class="specialForm.close_afternoon || specialForm.is_closed ? 'text-orange-700' : 'text-gray-700'">Cerrar Tardes</span>
                                                <div class="relative inline-flex items-center ml-2" :class="{'opacity-50 pointer-events-none': specialForm.is_closed}">
                                                    <input type="checkbox" v-model="specialForm.close_afternoon" class="sr-only peer" :disabled="specialForm.is_closed">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                                </div>
                                            </label>

                                            <label class="flex items-center justify-between px-4 py-3 rounded-xl border bg-white shadow-sm cursor-pointer transition-colors" :class="specialForm.is_permanent ? 'border-blue-400 bg-blue-50 hover:bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                                <span class="text-sm font-bold" :class="specialForm.is_permanent ? 'text-blue-800' : 'text-gray-700'">Excepción Permanente</span>
                                                <div class="relative inline-flex items-center ml-2">
                                                    <input type="checkbox" v-model="specialForm.is_permanent" class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 flex justify-end">
                                        <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold h-12 px-8 rounded-xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 hover:shadow-blue-500/40 w-full sm:w-auto flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Guardar Excepción
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <h4 class="font-bold text-gray-800 mb-4 text-lg">Próximas Excepciones Registradas</h4>
                            <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Horario Aplicado</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="special in special_schedules" :key="special.id" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-extrabold text-gray-900 capitalize">{{ formatSpanishDate(special.date) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span v-if="special.is_permanent" class="px-2.5 py-0.5 inline-flex text-[10px] leading-5 font-bold rounded bg-blue-100 text-blue-800 uppercase tracking-wider">Permanente</span>
                                                <span v-else class="px-2.5 py-0.5 inline-flex text-[10px] leading-5 font-bold rounded bg-gray-100 text-gray-600 uppercase tracking-wider">Puntual</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col space-y-1">
                                                    <span v-if="special.is_closed" class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 w-max">Cerrado Completamente</span>
                                                    <span v-else class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 w-max">Horario Modificado</span>
                                                    <span v-if="special.max_diners > 0" class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-orange-100 text-orange-800 border border-orange-200 w-max">Máximo {{ special.max_diners }} comensales</span>
                                                </div>
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
                                <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[telefono]</span>
                                <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[email]</span>
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

                                    <!-- Separador Admin -->
                                    <div class="md:col-span-2 border-t-2 border-dashed border-orange-200 pt-6 mt-4">
                                        <h4 class="text-lg font-extrabold text-orange-800 flex items-center mb-4">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                            Notificaciones al Administrador
                                        </h4>
                                        <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl mb-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Email del Administrador (donde recibir avisos)</label>
                                            <input type="email" v-model="emailForm.admin_email" placeholder="Ej: admin@sagaretxe.com" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500 h-11">
                                        </div>
                                    </div>

                                    <div class="bg-white border-2 border-orange-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-orange-400 transition-shadow">
                                        <label class="flex items-center text-sm font-extrabold text-gray-800 mb-3"><span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center mr-2">📩</span> Aviso Admin: Nueva Reserva</label>
                                        <input type="text" v-model="emailForm.subject_admin_reservation" placeholder="Asunto del correo" class="w-full mb-3 rounded-lg border-gray-300 text-sm h-10 shadow-sm focus:ring-orange-500">
                                        <textarea v-model="emailForm.email_admin_reservation" rows="5" class="w-full border-0 bg-orange-50 rounded-xl p-4 text-sm font-mono focus:ring-0 text-gray-700"></textarea>
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

        <Modal :show="isNewReservationModalOpen" @close="closeNewReservationModal" maxWidth="2xl">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Nueva Reserva Manual</h2>
                
                <form @submit.prevent="submitNewReservation" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="name" value="Nombre del Cliente" />
                            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="newReservationForm.name" required />
                            <InputError class="mt-2" :message="newReservationForm.errors.name" />
                        </div>
                        <div>
                            <InputLabel for="phone" value="Teléfono" />
                            <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="newReservationForm.phone" required />
                            <InputError class="mt-2" :message="newReservationForm.errors.phone" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="date" value="Fecha" />
                            <TextInput id="date" type="date" class="mt-1 block w-full" v-model="newReservationForm.date" required />
                            <InputError class="mt-2" :message="newReservationForm.errors.date" />
                        </div>
                        <div>
                            <InputLabel for="time" value="Hora" />
                            <TextInput id="time" type="time" class="mt-1 block w-full" v-model="newReservationForm.time" required />
                            <InputError class="mt-2" :message="newReservationForm.errors.time" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="adults" value="Adultos" />
                            <TextInput id="adults" type="number" min="0" class="mt-1 block w-full" v-model="newReservationForm.adults" />
                            <InputError class="mt-2" :message="newReservationForm.errors.adults" />
                        </div>
                        <div>
                            <InputLabel for="children" value="Niños" />
                            <TextInput id="children" type="number" min="0" class="mt-1 block w-full" v-model="newReservationForm.children" />
                            <InputError class="mt-2" :message="newReservationForm.errors.children" />
                        </div>
                        <div class="flex flex-col justify-end">
                            <span class="text-sm font-bold text-gray-700 bg-gray-100 p-2 rounded text-center">
                                Total Comensales: {{ newReservationForm.people }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Requerimientos Especiales" />
                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm text-gray-700">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="newReservationForm.allergies" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 mr-2" />
                                Alergias
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="newReservationForm.celiac" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 mr-2" />
                                Celíaco
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="newReservationForm.strollers" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 mr-2" />
                                Carritos de niño
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="newReservationForm.reduced_mobility" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 mr-2" />
                                Movilidad Reducida
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="newReservationForm.wheelchairs" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 mr-2" />
                                Sillas de ruedas
                            </label>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="notes" value="Notas u Observaciones" />
                        <textarea id="notes" v-model="newReservationForm.notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="closeNewReservationModal">Cancelar</SecondaryButton>
                        <PrimaryButton class="bg-blue-600 hover:bg-blue-700" :class="{ 'opacity-25': newReservationForm.processing }" :disabled="newReservationForm.processing">
                            Crear Reserva
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<style scoped>
.hide-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
