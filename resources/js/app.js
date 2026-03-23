import './bootstrap';
import $ from 'jquery'
import Sortable from 'sortablejs'
import Swiper from 'swiper'
import { Autoplay } from 'swiper/modules'
import confetti from 'canvas-confetti'

window.$ = $
window.jQuery = $
window.Sortable = Sortable
window.confetti = confetti

// ─── Character Carousel (Swiper) ────────────────────────────────

// Shared helpers
function setupCarouselClick(el) {
    let isDragging = false;
    return {
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
    };
}

function updateSlideA11y(slide, absProgress, visibleThreshold) {
    const isVisible = absProgress < visibleThreshold;
    if (isVisible) {
        slide.removeAttribute('aria-hidden');
        slide.removeAttribute('tabindex');
    } else {
        slide.setAttribute('aria-hidden', 'true');
        slide.setAttribute('tabindex', '-1');
    }
}

function setupAnimatedLayer(slide, absProgress) {
    const animatedEl = slide.querySelector('.character-animated-layer');
    if (!animatedEl) return;
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

function makeLabelUpdater(nameEl, jobEl) {
    return function updateCenterLabel(slides) {
        let closest = null;
        let minProgress = Infinity;
        for (let i = 0; i < slides.length; i++) {
            const ap = Math.abs(slides[i].progress);
            if (ap < minProgress) { minProgress = ap; closest = slides[i]; }
        }
        if (closest && nameEl) nameEl.textContent = closest.dataset.name || '';
        if (closest && jobEl) jobEl.textContent = closest.dataset.job || '';
    };
}

function runEntranceAnimation(el, swiper, entranceState, updateCenterLabel) {
    if (entranceState.triggered) return;
    entranceState.triggered = true;

    const slides = swiper.slides;
    let maxDelay = 0;

    for (let i = 0; i < slides.length; i++) {
        const slide = slides[i];
        const distFromCenter = Math.abs(slide.progress);
        const staggerDelay = Math.round(distFromCenter * 75);
        if (staggerDelay > maxDelay) maxDelay = staggerDelay;

        const targetScale = parseFloat(slide.dataset.targetScale) || 1;
        const targetOpacity = parseFloat(slide.dataset.targetOpacity) || 1;

        slide.style.transform = `scale(${targetScale * 1.4})`;
        slide.style.opacity = '0';
        slide.style.transition = 'none';
        slide.offsetHeight;

        slide.style.transition = `transform 650ms cubic-bezier(0.25, 0.1, 0.25, 1) ${staggerDelay}ms, opacity 650ms cubic-bezier(0.25, 0.1, 0.25, 1) ${staggerDelay}ms`;
        slide.style.transform = `scale(${targetScale})`;
        slide.style.opacity = targetOpacity;
    }

    el.classList.remove('carousel-entering');

    setTimeout(() => {
        entranceState.complete = true;
        for (let i = 0; i < slides.length; i++) {
            slides[i].style.transition = '';
            delete slides[i].dataset.targetScale;
            delete slides[i].dataset.targetOpacity;
        }
        swiper.update();
        updateCenterLabel(swiper.slides);
    }, maxDelay + 700);
}

function waitForImagesAndAnimate(el, swiper, entranceState, updateCenterLabel) {
    const trigger = () => runEntranceAnimation(el, swiper, entranceState, updateCenterLabel);
    const images = el.querySelectorAll('img[loading="eager"]');
    let loadedCount = 0;
    const totalImages = images.length;
    const maxWait = setTimeout(trigger, 2000);

    function onImageReady() {
        loadedCount++;
        if (loadedCount >= totalImages) { clearTimeout(maxWait); trigger(); }
    }

    images.forEach(img => {
        if (img.complete) onImageReady();
        else {
            img.addEventListener('load', onImageReady, { once: true });
            img.addEventListener('error', onImageReady, { once: true });
        }
    });

    if (totalImages === 0) { clearTimeout(maxWait); trigger(); }
}

// ─── Mobile Carousel (< md) ─────────────────────────────────
function initMobileCarousel() {
    const el = document.querySelector('.character-carousel-mobile');
    if (!el || el.swiper) return;

    const entranceState = { complete: false, triggered: false };
    const wrapper = el.parentElement.parentElement;
    const updateCenterLabel = makeLabelUpdater(
        wrapper.querySelector('.carousel-center-name'),
        wrapper.querySelector('.carousel-center-job')
    );
    const clickHandlers = setupCarouselClick(el);

    const swiper = new Swiper(el, {
        slidesPerView: 5,
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

        on: {
            touchStart: clickHandlers.touchStart,
            touchMove: clickHandlers.touchMove,
            click: clickHandlers.click,
            setTranslate(swiper) {
                for (let i = 0; i < swiper.slides.length; i++) {
                    const slide = swiper.slides[i];
                    const absProgress = Math.abs(slide.progress);

                    // Mobile curve: normalize over half of 5 slides (2)
                    const norm = Math.min(absProgress / 2, 1.2);
                    const scale = Math.max(1 - Math.pow(norm, 1.5) * 0.4, 0.55);
                    const opacity = Math.max(1 - Math.pow(norm, 1.3) * 0.85, 0.1);

                    if (entranceState.complete) {
                        slide.style.transform = `scale(${scale})`;
                        slide.style.opacity = opacity;
                        setupAnimatedLayer(slide, absProgress);
                        updateSlideA11y(slide, absProgress, 3);
                        updateCenterLabel(swiper.slides);
                    } else {
                        slide.dataset.targetScale = scale;
                        slide.dataset.targetOpacity = opacity;
                    }
                }
            },
            setTransition(swiper, duration) {
                if (!entranceState.complete) return;
                for (let i = 0; i < swiper.slides.length; i++) {
                    swiper.slides[i].style.transitionDuration = `${duration}ms`;
                }
            },
        },
    });

    waitForImagesAndAnimate(el, swiper, entranceState, updateCenterLabel);
}

// ─── Desktop Carousel (>= md) ───────────────────────────────
function initDesktopCarousel() {
    const el = document.querySelector('.character-carousel-desktop');
    if (!el || el.swiper) return;

    const entranceState = { complete: false, triggered: false };
    const wrapper = el.parentElement.parentElement;
    const updateCenterLabel = makeLabelUpdater(
        wrapper.querySelector('.carousel-center-name'),
        wrapper.querySelector('.carousel-center-job')
    );
    const clickHandlers = setupCarouselClick(el);

    const swiper = new Swiper(el, {
        slidesPerView: 9,
        slidesPerGroup: 1,
        centeredSlides: true,
        loop: true,
        grabCursor: true,
        spaceBetween: 6,
        speed: 750,
        watchSlidesProgress: true,
        simulateTouch: true,
        touchEventsTarget: 'container',
        passiveListeners: true,

        breakpoints: {
            1024: { slidesPerView: 11, spaceBetween: 6 },
        },

        on: {
            touchStart: clickHandlers.touchStart,
            touchMove: clickHandlers.touchMove,
            click: clickHandlers.click,
            setTranslate(swiper) {
                for (let i = 0; i < swiper.slides.length; i++) {
                    const slide = swiper.slides[i];
                    const absProgress = Math.abs(slide.progress);

                    // Desktop curve: original smooth fade (divisor ~5)
                    const norm = Math.min(absProgress / 5, 1.2);
                    const scale = Math.max(1 - Math.pow(norm, 1.5) * 0.4, 0.55);
                    const opacity = Math.max(1 - Math.pow(norm, 1.3) * 0.85, 0.1);

                    if (entranceState.complete) {
                        slide.style.transform = `scale(${scale})`;
                        slide.style.opacity = opacity;
                        setupAnimatedLayer(slide, absProgress);
                        updateSlideA11y(slide, absProgress, 6);
                        updateCenterLabel(swiper.slides);
                    } else {
                        slide.dataset.targetScale = scale;
                        slide.dataset.targetOpacity = opacity;
                    }
                }
            },
            setTransition(swiper, duration) {
                if (!entranceState.complete) return;
                for (let i = 0; i < swiper.slides.length; i++) {
                    swiper.slides[i].style.transitionDuration = `${duration}ms`;
                }
            },
        },
    });

    waitForImagesAndAnimate(el, swiper, entranceState, updateCenterLabel);
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

// ─── Collab Logo Slider (Swiper) ─────────────────────────────
function initCollabLogoSlider() {
    document.querySelectorAll('.collab-logo-slider').forEach(el => {
        if (el.swiper) return;
        new Swiper(el, {
            modules: [Autoplay],
            slidesPerView: 2,
            spaceBetween: 24,
            grabCursor: true,
            speed: 500,
            loop: true,
            autoplay: { delay: 3000, disableOnInteraction: false },
            breakpoints: {
                640: { slidesPerView: 3, spaceBetween: 32 },
                768: { slidesPerView: 4, spaceBetween: 40 },
                1024: { slidesPerView: 5, spaceBetween: 48 },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', () => { initMobileCarousel(); initDesktopCarousel(); initEpisodeCarousels(); initCollabLogoSlider(); });
document.addEventListener('livewire:navigated', () => { initMobileCarousel(); initDesktopCarousel(); initEpisodeCarousels(); initCollabLogoSlider(); });
