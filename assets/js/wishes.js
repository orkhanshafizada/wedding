const wishesForm = document.querySelector("[data-wishes-form]");
const formStatus = document.querySelector("[data-form-status]");

wishesForm?.addEventListener("submit", (event) => {
    event.preventDefault();

    if (!formStatus) {
        return;
    }

    const formData = new FormData(wishesForm);
    const fullName = String(formData.get("fullName") || "").trim();
    const phone = String(formData.get("phone") || "").trim();
    const wish = String(formData.get("wish") || "").trim();

    if (!fullName || !wish) {
        formStatus.textContent = "Zəhmət olmasa adınızı və arzunuzu qeyd edin.";
        return;
    }

    const whatsappPhone = "994501234567";
    const whatsappMessage = [
        "Nişan üçün arzu",
        `Ad və soyad: ${fullName}`,
        phone ? `Əlaqə nömrəsi: ${phone}` : "",
        `Arzu: ${wish}`
    ].filter(Boolean).join("\n");

    const whatsappUrl = `https://wa.me/${whatsappPhone}?text=${encodeURIComponent(whatsappMessage)}`;

    formStatus.textContent = "Təşəkkür edirik. Arzunuz göndərilmək üçün hazırlandı.";
    window.open(whatsappUrl, "_blank", "noopener");
    wishesForm.reset();

    if (window.gsap) {
        gsap.fromTo(formStatus, {
            y: 10,
            opacity: 0
        }, {
            y: 0,
            opacity: 1,
            duration: 0.35,
            ease: "power2.out"
        });
    }
});ee