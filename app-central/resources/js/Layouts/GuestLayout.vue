<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen relative flex flex-col justify-center items-center bg-blue-50 overflow-hidden">
        
        <!-- Fondo de Ondas SVG -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <svg class="absolute bottom-0 w-full" viewBox="0 0 1440 320" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path fill="#2563eb" fill-opacity="0.1" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,160C960,139,1056,149,1152,165.3C1248,181,1344,203,1392,213.3L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                <path fill="#1d4ed8" fill-opacity="0.15" d="M0,160L48,149.3C96,139,192,117,288,117.3C384,117,480,139,576,170.7C672,203,768,245,864,250.7C960,256,1056,224,1152,208C1248,192,1344,192,1392,192L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                <path fill="#1e3a8a" fill-opacity="0.8" d="M0,256L48,261.3C96,267,192,277,288,261.3C384,245,480,203,576,197.3C672,192,768,224,864,234.7C960,245,1056,235,1152,213.3C1248,192,1344,160,1392,144L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>

        <div class="relative z-10 w-full max-w-md">
            <div class="flex justify-center mb-8">
                <Link href="/">
                    <div class="bg-white p-4 rounded-full shadow-lg border border-blue-100">
                        <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                </Link>
            </div>

            <div class="w-full bg-white/90 backdrop-blur-md px-8 py-10 shadow-2xl sm:rounded-2xl border border-white/50">
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Sagaretxe Club</h2>
                <slot />
            </div>
        </div>
    </div>
</template>
