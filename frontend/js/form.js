alert('Form script loaded!');
console.log('Form script loaded immediately');

document.addEventListener('DOMContentLoaded', () => {
  console.log('Form script loaded in DOMContentLoaded');
  const contactForm = document.getElementById('contactForm');
  console.log('Contact form element:', contactForm);
  if (!contactForm) {
    console.error('Contact form not found!');
    alert('Contact form not found!');
    return;
  }

  contactForm.addEventListener('submit', (e) => {
    console.log('Form submitted');
    e.preventDefault();

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const topic = document.getElementById('topic').value.trim();
    const message = document.getElementById('message').value.trim();

    console.log('Form data:', { name, email, phone, topic, message });

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

    console.log('Sending to API...');
    fetch('http://localhost:8001/api/contact.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      console.log('Response status:', response.status);
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
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
      console.error('Error:', error);
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