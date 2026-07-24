const header = document.querySelector("[data-header]");
const menuToggle = document.querySelector("[data-menu-toggle]");
const musicToggle = document.querySelector("[data-music-toggle]");
const musicIcon = document.querySelector("[data-music-icon]");
const weddingMusic = document.getElementById("weddingMusic");

const closeNavigation = () => {
    header?.classList.remove("is-open");
};

const initializeNavigation = () => {
    menuToggle?.addEventListener("click", () => {
        header?.classList.toggle("is-open");
    });

    document.querySelectorAll(".main-navigation a").forEach((link) => {
        link.addEventListener("click", closeNavigation);
    });
};

const updateMusicToggleState = () => {
    if (!weddingMusic || !musicToggle || !musicIcon) {
        return;
    }

    const isMuted = weddingMusic.paused;
    musicToggle.classList.toggle("is-muted", isMuted);
    musicIcon.textContent = isMuted ? "×" : "♪";
};

const initializeMusicToggle = () => {
    updateMusicToggleState();

    musicToggle?.addEventListener("click", async () => {
        if (!weddingMusic) {
            return;
        }

        if (weddingMusic.paused) {
            try {
                await weddingMusic.play();
            } catch (error) {
                return;
            }

            updateMusicToggleState();

            return;
        }

        weddingMusic.pause();
        updateMusicToggleState();
    });

    weddingMusic?.addEventListener("play", updateMusicToggleState);
    weddingMusic?.addEventListener("pause", updateMusicToggleState);
};

const initializeScrollAnimations = () => {
    if (!window.gsap || !window.ScrollTrigger) {
        return;
    }

    gsap.registerPlugin(ScrollTrigger);

    gsap.utils.toArray(".reveal-item").forEach((element) => {
        gsap.from(element, {
            opacity: 0,
            y: 34,
            duration: 0.9,
            ease: "power3.out",
            scrollTrigger: {
                trigger: element,
                start: "top 86%"
            }
        });
    });

    gsap.utils.toArray(".reveal-card").forEach((element) => {
        gsap.from(element, {
            opacity: 0,
            y: 54,
            scale: 0.96,
            rotateX: 8,
            duration: 1,
            ease: "power3.out",
            scrollTrigger: {
                trigger: element,
                start: "top 84%"
            }
        });
    });

    gsap.utils.toArray(".reveal-gallery").forEach((element, index) => {
        gsap.from(element, {
            opacity: 0,
            y: 44,
            scale: 0.92,
            rotate: index % 2 === 0 ? -2 : 2,
            duration: 0.9,
            ease: "power3.out",
            scrollTrigger: {
                trigger: element,
                start: "top 88%"
            }
        });
    });
};

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closeNavigation();
    }
});

initializeNavigation();
initializeMusicToggle();
initializeScrollAnimations();