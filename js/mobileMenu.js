export const setupMobileMenu = () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        const toggleMenu = () => {
            menuToggle.classList.toggle('active');
            mainNav.classList.toggle('active');
            document.body.style.overflow = mainNav.classList.contains('active') ? 'hidden' : '';
        };

        menuToggle.addEventListener('click', toggleMenu);

        document.querySelectorAll('.main-nav a').forEach(link => {
            link.addEventListener('click', () => {
                if (mainNav.classList.contains('active')) {
                    toggleMenu();
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (mainNav.classList.contains('active') &&
                !e.target.closest('.main-nav') &&
                !e.target.closest('.menu-toggle')) {
                toggleMenu();
            }
        });

        menuToggle.addEventListener('touchstart', (e) => {
            e.preventDefault();
            toggleMenu();
        }, { passive: false });
    }
};