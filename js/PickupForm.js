// Pickup Form Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get checkbox and button elements
    const agreeCheckbox = document.getElementById('agreeTerms');
    const handoverBtn = document.getElementById('handoverBtn');

    // Enable/disable handover button based on checkbox
    if (agreeCheckbox && handoverBtn) {
        agreeCheckbox.addEventListener('change', function() {
            handoverBtn.disabled = !this.checked;
        });
    }

    // Handover button click handler (to be implemented)
    if (handoverBtn) {
        handoverBtn.addEventListener('click', function() {
            if (!this.disabled) {
                // Handover functionality will be implemented here
                console.log('Handover button clicked');
            }
        });
    }
});

