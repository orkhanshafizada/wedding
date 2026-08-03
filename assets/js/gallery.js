const galleryCards = Array.from(
    document.querySelectorAll("[data-gallery-index]")
);

const galleryModal = document.querySelector("[data-gallery-modal]");
const galleryModalImage = document.querySelector("[data-gallery-modal-image]");
const galleryModalCounter = document.querySelector("[data-gallery-counter]");
const galleryCloseButton = document.querySelector("[data-gallery-close]");
const galleryPrevButton = document.querySelector("[data-gallery-prev]");
const galleryNextButton = document.querySelector("[data-gallery-next]");

const galleryItems = galleryCards
    .map((card) => {
        const image = card.querySelector("img");

        if (!image) {
            return null;
        }

        return {
            image: image.getAttribute("src"),
            alt: image.getAttribute("alt") || "Orxan və Aytacın nişan şəkli"
        };
    })
    .filter(Boolean);

let activeGalleryIndex = 0;
let lastFocusedGalleryCard = null;
let touchStartX = null;
let touchStartY = null;

const isGalleryOpen = () => {
    return galleryModal?.classList.contains("is-active") ?? false;
};

const normalizeGalleryIndex = (index) => {
    if (galleryItems.length === 0) {
        return 0;
    }

    return (index + galleryItems.length) % galleryItems.length;
};

const updateGalleryImage = (item) => {
    if (!galleryModalImage || !galleryModalCounter) {
        return;
    }

    galleryModalImage.src = item.image;
    galleryModalImage.alt = item.alt;
    galleryModalCounter.textContent =
        `${activeGalleryIndex + 1} / ${galleryItems.length}`;
};

const renderGalleryModal = () => {
    const currentItem = galleryItems[activeGalleryIndex];

    if (!currentItem || !galleryModalImage || !galleryModalCounter) {
        return;
    }

    if (!window.gsap) {
        updateGalleryImage(currentItem);
        return;
    }

    gsap.killTweensOf(galleryModalImage);

    gsap.to(galleryModalImage, {
        opacity: 0,
        scale: 0.97,
        duration: 0.16,
        ease: "power1.out",
        onComplete: () => {
            updateGalleryImage(currentItem);

            gsap.fromTo(
                galleryModalImage,
                {
                    opacity: 0,
                    scale: 0.97
                },
                {
                    opacity: 1,
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                }
            );
        }
    });
};

const openGalleryModal = (index, sourceCard) => {
    if (
        !galleryModal ||
        !galleryModalImage ||
        galleryItems.length === 0
    ) {
        return;
    }

    activeGalleryIndex = normalizeGalleryIndex(index);
    lastFocusedGalleryCard = sourceCard ?? null;

    renderGalleryModal();

    galleryModal.classList.add("is-active");
    galleryModal.setAttribute("aria-hidden", "false");
    document.body.classList.add("is-locked");

    requestAnimationFrame(() => {
        galleryCloseButton?.focus({
            preventScroll: true
        });
    });

    if (!window.gsap) {
        return;
    }

    gsap.fromTo(
        ".gallery-modal-content",
        {
            y: 24,
            scale: 0.96
        },
        {
            y: 0,
            scale: 1,
            duration: 0.4,
            ease: "power3.out"
        }
    );
};

const closeGalleryModal = () => {
    if (!galleryModal || !isGalleryOpen()) {
        return;
    }

    const completeClosing = () => {
        galleryModal.classList.remove("is-active");
        galleryModal.setAttribute("aria-hidden", "true");

        if (galleryModalImage) {
            galleryModalImage.src = "";
            galleryModalImage.alt = "";
        }

        document.body.classList.remove("is-locked");

        lastFocusedGalleryCard?.focus({
            preventScroll: true
        });

        lastFocusedGalleryCard = null;
    };

    if (!window.gsap) {
        completeClosing();
        return;
    }

    gsap.to(galleryModal, {
        opacity: 0,
        duration: 0.2,
        ease: "power1.out",
        onComplete: () => {
            completeClosing();
            gsap.set(galleryModal, {
                clearProps: "opacity"
            });
        }
    });
};

const showPreviousGalleryItem = () => {
    activeGalleryIndex = normalizeGalleryIndex(activeGalleryIndex - 1);
    renderGalleryModal();
};

const showNextGalleryItem = () => {
    activeGalleryIndex = normalizeGalleryIndex(activeGalleryIndex + 1);
    renderGalleryModal();
};

const trapGalleryFocus = (event) => {
    if (
        event.key !== "Tab" ||
        !galleryModal ||
        !isGalleryOpen()
    ) {
        return;
    }

    const focusableElements = Array.from(
        galleryModal.querySelectorAll("button:not([disabled])")
    );

    if (focusableElements.length === 0) {
        return;
    }

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
        return;
    }

    if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
    }
};

galleryCards.forEach((card, fallbackIndex) => {
    card.addEventListener("click", () => {
        const declaredIndex = Number.parseInt(
            card.dataset.galleryIndex ?? "",
            10
        );

        const resolvedIndex = Number.isInteger(declaredIndex)
            ? declaredIndex
            : fallbackIndex;

        openGalleryModal(resolvedIndex, card);
    });
});

galleryCloseButton?.addEventListener("click", closeGalleryModal);
galleryPrevButton?.addEventListener("click", showPreviousGalleryItem);
galleryNextButton?.addEventListener("click", showNextGalleryItem);

galleryModal?.addEventListener("click", (event) => {
    if (event.target === galleryModal) {
        closeGalleryModal();
    }
});

galleryModal?.addEventListener(
    "touchstart",
    (event) => {
        const touch = event.changedTouches[0];

        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
    },
    {
        passive: true
    }
);

galleryModal?.addEventListener(
    "touchend",
    (event) => {
        if (touchStartX === null || touchStartY === null) {
            return;
        }

        const touch = event.changedTouches[0];
        const distanceX = touch.clientX - touchStartX;
        const distanceY = touch.clientY - touchStartY;

        touchStartX = null;
        touchStartY = null;

        if (
            Math.abs(distanceX) < 50 ||
            Math.abs(distanceX) <= Math.abs(distanceY)
        ) {
            return;
        }

        if (distanceX > 0) {
            showPreviousGalleryItem();
            return;
        }

        showNextGalleryItem();
    },
    {
        passive: true
    }
);

document.addEventListener("keydown", (event) => {
    if (!isGalleryOpen()) {
        return;
    }

    trapGalleryFocus(event);

    if (event.key === "Escape") {
        event.preventDefault();
        closeGalleryModal();
        return;
    }

    if (event.key === "ArrowLeft") {
        event.preventDefault();
        showPreviousGalleryItem();
        return;
    }

    if (event.key === "ArrowRight") {
        event.preventDefault();
        showNextGalleryItem();
    }
});

