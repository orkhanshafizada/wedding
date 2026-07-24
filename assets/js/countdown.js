const countdown = document.querySelector("[data-countdown]");
const daysElement = document.querySelector("[data-days]");
const hoursElement = document.querySelector("[data-hours]");
const minutesElement = document.querySelector("[data-minutes]");
const secondsElement = document.querySelector("[data-seconds]");

const formatCountdownNumber = (value) => String(value).padStart(2, "0");

const updateCountdown = () => {
    if (!countdown || !daysElement || !hoursElement || !minutesElement || !secondsElement) {
        return;
    }

    const targetDate = new Date(countdown.dataset.countdown).getTime();
    const currentDate = new Date().getTime();
    const distance = Math.max(targetDate - currentDate, 0);

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((distance / (1000 * 60)) % 60);
    const seconds = Math.floor((distance / 1000) % 60);

    daysElement.textContent = formatCountdownNumber(days);
    hoursElement.textContent = formatCountdownNumber(hours);
    minutesElement.textContent = formatCountdownNumber(minutes);
    secondsElement.textContent = formatCountdownNumber(seconds);
};

updateCountdown();
setInterval(updateCountdown, 1000);