document.addEventListener('DOMContentLoaded', function() {
    const formConfigs = [
        { formId: 'registration-form', buttonId: 'submit-button' },
        { formId: 'login-form', buttonId: 'login-button' },
        { formId: 'reset-form', buttonId: 'reset-button' }
    ];

    // Pour chaque configuration de formulaire possible
    formConfigs.forEach(config => {
        const form = document.getElementById(config.formId);
        if (form) {
            const button = document.getElementById(config.buttonId);
            if (button) {
                const normalState = button.querySelector('.normal-state');
                const loadingState = button.querySelector('.loading-state');
                
                if (normalState && loadingState) {
                    form.addEventListener('submit', function(e) {
                        // Disable the button
                        button.disabled = true;
                        
                        // Hide the normal text and show the animation
                        normalState.classList.add('hidden');
                        loadingState.classList.remove('hidden');
                        loadingState.style.display = 'flex'; // Forcer l'affichage en flex
                    });
                }
            }
        }
    });
    
    // Pour tous les boutons de bascule de visibilité du mot de passe sur n'importe quelle page
    const toggleButtons = document.querySelectorAll('[data-toggle-password]');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (input) {
                const showEye = button.querySelectorAll('.show-eye');
                const hideEye = button.querySelectorAll('.hide-eye');

                if (input.type === 'password') {
                    input.type = 'text';
                    showEye.forEach(eye => eye.classList.add('hidden'));
                    hideEye.forEach(eye => eye.classList.remove('hidden'));
                } else {
                    input.type = 'password';
                    showEye.forEach(eye => eye.classList.remove('hidden'));
                    hideEye.forEach(eye => eye.classList.add('hidden'));
                }
            }
        });
    });
});