// ── Core ─────────────────────────────────────────────────────────────────────
import './bootstrap';
import 'bootstrap';
import * as bootstrap from 'bootstrap';

// ── Icons ─────────────────────────────────────────────────────────────────────
import 'bootstrap-icons/font/bootstrap-icons.css';

// Swiper modules
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectCoverflow, EffectCube, Grid } from 'swiper/modules';
// Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-coverflow';
import 'swiper/css/effect-cube';
import 'swiper/css/grid';

// Team Swiper
const teamSwiper = new Swiper('.mySwiper', {
    modules: [Navigation, Pagination, Autoplay, EffectCoverflow],
    effect: 'coverflow',
    grabCursor: true,
    centeredSlides: true,
    slidesPerView: 'auto',
    coverflowEffect: {
        rotate: 50,
        stretch: 0,
        depth: 100,
        modifier: 1,
        slideShadows: true,
    },
    autoplay: {
        delay: 8500,
        disableOnInteraction: false,
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
});

// ── Telephone Input ──────────────────────────────────────────────────────────
import intlTelInput from 'intl-tel-input';
window.intlTelInput = intlTelInput;

const registerAlpineIntlTelInput = () => {
    Alpine.data('intlTelInput', (wireModelParam) => ({
        init() {
            const input = this.$el;
            const siteCountry = (window.sitePhoneCountry || 'bd').toLowerCase();
            const iti = window.intlTelInput(input, {
                allowDropdown: false,
                initialCountry: siteCountry,
                onlyCountries: [siteCountry],
                separateDialCode: true,
                strictMode: true,
                loadUtils: () => import("intl-tel-input/dist/js/utils.js")
            });

            // Dynamically discover wire:model attribute from element if not passed as parameter
            let wireModel = wireModelParam;
            if (!wireModel) {
                const attrs = input.attributes;
                for (let i = 0; i < attrs.length; i++) {
                    if (attrs[i].name.startsWith('wire:model')) {
                        wireModel = attrs[i].value;
                        break;
                    }
                }
            }

            // Discover x-model if present
            let xModel = null;
            const attrs = input.attributes;
            for (let i = 0; i < attrs.length; i++) {
                if (attrs[i].name === 'x-model' || attrs[i].name === 'x-model.defer') {
                    xModel = attrs[i].value;
                    break;
                }
            }

            let isInternalUpdate = false;

            // Attach references directly to the element to access them globally in the form submit handler
            input._iti = iti;
            input._hasBeenBlurred = false;

            // For standard HTML form inputs (non-Livewire), create a hidden input with the original name to store the full number
            let hiddenInput = null;
            if (!wireModel && input.name) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = input.name;
                let initialFull = iti.getNumber();
                if (initialFull && initialFull.startsWith('+')) {
                    initialFull = initialFull.substring(1);
                }
                hiddenInput.value = initialFull || input.value;
                input.removeAttribute('name');
                input.parentNode.appendChild(hiddenInput);
            }

            // Retrieve initial value from Livewire property, x-model, or input value
            let initialValue = '';
            if (wireModel && this.$wire) {
                try {
                    initialValue = this.$wire.get(wireModel);
                } catch (e) {}
            }
            if (!initialValue && xModel) {
                try {
                    initialValue = Alpine.evaluate(input, xModel);
                } catch (e) {}
            }
            if (!initialValue && input.value) {
                initialValue = input.value;
            }
            if (initialValue) {
                // Prepend '+' if the number starts with any country code (doesn't start with '+' or '0')
                if (!initialValue.startsWith('+') && !initialValue.startsWith('0')) {
                    initialValue = '+' + initialValue;
                }
                iti.setNumber(initialValue);
            }

            if (wireModel) {
                this.$watch(() => {
                    try {
                        return this.$wire.get(wireModel);
                    } catch (e) {
                        return null;
                    }
                }, value => {
                    if (value) {
                        const cleanValue = String(value).replace(/\D/g, '');
                        const cleanIti = iti.getNumber().replace(/\D/g, '');
                        if (cleanValue !== cleanIti) {
                            isInternalUpdate = true;
                            iti.setNumber('+' + cleanValue);
                            isInternalUpdate = false;
                        }
                    } else {
                        if (iti.getNumber() !== '') {
                            isInternalUpdate = true;
                            iti.setNumber('');
                            isInternalUpdate = false;
                        }
                    }
                });
            }

            if (xModel) {
                this.$watch(() => {
                    try {
                        return Alpine.evaluate(input, xModel);
                    } catch (e) {
                        return null;
                    }
                }, value => {
                    if (value) {
                        const cleanValue = String(value).replace(/\D/g, '');
                        const cleanIti = iti.getNumber().replace(/\D/g, '');
                        if (cleanValue !== cleanIti) {
                            isInternalUpdate = true;
                            iti.setNumber('+' + cleanValue);
                            isInternalUpdate = false;
                        }
                    } else {
                        if (iti.getNumber() !== '') {
                            isInternalUpdate = true;
                            iti.setNumber('');
                            isInternalUpdate = false;
                        }
                    }
                });
            }

            // Create or select an error message placeholder
            const errorMsg = document.createElement('div');
            errorMsg.className = 'invalid-feedback';
            errorMsg.style.display = 'none';
            errorMsg.style.fontSize = '0.875rem';
            errorMsg.style.marginTop = '0.25rem';
            // Insert errorMsg right after the intl-tel-input wrapper
            input.parentNode.parentNode.appendChild(errorMsg);

            const validateNumber = () => {
                if (!input._hasBeenBlurred) return;
                const val = input.value.trim();
                if (val) {
                    let isValid = iti.isValidNumber();

                    // Additionally reject fixed-line-only numbers (too short for mobile format)
                    // getNumberType() returns MOBILE(1), FIXED_LINE_OR_MOBILE(0), FIXED_LINE(2), etc.
                    if (isValid && window.intlTelInputUtils) {
                        const type    = iti.getNumberType();
                        const utils   = window.intlTelInputUtils;
                        const okTypes = [
                            utils.numberType.MOBILE,
                            utils.numberType.FIXED_LINE_OR_MOBILE,
                            -1  // UNKNOWN — don't block if type is indeterminate
                        ];
                        if (!okTypes.includes(type)) {
                            isValid = false;
                        }
                    }

                    if (isValid) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                        errorMsg.style.display = 'none';
                    } else {
                        const errorCode = iti.getValidationError();
                        const errorMap = [
                            "Invalid number",
                            "Invalid country code",
                            "Too short",
                            "Too long",
                            "Invalid number"
                        ];
                        // If number type is wrong (e.g. fixed-line only), show appropriate message
                        const type        = window.intlTelInputUtils ? iti.getNumberType() : -1;
                        const isWrongType = type === 2; // FIXED_LINE
                        errorMsg.innerText = isWrongType
                            ? "Please enter a mobile number"
                            : (errorMap[errorCode] || "Invalid phone number");
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                        errorMsg.style.display = 'block';
                    }
                } else {
                    input.classList.remove('is-invalid', 'is-valid');
                    errorMsg.style.display = 'none';
                }
            };

            input._validateNumber = validateNumber;

            // Intercept all input events to prevent Alpine's native model sync from reading the partial number.
            // We update Alpine/Livewire models manually in handleUpdate with the full number.
            input.addEventListener('input', (e) => {
                e.stopPropagation();
            }, true);

            const handleUpdate = () => {
                if (isInternalUpdate) return;
                let fullNumber = iti.getNumber();
                // Strip the leading '+' prefix to match database representation (e.g. 8801751136819)
                if (fullNumber && fullNumber.startsWith('+')) {
                    fullNumber = fullNumber.substring(1);
                }
                if (wireModel) {
                    this.$wire.set(wireModel, fullNumber);
                } else if (xModel) {
                    try {
                        Alpine.evaluate(input, `${xModel} = '${fullNumber}'`);
                    } catch (e) {
                        console.warn('Failed to set x-model value via Alpine.evaluate:', e);
                    }
                } else if (hiddenInput) {
                    hiddenInput.value = fullNumber;
                }

                validateNumber();
            };

            input.addEventListener('countrychange', handleUpdate);
            input.addEventListener('blur', () => {
                input._hasBeenBlurred = true;
                handleUpdate();
            });
            input.addEventListener('input', () => {
                handleUpdate();
                if (input._hasBeenBlurred) {
                    validateNumber();
                }
            });
            input.addEventListener('change', handleUpdate);
        }
    }));
};

