document.addEventListener("DOMContentLoaded", () => {
    const infoModal = document.getElementById("infoModal");
    const modalOverlay = document.getElementById("modalOverlay");
    const openInfoModal = document.getElementById("openInfoModal");
    const closeInfoModal = document.getElementById("closeInfoModal");

    // Открытие модального окна
    openInfoModal.addEventListener("click", () => {
        infoModal.style.display = "block";
        modalOverlay.style.display = "block";
    });

    // Закрытие модального окна
    closeInfoModal.addEventListener("click", () => {
        infoModal.style.display = "none";
        modalOverlay.style.display = "none";
    });

    // Закрытие по клику на затемнение
    modalOverlay.addEventListener("click", () => {
        infoModal.style.display = "none";
        modalOverlay.style.display = "none";
    });
});
