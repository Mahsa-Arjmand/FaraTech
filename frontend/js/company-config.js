/**
 * FaraTech — Central Company Data (Bilingual)
 * Plain script. Must load BEFORE company-bind.js.
 *
 * هر سرویس حالا دارای title_fa/description_fa/list_fa است.
 * company-bind.js بر اساس FARATECH.currentLang این‌ها را انتخاب می‌کند.
 */
window.FARATECH = {
  name: "FaraTech",
  name_fa: "فرا تِک",
  tagline: "Your Ideas. Our Technology. Infinite Possibilities.",
  tagline_fa: "ایده‌های شما. تکنولوژی ما. امکانات بی‌نهایت.",
  supportingTagline: "Ideas into Digital Solutions.",
  supportingTagline_fa: "ایده‌ها به راهکارهای دیجیتال.",

  description:
    "FaraTech is a digital technology studio that transforms ideas into practical digital solutions. We help businesses develop and grow through modern web experiences, custom software, intelligent AI solutions, and business-process automation.",
  description_fa:
    "فرا تِک یک استودیوی فناوری دیجیتال است که ایده‌ها را به راهکارهای عملی دیجیتال تبدیل می‌کند. ما به کسب‌وکارها کمک می‌کنیم تا از طریق تجربه‌های وب مدرن، نرم‌افزار سفارشی، راهکارهای هوشمند مبتنی بر AI و اتوماسیون فرآیندهای کسب‌وکار رشد کنند.",

  descriptionLong:
    "FaraTech is a digital technology studio that transforms ideas into practical digital solutions. We help businesses develop and grow through modern web experiences, custom software, intelligent AI solutions, and business-process automation. Our focus is on combining thoughtful design, modern engineering, and intelligent technology to create useful, scalable, and forward-looking digital products.",
  descriptionLong_fa:
    "فرا تِک یک استودیوی فناوری دیجیتال است که ایده‌ها را به راهکارهای عملی دیجیتال تبدیل می‌کند. ما به کسب‌وکارها کمک می‌کنیم تا از طریق تجربه‌های وب مدرن، نرم‌افزار سفارشی، راهکارهای هوشمند مبتنی بر AI و اتوماسیون فرآیندهای کسب‌وکار رشد کنند. تمرکز ما بر ترکیب طراحی متفکرانه، مهندسی مدرن و فناوری هوشمند برای ساخت محصولات دیجیتال مفید، مقیاس‌پذیر و آینده‌نگر است.",

  email: "faratechteam@gmail.com",
  instagram: "https://www.instagram.com/faratech.lab/",
  linkedin: "https://www.linkedin.com/company/faratechlab",
  phone: null,
  address: null,

  founders: [
    {
      name: "Arezoo Safari",
      role: "AI & Software Development",
      role_fa: "هوش مصنوعی و توسعه نرم‌افزار",
      focus:
        "Artificial intelligence, intelligent systems, software engineering, and the development of AI-powered digital products.",
      focus_fa:
        "هوش مصنوعی، سیستم‌های هوشمند، مهندسی نرم‌افزار و توسعه محصولات دیجیتال مبتنی بر AI.",
      initials: "AS",
      photo: null,
    },
    {
      name: "Mahsa Arjmandfar",
      role: "Software & Digital Solutions",
      role_fa: "نرم‌افزار و راهکارهای دیجیتال",
      focus:
        "Software development, digital solutions, intelligent systems, and building practical technology products.",
      focus_fa:
        "توسعه نرم‌افزار، راهکارهای دیجیتال، سیستم‌های هوشمند و ساخت محصولات فناوری عملی.",
      initials: "MA",
      photo: null,
    },
    {
      name: "Hannaneh Pourmozeh",
      role: "Sales & Consultant",
      role_fa: "فروش و مشاور",
      focus:
        "Sales strategy, client consulting, and business development.",
      focus_fa:
        "استراتژی فروش، مشاوره مشتریان و توسعه کسب‌وکار.",
      initials: "HP",
      photo: null,
    },
    {
      name: "Sara Bayat",
      role: "Advertising & Digital Marketing",
      role_fa: "تبلیغات و دیجیتال مارکتینگ",
      focus:
        "Digital marketing, advertising campaigns, and brand promotion.",
      focus_fa:
        "دیجیتال مارکتینگ، کمپین‌های تبلیغاتی و ترویج برند.",
      initials: "SB",
      photo: null,
    },
  ],

  services: [
    {
      id: "webDev",
      title: "Web Development & Modern Website Design",
      title_fa: "توسعه وب و طراحی سایت مدرن",
      description:
        "Design and develop modern, responsive, high-quality websites with thoughtful user experiences, strong visual identity, and maintainable technical foundations.",
      description_fa:
        "طراحی و توسعه وب‌سایت‌های مدرن، واکنش‌گرا و باکیفیت با تجربه‌های کاربری متفکرانه، هویت بصری قوی و پایه‌های فنی قابل نگهداری.",
      icon: "monitor",
      list: [
        "Custom responsive design",
        "Strong visual identity",
        "Maintainable foundations",
      ],
      list_fa: [
        "طراحی واکنش‌گرای سفارشی",
        "هویت بصری قوی",
        "پایه‌های قابل نگهداری",
      ],
    },
    {
      id: "customSoftware",
      title: "Custom Software Development",
      title_fa: "توسعه نرم‌افزار سفارشی",
      description:
        "Build software solutions tailored to specific business requirements, workflows, and operational needs.",
      description_fa:
        "ساخت راهکارهای نرم‌افزاری متناسب با نیازها، گردش‌کارها و نیازهای عملیاتی خاص کسب‌وکار.",
      icon: "code",
      list: [
        "Tailored workflows",
        "Scalable architecture",
        "Built to fit your needs",
      ],
      list_fa: [
        "گردش‌کارهای اختصاصی",
        "معماری مقیاس‌پذیر",
        "طراحی‌شده برای نیاز شما",
      ],
    },
    {
      id: "ai",
      title: "Artificial Intelligence Solutions",
      title_fa: "راهکارهای هوش مصنوعی",
      description:
        "Develop practical AI-powered applications, intelligent systems, and machine-learning solutions designed around real use cases.",
      description_fa:
        "توسعه کاربردهای عملی مبتنی بر هوش مصنوعی، سیستم‌های هوشمند و راهکارهای یادگیری ماشین طراحی‌شده بر اساس موارد استفاده‌ی واقعی.",
      icon: "brain",
      list: ["Applied AI", "Real use cases", "Intelligent systems"],
      list_fa: ["AI کاربردی", "موارد استفاده واقعی", "سیستم‌های هوشمند"],
    },
    {
      id: "automation",
      title: "Business Process Automation",
      title_fa: "اتوماسیون فرآیندهای کسب‌وکار",
      description:
        "Improve workflows by reducing repetitive manual tasks and integrating intelligent automation into business operations.",
      description_fa:
        "بهبود گردش‌کارها با کاهش وظایف دستی تکراری و یکپارچه‌سازی اتوماسیون هوشمند در عملیات کسب‌وکار.",
      icon: "bolt",
      list: ["Reduce manual work", "Smarter workflows", "Intelligent integration"],
      list_fa: ["کاهش کار دستی", "گردش‌کارهای هوشمندتر", "یکپارچه‌سازی هوشمند"],
    },
  ],

  projects: [],
  testimonials: [],

  nav: [
    { label: "Home", label_fa: "خانه", href: "index.html" },
    { label: "About", label_fa: "درباره ما", href: "about.html" },
    { label: "Services", label_fa: "خدمات", href: "services.html" },
    { label: "Work", label_fa: "نمونه‌کارها", href: "portfolio.html" },
    { label: "Team", label_fa: "تیم", href: "team.html" },
    { label: "AI Lab", label_fa: "آزمایشگاه AI", href: "ai-lab.html" },
    { label: "Contact", label_fa: "تماس", href: "contact.html" },
  ],

  // ─── Helper ─────────────────────────────────────────────────
  /**
   * زبان فعلی را از FT_I18N می‌خواند یا از HTML lang attribute یا پیش‌فرض 'fa'.
   * @returns {'fa'|'en'}
   */
  get currentLang() {
    // اول از FT_I18N بخوان اگر وجود دارد
    if (window.FT_I18N && window.FT_I18N.current) {
      return window.FT_I18N.current;
    }
    // سپس از HTML lang attribute بخوان
    const htmlLang = document.documentElement.getAttribute('lang');
    if (htmlLang === 'fa' || htmlLang === 'en') {
      return htmlLang;
    }
    // پیش‌فرض
    return 'fa';
  },

  /**
   * انتخاب فیلد ترجمه‌شده.
   * @param {object} obj
   * @param {string} base - نام فیلد پایه (مثلاً 'title')
   * @returns {string}
   */
  pick(obj, base) {
    if (!obj) return '';
    const lang = this.currentLang;
    if (lang === 'fa') {
      const faKey = base + '_fa';
      if (obj[faKey] && String(obj[faKey]).trim() !== '') return obj[faKey];
    }
    return obj[base] || '';
  },
};