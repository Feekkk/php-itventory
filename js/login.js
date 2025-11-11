document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const togglePassword = document.querySelector(".toggle-password");
    const passwordInput = document.getElementById("password");

    // Toggle password visibility
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener("click", () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            
            // Update icon
            const svg = togglePassword.querySelector("svg");
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
    }

    // Form submission handling
    if (loginForm) {
        loginForm.addEventListener("submit", (e) => {
            e.preventDefault();
            
            const email = document.getElementById("email").value;
            const password = passwordInput.value;
            const remember = document.getElementById("remember").checked;

            // Basic validation
            if (!email || !password) {
                showError("Please fill in all fields");
                return;
            }

            // Show loading state
            const submitButton = loginForm.querySelector(".login-button");
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span>Signing in...</span>';

            // Simulate API call (replace with actual authentication logic)
            setTimeout(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;

                // TODO: Replace with actual authentication logic
                console.log("Login attempt:", { email, password, remember });
                
                // Example: Redirect based on user role (implement actual logic)
                // window.location.href = "../admin/dashboard.php";
                // or
                // window.location.href = "../technician/dashboard.php";
            }, 1000);
        });
    }

    // Show error message function
    function showError(message) {
        // Remove existing error message if any
        const existingError = document.querySelector(".error-message");
        if (existingError) {
            existingError.remove();
        }

        // Create error message element
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
        loginForm.insertBefore(errorDiv, loginForm.firstChild);

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
        });

        input.addEventListener("blur", function() {
            this.parentElement.classList.remove("focused");
        });
    });
});

