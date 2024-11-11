
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".confirm-action").forEach(action => {
        action.addEventListener("click",e => {
            e.preventDefault();
            document.querySelector("#modal-unpublish-button").addEventListener("click",() => {
                location.replace(action.getAttribute("href"));
            });
        });
    });
}); 