<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Html5QrcodeScanner } from 'html5-qrcode';

const props = defineProps({
    members: {
        type: Array,
        default: () => []
    },
    settings: {
        type: Object,
        default: () => ({})
    }
});

const scanResult = ref(null);
const scanError = ref(null);
const memberData = ref(null);
const loading = ref(false);

const startScanner = () => {
    scanResult.value = null;
    memberData.value = null;
    scanError.value = null;
    
    const scanner = new Html5QrcodeScanner('reader', {
        qrbox: { width: 250, height: 250 },
        fps: 20,
    });

    scanner.render(async (text) => {
        scanResult.value = text;
        scanner.clear();
        await validateQr(text);
    }, () => {});
};

const validateQr = async (token) => {
    loading.value = true;
    try {
        const response = await fetch(`/api/members/validate?token=${token}`);
        const data = await response.json();
        
        memberData.value = data;
        
        if (data.valid) {
            await fetch('/api/validations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ member_id: data.member.id, method: 'qr' })
            });
        }
    } catch (e) {
        scanError.value = "Error conectando con el servidor";
    } finally {
        loading.value = false;
    }
};

const phoneSearch = ref('');
const searchResult = ref(null);

const searchPhone = async () => {
    if (!phoneSearch.value) return;
    
    loading.value = true;
    searchResult.value = null;
    try {
        const response = await fetch(`/api/members/check?phone=${phoneSearch.value}`);
        const data = await response.json();
        searchResult.value = data;
        
        if (data.exists && data.active) {
            await fetch('/api/validations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ member_id: data.member_id, method: 'phone' })
            });
        }
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

// MODAL LÓGICA
const showModal = ref(false);
const editMemberId = ref(null);
const isCreating = ref(false);
const editForm = useForm({
    name: '', surname: '', postal_code: '',
    phone: '', email: '', pref_space: '', pref_food: '', pref_drink1: '',
    pref_drink2: '', pref_time: '', how_knew_us: '', active: true,
});

const openEditModal = (member = null) => {
    if (member) {
        isCreating.value = false;
        editMemberId.value = member.id;
        editForm.name = member.name || '';
    editForm.surname = member.surname || '';
    editForm.postal_code = member.postal_code || '';
    editForm.phone = member.phone || '';
    editForm.email = member.email || '';
    editForm.pref_space = member.pref_space || '';
    editForm.pref_food = member.pref_food || '';
    editForm.pref_drink1 = member.pref_drink1 || '';
    editForm.pref_drink2 = member.pref_drink2 || '';
    editForm.pref_time = member.pref_time || '';
    editForm.how_knew_us = member.how_knew_us || '';
    } else {
        isCreating.value = true;
        editMemberId.value = null;
        editForm.reset();
    }
    showModal.value = true;
};

const closeEditModal = () => {
    showModal.value = false;
    editForm.reset();
};

const saveMember = () => {
    if (isCreating.value) {
        editForm.post(route('club.store'), {
            onSuccess: () => closeEditModal()
        });
    } else {
        editForm.put(`/api/members/${editMemberId.value}`, {
            onSuccess: () => closeEditModal()
        });
    }
};

const deleteMember = (member) => {
    if (confirm(`¿Estás seguro de que quieres eliminar a ${member.name} ${member.surname || ''}? Esta acción no se puede deshacer.`)) {
        router.delete(`/club/${member.id}`);
    }
};

const activeTab = ref('miembros'); // 'miembros', 'emails'
const emailSubTab = ref('plantillas'); // 'plantillas', 'cabecera', 'pie'

const emailForm = useForm({
    club_from_name: props.settings.club_from_name || 'Club Sagaretxe',
    club_from_email: props.settings.club_from_email || 'club@sagaretxe.com',
    club_email_header: props.settings.club_email_header || '',
    club_email_footer: props.settings.club_email_footer || '',
    club_subject_welcome: props.settings.club_subject_welcome || '¡Bienvenido al Club Sagaretxe!',
    club_email_welcome: props.settings.club_email_welcome || 'Hola [nombre],\n¡Gracias por unirte al Club Sagaretxe! Adjuntamos tu código QR para identificarte.',
    club_admin_email: props.settings.club_admin_email || '',
    club_subject_admin: props.settings.club_subject_admin || '🎉 Nuevo Miembro del Club',
    club_email_admin: props.settings.club_email_admin || 'Se ha registrado un nuevo miembro en el Club Sagaretxe:\n\nNombre: [nombre] [apellidos]\nTeléfono: [telefono]\nEmail: [email]\nCódigo Postal: [cp]'
});

const saveEmails = () => {
    emailForm.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: show a success notification here if you have a component
        }
    });
};
</script>

