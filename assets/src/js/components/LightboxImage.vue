<template>
    <div class="h-full">
        <div class="group h-full relative">
            <div class="bg-black/40 opacity-0 group-hover:opacity-100 flex justify-center items-center transition-all duration-300 absolute inset-0">
                <div
                    @click="openLightbox"
                    class="cursor-pointer select-none size-12 bg-blue-100 hover:bg-blue-200 transition-all duration-300 rounded-2xl flex items-center justify-center hover:scale-110 active:scale-95"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                    </svg>
                </div>
            </div>

            <img v-if="!invisible" :src="src" :alt="alt">
        </div>

        <!-- Lightbox -->
        <transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-200 ease-in"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                @click.self="closeLightbox"
                class="fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm flex flex-col max-h-screen"
            >
                <!-- Header -->
                <div class="flex p-8 pb-4">
                    <div
                        @click="closeLightbox"
                        class="cursor-pointer select-none size-12 bg-blue-100 hover:bg-blue-200
                               transition-all duration-200 rounded-2xl flex items-center justify-center
                               ml-auto hover:scale-110 active:scale-95"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center flex-1">
                    <transition
                        enter-active-class="transition-all duration-600 ease-[cubic-bezier(.16,1,.3,1)]"
                        leave-active-class="transition-all duration-200 ease-in"
                        enter-from-class="opacity-0 scale-90 translate-y-6"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-6"
                    >
                        <div class="overflow-y-auto max-h-[70vh] mx-6">
                            <img
                                :src="imageSrc ?? fullsizeSrc ?? src"
                                :alt="imageAlt ?? alt"
                                class="block"
                            />
                        </div>
                    </transition>
                    <span class="bg-black/40 rounded-xl shadow text-white font-bold py-2 my-4 px-3">{{ imageAlt ?? alt }}</span>
                </div>
                <div v-if="group" class="flex gap-4 mb-12 mx-16 flex-wrap lg:flex-nowrap justify-center">
                    <div v-for="item in group" @click="imageSrc = item.src; imageAlt = item.alt;" class="w-full max-w-24 cursor-pointer">
                        <img :class="`mb-2 ${(imageSrc ?? src) === item.src ? 'outline outline-blue-400 shadow' : ''}`" :src="item.src" :alt="item.label">
                        <span class="text-center block text-white">{{item.label}}</span>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>
export default {
    name: 'LightboxImage',
    props: ['src', 'alt', 'invisible', 'fullsizeSrc', 'group'],
    data() {
        return {
            open: false,
            imageSrc: null,
            imageAlt: null,
        };
    },
    methods: {
        openLightbox() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
            window.addEventListener('keydown', this.onKeydown);
            console.log(this.group);
        },
        closeLightbox() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
            window.removeEventListener('keydown', this.onKeydown);
        },
        onKeydown(e) {
            if (e.key === 'Escape') {
                this.closeLightbox();
            }

            if (!this.group) {
                return;
            }

            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                const index = this.group.findIndex((item) => item.src === (this.imageSrc ?? this.src)) + (e.key === 'ArrowRight' ? 1 : -1);
                const next = this.group[index > this.group.length - 1 ? 0 : (index < 0 ? this.group.length - 1 : index)];
                this.imageSrc = next.src;
                this.imageAlt = next.alt;
            }
        },
    },
    beforeUnmount() {
        document.body.classList.remove('overflow-hidden');
        window.removeEventListener('keydown', this.onKeydown);
    },
};
</script>
