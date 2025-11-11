document.addEventListener("DOMContentLoaded", () => {
    const burgerMenu = document.getElementById("burgerMenu");
    const dropdownMenu = document.getElementById("dropdownMenu");
    let menuOverlay = document.querySelector(".menu-overlay");

    // Create overlay if it doesn't exist
    if (!menuOverlay) {
        menuOverlay = document.createElement("div");
        menuOverlay.className = "menu-overlay";
        document.body.appendChild(menuOverlay);
    }

    // Toggle menu
    if (burgerMenu && dropdownMenu) {
        burgerMenu.addEventListener("click", (e) => {
            e.stopPropagation();
            burgerMenu.classList.toggle("active");
            dropdownMenu.classList.toggle("active");
            menuOverlay.classList.toggle("active");
            document.body.style.overflow = dropdownMenu.classList.contains("active") ? "hidden" : "";
        });

        // Close menu when clicking overlay
        menuOverlay.addEventListener("click", () => {
            burgerMenu.classList.remove("active");
            dropdownMenu.classList.remove("active");
            menuOverlay.classList.remove("active");
            document.body.style.overflow = "";
        });

        // Close menu when clicking outside
        document.addEventListener("click", (e) => {
            if (!dropdownMenu.contains(e.target) && !burgerMenu.contains(e.target)) {
                if (dropdownMenu.classList.contains("active")) {
                    burgerMenu.classList.remove("active");
                    dropdownMenu.classList.remove("active");
                    menuOverlay.classList.remove("active");
                    document.body.style.overflow = "";
                }
            }
        });

        // Close menu on escape key
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && dropdownMenu.classList.contains("active")) {
                burgerMenu.classList.remove("active");
                dropdownMenu.classList.remove("active");
                menuOverlay.classList.remove("active");
                document.body.style.overflow = "";
            }
        });

        // Handle menu item clicks
        const menuItems = dropdownMenu.querySelectorAll(".menu-item");
        menuItems.forEach(item => {
            item.addEventListener("click", () => {
                // Remove active class from all items
                menuItems.forEach(i => i.classList.remove("active"));
                // Add active class to clicked item
                item.classList.add("active");
            });
        });
    }
});

