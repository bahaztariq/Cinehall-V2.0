<template>
    <footer class="w-full py-12 sm:py-16 px-6 sm:px-10 lg:px-margin-desktop mt-16 sm:mt-20 border-t border-outline-variant bg-surface-container-low">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 md:gap-12 mb-10 md:mb-16">
                <!-- Branding column -->
                <div class="col-span-1 md:col-span-1 space-y-4">
                    <router-link to="/" class="flex items-center gap-3 select-none">
                        <img src="/assets/logo_icon.png" class="h-9 w-9 object-contain" alt="" />
                        <span class="font-serif text-2xl font-bold text-primary tracking-tight">Cinehall</span>
                    </router-link>
                    <p class="text-on-surface-variant text-sm leading-relaxed">
                        Redefining the cinematic experience with modern opulence and unparalleled service.
                    </p>
                </div>
                
                <!-- Quick links -->
                <div>
                    <h4 class="font-sans text-sm font-bold text-primary uppercase tracking-widest mb-6">Experience</h4>
                    <ul class="space-y-4">
                        <li><router-link to="/" class="text-on-surface-variant hover:text-primary transition-colors text-sm">Now Playing</router-link></li>
                        <li><router-link to="/favourites" class="text-on-surface-variant hover:text-primary transition-colors text-sm">Favourites</router-link></li>
                        <li><a href="#" class="text-on-surface-variant hover:text-primary transition-colors text-sm">Private Screenings</a></li>
                        <li><a href="#" class="text-on-surface-variant hover:text-primary transition-colors text-sm">Gourmet Dining</a></li>
                    </ul>
                </div>

                <!-- Legal links -->
                <div>
                    <h4 class="font-sans text-sm font-bold text-primary uppercase tracking-widest mb-6">Legal</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-on-surface-variant hover:text-primary transition-colors text-sm">Privacy Policy</a></li>
                        <li><a href="#" class="text-on-surface-variant hover:text-primary transition-colors text-sm">Terms of Service</a></li>
                        <li><a href="#" class="text-on-surface-variant hover:text-primary transition-colors text-sm">Cookie Policy</a></li>
                    </ul>
                </div>

                <!-- Socials/Actions -->
                <div>
                    <h4 class="font-sans text-sm font-bold text-primary uppercase tracking-widest mb-6">Follow the Magic</h4>
                    <div class="flex gap-4">
                        <a href="#" aria-label="Visit our website" class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-all">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">public</span>
                        </a>
                        <a href="#" aria-label="Share" class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-all">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">share</span>
                        </a>
                        <a href="#" aria-label="Email us" class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary hover:bg-primary hover:text-on-primary transition-all">
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">mail</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom panel -->
            <div class="pt-8 border-t border-outline-variant flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="font-sans text-xs uppercase tracking-widest text-on-surface-variant">&copy; {{ new Date().getFullYear() }} Cinehall. Modern Opulence in Cinema.</p>
                <div class="flex items-center gap-2 text-primary font-bold">
                    <span class="material-symbols-outlined" aria-hidden="true">verified</span>
                    <span class="text-xs uppercase tracking-widest">Certified Luxury Experience</span>
                </div>
            </div>
        </div>
    </footer>
</template>
