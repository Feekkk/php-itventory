document.addEventListener("DOMContentLoaded", () => {
    const navbar = document.querySelector(".navbar");
    const searchBar = document.querySelector(".search-bar");
    const searchInput = document.querySelector(".search-bar input");

    const updateNavbarState = () => {
        if (!navbar) {
            return;
        }

        if (window.scrollY > 12) {
            navbar.classList.add("is-scrolled");
        } else {
            navbar.classList.remove("is-scrolled");
        }
    };

    updateNavbarState();
    window.addEventListener("scroll", updateNavbarState);

    if (searchBar && searchInput) {
        searchInput.addEventListener("focus", () => {
            searchBar.classList.add("is-focused");
        });

        searchInput.addEventListener("blur", () => {
            searchBar.classList.remove("is-focused");
        });
    }
});

