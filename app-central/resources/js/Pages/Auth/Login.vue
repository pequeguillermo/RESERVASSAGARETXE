<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    role: 'admin',
    access_code: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('access_code'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="role" value="Tipo de Acceso" />

                <select
                    id="role"
                    v-model="form.role"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                >
                    <option value="superadmin">Superadministrador</option>
                    <option value="admin">Administrador</option>
                </select>

                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div class="mt-4">
                <InputLabel for="access_code" value="Número de Acceso" />

                <TextInput
                    id="access_code"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.access_code"
                    required
                    autofocus
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.access_code" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600"
                        >Recordarme</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    ¿Olvidaste tu contraseña?
                </Link>

                <PrimaryButton
                    class="ms-4 w-full justify-center py-3 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-xl shadow-md transition-all duration-200"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Entrar al Sistema
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
