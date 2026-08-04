function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = () => resolve(src);
        script.onerror = () => reject(new Error(`Script yüklənmədi: ${src}`));
        document.head.appendChild(script);
    });
}

// Lazımi scriptləri yüklə
const scriptsToLoad = [];

if (document.querySelector("[toast-list]")) {
    scriptsToLoad.push(loadScript('https://cdn.jsdelivr.net/npm/toastify-js'));
}

if (document.querySelector("[data-choices]")) {
    scriptsToLoad.push(loadScript('/admin/assets/libs/choices.js/public/assets/scripts/choices.min.js'));
}

if (document.querySelector("[data-provider]")) {
    scriptsToLoad.push(loadScript('/admin/assets/libs/flatpickr/flatpickr.min.js'));
}

// Hamısı yükləndikdən sonra
Promise.all(scriptsToLoad)
    .then(() => console.log('Bütün scriptlər yükləndi'))
    .catch(err => console.error('Xəta:', err));
