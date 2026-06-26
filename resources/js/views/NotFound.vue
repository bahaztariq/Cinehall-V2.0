<template>
    <div class="relative min-h-[70vh] flex flex-col items-center justify-center text-center py-20 px-5 overflow-hidden">
        <h1 class="text-[6rem] sm:text-9xl font-black text-on-surface/5 absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 -z-10 select-none">404</h1>
        <h2 class="text-3xl sm:text-4xl font-black text-on-surface mb-4 uppercase italic">Page <span class="text-primary">Not Found</span></h2>
        <p class="text-on-surface-variant text-sm sm:text-base mb-8 max-w-md mx-auto">
            The page you're looking for doesn't exist or has been moved to another dimension.
        </p>
        <router-link to="/" class="inline-block gold-gradient px-8 py-3 rounded-xl font-bold uppercase tracking-widest transition-all shadow-[0_8px_30px_rgba(0,0,0,0.45)]">
            Back to Home
        </router-link>
    </div>
</template>
