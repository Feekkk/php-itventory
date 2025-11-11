document.addEventListener("DOMContentLoaded", () => {
    const registerForm = document.getElementById("registerForm");
    const togglePasswordButtons = document.querySelectorAll(".toggle-password");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm_password");

    // Toggle password visibility for both password fields
    togglePasswordButtons.forEach((toggleBtn, index) => {
        toggleBtn.addEventListener("click", () => {
            const targetInput = index === 0 ? passwordInput : confirmPasswordInput;
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
    if (passwordInput) {
        passwordInput.addEventListener("input", validatePassword);
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener("input", validatePasswordMatch);
    }

    // Form submission handling
    if (registerForm) {
        registerForm.addEventListener("submit", (e) => {
            const staffId = document.getElementById("staff_id").value.trim();
            const fullName = document.getElementById("full_name").value.trim();
            const email = document.getElementById("email").value.trim();
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Clear previous errors
            clearErrors();

            // Validation
            let isValid = true;

            if (!staffId) {
                e.preventDefault();
                showFieldError("staff_id", "Staff ID is required");
                isValid = false;
            }

            if (!fullName) {
                e.preventDefault();
                showFieldError("full_name", "Full name is required");
                isValid = false;
            } else if (fullName.length < 3) {
                e.preventDefault();
                showFieldError("full_name", "Full name must be at least 3 characters");
                isValid = false;
            }

            if (!email) {
                e.preventDefault();
                showFieldError("email", "Email is required");
                isValid = false;
            } else if (!isValidEmail(email)) {
                e.preventDefault();
                showFieldError("email", "Please enter a valid email address");
                isValid = false;
            }

            if (!password) {
                e.preventDefault();
                showFieldError("password", "Password is required");
                isValid = false;
            } else if (password.length < 8) {
                e.preventDefault();
                showFieldError("password", "Password must be at least 8 characters long");
                isValid = false;
            }

            if (!confirmPassword) {
                e.preventDefault();
                showFieldError("confirm_password", "Please confirm your password");
                isValid = false;
            } else if (password !== confirmPassword) {
                e.preventDefault();
                showFieldError("confirm_password", "Passwords do not match");
                isValid = false;
            }

            // Only prevent default if validation failed
            if (!isValid) {
                return;
            }

            // Show loading state
            const submitButton = registerForm.querySelector(".login-button");
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span>Creating account...</span>';

            // Form will submit normally to PHP handler - don't prevent default
        });
    }

    // Validation functions
    function validatePassword() {
        const password = passwordInput.value;
        const passwordHint = document.querySelector(".password-hint");
        
        if (password.length > 0 && password.length < 8) {
            if (passwordHint) {
                passwordHint.style.color = "#dc2626";
            }
            passwordInput.style.borderColor = "#dc2626";
        } else {
            if (passwordHint) {
                passwordHint.style.color = "#6b7280";
            }
            passwordInput.style.borderColor = "";
        }
    }

    function validatePasswordMatch() {
        const password = passwordInput.value;
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

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Error handling functions
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

    function clearErrors() {
        const inputs = registerForm.querySelectorAll("input");
        inputs.forEach(input => {
            input.style.borderColor = "";
        });

        const errors = registerForm.querySelectorAll(".field-error");
        errors.forEach(error => error.remove());
    }

    function showSuccess(message) {
        // Remove existing messages
        const existingMessage = document.querySelector(".success-message, .error-message");
        if (existingMessage) {
            existingMessage.remove();
        }

        // Create success message
        const successDiv = document.createElement("div");
        successDiv.className = "success-message";
        successDiv.style.cssText = `
            background: #d1fae5;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid #a7f3d0;
        `;
        successDiv.textContent = message;

        // Insert before form
        registerForm.insertBefore(successDiv, registerForm.firstChild);
    }

    function showError(message) {
        // Remove existing messages
        const existingMessage = document.querySelector(".success-message, .error-message");
        if (existingMessage) {
            existingMessage.remove();
        }

        // Create error message
        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message";
        errorDiv.style.cssText = `
            background: #fee2e2;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            border: 1px solid #fecaca;
        `;
        errorDiv.textContent = message;

        // Insert before form
        registerForm.insertBefore(errorDiv, registerForm.firstChild);

        // Remove after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    // Add focus effects to inputs
    const inputs = document.querySelectorAll(".form-group input");
    inputs.forEach(input => {
        input.addEventListener("focus", function() {
            this.parentElement.classList.add("focused");
            // Clear error styling on focus
            if (this.style.borderColor === "rgb(220, 38, 38)") {
                this.style.borderColor = "";
            }
        });

        input.addEventListener("blur", function() {
            this.parentElement.classList.remove("focused");
        });
    });
});

