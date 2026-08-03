// assets/js/intro.js

const intro = document.querySelector("[data-intro]");
const page = document.querySelector("[data-page]");
const openInvitationButton = document.querySelector(
    "[data-open-invitation]"
);
const weddingMusicElement = document.getElementById("weddingMusic");
const musicToggleButton = document.querySelector("[data-music-toggle]");
const musicIconElement = document.querySelector("[data-music-icon]");
const envelopeElement = document.querySelector(".envelope");
const envelopeFlapElement = document.querySelector(".envelope-flap");
const invitationCardElement = document.querySelector(
    ".intro-invitation-card"
);

const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
);

let isInvitationOpening = false;
let openingTimeline = null;

const syncMusicButtonState = (isPlaying) => {
    if (!musicToggleButton || !musicIconElement) {
        return;
    }

    musicToggleButton.classList.toggle("is-muted", !isPlaying);
    musicToggleButton.setAttribute("aria-pressed", String(isPlaying));
    musicIconElement.textContent = isPlaying ? "♪" : "×";
};

const playWeddingMusic = async () => {
    if (!weddingMusicElement) {
        return;
    }

    try {
        weddingMusicElement.volume = 0.45;

        await weddingMusicElement.play();

        syncMusicButtonState(true);
    } catch {
        syncMusicButtonState(false);
    }
};

const resetWindowPosition = () => {
    window.history.replaceState(
        null,
        "",
        `${window.location.pathname}${window.location.search}`
    );

    window.scrollTo({
        top: 0,
        left: 0,
        behavior: "auto"
    });

    requestAnimationFrame(() => {
        window.scrollTo(0, 0);
    });
};

const finishOpening = () => {
    openingTimeline?.kill();
    openingTimeline = null;

    page?.classList.add("is-visible");
    document.body.classList.remove("is-locked");

    resetWindowPosition();

    intro?.remove();
};

const getInvitationCardCenterPosition = (gsapInstance) => {
    gsapInstance.set(invitationCardElement, {
        x: 0,
        y: 0,
        xPercent: 0,
        yPercent: 0
    });

    const cardRectangle = invitationCardElement.getBoundingClientRect();

    return {
        x: window.innerWidth / 2 -
            (cardRectangle.left + cardRectangle.width / 2),
        y: window.innerHeight / 2 -
            (cardRectangle.top + cardRectangle.height / 2)
    };
};

const openInvitationWithoutAnimation = () => {
    page?.classList.add("is-visible");
    document.body.classList.remove("is-locked");
    resetWindowPosition();
    intro?.remove();
};

const openInvitation = async () => {
    if (isInvitationOpening || !intro) {
        return;
    }

    isInvitationOpening = true;

    openInvitationButton?.setAttribute("disabled", "");
    envelopeElement?.classList.add("is-opening");

    await playWeddingMusic();

    if (
        !window.gsap ||
        prefersReducedMotion.matches ||
        !envelopeElement ||
        !envelopeFlapElement ||
        !invitationCardElement
    ) {
        openInvitationWithoutAnimation();
        return;
    }

    const gsapInstance = window.gsap;

    gsapInstance.killTweensOf([
        intro,
        envelopeElement,
        envelopeFlapElement,
        invitationCardElement,
        ".intro-seal",
        ".intro-hint",
        ".intro-scene-kicker",
        ".envelope-front",
        ".envelope-back"
    ]);

    gsapInstance.set(page, {
        autoAlpha: 1
    });

    gsapInstance.set(invitationCardElement, {
        visibility: "visible",
        autoAlpha: 0,
        x: 0,
        y: 0,
        xPercent: 0,
        yPercent: 12,
        scale: 0.96,
        transformOrigin: "50% 50%"
    });

    gsapInstance.set(envelopeFlapElement, {
        rotationX: 0,
        transformPerspective: 1800,
        transformOrigin: "50% 0%"
    });

    const cardCenterPosition = getInvitationCardCenterPosition(
        gsapInstance
    );

    gsapInstance.set(invitationCardElement, {
        x: 0,
        y: 0,
        yPercent: 12,
        scale: 0.96,
        autoAlpha: 0
    });

    openingTimeline = gsapInstance.timeline({
        defaults: {
            overwrite: "auto"
        },
        onComplete: finishOpening
    });

    openingTimeline
        .to(".intro-hint, .intro-scene-kicker", {
            autoAlpha: 0,
            y: 7,
            duration: 0.42,
            ease: "power2.out",
            stagger: 0.05
        })
        .to(
            ".intro-seal",
            {
                autoAlpha: 0,
                scale: 0.72,
                rotation: -8,
                duration: 0.48,
                ease: "power2.in"
            },
            "-=0.26"
        )
        .to(
            envelopeFlapElement,
            {
                rotationX: -178,
                duration: 1.25,
                ease: "power2.inOut"
            },
            "-=0.02"
        )
        .set(envelopeFlapElement, {
            zIndex: 1
        })
        .to(
            invitationCardElement,
            {
                autoAlpha: 1,
                scale: 1,
                duration: 0.28,
                ease: "power1.out"
            },
            "-=0.08"
        )
        .to(
            invitationCardElement,
            {
                yPercent: -58,
                duration: 1.55,
                ease: "power3.out"
            },
            "-=0.05"
        )
        .to(
            ".envelope-front, .envelope-back, .envelope-flap",
            {
                yPercent: 24,
                autoAlpha: 0,
                duration: 0.88,
                ease: "power2.inOut",
                stagger: 0.025
            },
            "-=0.34"
        )
        .to(
            invitationCardElement,
            {
                x: cardCenterPosition.x,
                y: cardCenterPosition.y,
                yPercent: 0,
                scale: 1.035,
                duration: 1.05,
                ease: "power3.inOut"
            },
            "-=0.72"
        )
        .to(
            invitationCardElement,
            {
                scale: 1.055,
                duration: 0.42,
                ease: "sine.inOut"
            }
        )
        .to(
            invitationCardElement,
            {
                autoAlpha: 0,
                scale: 1.09,
                duration: 0.62,
                ease: "power2.in"
            },
            "+=0.35"
        )
        .to(
            intro,
            {
                autoAlpha: 0,
                duration: 0.52,
                ease: "power2.inOut"
            },
            "-=0.38"
        );
};

document.body.classList.add("is-locked");

openInvitationButton?.addEventListener("click", openInvitation, {
    once: true
});

window.addEventListener("pagehide", () => {
    openingTimeline?.kill();
});