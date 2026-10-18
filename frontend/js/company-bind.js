/**
 * FaraTech — DOM Binder
 * Plain script. No imports. Reads window.FARATECH.
 * Must load AFTER company-config.js.
 */
(function () {
  "use strict";

  const FARATECH = window.FARATECH;
  if (!FARATECH) {
    console.error("[FaraTech] company-config.js not loaded before company-bind.js");
    return;
  }

  const get = (obj, path) =>
    path.split(".").reduce((a, k) => (a == null ? a : a[k]), obj);

  const ICONS = {
    monitor: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>`,
    code: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></svg>`,
    brain: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a5 5 0 00-5 5v2a4 4 0 000 8 5 5 0 005 5 5 5 0 005-5 4 4 0 000-8V7a5 5 0 00-5-5z"/></svg>`,
    bolt: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>`,
    mail: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>`,
    instagram: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>`,
    linkedin: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>`,
  };

  /* ---- text / link binding ---- */
  function bindText() {
    document.querySelectorAll("[data-ft]").forEach((el) => {
      const path = el.getAttribute("data-ft");
      const val = get(FARATECH, path);
      if (val == null) return;
      const as = el.getAttribute("data-ft-as") || "text";
      if (as === "email") {
        el.textContent = val;
        if (el.tagName === "A") el.href = "mailto:" + val;
      } else if (as === "email-text") {
        el.textContent = val;
      } else if (as === "href") {
        if (el.tagName === "A") el.href = val;
      } else if (as === "html") {
        el.innerHTML = val;
      } else {
        el.textContent = val;
        if (el.tagName === "A" && /^https?:/.test(val)) {
          el.href = val;
          el.target = "_blank";
          el.rel = "noopener noreferrer";
        }
      }
    });
  }

  /* ---- navigation ---- */
  function renderNav() {
    const ul = document.getElementById("navLinks");
    if (!ul) return;
    const current = location.pathname.split("/").pop() || "index.html";
    ul.innerHTML = FARATECH.nav
      .map(
        (n) =>
          `<li><a href="${n.href}"${
            n.href === current ? ' class="active"' : ""
          }>${n.label}</a></li>`
      )
      .join("");
  }

  /* ---- services: home (3), compact (all), detailed (all) ---- */
  function renderServices() {
    // HOME — first 3 only
    document.querySelectorAll('[data-ft-render="services-home"]').forEach((grid) => {
      const homeServices = FARATECH.services.slice(0, 3);
      grid.innerHTML = homeServices
        .map(
          (s, i) => `
          <article class="service-item card-3d" data-reveal="3d" ${
            i ? `data-delay="${i * 150}"` : ""
          } data-tilt data-tilt-max="10">
            <div class="card-3d-inner">
              <div class="service-icon-wrap">${ICONS[s.icon] || ICONS.monitor}</div>
              <h3>${s.title}</h3>
              <p>${s.description}</p>
              <ul class="service-list">${s.list
                .map((l) => `<li>${l}</li>`)
                .join("")}</ul>
              <div class="card-shine"></div>
            </div>
          </article>`
        )
        .join("");
    });

    // COMPACT — all services (compact card style)
    document
      .querySelectorAll('[data-ft-render="services"][data-ft-variant="compact"]')
      .forEach((grid) => {
        grid.innerHTML = FARATECH.services
          .map(
            (s, i) => `
          <article class="service-item card-3d" data-reveal="3d" ${
            i ? `data-delay="${i * 120}"` : ""
          } data-tilt data-tilt-max="8">
            <div class="card-3d-inner">
              <div class="service-icon-wrap">${ICONS[s.icon] || ICONS.monitor}</div>
              <h3>${s.title}</h3>
              <p>${s.description}</p>
              <ul class="service-list">${s.list
                .map((l) => `<li>${l}</li>`)
                .join("")}</ul>
              <div class="card-shine"></div>
            </div>
          </article>`
          )
          .join("");
      });

    // DETAILED — all services (services.html)
    document
      .querySelectorAll('[data-ft-render="services"][data-ft-variant="detailed"]')
      .forEach((grid) => {
        grid.innerHTML = FARATECH.services
          .map(
            (s, i) => `
          <article class="service-detail card-3d" data-reveal="3d" ${
            i ? `data-delay="${i * 120}"` : ""
          } data-tilt data-tilt-max="8">
            <div class="card-3d-inner">
              <div class="service-detail-icon">${ICONS[s.icon] || ICONS.monitor}</div>
              <h3>${s.title}</h3>
              <p>${s.description}</p>
              <ul class="service-detail-list">${s.list
                .map((l) => `<li>${l}</li>`)
                .join("")}</ul>
              <div class="card-shine"></div>
            </div>
          </article>`
          )
          .join("");
      });
  }

  /* ---- founders ---- */
  function renderFounders() {
    document.querySelectorAll('[data-ft-render="founders"]').forEach((grid) => {
      grid.innerHTML = FARATECH.founders
        .map(
          (f, i) => `
          <article class="team-card card-3d" data-reveal="3d" ${
            i ? `data-delay="${i * 150}"` : ""
          } data-tilt data-tilt-max="8">
            <div class="card-3d-inner">
              ${
                f.photo
                  ? `<img class="team-photo" src="${f.photo}" alt="${f.name}" loading="lazy" />`
                  : `<div class="team-avatar">${f.initials}</div>`
              }
              <h3>${f.name}</h3>
              <span class="team-role">${f.role}</span>
              <p>${f.focus}</p>
              <div class="card-shine"></div>
            </div>
          </article>`
        )
        .join("");
    });
  }

  /* ---- socials ---- */
  function renderSocials() {
    document.querySelectorAll('[data-ft-render="socials"]').forEach((wrap) => {
      const items = [];
      if (FARATECH.instagram)
        items.push(
          `<a href="${FARATECH.instagram}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">${ICONS.instagram}</a>`
        );
      if (FARATECH.linkedin)
        items.push(
          `<a href="${FARATECH.linkedin}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">${ICONS.linkedin}</a>`
        );
      if (FARATECH.email)
        items.push(
          `<a href="mailto:${FARATECH.email}" aria-label="Email">${ICONS.mail}</a>`
        );
      wrap.innerHTML = items.join("");
    });
  }

  /* ---- projects (empty state / API) ---- */
  function renderProjects() {
    document.querySelectorAll('[data-ft-render="projects"]').forEach((grid) => {
      // Fetch projects from API
      fetch('http://localhost:8001/api/public_projects.php')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.data.length > 0) {
            // Show first 3 projects
            const projects = data.data.slice(0, 3);
            grid.innerHTML = projects.map((project, index) => {
              const technologies = project.technologies || [];
              const tag = technologies.length > 0 ? technologies[0] : 'WEB';
              const tagClass = getCategoryTagClass(project.category);

              return `
                <article class="portfolio-card flip-card" data-reveal="3d" ${index ? `data-delay="${index * 100}"` : ''}>
                  <div class="flip-inner">
                    <div class="flip-front">
                      <div class="portfolio-thumb">
                        <div class="portfolio-thumb-overlay">${project.title.substring(0, 4).toUpperCase()}</div>
                      </div>
                      <div class="portfolio-body">
                        <span class="tag ${tagClass}">${tag.toUpperCase()}</span>
                        <h3>${project.title}</h3>
                      </div>
                    </div>
                    <div class="flip-back">
                      <div class="flip-back-content">
                        <span class="tag ${tagClass}">${tag.toUpperCase()}</span>
                        <h3>${project.title}</h3>
                        <p>${project.description || 'A custom project.'}</p>
                        <a href="project-detail.html?id=${project.id}" class="flip-link">View details
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </article>
              `;
            }).join('');
          } else {
            // Show empty state
            grid.innerHTML = `
              <div class="projects-empty" data-reveal="zoom">
                <div class="projects-empty-inner">
                  <div class="projects-empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <rect x="3" y="3" width="18" height="18" rx="2"/>
                      <path d="M3 9h18M9 21V9"/>
                    </svg>
                  </div>
                  <h3>Portfolio coming soon</h3>
                  <p>We're preparing a curated selection of our recent work. In the meantime, reach out to discuss how we can help with your project.</p>
                  <a href="contact.html" class="btn btn-primary magnetic">
                    Start a conversation
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
              </div>`;
          }
        })
        .catch(error => {
          console.error('Error loading projects:', error);
          grid.innerHTML = '<p>Error loading projects. Please try again later.</p>';
        });
    });
  }

  function getCategoryTagClass(category) {
    const classMap = {
      'E-Commerce': 'tag-gold',
      'Corporate': 'tag-blue',
      'Web App': 'tag-purple',
      'Website': 'tag-green',
      'Platform': 'tag-red',
      'AI Product': 'tag-orange',
      'SaaS': 'tag-pink'
    };
    return classMap[category] || 'tag-blue';
  }

  /* ---- hide unavailable ---- */
  function hideUnavailable() {
    document.querySelectorAll("[data-ft-if]").forEach((el) => {
      const key = el.getAttribute("data-ft-if");
      if (!get(FARATECH, key)) el.remove();
    });
  }

  /* ---- year ---- */
  function setYear() {
    document.querySelectorAll("[data-ft-year]").forEach((el) => {
      el.textContent = new Date().getFullYear();
    });
  }

  /* ---- init ---- */
  function init() {
    renderNav();
    bindText();
    renderServices();
    renderFounders();
    renderSocials();
    renderProjects();
    hideUnavailable();
    setYear();
    document.dispatchEvent(new CustomEvent("ft:content-ready"));
  }

  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", init);
  else init();
})();