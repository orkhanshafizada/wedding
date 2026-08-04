const wishesForm = document.querySelector("[data-wishes-form]");
const formStatus = document.querySelector("[data-form-status]");

const showFormStatus = (message, type = "success") => {
    if (!formStatus) {
        return;
    }

    formStatus.textContent = message;
    formStatus.dataset.status = type;

    if (window.gsap) {
        window.gsap.fromTo(
            formStatus,
            {
                y: 10,
                opacity: 0,
            },
            {
                y: 0,
                opacity: 1,
                duration: 0.35,
                ease: "power2.out",
            }
        );
    }
};

const firstValidationMessage = (errors) => {
    if (!errors || typeof errors !== "object") {
        return null;
    }

    for (const messages of Object.values(errors)) {
        if (Array.isArray(messages) && messages.length > 0) {
            return String(messages[0]);
        }

        if (typeof messages === "string" && messages.trim() !== "") {
            return messages;
        }
    }

    return null;
};

const insertEmoji = (textarea, emoji) => {
    const selectionStart = textarea.selectionStart ?? textarea.value.length;
    const selectionEnd = textarea.selectionEnd ?? textarea.value.length;

    textarea.setRangeText(
        emoji,
        selectionStart,
        selectionEnd,
        "end"
    );

    textarea.dispatchEvent(
        new Event("input", {
            bubbles: true,
        })
    );

    textarea.focus();
};

document.querySelectorAll("[data-emoji-textarea]").forEach((container) => {
    const textarea = container.querySelector("[data-emoji-input]");

    if (!(textarea instanceof HTMLTextAreaElement)) {
        return;
    }

    container.querySelectorAll("[data-emoji]").forEach((button) => {
        button.addEventListener("click", () => {
            const emoji = button.dataset.emoji;

            if (!emoji) {
                return;
            }

            insertEmoji(textarea, emoji);
        });
    });
});

wishesForm?.addEventListener("submit", async (event) => {
    event.preventDefault();

    if (!formStatus || wishesForm.dataset.submitting === "true") {
        return;
    }

    const submitButton = wishesForm.querySelector('button[type="submit"]');
    const formData = new FormData(wishesForm);

    wishesForm.dataset.submitting = "true";

    if (submitButton) {
        submitButton.disabled = true;
    }

    showFormStatus("Arzunuz göndərilir...", "loading");

    try {
        const response = await fetch(wishesForm.action, {
            method: "POST",
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: formData,
        });

        const responseData = await response.json().catch(() => ({}));

        if (!response.ok) {
            const validationMessage = firstValidationMessage(
                responseData.errors
            );

            throw new Error(
                validationMessage
                || responseData.message
                || "Arzunuz göndərilərkən xəta baş verdi."
            );
        }

        wishesForm.reset();

        showFormStatus(
            "Təşəkkür edirik. Arzunuz təsdiqləndikdən sonra saytda görünəcək.",
            "success"
        );
    } catch (error) {
        showFormStatus(
            error instanceof Error
                ? error.message
                : "Arzunuz göndərilərkən xəta baş verdi.",
            "error"
        );
    } finally {
        wishesForm.dataset.submitting = "false";

        if (submitButton) {
            submitButton.disabled = false;
        }
    }
});