if (typeof Alpine !== 'undefined') {
    registerAlpineIntlTelInput();
} else {
    document.addEventListener('alpine:init', registerAlpineIntlTelInput);
}

// ── Back to Top Button ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const backToTopBtn = document.getElementById('btn-back-to-top');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            // Show/hide button
            if (window.scrollY > 300) {
                backToTopBtn.style.display = 'flex';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });
        
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});

// ── Swiper Helpers & Responsive Re-initialization ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    let pricingSwiper = null;
    let blogSwiper = null;
    let reviewsSwiper = null;
    let testimonialSwiper = null;
    let lastIsMobile = window.innerWidth < 992;
    
    const initSwipers = () => {
        const isMobile = window.innerWidth < 992;
        
        // 1. Pricing Swiper
        if (document.querySelector('.pricing-swiper')) {
            if (pricingSwiper) pricingSwiper.destroy(true, true);
            
            let config = {
                modules: [Navigation, Pagination, Autoplay, EffectCoverflow, EffectCube],
                autoplay: {
                    delay: 6000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '#pricing-table .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '#pricing-table .pricing-next-btn',
                    prevEl: '#pricing-table .pricing-prev-btn',
                }
            };
            
            if (isMobile) {
                config.effect = 'coverflow';
                config.grabCursor = true;
                config.centeredSlides = true;
                config.slidesPerView = 'auto';
                config.coverflowEffect = {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows: true,
                };
            } else {
                config.slidesPerView = 4;
                config.spaceBetween = 30;
            }
            pricingSwiper = new Swiper('.pricing-swiper', config);
        }
        
        // 2. Blog Swiper
        if (document.querySelector('.blog-swiper')) {
            if (blogSwiper) blogSwiper.destroy(true, true);
            
            let config = {
                modules: [Navigation, Pagination, Autoplay, EffectCoverflow, EffectCube],
                autoplay: {
                    delay: 7000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '#blog .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '#blog .blog-next-btn',
                    prevEl: '#blog .blog-prev-btn',
                }
            };
            
            if (isMobile) {
                config.effect = 'cube';
                config.grabCursor = true;
                config.cubeEffect = {
                    shadow: true,
                    slideShadows: true,
                    shadowOffset: 20,
                    shadowScale: 0.94,
                };
            } else {
                config.slidesPerView = 3;
                config.spaceBetween = 30;
            }
            blogSwiper = new Swiper('.blog-swiper', config);
        }
        
        // 3. Reviews Swiper
        if (document.querySelector('.reviews-swiper')) {
            if (reviewsSwiper) reviewsSwiper.destroy(true, true);
            
            let config = {
                modules: [Navigation, Pagination, Autoplay, EffectCoverflow, Grid],
                autoplay: {
                    delay: 8000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '#reviews .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '#reviews .reviews-next-btn',
                    prevEl: '#reviews .reviews-prev-btn',
                }
            };
            
            if (isMobile) {
                config.effect = 'coverflow';
                config.grabCursor = true;
                config.centeredSlides = true;
                config.slidesPerView = 'auto';
                config.coverflowEffect = {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows: true,
                };
            } else {
                config.slidesPerView = 3;
                config.spaceBetween = 30;
            }
            reviewsSwiper = new Swiper('.reviews-swiper', config);
        }
        
        // 4. Testimonials Swiper
        if (document.querySelector('.testimonial-swiper')) {
            if (testimonialSwiper) testimonialSwiper.destroy(true, true);
            
            let config = {
                modules: [Navigation, Pagination, Autoplay, EffectCoverflow],
                autoplay: {
                    delay: 6500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '#testimonial .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '#testimonial .testimonial-next-btn',
                    prevEl: '#testimonial .testimonial-prev-btn',
                }
            };
            
            if (isMobile) {
                config.effect = 'coverflow';
                config.grabCursor = true;
                config.centeredSlides = true;
                config.slidesPerView = 'auto';
                config.coverflowEffect = {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows: true,
                };
            } else {
                config.slidesPerView = 2;
                config.spaceBetween = 30;
            }
            testimonialSwiper = new Swiper('.testimonial-swiper', config);
        }
    };
    
    // Bootstrap Gallery Modal Image dynamic loader
    const galleryModal = document.getElementById('galleryModal');
    if (galleryModal) {
        galleryModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const imageSrc = button.getAttribute('data-bs-image');
            const caption = button.getAttribute('data-bs-caption');
            
            const modalImage = galleryModal.querySelector('#galleryModalImage');
            const modalTitle = galleryModal.querySelector('#galleryModalLabel');
            
            if (modalImage) modalImage.src = imageSrc;
            if (modalTitle) modalTitle.textContent = caption || '';
        });
    }
    
    // Initial call
    initSwipers();
    
    // Listen to resize and re-init ONLY when crossing the breakpoint threshold
    window.addEventListener('resize', () => {
        const isMobile = window.innerWidth < 992;
        if (isMobile !== lastIsMobile) {
            lastIsMobile = isMobile;
            initSwipers();
        }
    });
});