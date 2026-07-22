<link rel="icon" type="image/png" href="{{ asset('img/logo-karya-tantri-abadi.png') }}">
<link rel="apple-touch-icon" href="{{ asset('img/logo-karya-tantri-abadi.png') }}">
<title>{{ config('app.name') }} - Sistem Manajemen Karya Tantri Abadi</title>

<style>
    /* Custom styles for inventory reports */
    .inventory-card {
        transition: transform 0.2s ease-in-out;
    }
    
    .inventory-card:hover {
        transform: translateY(-2px);
    }
    
    /* Custom badge colors */
    .stock-low {
        background-color: #fef3c7 !important;
        color: #92400e !important;
    }
    
    .stock-out {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
    }
    
    .stock-normal {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
    }
    
    /* Report summary cards */
    .report-summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }
    
    .report-summary-card.revenue {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .report-summary-card.profit {
        background: linear-gradient(135deg, #fceabb 0%, #f8b500 100%);
        color: #333;
    }
    
    .report-summary-card.loss {
        background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Login redirect handler loaded');
    
    // Only run on login page
    if (window.location.pathname === '/' || 
        window.location.pathname === '/login' || 
        window.location.pathname.includes('/login')) {
        
        console.log('Login page detected, setting up redirect handling');
        
        // Function to check authentication and redirect
        function checkAuthAndRedirect() {
            fetch('/check-auth-redirect', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.redirect && data.redirect !== '/login') {
                    console.log('Redirecting to:', data.redirect);
                    window.location.href = data.redirect;
                }
            })
            .catch(err => {
                console.log('Error checking auth:', err);
            });
        }
        
        // Listen for successful Livewire authentication
        document.addEventListener('livewire:finished', function(event) {
            console.log('Livewire finished processing');
            
            // Small delay to allow auth state to update
            setTimeout(function() {
                checkAuthAndRedirect();
            }, 300);
        });
        
        // Listen for form submissions on login forms
        document.addEventListener('submit', function(e) {
            const form = e.target;
            
            if (form && (form.hasAttribute('wire:submit') || form.querySelector('[wire\\:submit]'))) {
                console.log('Login form submitted via Livewire');
                
                // Wait for Livewire to process then check auth
                setTimeout(function() {
                    checkAuthAndRedirect();
                }, 1000);
            }
        });
        
        // Alternative: Listen for any successful navigation/response
        document.addEventListener('livewire:navigated', function() {
            console.log('Livewire navigation detected');
            setTimeout(checkAuthAndRedirect, 200);
        });
        
        // Listen for Livewire responses that might indicate successful login
        document.addEventListener('livewire:response', function(event) {
            console.log('Livewire response received');
            
            // Check if response might be from authentication
            if (event.detail && event.detail.response) {
                setTimeout(checkAuthAndRedirect, 500);
            }
        });
    }
});
</script>
