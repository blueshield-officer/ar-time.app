document.addEventListener("DOMContentLoaded", function () {
    const fireContainers = document.getElementsByClassName('fire');
    if (!fireContainers) return; // Ensure the element exists
    Array.from(fireContainers).forEach((fireContainer) => {
        const parts = 50;

        for (let i = 0; i < parts; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            fireContainer.appendChild(particle);
        }
    })
});