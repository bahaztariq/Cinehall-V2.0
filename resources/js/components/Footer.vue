<template>
    <footer class="bg-black border-t border-white/10 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <span class="text-2xl font-black tracking-tighter text-white uppercase italic mb-4 block">
                        Cine<span class="text-yellow-600">hall</span>
                    </span>
                    <p class="text-gray-400 max-w-sm">
                        Experience the magic of cinema with Cinehall. The ultimate destination for movie lovers.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-widest">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><router-link to="/" class="text-gray-400 hover:text-white transition-colors text-sm">Home</router-link></li>
                        <li><router-link to="/login" class="text-gray-400 hover:text-white transition-colors text-sm">Login</router-link></li>
                        <li><router-link to="/register" class="text-gray-400 hover:text-white transition-colors text-sm">Register</router-link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-widest">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-white/5 text-center text-gray-500 text-sm">
                &copy; {{ new Date().getFullYear() }} Cinehall. All rights reserved.
            </div>
        </div>
    </footer>
</template>
