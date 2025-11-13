document.addEventListener("DOMContentLoaded", () => {
    const categoryFilter = document.getElementById("category_filter");
    const equipmentSelect = document.getElementById("equipment_id");
    const form = document.getElementById("addPickupForm");

    if (!categoryFilter || !equipmentSelect) {
        return;
    }

    // Store all equipment options with their attributes
    const allEquipmentOptions = [];
    Array.from(equipmentSelect.options).forEach(option => {
        if (option.value !== "") {
            allEquipmentOptions.push({
                value: option.value,
                text: option.text,
                category: option.getAttribute("data-category"),
                selected: option.selected
            });
        }
    });

    // Filter equipment based on category selection
    function filterEquipmentByCategory() {
        const selectedCategory = categoryFilter.value;
        const currentValue = equipmentSelect.value;

        // Clear current options except the first "Select Equipment" option
        equipmentSelect.innerHTML = '<option value="">Select Equipment</option>';

        // Filter and add options based on selected category
        allEquipmentOptions.forEach(optionData => {
            const categoryId = optionData.category;
            
            // Show option if:
            // 1. No category is selected (show all), OR
            // 2. The option's category matches the selected category
            if (!selectedCategory || categoryId === selectedCategory) {
                const option = document.createElement("option");
                option.value = optionData.value;
                option.textContent = optionData.text;
                if (categoryId) {
                    option.setAttribute("data-category", categoryId);
                }
                equipmentSelect.appendChild(option);
            }
        });

        // Restore previously selected value if it's still available
        if (currentValue && Array.from(equipmentSelect.options).some(opt => opt.value === currentValue)) {
            equipmentSelect.value = currentValue;
        } else {
            equipmentSelect.value = "";
        }
    }

    // Add event listener to category filter
    categoryFilter.addEventListener("change", filterEquipmentByCategory);

    // Filter on page load if a category is already selected
    if (categoryFilter.value) {
        filterEquipmentByCategory();
    }

    // Form validation
    if (form) {
        form.addEventListener("submit", (e) => {
            const equipmentId = equipmentSelect.value;
            const lecturerId = document.getElementById("lecturer_id")?.value.trim();
            const lecturerName = document.getElementById("lecturer_name")?.value.trim();
            const lecturerEmail = document.getElementById("lecturer_email")?.value.trim();

            // Clear previous errors
            clearErrors();

            let isValid = true;

            if (!equipmentId) {
                showFieldError("equipment_id", "Please select an equipment");
                isValid = false;
            }

            if (!lecturerId) {
                showFieldError("lecturer_id", "Lecturer ID is required");
                isValid = false;
            }

            if (!lecturerName) {
                showFieldError("lecturer_name", "Lecturer name is required");
                isValid = false;
            }

            if (!lecturerEmail) {
                showFieldError("lecturer_email", "Lecturer email is required");
                isValid = false;
            } else if (!isValidEmail(lecturerEmail)) {
                showFieldError("lecturer_email", "Please enter a valid email address");
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            const submitButton = form.querySelector(".btn-submit");
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<span>Adding...</span>';
                
                // Re-enable after a delay in case of error
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }, 5000);
            }
        });
    }

    // Helper functions
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        // Remove existing error
        clearFieldError(fieldId);

        // Add error class
        field.classList.add("error");

        // Create error message element
        const errorDiv = document.createElement("div");
        errorDiv.className = "field-error";
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        `;

        // Insert error message after the field
        const formGroup = field.closest(".form-group");
        if (formGroup) {
            formGroup.appendChild(errorDiv);
        }
    }

    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.classList.remove("error");
        const formGroup = field.closest(".form-group");
        if (formGroup) {
            const errorDiv = formGroup.querySelector(".field-error");
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    }

    function clearErrors() {
        const errorFields = document.querySelectorAll(".error");
        errorFields.forEach(field => {
            field.classList.remove("error");
        });

        const errorMessages = document.querySelectorAll(".field-error");
        errorMessages.forEach(msg => {
            msg.remove();
        });
    }
});

