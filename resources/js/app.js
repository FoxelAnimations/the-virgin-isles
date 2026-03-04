import './bootstrap';
import $ from 'jquery'
import Sortable from 'sortablejs'
import Swiper from 'swiper'

window.$ = $
window.jQuery = $
window.Sortable = Sortable

// ─── Character Carousel (Swiper) ────────────────────────────────
function initCharacterCarousel() {
    const el = document.querySelector('.character-carousel');
    if (!el || el.swiper) return;

    let entranceComplete = false;
    let entranceTriggered = false;
    let isDragging = false;

    // Name/job label elements (labels are in the wire:ignore wrapper, two levels up)
    const wrapper = el.parentElement.parentElement;
    const nameEl = wrapper.querySelector('.carousel-center-name');
    const jobEl = wrapper.querySelector('.carousel-center-job');

    function updateCenterLabel(slides) {
        let closest = null;
        let minProgress = Infinity;
        for (let i = 0; i < slides.length; i++) {
            const ap = Math.abs(slides[i].progress);
            if (ap < minProgress) {
                minProgress = ap;
                closest = slides[i];
            }
        }
        if (closest && nameEl) {
            nameEl.textContent = closest.dataset.name || '';
        }
        if (closest && jobEl) {
            jobEl.textContent = closest.dataset.job || '';
        }
    }

    const swiper = new Swiper(el, {
        slidesPerView: 3,
        slidesPerGroup: 1,
        centeredSlides: true,
        loop: true,
        grabCursor: true,
        spaceBetween: 4,
        speed: 750,
        watchSlidesProgress: true,
        simulateTouch: true,
        touchEventsTarget: 'container',
        passiveListeners: true,

        breakpoints: {
            640: { slidesPerView: 5, spaceBetween: 4 },
            768: { slidesPerView: 9, spaceBetween: 6 },
            1024: { slidesPerView: 11, spaceBetween: 6 },
        },

        on: {
            touchStart() { isDragging = false; },
            touchMove() { isDragging = true; },
            click(swiper, event) {
                if (isDragging) return;
                const slide = event.target.closest('.swiper-slide');
                if (!slide || !slide.dataset.characterJson) return;
                try {
                    const data = JSON.parse(slide.dataset.characterJson);
                    el.dispatchEvent(new CustomEvent('character-popup', { bubbles: true, detail: data }));
                } catch (e) {}
            },
            setTranslate(swiper) {
                // Adapt curve based on current slidesPerView so center is always prominent
                const spv = swiper.params.slidesPerView || 3;
                const halfView = Math.max(Math.floor(spv / 2), 1);

                for (let i = 0; i < swiper.slides.length; i++) {
                    const slide = swiper.slides[i];
                    const absProgress = Math.abs(slide.progress);

                    // Normalize: 0 at center, ~1 at visible edge
                    const norm = Math.min(absProgress / halfView, 1.2);
                    const scale = Math.max(1 - Math.pow(norm, 1.5) * 0.4, 0.55);
                    const opacity = Math.max(1 - Math.pow(norm, 1.3) * 0.85, 0.1);

                    if (entranceComplete) {
                        slide.style.transform = `scale(${scale})`;
                        slide.style.opacity = opacity;

                        // Animated layer: show when selected (center)
                        const animatedEl = slide.querySelector('.character-animated-layer');
                        if (animatedEl) {
                            const isSelected = absProgress < 0.5;
                            const wasSelected = slide.dataset.wasSelected === 'true';

                            if (isSelected && !wasSelected) {
                                animatedEl.style.opacity = '1';
                                const staticImg = slide.querySelector('.character-static-img');
                                if (staticImg) staticImg.style.opacity = '0';
                                const hoverImg = slide.querySelector('.character-hover-img');
                                if (hoverImg) hoverImg.style.opacity = '0';

                                if (animatedEl.tagName === 'VIDEO') {
                                    animatedEl.currentTime = 0;
                                    animatedEl.play().catch(() => {});
                                } else {
                                    const src = animatedEl.src;
                                    animatedEl.src = '';
                                    animatedEl.offsetHeight;
                                    animatedEl.src = src;
                                }
                            } else if (!isSelected && wasSelected) {
                                animatedEl.style.opacity = '0.01';
                                const staticImg = slide.querySelector('.character-static-img');
                                if (staticImg) staticImg.style.opacity = '';
                                const hoverImg = slide.querySelector('.character-hover-img');
                                if (hoverImg) hoverImg.style.opacity = '';
                            }

                            slide.dataset.wasSelected = isSelected ? 'true' : 'false';
                        }

                        // Update name/age label continuously during swipe
                        updateCenterLabel(swiper.slides);
                    } else {
                        slide.dataset.targetScale = scale;
                        slide.dataset.targetOpacity = opacity;
                    }
                }
            },
            setTransition(swiper, duration) {
                if (!entranceComplete) return;
                for (let i = 0; i < swiper.slides.length; i++) {
                    swiper.slides[i].style.transitionDuration = `${duration}ms`;
                }
            },
        },
    });

    // ─── Entrance Animation ─────────────────────────────────────
    function triggerEntrance() {
        if (entranceTriggered) return;
        entranceTriggered = true;

        const slides = swiper.slides;
        let maxDelay = 0;

        for (let i = 0; i < slides.length; i++) {
            const slide = slides[i];
            const distFromCenter = Math.abs(slide.progress);
            const staggerDelay = Math.round(distFromCenter * 75);
            if (staggerDelay > maxDelay) maxDelay = staggerDelay;

            const targetScale = parseFloat(slide.dataset.targetScale) || 1;
            const targetOpacity = parseFloat(slide.dataset.targetOpacity) || 1;

            // Start: invisible, 1.4x enlarged relative to target
            slide.style.transform = `scale(${targetScale * 1.4})`;
            slide.style.opacity = '0';
            slide.style.transition = 'none';
            slide.offsetHeight; // force reflow

            // Animate to target
            slide.style.transition = `transform 650ms cubic-bezier(0.25, 0.1, 0.25, 1) ${staggerDelay}ms, opacity 650ms cubic-bezier(0.25, 0.1, 0.25, 1) ${staggerDelay}ms`;
            slide.style.transform = `scale(${targetScale})`;
            slide.style.opacity = targetOpacity;
        }

        // Remove .carousel-entering so CSS override stops
        el.classList.remove('carousel-entering');

        // After animation finishes, hand control to Swiper
        setTimeout(() => {
            entranceComplete = true;
            for (let i = 0; i < slides.length; i++) {
                slides[i].style.transition = '';
                delete slides[i].dataset.targetScale;
                delete slides[i].dataset.targetOpacity;
            }
            swiper.update();

            // Show initial center label
            updateCenterLabel(swiper.slides);
        }, maxDelay + 700);
    }

    // Wait for images to load, or max 2 seconds
    const images = el.querySelectorAll('img[loading="eager"]');
    let loadedCount = 0;
    const totalImages = images.length;
    const maxWait = setTimeout(triggerEntrance, 2000);

    function onImageReady() {
        loadedCount++;
        if (loadedCount >= totalImages) {
            clearTimeout(maxWait);
            triggerEntrance();
        }
    }

    images.forEach(img => {
        if (img.complete) {
            onImageReady();
        } else {
            img.addEventListener('load', onImageReady, { once: true });
            img.addEventListener('error', onImageReady, { once: true });
        }
    });

    // Fallback if no images at all
    if (totalImages === 0) {
        clearTimeout(maxWait);
        triggerEntrance();
    }
}

// ─── Episode Category Carousels (Swiper) ─────────────────────
function initEpisodeCarousels() {
    document.querySelectorAll('.episode-carousel').forEach(el => {
        if (el.swiper) return;
        new Swiper(el, {
            slidesPerView: 2,
            slidesPerGroup: 1,
            spaceBetween: 12,
            grabCursor: true,
            speed: 500,
            breakpoints: {
                640: { slidesPerView: 3, spaceBetween: 16 },
                1024: { slidesPerView: 4, spaceBetween: 16 },
                1280: { slidesPerView: 5, spaceBetween: 16 },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', () => { initCharacterCarousel(); initEpisodeCarousels(); });
document.addEventListener('livewire:navigated', () => { initCharacterCarousel(); initEpisodeCarousels(); });
