const introElement = document.querySelector("[data-intro]");
const pageElement = document.querySelector("[data-page]");
const openInvitationButton = document.querySelector(
    "[data-open-invitation]",
);
const weddingMusicElement = document.getElementById("weddingMusic");
const musicToggleButton = document.querySelector("[data-music-toggle]");
const musicIconElement = document.querySelector("[data-music-icon]");
const envelopeElement = document.querySelector(".envelope");
const envelopeBackElement = document.querySelector(".envelope-back");
const envelopeFrontElement = document.querySelector(".envelope-front");
const envelopeFlapElement = document.querySelector(".envelope-flap");
const envelopeFlapOrnamentElement = document.querySelector(
    ".envelope-flap-ornament",
);
const invitationCardElement = document.querySelector(
    ".intro-invitation-card",
);
const introSealElement = document.querySelector(".intro-seal");
const introHintElement = document.querySelector(".intro-hint");
const introKickerElement = document.querySelector(
    ".intro-scene-kicker",
);

const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
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
        `${window.location.pathname}${window.location.search}`,
    );

    window.scrollTo({
        top: 0,
        left: 0,
        behavior: "auto",
    });

    requestAnimationFrame(() => {
        window.scrollTo(0, 0);
    });
};

const unlockPage = () => {
    pageElement?.classList.add("is-visible");
    document.body.classList.remove("is-locked");

    resetWindowPosition();
};

const finishOpening = () => {
    openingTimeline?.kill();
    openingTimeline = null;

    unlockPage();

    introElement?.remove();
};

const openInvitationWithoutAnimation = () => {
    unlockPage();
    introElement?.remove();
};

const getCardCenterPosition = (gsapInstance) => {
    gsapInstance.set(invitationCardElement, {
        x: 0,
        y: 0,
        xPercent: 0,
        yPercent: 0,
    });

    const cardRectangle = invitationCardElement.getBoundingClientRect();

    return {
        x:
            window.innerWidth / 2 -
            (cardRectangle.left + cardRectangle.width / 2),
        y:
            window.innerHeight / 2 -
            (cardRectangle.top + cardRectangle.height / 2),
    };
};

const prepareOpeningElements = (gsapInstance) => {
    gsapInstance.killTweensOf([
        introElement,
        envelopeElement,
        envelopeBackElement,
        envelopeFrontElement,
        envelopeFlapElement,
        envelopeFlapOrnamentElement,
        invitationCardElement,
        introSealElement,
        introHintElement,
        introKickerElement,
    ]);

    gsapInstance.set(pageElement, {
        autoAlpha: 1,
    });

    gsapInstance.set(envelopeElement, {
        rotationX: 0,
        rotationY: 0,
        rotationZ: 0,
        scale: 1,
        y: 0,
        transformPerspective: 1900,
        transformOrigin: "50% 50%",
    });

    gsapInstance.set(envelopeFlapElement, {
        display: "block",
        visibility: "visible",
        autoAlpha: 1,
        rotationX: 0,
        y: 0,
        zIndex: 5,
        transformPerspective: 1900,
        transformOrigin: "50% 0%",
    });

    gsapInstance.set(envelopeFlapOrnamentElement, {
        display: "block",
        visibility: "visible",
        autoAlpha: 1,
    });

    gsapInstance.set(invitationCardElement, {
        visibility: "visible",
        autoAlpha: 0,
        x: 0,
        y: 0,
        xPercent: 0,
        yPercent: 12,
        rotation: 0,
        scale: 0.955,
        transformOrigin: "50% 50%",
    });
};

