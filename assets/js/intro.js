const intro = document.querySelector("[data-intro]");
const page = document.querySelector("[data-page]");
const openInvitationButton = document.querySelector("[data-open-invitation]");
const introContent = document.querySelector(".intro-content");
const leftCurtain = document.querySelector(".cinema-curtain-left");
const rightCurtain = document.querySelector(".cinema-curtain-right");
const leftFabric = document.querySelector(".cinema-curtain-left .curtain-fabric");
const rightFabric = document.querySelector(".cinema-curtain-right .curtain-fabric");
const leftEdge = document.querySelector(".curtain-book-edge-left");
const rightEdge = document.querySelector(".curtain-book-edge-right");
const curtainValance = document.querySelector(".curtain-valance");
const curtainCenterLine = document.querySelector(".curtain-center-line");
const curtainFloorShadow = document.querySelector(".curtain-floor-shadow");
const introSparkles = document.querySelectorAll(".intro-sparkles span");
const weddingMusicElement = document.getElementById("weddingMusic");
const musicToggleButton = document.querySelector("[data-music-toggle]");
const musicIconElement = document.querySelector("[data-music-icon]");

let isInvitationOpening = false;

const syncMusicButtonState = (isPlaying) => {
    if (!musicToggleButton || !musicIconElement) {
        return;
    }

    musicToggleButton.classList.toggle("is-muted", !isPlaying);
    musicIconElement.textContent = isPlaying ? "♪" : "×";
};

const playWeddingMusic = async () => {
    if (!weddingMusicElement) {
        return false;
    }

    try {
        weddingMusicElement.volume = 0.42;
        await weddingMusicElement.play();
        syncMusicButtonState(true);

        return true;
    } catch (error) {
        syncMusicButtonState(false);

        return false;
    }
};

