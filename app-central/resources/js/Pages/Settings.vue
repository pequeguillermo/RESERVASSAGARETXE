<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    resend_api_key: props.settings.resend_api_key || '',
});

const saveSettings = () => {
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => alert('Configuración guardada')
    });
};
</script>

<template>
    <Head title="Configuración" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-3xl font-extrabold leading-tight text-gray-900 tracking-tight">
                Configuración del Sistema
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-2xl rounded-3xl border border-gray-100">
                    <div class="p-8">
                        <div class="flex items-center mb-6">
                            <div class="bg-black p-3 rounded-xl mr-4 shadow-md">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">API y Servicios Externos</h3>
                                <p class="text-gray-500">Configuración de proveedores de correo y servicios de terceros.</p>
                            </div>
                        </div>

                        <form @submit.prevent="saveSettings" class="space-y-6">
                            
                            <div class="bg-gray-50 border border-gray-200 p-6 rounded-2xl">
                                <label class="block text-sm font-bold text-gray-800 mb-2">Resend API Key</label>
                                <p class="text-sm text-gray-500 mb-4">Clave de la API para el envío de correos transaccionales a través de Resend.com</p>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    </div>
                                    <input type="password" v-model="form.resend_api_key" placeholder="re_************************" class="w-full pl-10 rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono">
                                </div>
                            </div>
                            
                            <div class="pt-4 flex justify-end">
                                <button type="submit" :disabled="form.processing" class="bg-black hover:bg-gray-800 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-transform hover:-translate-y-1">
                                    {{ form.processing ? 'Guardando...' : 'Guardar Configuración' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
