let arrow = document.querySelectorAll(".arrow");

arrow.forEach((btnArrow) => {
    btnArrow.addEventListener("click", () => {
        let arrowParent = btnArrow.parentElement.parentElement;
        arrowParent.classList.toggle("show-menu");
    });
});