const openInvitation = async () => {
    if (isInvitationOpening) {
        return;
    }

    isInvitationOpening = true;

    await playWeddingMusic();

    if (window.gsap) {
        gsap.set(page, {
            autoAlpha: 1
        });

        gsap.set([leftCurtain, rightCurtain, leftFabric, rightFabric, leftEdge, rightEdge], {
            transformPerspective: 2200,
            transformStyle: "preserve-3d",
            backfaceVisibility: "visible"
        });

        gsap.set(leftCurtain, {
            transformOrigin: "left center"
        });

        gsap.set(rightCurtain, {
            transformOrigin: "right center"
        });

        gsap.set(leftFabric, {
            transformOrigin: "left center",
            clipPath: "polygon(0 0, 100% 0, 100% 100%, 0 100%)"
        });

        gsap.set(rightFabric, {
            transformOrigin: "right center",
            clipPath: "polygon(0 0, 100% 0, 100% 100%, 0 100%)"
        });

        const timeline = gsap.timeline({
            defaults: {
                ease: "power4.inOut"
            },
            onComplete: () => {
                intro?.remove();
                document.body.classList.remove("is-locked");
            }
        });

        timeline
            .to(openInvitationButton, {
                scale: 0.94,
                duration: 0.16,
                ease: "power2.out"
            })
            .to(introContent, {
                opacity: 0,
                y: -34,
                scale: 0.96,
                duration: 0.5,
                ease: "power2.inOut"
            })
            .to(introSparkles, {
                opacity: 0,
                scale: 2.2,
                y: -28,
                duration: 0.4,
                stagger: 0.035
            }, "-=0.26")
            .to(curtainCenterLine, {
                opacity: 0,
                scaleY: 0.72,
                duration: 0.34,
                ease: "power2.out"
            }, "-=0.14")
            .to(leftFabric, {
                rotationY: -34,
                skewY: -8,
                xPercent: -2,
                z: 42,
                borderTopRightRadius: "18vw",
                borderBottomRightRadius: "22vw",
                clipPath: "polygon(0 0, 100% 7%, 86% 100%, 0 100%)",
                duration: 0.62,
                ease: "power2.inOut"
            }, "-=0.02")
            .to(rightFabric, {
                rotationY: 34,
                skewY: 8,
                xPercent: 2,
                z: 42,
                borderTopLeftRadius: "18vw",
                borderBottomLeftRadius: "22vw",
                clipPath: "polygon(0 7%, 100% 0, 100% 100%, 14% 100%)",
                duration: 0.62,
                ease: "power2.inOut"
            }, "<")
            .to(leftEdge, {
                rotationY: -24,
                skewY: -6,
                x: -18,
                z: 90,
                duration: 0.62,
                ease: "power2.inOut"
            }, "<")
            .to(rightEdge, {
                rotationY: 24,
                skewY: 6,
                x: 18,
                z: 90,
                duration: 0.62,
                ease: "power2.inOut"
            }, "<")
            .to(leftCurtain, {
                rotationY: -76,
                rotationZ: -2.6,
                skewY: -9,
                xPercent: -11,
                z: -70,
                filter: "brightness(0.76)",
                duration: 0.86,
                ease: "power3.inOut"
            }, "-=0.08")
            .to(rightCurtain, {
                rotationY: 76,
                rotationZ: 2.6,
                skewY: 9,
                xPercent: 11,
                z: -70,
                filter: "brightness(0.76)",
                duration: 0.86,
                ease: "power3.inOut"
            }, "<")
            .to(leftFabric, {
                rotationY: -72,
                skewY: -16,
                skewX: 3,
                xPercent: -4,
                z: 120,
                borderTopRightRadius: "28vw",
                borderBottomRightRadius: "34vw",
                clipPath: "polygon(0 0, 100% 14%, 74% 100%, 0 100%)",
                duration: 0.86,
                ease: "power3.inOut"
            }, "<")
            .to(rightFabric, {
                rotationY: 72,
                skewY: 16,
                skewX: -3,
                xPercent: 4,
                z: 120,
                borderTopLeftRadius: "28vw",
                borderBottomLeftRadius: "34vw",
                clipPath: "polygon(0 14%, 100% 0, 100% 100%, 26% 100%)",
                duration: 0.86,
                ease: "power3.inOut"
            }, "<")
            .to(leftEdge, {
                rotationY: -74,
                rotationZ: -4,
                skewY: -13,
                x: -54,
                z: 180,
                filter: "brightness(1.06)",
                duration: 0.86,
                ease: "power3.inOut"
            }, "<")
            .to(rightEdge, {
                rotationY: 74,
                rotationZ: 4,
                skewY: 13,
                x: 54,
                z: 180,
                filter: "brightness(1.06)",
                duration: 0.86,
                ease: "power3.inOut"
            }, "<")
            .to(leftCurtain, {
                rotationY: -122,
                rotationZ: -4.5,
                skewY: -15,
                xPercent: -25,
                z: -180,
                filter: "brightness(0.58)",
                duration: 1.05,
                ease: "power4.inOut"
            })
            .to(rightCurtain, {
                rotationY: 122,
                rotationZ: 4.5,
                skewY: 15,
                xPercent: 25,
                z: -180,
                filter: "brightness(0.58)",
                duration: 1.05,
                ease: "power4.inOut"
            }, "<")
            .to(leftFabric, {
                rotationY: -132,
                rotationZ: -2,
                skewY: -22,
                skewX: 5,
                xPercent: -9,
                z: 170,
                borderTopRightRadius: "38vw",
                borderBottomRightRadius: "46vw",
                clipPath: "polygon(0 0, 100% 22%, 54% 100%, 0 100%)",
                duration: 1.05,
                ease: "power4.inOut"
            }, "<")
            .to(rightFabric, {
                rotationY: 132,
                rotationZ: 2,
                skewY: 22,
                skewX: -5,
                xPercent: 9,
                z: 170,
                borderTopLeftRadius: "38vw",
                borderBottomLeftRadius: "46vw",
                clipPath: "polygon(0 22%, 100% 0, 100% 100%, 46% 100%)",
                duration: 1.05,
                ease: "power4.inOut"
            }, "<")
            .to(leftEdge, {
                rotationY: -126,
                rotationZ: -8,
                skewY: -20,
                x: -120,
                z: 240,
                filter: "brightness(0.9)",
                duration: 1.05,
                ease: "power4.inOut"
            }, "<")
            .to(rightEdge, {
                rotationY: 126,
                rotationZ: 8,
                skewY: 20,
                x: 120,
                z: 240,
                filter: "brightness(0.9)",
                duration: 1.05,
                ease: "power4.inOut"
            }, "<")
            .to(curtainValance, {
                yPercent: -104,
                scaleY: 0.78,
                duration: 1.08,
                ease: "power3.inOut"
            }, "-=1.7")
            .to(curtainFloorShadow, {
                opacity: 0,
                scaleX: 0.52,
                duration: 1.1,
                ease: "power3.out"
            }, "-=1.55")
            .from(".site-header", {
                y: -28,
                opacity: 0,
                duration: 0.7,
                ease: "power3.out"
            }, "-=0.36")
            .from(".music-toggle", {
                y: 24,
                opacity: 0,
                scale: 0.86,
                duration: 0.55,
                ease: "back.out(1.7)"
            }, "-=0.5")
            .from(".hero-content .reveal-item", {
                opacity: 0,
                y: 34,
                duration: 0.78,
                stagger: 0.1,
                ease: "power3.out"
            }, "-=0.22")
            .to(intro, {
                opacity: 0,
                duration: 0.34,
                ease: "power2.out"
            }, "-=0.14");

        return;
    }

    page?.classList.add("is-visible");
    intro?.remove();
    document.body.classList.remove("is-locked");
};

document.body.classList.add("is-locked");
openInvitationButton?.addEventListener("click", openInvitation);