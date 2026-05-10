<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Panel de Control Principal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800 tracking-tight">
                Panel de Control Principal
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-4 sm:px-0">
                    
                    <!-- Tarjeta Reservas -->
                    <Link :href="route('reservations.index')" class="group block h-full">
                        <div class="h-full bg-white overflow-hidden shadow-md hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 rounded-2xl flex flex-col border border-gray-100">
                            <div class="bg-blue-600 p-8 flex justify-center items-center group-hover:bg-blue-700 transition-colors duration-300">
                                <svg class="w-24 h-24 text-white opacity-90 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="p-8 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Gestión de Reservas</h3>
                                    <p class="text-gray-500 text-lg leading-relaxed">
                                        Administra el libro de reservas, verifica mesas, confirma asistencias y revisa los descuentos automáticos aplicados a los miembros.
                                    </p>
                                </div>
                                <div class="mt-6 flex items-center text-blue-600 font-semibold text-lg">
                                    <span>Acceder al panel</span>
                                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- Tarjeta Club Sagaretxe -->
                    <Link :href="route('club')" class="group block h-full">
                        <div class="h-full bg-white overflow-hidden shadow-md hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 rounded-2xl flex flex-col border border-gray-100">
                            <div class="bg-indigo-600 p-8 flex justify-center items-center group-hover:bg-indigo-700 transition-colors duration-300">
                                <svg class="w-24 h-24 text-white opacity-90 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            </div>
                            <div class="p-8 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Club Sagaretxe</h3>
                                    <p class="text-gray-500 text-lg leading-relaxed">
                                        Escanea los códigos QR de los miembros en puerta o realiza validaciones manuales por teléfono para registrar accesos.
                                    </p>
                                </div>
                                <div class="mt-6 flex items-center text-indigo-600 font-semibold text-lg">
                                    <span>Acceder al escáner</span>
                                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </div>
                            </div>
                        </div>
                    </Link>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
