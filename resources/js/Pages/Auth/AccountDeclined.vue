<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Account Declined - AI Blog Generator" />

    <div class="min-h-screen flex">
        <!-- Left Side - Content -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-md w-full space-y-8 text-center">
                <!-- Icon -->
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-rose-600 rounded-2xl blur-xl opacity-50"></div>
                        <div class="relative bg-gradient-to-br from-red-500 via-rose-500 to-red-600 p-4 rounded-2xl shadow-2xl">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Header -->
                <div>
                    <h2 class="text-4xl font-extrabold text-gray-900 mb-2">
                        Account Not Approved
                    </h2>
                    <p class="text-gray-600 text-lg">
                        We're sorry, but your account was not approved
                    </p>
                </div>

                <!-- Message -->
                <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-red-800 mb-2">Account Declined</h3>
                            <p class="text-red-700">
                                Unfortunately, your account registration was declined by our administrators. If you believe this was a mistake, please contact our support team.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="text-gray-600 text-sm">
                    <p>If you have questions about this decision, please reach out to our support team for assistance.</p>
                </div>

                <!-- Back to Login -->
                <div class="pt-4 space-y-3">
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center justify-center w-full py-3 px-4 border border-gray-300 text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150 ease-in-out"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Login
                    </Link>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero Section -->
        <div class="hidden lg:flex lg:flex-1 relative bg-gradient-to-br from-red-500 via-rose-500 to-red-600 overflow-hidden">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0">
                <div class="absolute top-20 left-20 w-72 h-72 bg-red-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute top-40 right-20 w-72 h-72 bg-rose-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-20 left-40 w-72 h-72 bg-red-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
            </div>

            <!-- Content -->
            <div class="relative flex flex-col justify-center px-12 text-white z-10">
                <div class="max-w-xl">
                    <h1 class="text-5xl font-extrabold mb-6 leading-tight">
                        We're Sorry
                    </h1>
                    <p class="text-xl text-red-100 mb-8 leading-relaxed">
                        Your account registration was not approved at this time. This could be for various reasons.
                    </p>

                    <!-- Reasons -->
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 mt-1">
                                <div class="flex items-center justify-center h-10 w-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-1">Have Questions?</h3>
                                <p class="text-red-100">Contact our support team for clarification</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 mt-1">
                                <div class="flex items-center justify-center h-10 w-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-1">Try Again</h3>
                                <p class="text-red-100">You may be able to register again with updated information</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes blob {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
