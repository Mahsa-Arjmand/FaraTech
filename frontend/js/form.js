document.addEventListener('DOMContentLoaded', () => {
  const contactForm = document.getElementById('contactForm');
  if (!contactForm) {
    return;
  }

  contactForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone') ? document.getElementById('phone').value.trim() : '';
    const topic = document.getElementById('topic') ? document.getElementById('topic').value.trim() : '';
    const message = document.getElementById('message').value.trim();

    if (!name || !email || !message) {
      alert('Please fill in all required fields.');
      return;
    }

    const btn = contactForm.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Sending...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('topic', topic);
    formData.append('message', message);

    fetch('http://localhost:8000/api/contact.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      return response.json();
    })
    .then(data => {
      if (data.success) {
        btn.innerHTML = '✓ Message Sent!';
        btn.style.background = '#10b981';

        setTimeout(() => {
          btn.innerHTML = originalText;
          btn.style.background = '';
          btn.disabled = false;
          contactForm.reset();
        }, 2000);
      } else {
        btn.innerHTML = 'Error';
        btn.style.background = '#ef4444';
        alert(data.message || 'Failed to send message');

        setTimeout(() => {
          btn.innerHTML = originalText;
          btn.style.background = '';
          btn.disabled = false;
        }, 2000);
      }
    })
    .catch(error => {
      btn.innerHTML = 'Error';
      btn.style.background = '#ef4444';
      alert('Failed to send message. Please try again.');

      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = '';
        btn.disabled = false;
      }, 2000);
    });
  });
});