const createOpeningTimeline = (
    gsapInstance,
    cardCenterPosition,
) => {
    openingTimeline = gsapInstance.timeline({
        defaults: {
            overwrite: "auto",
        },
        onComplete: finishOpening,
    });

    openingTimeline
        .to([introHintElement, introKickerElement], {
            autoAlpha: 0,
            y: 8,
            duration: 0.42,
            ease: "power2.out",
            stagger: 0.06,
        })
        .to(
            introSealElement,
            {
                scale: 1.12,
                rotation: 4,
                duration: 0.2,
                ease: "power2.out",
            },
            "-=0.24",
        )
        .to(introSealElement, {
            autoAlpha: 0,
            scale: 0.62,
            rotation: -14,
            y: 10,
            duration: 0.55,
            ease: "back.in(1.8)",
        })
        .to(
            envelopeElement,
            {
                scale: 1.025,
                rotationZ: -0.5,
                duration: 0.35,
                ease: "sine.out",
                force3D: true,
            },
            "-=0.28",
        )
        .to(
            envelopeFlapOrnamentElement,
            {
                autoAlpha: 0,
                duration: 0.3,
                ease: "power1.in",
            },
            "-=0.1",
        )
        .to(
            envelopeFlapElement,
            {
                rotationX: -92,
                duration: 1.15,
                ease: "power2.inOut",
                force3D: true,
            },
            "-=0.16",
        )
        .set(envelopeFlapElement, {
            display: "none",
            visibility: "hidden",
            autoAlpha: 0,
        })
        .to(
            envelopeElement,
            {
                scale: 1,
                rotationZ: 0,
                duration: 0.32,
                ease: "sine.inOut",
                force3D: true,
            },
            "-=0.08",
        )
        .to(
            invitationCardElement,
            {
                autoAlpha: 1,
                scale: 1,
                duration: 0.42,
                ease: "power2.out",
            },
            "-=0.2",
        )
        .to(
            invitationCardElement,
            {
                yPercent: -60,
                rotation: -0.4,
                duration: 1.65,
                ease: "power3.out",
                force3D: true,
            },
            "-=0.04",
        )
        .to(invitationCardElement, {
            yPercent: -57,
            rotation: 0.35,
            duration: 0.34,
            ease: "sine.inOut",
            force3D: true,
        })
        .to(invitationCardElement, {
            yPercent: -60,
            rotation: 0,
            duration: 0.28,
            ease: "sine.inOut",
            force3D: true,
        })
        .to(
            [envelopeFrontElement, envelopeBackElement],
            {
                yPercent: 24,
                autoAlpha: 0,
                scale: 0.96,
                duration: 0.92,
                ease: "power2.inOut",
                stagger: 0.025,
                force3D: true,
            },
            "-=0.38",
        )
        .to(
            invitationCardElement,
            {
                x: cardCenterPosition.x,
                y: cardCenterPosition.y,
                yPercent: 0,
                scale: 1.035,
                duration: 1.15,
                ease: "power3.inOut",
                force3D: true,
            },
            "-=0.73",
        )
        .to(invitationCardElement, {
            scale: 1.055,
            duration: 0.48,
            ease: "sine.inOut",
            force3D: true,
        })
        .to(
            invitationCardElement,
            {
                autoAlpha: 0,
                scale: 1.1,
                duration: 0.68,
                ease: "power2.in",
                force3D: true,
            },
            "+=0.42",
        )
        .to(
            introElement,
            {
                autoAlpha: 0,
                duration: 0.58,
                ease: "power2.inOut",
            },
            "-=0.42",
        );
};

const openInvitation = async () => {
    if (isInvitationOpening || !introElement) {
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

    prepareOpeningElements(gsapInstance);

    const cardCenterPosition = getCardCenterPosition(gsapInstance);

    gsapInstance.set(invitationCardElement, {
        x: 0,
        y: 0,
        yPercent: 12,
        scale: 0.955,
        autoAlpha: 0,
    });

    createOpeningTimeline(gsapInstance, cardCenterPosition);
};

document.body.classList.add("is-locked");

openInvitationButton?.addEventListener("click", openInvitation, {
    once: true,
});

window.addEventListener("pagehide", () => {
    openingTimeline?.kill();
    openingTimeline = null;
});