<template>
    <Head title="Club Sagaretxe" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-3xl font-extrabold leading-tight text-gray-900 tracking-tight">
                    Club Sagaretxe
                </h2>
                <nav v-if="$page.props.auth.user.role === 'superadmin'" class="flex space-x-4 bg-white p-1 rounded-xl shadow-sm border border-gray-100">
                    <button @click="activeTab = 'miembros'" :class="activeTab === 'miembros' ? 'bg-indigo-600 text-white shadow' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2 rounded-lg font-bold text-sm transition-all">Directorio y Acceso</button>
                    <button @click="activeTab = 'emails'" :class="activeTab === 'emails' ? 'bg-indigo-600 text-white shadow' : 'text-gray-500 hover:text-gray-700'" class="px-5 py-2 rounded-lg font-bold text-sm transition-all">Emails Automáticos</button>
                </nav>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div v-show="activeTab === 'miembros'" class="space-y-8">
                    <!-- Control de Acceso (Arriba) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-4 sm:px-0">
                        <!-- Scanner -->
                        <div class="bg-white overflow-hidden shadow-lg rounded-2xl p-8 flex flex-col items-center border border-gray-100 hover:shadow-xl transition-all">
                            <div class="bg-indigo-100 p-4 rounded-full mb-6">
                                <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            </div>
                            <h3 class="text-2xl font-bold mb-6 text-gray-800 text-center tracking-tight">Escanear QR de Miembro</h3>
                            <button @click="startScanner" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl text-lg shadow-md transition-all duration-200">
                                Abrir Cámara
                            </button>
                            
                            <div id="reader" class="mt-6 w-full max-w-sm rounded-xl overflow-hidden border-2 border-indigo-50"></div>
                            
                            <div v-if="loading" class="mt-6 text-indigo-500 font-semibold animate-pulse">Procesando validación...</div>
                            
                            <div v-if="memberData" class="mt-6 w-full p-6 rounded-xl shadow-inner text-center border-2 transition-all duration-300" :class="memberData.valid ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                                <p class="text-2xl font-extrabold tracking-tight" :class="memberData.valid ? 'text-green-700' : 'text-red-700'">
                                    {{ memberData.valid ? 'ACCESO PERMITIDO' : 'ACCESO DENEGADO' }}
                                </p>
                                <p class="mt-3 text-xl font-medium text-gray-800" v-if="memberData.member">{{ memberData.member.name }}</p>
                                <p class="mt-1 text-md font-semibold text-red-500" v-if="!memberData.valid">Código Inválido o Caducado</p>
                            </div>
                        </div>

                        <!-- Búsqueda Manual -->
                        <div class="bg-white overflow-hidden shadow-lg rounded-2xl p-8 border border-gray-100 flex flex-col items-center hover:shadow-xl transition-all">
                            <div class="bg-blue-100 p-4 rounded-full mb-6">
                                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <h3 class="text-2xl font-bold mb-6 text-gray-800 text-center tracking-tight">Validación Manual</h3>
                            <div class="flex flex-col space-y-4 w-full">
                                <input type="text" v-model="phoneSearch" placeholder="Teléfono o Nº de Socio..." class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xl py-4" @keyup.enter="searchPhone">
                                <button @click="searchPhone" class="w-full py-4 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl text-lg shadow-md transition-all">
                                    Buscar y Validar
                                </button>
                            </div>
                            
                            <div v-if="searchResult" class="mt-8 w-full p-6 rounded-xl shadow-inner text-center border-2 transition-all duration-300" :class="(searchResult.exists && searchResult.active) ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                                <p class="text-2xl font-extrabold tracking-tight" :class="(searchResult.exists && searchResult.active) ? 'text-green-700' : 'text-red-700'">
                                    {{ (searchResult.exists && searchResult.active) ? 'ACCESO PERMITIDO' : 'ACCESO DENEGADO' }}
                                </p>
                                <p class="mt-3 text-xl font-medium text-gray-800" v-if="searchResult.exists">{{ searchResult.name }}</p>
                                <p class="mt-1 text-md font-semibold text-red-500" v-if="!searchResult.exists">No registrado</p>
                            </div>
                        </div>
                    </div>

                    <!-- Listado de Miembros (Debajo) -->
                    <div class="bg-white overflow-hidden shadow-2xl rounded-3xl border border-gray-100 p-6 sm:px-0 mx-4 sm:mx-0">
                        <div class="px-6 pb-4 border-b border-gray-100 flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-extrabold text-gray-900">Directorio de Miembros</h3>
                                <p class="text-sm text-gray-500 mt-1">Listado completo de clientes registrados en el club.</p>
                                <button @click="openEditModal()" class="mt-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg shadow transition-colors">
                                    + Nuevo Miembro Manual
                                </button>
                            </div>
                            <div class="bg-indigo-100 text-indigo-800 text-sm font-bold px-4 py-2 rounded-full shadow-sm flex items-center border border-indigo-200">
                                {{ members.length }} Miembros
                            </div>
                        </div>
                        <div class="overflow-x-auto p-4">
                            <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre del Socio</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contacto</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reservas</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha Alta</th>
                                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="member in members" :key="member.id" class="hover:bg-indigo-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-lg">
                                                            {{ member.name.charAt(0).toUpperCase() }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-bold text-gray-900">{{ member.name }} {{ member.surname }}</div>
                                                        <div class="text-xs text-gray-500">nº de Socio: {{ member.id + 9000 }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 flex flex-col font-medium">
                                                    <span>{{ member.phone }}</span>
                                                    <span class="text-xs text-gray-500">{{ member.email }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-sm">
                                                    {{ member.reservations_count }} Reservas
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                                {{ new Date(member.created_at).toLocaleDateString('es-ES') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <button @click="openEditModal(member)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-lg transition-colors font-bold shadow-sm border border-indigo-100">
                                                        Editar / Ver
                                                    </button>
                                                    <button @click="deleteMember(member)" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-1.5 rounded-lg transition-colors shadow-sm border border-red-100" title="Eliminar miembro">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB EMAILS CLUB -->
                <div v-show="activeTab === 'emails'" class="bg-white overflow-hidden shadow-2xl rounded-3xl border border-gray-100 p-8 h-full">
                    <div class="flex flex-col xl:flex-row xl:justify-between xl:items-center gap-6 mb-8">
                        <div>
                            <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Plantillas Automáticas: Club Sagaretxe</h3>
                            <p class="text-gray-500">Configura los mensajes de bienvenida y códigos QR para los miembros del club.</p>
                        </div>
                        <div class="bg-gray-100/80 backdrop-blur-sm p-1.5 rounded-2xl shadow-sm flex flex-wrap sm:flex-nowrap gap-1 sm:space-x-1 border border-gray-200">
                            <button @click="emailSubTab = 'plantillas'" :class="emailSubTab === 'plantillas' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'" class="flex-1 sm:flex-none whitespace-nowrap px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-200">Plantillas</button>
                            <button @click="emailSubTab = 'cabecera'" :class="emailSubTab === 'cabecera' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'" class="flex-1 sm:flex-none whitespace-nowrap px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-200">Cabecera</button>
                            <button @click="emailSubTab = 'pie'" :class="emailSubTab === 'pie' ? 'bg-white text-indigo-700 shadow-md ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'" class="flex-1 sm:flex-none whitespace-nowrap px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-200">Pie de Página</button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-8 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <span class="text-sm font-bold text-gray-700 w-full mb-2">Shortcodes Disponibles:</span>
                        <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[nombre]</span>
                        <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[apellidos]</span>
                        <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[telefono]</span>
                        <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[email]</span>
                        <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[cp]</span>
                        <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[qr]</span>
                        <span class="bg-white text-indigo-600 font-mono text-xs px-3 py-1 rounded-full font-bold border shadow-sm">[numero_socio]</span>
                    </div>

                    <form @submit.prevent="saveEmails" class="space-y-6">
                        
                        <div v-show="emailSubTab === 'plantillas'" class="grid grid-cols-1 gap-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-indigo-50 p-6 rounded-2xl border border-indigo-100 mb-2">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Remitente (Desde - Nombre)</label>
                                    <input type="text" v-model="emailForm.club_from_name" placeholder="Ej: Club Sagaretxe" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 h-11">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Email Remitente</label>
                                    <input type="email" v-model="emailForm.club_from_email" placeholder="Ej: club@sagaretxe.com" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 h-11">
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500 transition-shadow">
                                <label class="flex items-center text-sm font-extrabold text-gray-800 mb-3"><span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center mr-2">1</span> Correo de Bienvenida (y QR)</label>
                                <input type="text" v-model="emailForm.club_subject_welcome" placeholder="Asunto del correo" class="w-full mb-3 rounded-lg border-gray-300 text-sm h-10 shadow-sm focus:ring-indigo-500">
                                <textarea v-model="emailForm.club_email_welcome" rows="7" class="w-full border-0 bg-gray-50 rounded-xl p-4 text-sm font-mono focus:ring-0 text-gray-700"></textarea>
                            </div>

                            <!-- Separador Admin -->
                            <div class="border-t-2 border-dashed border-orange-200 pt-6 mt-4">
                                <h4 class="text-lg font-extrabold text-orange-800 flex items-center mb-4">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    Notificaciones al Administrador
                                </h4>
                                <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl mb-4">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Email del Administrador (donde recibir avisos)</label>
                                    <input type="email" v-model="emailForm.club_admin_email" placeholder="Ej: admin@sagaretxe.com" class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500 h-11">
                                </div>
                            </div>

                            <div class="bg-white border-2 border-orange-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-orange-400 transition-shadow">
                                <label class="flex items-center text-sm font-extrabold text-gray-800 mb-3"><span class="w-6 h-6 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center mr-2">📩</span> Aviso Admin: Nuevo Miembro</label>
                                <input type="text" v-model="emailForm.club_subject_admin" placeholder="Asunto del correo" class="w-full mb-3 rounded-lg border-gray-300 text-sm h-10 shadow-sm focus:ring-orange-500">
                                <textarea v-model="emailForm.club_email_admin" rows="5" class="w-full border-0 bg-orange-50 rounded-xl p-4 text-sm font-mono focus:ring-0 text-gray-700"></textarea>
                            </div>
                        </div>

                        <div v-show="emailSubTab === 'cabecera'">
                            <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500">
                                <label class="block text-sm font-extrabold text-gray-800 mb-3">Cabecera HTML (Header)</label>
                                <textarea v-model="emailForm.club_email_header" rows="12" placeholder="Introduce el HTML de la cabecera (incluyendo logo, estilos iniciales...)" class="w-full border-gray-300 rounded-xl p-4 text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>

                        <div v-show="emailSubTab === 'pie'">
                            <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-sm focus-within:ring-2 focus-within:ring-indigo-500">
                                <label class="block text-sm font-extrabold text-gray-800 mb-3">Pie de Página HTML (Footer)</label>
                                <textarea v-model="emailForm.club_email_footer" rows="12" placeholder="Introduce el HTML del pie de página (redes sociales, información legal...)" class="w-full border-gray-300 rounded-xl p-4 text-sm font-mono focus:border-indigo-500 focus:ring-indigo-500"></textarea>
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

        <!-- MODAL EDITAR MIEMBRO -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-75 overflow-y-auto">
            <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full mx-auto my-8 border border-gray-200 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
                <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-2xl font-bold text-gray-900">{{ isCreating ? 'Nuevo Miembro del Club' : 'Perfil del Miembro' }}</h3>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto">
                    <form @submit.prevent="saveMember" class="space-y-6">
                        <h4 class="text-lg font-bold text-gray-700 border-b pb-2">Información Personal</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><label class="block text-sm font-bold text-gray-700">Nombre <span class="text-red-500">*</span></label><input type="text" v-model="editForm.name" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"></div>
                            <div><label class="block text-sm font-bold text-gray-700">Apellidos <span class="text-red-500">*</span></label><input type="text" v-model="editForm.surname" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"></div>
                            <div><label class="block text-sm font-bold text-gray-700">Teléfono (Móvil) <span class="text-red-500">*</span></label><input type="tel" v-model="editForm.phone" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"></div>
                            <div><label class="block text-sm font-bold text-gray-700">Email <span class="text-red-500">*</span></label><input type="email" v-model="editForm.email" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"></div>
                            <div><label class="block text-sm font-bold text-gray-700">Código Postal <span class="text-red-500">*</span></label><input type="text" v-model="editForm.postal_code" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"></div>
                        </div>

                        <h4 class="text-lg font-bold text-gray-700 border-b pb-2 mt-8">Preferencias</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><label class="block text-sm font-bold text-gray-700">¿Barra o Sala? <span class="text-red-500">*</span></label>
                                <select v-model="editForm.pref_space" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"><option value="">Seleccione una opción</option><option value="barra">Barra</option><option value="sala">Sala</option></select>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-700">¿Carne o Pescado? <span class="text-red-500">*</span></label>
                                <select v-model="editForm.pref_food" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"><option value="">Seleccione una opción</option><option value="carne">Carne</option><option value="pescado">Pescado</option></select>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-700">¿Cerveza o Sidra? <span class="text-red-500">*</span></label>
                                <select v-model="editForm.pref_drink1" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"><option value="">Seleccione una opción</option><option value="cerveza">Cerveza</option><option value="sidra">Sidra</option></select>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-700">¿Vino Tinto o Blanco? <span class="text-red-500">*</span></label>
                                <select v-model="editForm.pref_drink2" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"><option value="">Seleccione una opción</option><option value="tinto">Vino Tinto</option><option value="blanco">Vino Blanco</option></select>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-700">¿Entre semana o fin de semana? <span class="text-red-500">*</span></label>
                                <select v-model="editForm.pref_time" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"><option value="">Seleccione una opción</option><option value="semana">Entre semana</option><option value="finde">Fin de semana</option></select>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-700">¿Cómo nos ha conocido?</label>
                                <select v-model="editForm.how_knew_us" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm h-11"><option value="">Seleccione...</option><option value="prensa">Prensa</option><option value="tv">TV</option><option value="internet">Internet</option><option value="conocido">Por un conocido</option><option value="vecino">Vecino del barrio</option></select>
                            </div>
                        </div>

                        <div class="pt-6 flex justify-end gap-3 border-t">
                            <button type="button" @click="closeEditModal" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-6 rounded-xl shadow-sm transition-all">Cancelar</button>
                            <button type="submit" :disabled="editForm.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-8 rounded-xl shadow-md transition-all">
                                {{ editForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
