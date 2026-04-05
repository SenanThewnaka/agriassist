document.addEventListener('DOMContentLoaded', () => {
    // Initialize icons
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Password toggle helper
    window.togglePassword = function(id) {
        const input = document.getElementById(id);
        const eye = document.getElementById(id + '-eye');
        const eyeOff = document.getElementById(id + '-eye-off');
        
        if (input.type === 'password') {
            input.type = 'text';
            eye.classList.add('hidden');
            eyeOff.classList.remove('hidden');
        } else {
            input.type = 'password';
            eye.classList.remove('hidden');
            eyeOff.classList.add('hidden');
        }
    };

    const authForms = document.querySelectorAll('form[action*="login"], form[action*="register"]');
    
    authForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            // Loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i data-lucide="loader-2" class="w-5 h-5 animate-spin mx-auto"></i>`;
            if (window.lucide) window.lucide.createIcons();

            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.showToast(data.message, 'success');
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                } else {
                    const message = data.message || 'Authentication failed. Please check your credentials.';
                    window.showToast(message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    if (window.lucide) window.lucide.createIcons();
                }
            } catch (error) {
                console.error('Auth error:', error);
                window.showToast('A connection error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                if (window.lucide) window.lucide.createIcons();
            }
        });
    });
});
