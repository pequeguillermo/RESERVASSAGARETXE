<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    customers: Array
});

const showModal = ref(false);

const form = useForm({
    name: '',
    phone: '',
    email: '',
});

const submitCustomer = () => {
    form.post(route('customers.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Listado de Clientes
                </h2>
                <button @click="showModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-colors">
                    + Añadir Cliente
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="p-4 font-bold text-gray-600">Nombre</th>
                                    <th class="p-4 font-bold text-gray-600">Teléfono</th>
                                    <th class="p-4 font-bold text-gray-600">Email</th>
                                    <th class="p-4 font-bold text-gray-600 text-right">Fecha de Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="customers.length === 0">
                                    <td colspan="4" class="p-8 text-center text-gray-500">No hay clientes registrados.</td>
                                </tr>
                                <tr v-for="customer in customers" :key="customer.id" class="border-b hover:bg-gray-50">
                                    <td class="p-4">{{ customer.name || '-' }}</td>
                                    <td class="p-4">{{ customer.phone || '-' }}</td>
                                    <td class="p-4">{{ customer.email || '-' }}</td>
                                    <td class="p-4 text-right text-sm text-gray-500">{{ new Date(customer.created_at).toLocaleDateString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Añadir Cliente -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 relative">
                <button @click="showModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Añadir Nuevo Cliente</h3>
                <form @submit.prevent="submitCustomer" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre (opcional)</label>
                        <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono (opcional)</label>
                        <input v-model="form.phone" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (opcional)</label>
                        <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 font-medium">Cancelar</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
