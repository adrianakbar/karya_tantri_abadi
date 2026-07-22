<div class="mt-2" wire:ignore>
    {{-- Render the reCAPTCHA JS and widget --}}
    {!! NoCaptcha::renderJs() !!}
    {!! NoCaptcha::display(['data-theme' => 'dark', 'data-callback' => 'onCaptchaSuccess', 'data-expired-callback' => 'onCaptchaExpired', 'data-error-callback' => 'onCaptchaError']) !!}
</div>

<script>
    // Keep this script outside Livewire's diffing
    window.onCaptchaSuccess = function(token) {
        // Update hidden field in Livewire form state
        if (window.Livewire) {
            const comp = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
            if (comp) {
                comp.set('data.g_recaptcha', token)
            }
        }
    }

    window.onCaptchaExpired = function() {
        if (window.Livewire) {
            const comp = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
            if (comp) {
                comp.set('data.g_recaptcha', null)
            }
        }
        // Reset the reCAPTCHA widget
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
        }
    }

    window.onCaptchaError = function() {
        if (window.Livewire) {
            const comp = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
            if (comp) {
                comp.set('data.g_recaptcha', null)
            }
        }
        // Reset the reCAPTCHA widget
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
        }
    }

    // Reset reCAPTCHA when validation fails (listen to Livewire events)
    document.addEventListener('livewire:init', function() {
        Livewire.on('reset-recaptcha', () => {
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
                // Clear the hidden field
                const comp = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
                if (comp) {
                    comp.set('data.g_recaptcha', null)
                }
            }
        });
    });
</script>
