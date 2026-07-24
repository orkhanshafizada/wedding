const galleryCards = document.querySelectorAll("[data-gallery-index]");
const galleryModal = document.querySelector("[data-gallery-modal]");
const galleryModalImage = document.querySelector("[data-gallery-modal-image]");
const galleryModalCounter = document.querySelector("[data-gallery-counter]");
const galleryCloseButton = document.querySelector("[data-gallery-close]");
const galleryPrevButton = document.querySelector("[data-gallery-prev]");
const galleryNextButton = document.querySelector("[data-gallery-next]");

const galleryItems = [
    {
        image: "https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=1600&q=90"
    },
    {
        image: "https://images.unsplash.com/photo-1523438885200-e635ba2c371e?auto=format&fit=crop&w=1600&q=90"
    },
    {
        image: "https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1600&q=90"
    },
    {
        image: "https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=1600&q=90"
    },
    {
        image: "https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1600&q=90"
    }
];

let activeGalleryIndex = 0;

const renderGalleryModal = () => {
    const currentItem = galleryItems[activeGalleryIndex];

    if (!currentItem || !galleryModalImage || !galleryModalCounter) {
        return;
    }

    if (window.gsap) {
        gsap.to(galleryModalImage, {
            opacity: 0,
            scale: 0.96,
            duration: 0.18,
            onComplete: () => {
                galleryModalImage.src = currentItem.image;
                galleryModalImage.alt = "Toy şəkli";
                galleryModalCounter.textContent = `${activeGalleryIndex + 1} / ${galleryItems.length}`;

                gsap.to(galleryModalImage, {
                    opacity: 1,
                    scale: 1,
                    duration: 0.32,
                    ease: "power2.out"
                });
            }
        });

        return;
    }

    galleryModalImage.src = currentItem.image;
    galleryModalImage.alt = "Toy şəkli";
    galleryModalCounter.textContent = `${activeGalleryIndex + 1} / ${galleryItems.length}`;
};

const openGalleryModal = (index) => {
    if (!galleryModal) {
        return;
    }

    activeGalleryIndex = index;
    renderGalleryModal();

    galleryModal.classList.add("is-active");
    galleryModal.setAttribute("aria-hidden", "false");
    document.body.classList.add("is-locked");

    if (window.gsap) {
        gsap.fromTo(galleryModal, {
            opacity: 0
        }, {
            opacity: 1,
            duration: 0.28,
            ease: "power2.out"
        });

        gsap.fromTo(".gallery-modal-content", {
            y: 26,
            scale: 0.96
        }, {
            y: 0,
            scale: 1,
            duration: 0.42,
            ease: "power3.out"
        });
    }
};

const closeGalleryModal = () => {
    if (!galleryModal || !galleryModalImage) {
        return;
    }

    galleryModal.classList.remove("is-active");
    galleryModal.setAttribute("aria-hidden", "true");
    galleryModalImage.src = "";
    document.body.classList.remove("is-locked");
};

const showPreviousGalleryItem = () => {
    activeGalleryIndex = activeGalleryIndex === 0 ? galleryItems.length - 1 : activeGalleryIndex - 1;
    renderGalleryModal();
};

const showNextGalleryItem = () => {
    activeGalleryIndex = activeGalleryIndex === galleryItems.length - 1 ? 0 : activeGalleryIndex + 1;
    renderGalleryModal();
};

galleryCards.forEach((card) => {
    card.addEventListener("click", () => {
        openGalleryModal(Number(card.dataset.galleryIndex));
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

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closeGalleryModal();
    }

    if (!galleryModal?.classList.contains("is-active")) {
        return;
    }

    if (event.key === "ArrowLeft") {
        showPreviousGalleryItem();
    }

    if (event.key === "ArrowRight") {
        showNextGalleryItem();
    }
});