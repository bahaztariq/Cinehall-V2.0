<template>
    <div class="text-center py-20 px-5">
        <h1 class="text-9xl font-black text-white/10 absolute left-1/2 -translate-x-1/2 top-40 -z-10 select-none">404</h1>
        <h2 class="text-4xl font-black text-white mb-4 uppercase italic">Page <span class="text-yellow-600">Not Found</span></h2>
        <p class="text-gray-400 mb-8 max-w-md mx-auto">
            The page you're looking for doesn't exist or has been moved to another dimension.
        </p>
        <router-link to="/" class="inline-block bg-yellow-600 hover:bg-yellow-700 text-white font-bold px-8 py-3 rounded-xl transition-all shadow-lg shadow-yellow-600/20 uppercase tracking-widest">
            Back to Home
        </router-link>
    </div>
</template>
