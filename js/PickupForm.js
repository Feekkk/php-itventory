// Pickup Form Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get checkbox and button elements (only if they exist - for pending handovers)
    const agreeCheckbox = document.getElementById('agreeTerms');
    const handoverBtn = document.getElementById('handoverBtn');
    const handoverForm = document.getElementById('handoverForm');
    const agreeTermsHidden = document.getElementById('agreeTermsHidden');

    // Only set up handlers if elements exist (i.e., for pending handovers)
    if (agreeCheckbox && handoverBtn) {
        // Enable/disable handover button based on checkbox
        agreeCheckbox.addEventListener('change', function() {
            handoverBtn.disabled = !this.checked;
            // Update hidden input value
            if (agreeTermsHidden) {
                agreeTermsHidden.value = this.checked ? 'on' : '';
            }
        });
    }

    // Form submission handler (only for pending handovers)
    if (handoverForm && agreeCheckbox) {
        handoverForm.addEventListener('submit', function(e) {
            // Double check that terms are agreed
            if (!agreeCheckbox.checked) {
                e.preventDefault();
                alert('Please agree to the terms and conditions to proceed.');
                return false;
            }
            
            // Show confirmation dialog
            if (!confirm('Are you sure you want to confirm this handover? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    }
});

