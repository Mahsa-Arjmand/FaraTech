document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');

  if (toggle && navLinks) {
    // Keep in sync with the breakpoint in css/responsive.css where the menu collapses
    const compactNav = window.matchMedia('(max-width: 1024px)');

    toggle.setAttribute('aria-controls', 'navLinks');
    toggle.setAttribute('aria-expanded', 'false');

    const setOpen = (open) => {
      navLinks.classList.toggle('active', open);
      toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => setOpen(!navLinks.classList.contains('active')));
    navLinks.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));

    document.addEventListener('click', (e) => {
      if (navLinks.classList.contains('active') && !navLinks.contains(e.target) && !toggle.contains(e.target)) {
        setOpen(false);
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && navLinks.classList.contains('active')) {
        setOpen(false);
        toggle.focus();
      }
    });

    // Reset when rotating/resizing into the full desktop navigation
    compactNav.addEventListener('change', (e) => { if (!e.matches) setOpen(false); });
  }
});