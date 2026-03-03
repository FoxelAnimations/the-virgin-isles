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

    const slideCount = el.querySelectorAll('.swiper-slide').length;
    const canLoop = slideCount >= 5;

    new Swiper(el, {
        slidesPerView: Math.min(slideCount, 5),
        slidesPerGroup: canLoop ? 2 : 1,
        centeredSlides: true,
        loop: canLoop,
        grabCursor: true,
        spaceBetween: 16,
        speed: 750,
        watchSlidesProgress: true,

        breakpoints: {
            768: {
                slidesPerView: Math.min(slideCount, 7),
                slidesPerGroup: canLoop ? 2 : 1,
                spaceBetween: 16,
            },
            1024: {
                slidesPerView: Math.min(slideCount, 9),
                slidesPerGroup: canLoop ? 3 : 1,
                spaceBetween: 16,
            },
        },

        on: {
            setTranslate(swiper) {
                for (let i = 0; i < swiper.slides.length; i++) {
                    const slide = swiper.slides[i];
                    const absProgress = Math.min(Math.abs(slide.progress), 3);
                    const scale = Math.max(1 - absProgress * 0.25, 0.4);
                    const opacity = Math.max(1 - absProgress * 0.5, 0.08);
                    slide.style.transform = `scale(${scale})`;
                    slide.style.opacity = opacity;
                }
            },
            setTransition(swiper, duration) {
                for (let i = 0; i < swiper.slides.length; i++) {
                    swiper.slides[i].style.transitionDuration = `${duration}ms`;
                }
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', initCharacterCarousel);
document.addEventListener('livewire:navigated', initCharacterCarousel);