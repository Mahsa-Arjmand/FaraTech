document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');

  if (menuToggle && navLinks) {
    const setMenuOpen = (open) => {
      navLinks.classList.toggle('active', open);
      menuToggle.classList.toggle('active', open);
      document.documentElement.classList.toggle('nav-open', open);
    };

    const toggleMenu = () => {
      setMenuOpen(!navLinks.classList.contains('active'));
    };

    const closeMenu = () => {
      setMenuOpen(false);
    };

    menuToggle.addEventListener('click', toggleMenu);

    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeMenu);
    });

    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && navLinks.classList.contains('active')) {
        closeMenu();
      }
    });

    // Close menu when clicking outside the menu
    document.addEventListener('click', (e) => {
      if (navLinks.classList.contains('active') &&
          !navLinks.contains(e.target) &&
          !menuToggle.contains(e.target)) {
        closeMenu();
      }
    });
  }
});