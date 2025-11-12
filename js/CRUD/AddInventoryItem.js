// Add Inventory Item Form JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addInventoryForm');
    const equipmentIdInput = document.getElementById('equipment_id');
    const equipmentNameInput = document.getElementById('equipment_name');

    // Auto-format equipment ID (uppercase)
    if (equipmentIdInput) {
        equipmentIdInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc2626';
                } else {
                    field.style.borderColor = '#e5e7eb';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        // Remove error styling on input
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#e5e7eb';
            });
        });
    }
});

