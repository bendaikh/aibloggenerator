<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Registration Pending - AI Blog Generator" />

    <div class="min-h-screen flex">
        <!-- Left Side - Content -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-md w-full space-y-8 text-center">
                <!-- Icon -->
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl blur-xl opacity-50 animate-pulse"></div>
                        <div class="relative bg-gradient-to-br from-yellow-400 via-orange-500 to-amber-600 p-4 rounded-2xl shadow-2xl">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Header -->
                <div>
                    <h2 class="text-4xl font-extrabold text-gray-900 mb-2">
                        Registration Submitted!
                    </h2>
                    <p class="text-gray-600 text-lg">
                        Thank you for registering with us
                    </p>
                </div>

                <!-- Message -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-semibold text-yellow-800 mb-2">Pending Approval</h3>
                            <p class="text-yellow-700">
                                Your account is pending approval from our administrators. You will be able to log in once your account has been approved.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- What happens next -->
                <div class="text-left space-y-4">
                    <h4 class="text-lg font-semibold text-gray-900">What happens next?</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <span class="text-gray-600">Our team will review your registration</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <span class="text-gray-600">You'll receive an email once approved</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <span class="text-gray-600">Then you can log in and start creating content</span>
                        </li>
                    </ul>
                </div>

                <!-- Back to Login -->
                <div class="pt-4">
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
        <div class="hidden lg:flex lg:flex-1 relative bg-gradient-to-br from-yellow-500 via-orange-500 to-amber-600 overflow-hidden">
            <!-- Animated Background Elements -->
            <div class="absolute inset-0">
                <div class="absolute top-20 left-20 w-72 h-72 bg-yellow-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute top-40 right-20 w-72 h-72 bg-orange-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-20 left-40 w-72 h-72 bg-amber-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
            </div>

            <!-- Content -->
            <div class="relative flex flex-col justify-center px-12 text-white z-10">
                <div class="max-w-xl">
                    <h1 class="text-5xl font-extrabold mb-6 leading-tight">
                        Almost There!
                    </h1>
                    <p class="text-xl text-yellow-100 mb-8 leading-relaxed">
                        Your registration is being processed. Our team ensures quality by reviewing all new accounts before granting access.
                    </p>

                    <!-- Features -->
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 mt-1">
                                <div class="flex items-center justify-center h-10 w-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-1">Quality Assurance</h3>
                                <p class="text-yellow-100">We review all accounts to maintain a high-quality community</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 mt-1">
                                <div class="flex items-center justify-center h-10 w-10 rounded-xl bg-white bg-opacity-20 backdrop-blur-sm">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-1">Quick Review</h3>
                                <p class="text-yellow-100">Most accounts are approved within 24 hours</p>
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
