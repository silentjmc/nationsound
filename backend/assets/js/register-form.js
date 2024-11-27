document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registration-form');
    const submitButton = document.getElementById('submit-button');
    const normalState = submitButton.querySelector('.normal-state');
    const loadingState = submitButton.querySelector('.loading-state');

    form.addEventListener('submit', function(e) {
        // Désactiver le bouton
        submitButton.disabled = true;
        
        // Cacher le texte normal et montrer l'animation
        normalState.classList.add('hidden');
        loadingState.classList.remove('hidden');
        loadingState.style.display = 'flex'; // Forcer l'affichage en flex
    });
});

// Fonction pour basculer la visibilité du mot de passe
function togglePasswordVisibility(button, inputId) {
    const input = document.getElementById(inputId);
    const showEye = button.querySelectorAll('.show-eye');
    const hideEye = button.querySelector('.hide-eye');

    if (input.type === 'password') {
        input.type = 'text';
        showEye.forEach(eye => eye.classList.add('hidden'));
        hideEye.classList.remove('hidden');
    } else {
        input.type = 'password';
        showEye.forEach(eye => eye.classList.remove('hidden'));
        hideEye.classList.add('hidden');
    }
}