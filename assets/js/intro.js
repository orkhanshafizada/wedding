const intro = document.querySelector("[data-intro]");
const page = document.querySelector("[data-page]");
const openInvitationButton = document.querySelector("[data-open-invitation]");
const weddingMusicElement = document.getElementById("weddingMusic");
const musicToggleButton = document.querySelector("[data-music-toggle]");
const musicIconElement = document.querySelector("[data-music-icon]");
const envelopeElement = document.querySelector(".envelope");

let isInvitationOpening = false;

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

const finishOpening = () => {
    page?.classList.add("is-visible");
    intro?.remove();
    document.body.classList.remove("is-locked");
    window.history.replaceState(null, "", `${window.location.pathname}${window.location.search}`);

    window.scrollTo({
        top: 0,
        left: 0,
        behavior: "instant"
    });

    requestAnimationFrame(() => {
        window.scrollTo(0, 0);
    });
};

const openInvitation = async () => {
    if (isInvitationOpening) {
        return;
    }

    isInvitationOpening = true;
    envelopeElement?.classList.add("is-opening");

    await playWeddingMusic();

    if (!window.gsap) {
        finishOpening();
        return;
    }

    gsap.set(page, { autoAlpha: 1 });

    const timeline = gsap.timeline({
        defaults: { ease: "power3.inOut" },
        onComplete: finishOpening
    });

    timeline
        .to(".intro-seal", {
            autoAlpha: 0,
            scale: 0.72,
            duration: 0.45
        })
        .to(".envelope-flap", {
            rotateX: 180,
            duration: 1.25
        }, "-=0.08")
        .set(".envelope-flap", { zIndex: 1 })
        .to(".intro-invitation-card", {
            yPercent: -54,
            duration: 1.55,
            ease: "power2.out"
        }, "-=0.22")
        .to(".envelope-front, .envelope-back", {
            yPercent: 34,
            autoAlpha: 0,
            duration: 1
        }, "-=0.3")
        .to(".intro-invitation-card", {
            scale: 1.06,
            autoAlpha: 0,
            duration: 0.8
        }, "-=0.18")
        .from(".site-header", {
            y: -25,
            autoAlpha: 0,
            duration: 0.7
        }, "-=0.2")
        .from(".hero-content .reveal-item", {
            y: 24,
            autoAlpha: 0,
            duration: 0.82,
            stagger: 0.1
        }, "-=0.35")
        .to(intro, {
            autoAlpha: 0,
            duration: 0.38
        }, "-=0.15");
};

document.body.classList.add("is-locked");
openInvitationButton?.addEventListener("click", openInvitation);