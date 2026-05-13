<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    users: Array,
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);

const form = useForm({
    name: '',
    role: 'admin',
    access_code: '',
});

const openModal = (user = null) => {
    if (user) {
        isEditing.value = true;
        editingUserId.value = user.id;
        form.name = user.name;
        form.role = user.role;
        form.access_code = user.access_code;
    } else {
        isEditing.value = false;
        editingUserId.value = null;
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('users.update', editingUserId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (user) => {
    if (confirm(`¿Estás seguro de que deseas eliminar a ${user.name}?`)) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Usuarios" />

        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Administradores del Sistema
                </h2>
                <PrimaryButton @click="openModal()" class="bg-blue-600 hover:bg-blue-700">
                    + Nuevo Administrador
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div v-if="$page.props.errors && $page.props.errors.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ $page.props.errors.error }}
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código Acceso</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span :class="user.role === 'superadmin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ user.role === 'superadmin' ? 'Superadministrador' : 'Administrador' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.access_code || '---' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="openModal(user)" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</button>
                                    <button @click="confirmDelete(user)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isEditing ? 'Editar Administrador' : 'Nuevo Administrador' }}
                </h2>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <InputLabel for="name" value="Nombre" />
                        <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="role" value="Rol" />
                        <select id="role" v-model="form.role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="superadmin">Superadministrador</option>
                            <option value="admin">Administrador</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.role" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="access_code" value="Código de Acceso (Numérico o PIN)" />
                        <TextInput id="access_code" type="text" class="mt-1 block w-full" v-model="form.access_code" required />
                        <InputError class="mt-2" :message="form.errors.access_code" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                        <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Guardar
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
