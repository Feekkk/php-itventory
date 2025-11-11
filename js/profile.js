document.addEventListener("DOMContentLoaded", () => {
    const profileForm = document.getElementById("profileForm");
    const togglePasswordButtons = document.querySelectorAll(".toggle-password");
    const newPasswordInput = document.getElementById("new_password");
    const confirmPasswordInput = document.getElementById("confirm_password");
    const currentPasswordInput = document.getElementById("current_password");

    // Toggle password visibility
    togglePasswordButtons.forEach((toggleBtn) => {
        toggleBtn.addEventListener("click", () => {
            const passwordWrapper = toggleBtn.closest(".password-wrapper");
            const targetInput = passwordWrapper.querySelector("input");
            const type = targetInput.getAttribute("type") === "password" ? "text" : "password";
            targetInput.setAttribute("type", type);
            
            // Update icon
            const svg = toggleBtn.querySelector("svg");
            if (type === "text") {
                svg.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></line>
                `;
            } else {
                svg.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"></circle>
                `;
            }
        });
    });

    // Real-time password validation
    if (newPasswordInput && confirmPasswordInput) {
        newPasswordInput.addEventListener("input", validatePassword);
        confirmPasswordInput.addEventListener("input", validatePasswordMatch);
    }

    // Form validation
    if (profileForm) {
        profileForm.addEventListener("submit", (e) => {
            const newPassword = newPasswordInput?.value || "";
            const confirmPassword = confirmPasswordInput?.value || "";
            const currentPassword = currentPasswordInput?.value || "";

            // Clear previous errors
            clearFieldErrors();

            let isValid = true;

            // If new password is provided, validate password fields
            if (newPassword || confirmPassword || currentPassword) {
                if (!currentPassword) {
                    showFieldError("current_password", "Current password is required to change password");
                    isValid = false;
                }

                if (!newPassword) {
                    showFieldError("new_password", "New password is required");
                    isValid = false;
                } else if (newPassword.length < 8) {
                    showFieldError("new_password", "Password must be at least 8 characters long");
                    isValid = false;
                }

                if (!confirmPassword) {
                    showFieldError("confirm_password", "Please confirm your password");
                    isValid = false;
                } else if (newPassword !== confirmPassword) {
                    showFieldError("confirm_password", "Passwords do not match");
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
                return;
            }

            // Show loading state
            const submitButton = profileForm.querySelector(".btn-primary");
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span>Saving...</span>';
        });
    }

    function validatePassword() {
        const password = newPasswordInput.value;
        
        if (password.length > 0 && password.length < 8) {
            newPasswordInput.style.borderColor = "#dc2626";
        } else {
            newPasswordInput.style.borderColor = "";
        }
    }

    function validatePasswordMatch() {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                confirmPasswordInput.style.borderColor = "#dc2626";
            } else {
                confirmPasswordInput.style.borderColor = "#2dc48d";
            }
        } else {
            confirmPasswordInput.style.borderColor = "";
        }
    }

    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.style.borderColor = "#dc2626";
        
        // Remove existing error if any
        const existingError = field.parentElement.querySelector(".field-error");
        if (existingError) {
            existingError.remove();
        }

        // Create error message
        const errorDiv = document.createElement("div");
        errorDiv.className = "field-error";
        errorDiv.style.cssText = `
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        `;
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
    }

    function clearFieldErrors() {
        const inputs = profileForm.querySelectorAll("input");
        inputs.forEach(input => {
            input.style.borderColor = "";
        });

        const errors = profileForm.querySelectorAll(".field-error");
        errors.forEach(error => error.remove());
    }

    // Add focus effects
    const inputs = document.querySelectorAll(".form-group input");
    inputs.forEach(input => {
        input.addEventListener("focus", function() {
            this.style.borderColor = "#2dc48d";
        });

        input.addEventListener("blur", function() {
            if (this.style.borderColor !== "rgb(220, 38, 38)") {
                this.style.borderColor = "";
            }
        });
    });
});

