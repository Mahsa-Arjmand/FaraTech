/* Contact form validation */
(function () {
  const form = document.getElementById('contactForm');
  if (!form) return;

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const name = form.name.value.trim();
    const email = form.email.value.trim();
    const message = form.message.value.trim();

    form.querySelectorAll('.error').forEach((el) => el.classList.remove('error'));

    let ok = true;
    if (name.length < 2) {
      form.name.classList.add('error');
      ok = false;
    }
    if (!emailRegex.test(email)) {
      form.email.classList.add('error');
      ok = false;
    }
    if (message.length < 10) {
      form.message.classList.add('error');
      ok = false;
    }

    if (!ok) {
      alert('Please fill out the highlighted fields correctly.');
      return;
    }

    console.log('Form data:', { name, email, message });
    alert('Your message has been sent. The FaraTech team will get back to you shortly.');
    form.reset();
  });
})();