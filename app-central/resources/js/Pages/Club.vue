<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Html5QrcodeScanner } from 'html5-qrcode';

const scanResult = ref(null);
const scanError = ref(null);
const memberData = ref(null);
const loading = ref(false);

const startScanner = () => {
    scanResult.value = null;
    memberData.value = null;
    scanError.value = null;
    
    // Configurar scanner
    const scanner = new Html5QrcodeScanner('reader', {
        qrbox: {
            width: 250,
            height: 250,
        },
        fps: 20,
    });

    scanner.render(async (text) => {
        scanResult.value = text;
        scanner.clear();
        await validateQr(text);
    }, (error) => {
        // Ignorar errores de no encontrar QR durante el escaneo continuo
    });
};

const validateQr = async (token) => {
    loading.value = true;
    try {
        const response = await fetch(`/api/members/validate?token=${token}`);
        const data = await response.json();
        
        memberData.value = data;
        
        if (data.valid) {
            // Register validation via API
            await fetch('/api/validations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    member_id: data.member.id,
                    method: 'qr'
                })
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
                body: JSON.stringify({
                    member_id: data.member_id,
                    method: 'phone'
                })
            });
        }
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head title="Club Sagaretxe - Control de Acceso" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight">
                Control de Acceso Club
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-4 sm:px-0">
                    <!-- Scanner -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-2xl p-8 flex flex-col items-center border border-gray-100 transition-all hover:shadow-xl">
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
                            <div class="flex justify-center mb-2">
                                <svg v-if="memberData.valid" class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg v-else class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-2xl font-extrabold tracking-tight" :class="memberData.valid ? 'text-green-700' : 'text-red-700'">
                                {{ memberData.valid ? 'ACCESO PERMITIDO' : 'ACCESO DENEGADO' }}
                            </p>
                            <p class="mt-3 text-xl font-medium text-gray-800" v-if="memberData.member">
                                {{ memberData.member.name }}
                            </p>
                            <p class="mt-1 text-md font-semibold text-red-500" v-if="memberData.member && !memberData.member.active">
                                Motivo: Cuenta Inactiva
                            </p>
                            <p class="mt-1 text-md font-semibold text-red-500" v-if="!memberData.member">
                                Motivo: Código QR Inválido o Caducado
                            </p>
                        </div>
                    </div>

                    <!-- Búsqueda por teléfono -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-2xl p-8 border border-gray-100 flex flex-col items-center transition-all hover:shadow-xl">
                        <div class="bg-blue-100 p-4 rounded-full mb-6">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-6 text-gray-800 text-center tracking-tight">Validación Manual</h3>
                        <div class="flex flex-col space-y-4 w-full">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-semibold">+34</span>
                                </div>
                                <input type="tel" v-model="phoneSearch" placeholder="Teléfono del socio..." class="w-full pl-12 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xl py-4 transition-all" @keyup.enter="searchPhone">
                            </div>
                            <button @click="searchPhone" class="w-full py-4 bg-gray-900 hover:bg-gray-800 active:bg-black text-white font-bold rounded-xl text-lg shadow-md transition-all duration-200">
                                Buscar y Validar Entrada
                            </button>
                        </div>
                        
                        <div v-if="searchResult" class="mt-8 w-full p-6 rounded-xl shadow-inner text-center border-2 transition-all duration-300" :class="(searchResult.exists && searchResult.active) ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                            <div class="flex justify-center mb-2">
                                <svg v-if="(searchResult.exists && searchResult.active)" class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg v-else class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-2xl font-extrabold tracking-tight" :class="(searchResult.exists && searchResult.active) ? 'text-green-700' : 'text-red-700'">
                                {{ (searchResult.exists && searchResult.active) ? 'ACCESO PERMITIDO' : 'ACCESO DENEGADO' }}
                            </p>
                            <p class="mt-3 text-xl font-medium text-gray-800" v-if="searchResult.exists">
                                {{ searchResult.name }}
                            </p>
                            <p class="mt-1 text-md font-semibold text-red-500" v-if="searchResult.exists && !searchResult.active">
                                Motivo: Cuenta Inactiva
                            </p>
                            <p class="mt-1 text-md font-semibold text-red-500" v-if="!searchResult.exists">
                                Motivo: Teléfono no registrado en la base de datos
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
