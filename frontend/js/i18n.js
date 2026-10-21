/**
 * FaraTech — i18n Engine
 * دوزبانهسازی سایت (فارسی پیشفرض + انگلیسی)
 *
 * استفاده در HTML:
 *   <h1 data-i18n-html="hero.title">...</h1>
 *   <p data-i18n="hero.lead">...</p>
 *   <input data-i18n-attr="placeholder:form.namePlaceholder" />
 *   <button data-lang-switch="fa">فا</button>
 *
 * API عمومی:
 *   FT_I18N.t('key')              → رشته ترجمهشده
 *   FT_I18N.setLang('fa'|'en')    → تغییر زبان
 *   FT_I18N.current               → زبان فعلی
 *   FT_I18N.isRTL                 → true اگر فارسی
 *
 * رویداد:
 *   document.addEventListener('ft:langchange', e => e.detail.lang)
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'ft_lang';
  const DEFAULT_LANG = 'fa'; // فارسی پیشفرض

  // =====================================================================
  // دیکشنری ترجمهها
  // =====================================================================
  const dict = {
    // ---------------------------------------------------------------
    // فارسی
    // ---------------------------------------------------------------
    fa: {
      // Brand
      'brand.name': 'فرا تِک',
      'brand.tagline': 'آژانس',

      // Nav
      'nav.home': 'خانه',
      'nav.about': 'درباره ما',
      'nav.services': 'خدمات',
      'nav.work': 'نمونه‌کارها',
      'nav.team': 'تیم',
      'nav.aiLab': 'آزمایشگاه AI',
      'nav.contact': 'تماس',
      'nav.startProject': 'شروع پروژه',

      // Language switcher
      'lang.fa': 'فا',
      'lang.en': 'EN',

      // Generic
      'common.readMore': 'ادامه',
      'common.viewAll': 'مشاهده همه',
      'common.learnMore': 'بیشتر بدانید',
      'common.getStarted': 'شروع کنیم',
      'common.contactUs': 'تماس با ما',
      'common.backToHome': 'بازگشت به خانه',
      'common.loading': 'در حال بارگذاری...',
      'common.noResults': 'موردی یافت نشد.',
      'common.error': 'خطا در بارگذاری. لطفاً دوباره تلاش کنید.',
      'common.sendMessage': 'ارسال پیام',
      'common.sending': 'در حال ارسال...',
      'common.messageSent': 'پیام ارسال شد!',
      'common.fullName': 'نام و نام خانوادگی',
      'common.phone': 'تلفن',
      'common.email': 'ایمیل',
      'common.message': 'پیام',
      'common.required': 'الزامی',
      'common.select': 'انتخاب کنید',
      'common.name': 'نام',
      'common.date': 'تاریخ',
      'common.status': 'وضعیت',
      'common.category': 'دسته‌بندی',
      'common.client': 'کارفرما',
      'common.technologies': 'تکنولوژی‌ها',

      // Home — Hero
      'home.hero.badge': 'ایده‌ها به راهکارهای دیجیتال',
      'home.hero.title': '<span class="hero-title-line1">ایده‌های شما</span> <span class="hero-title-line2">تکنولوژی ما</span> <span class="accent">امکانات بی‌نهایت</span>',
      'home.hero.lead': 'فرا تِک یک استودیوی فناوری دیجیتال است که بر تبدیل ایده‌ها به راهکارهای عملی دیجیتال تمرکز دارد. ما به کسب‌وکارها کمک می‌کنیم تا از طریق تجربه‌های وب مدرن، توسعه نرم‌افزار سفارشی، راهکارهای مبتنی بر هوش مصنوعی و اتوماسیون هوشمند فرآیندهای کسب‌وکار رشد کنند.',
      'home.hero.ctaPrimary': 'مشاوره رایگان دریافت کنید',
      'home.hero.ctaSecondary': 'مشاهده نمونه‌کارها',
      'home.hero.stat1Title': 'نوآوری',
      'home.hero.stat1Sub': 'محور',
      'home.hero.stat2Title': 'کیفیت',
      'home.hero.stat2Sub': 'محور',
      'home.hero.stat3Title': 'نتیجه',
      'home.hero.stat3Sub': 'محور',

      // Home — Hero frame
      'home.frame.url': 'faratech/dashboard',
      'home.frame.activeProject': 'پروژه فعال',
      'home.frame.title': 'ساخت راهکارهای دیجیتال',
      'home.frame.webDev': 'توسعه وب',
      'home.frame.inProgress': 'در حال انجام',
      'home.frame.aiIntegration': 'یکپارچه‌سازی AI',
      'home.frame.planning': 'در حال برنامه‌ریزی',
      'home.frame.livePreview': 'پیش‌نمایش زنده',
      'home.frame.fast': 'سریع',
      'home.frame.quality': 'کیفیت',
      'home.frame.trusted': 'مورد اعتماد',
      'home.scrollHint': 'اسکرول کنید',

      // Home — Trusted
      'home.trusted.label': 'مورد اعتماد تیم‌های حرفه‌ای',

      // Home — Services
      'home.services.eyebrow': 'خدمات',
      'home.services.title': 'راهکارهای دیجیتال متناسب با نیاز شما',
      'home.services.subtitle': 'سه حوزه اصلی که روی آن‌ها تمرکز داریم — و بیشتر در ادامه.',
      'home.services.viewAll': 'مشاهده همه خدمات',

      // Home — Portfolio
      'home.portfolio.eyebrow': 'نمونه‌کارهای منتخب',
      'home.portfolio.title': 'پروژه‌هایی که با دقت ساختیم',
      'home.portfolio.viewAll': 'مشاهده همه پروژه‌ها',

      // Home — AI Teaser
      'home.aiTeaser.tag': 'آزمایشگاه AI فرا تِک',
      'home.aiTeaser.title': 'ما فقط وب‌سایت نمی‌سازیم — با AI می‌سازیم',
      'home.aiTeaser.description': 'از چت‌بات‌های مبتنی بر هوش مصنوعی و تولید محتوا تا اتوماسیون هوشمند و مدل‌های سفارشی یادگیری ماشین — آزمایشگاه AI ما راهکارهای واقعی و در سطح کسب‌وکار ارائه می‌دهد.',
      'home.aiTeaser.cta': 'کاوش در آزمایشگاه AI',

      // Home — Process
      'home.process.eyebrow': 'فرآیند ما',
      'home.process.title': 'از ایده تا راه‌اندازی، در چهار گام',
      'home.process.subtitle': 'فرآیندی شفاف، ساختاریافته و قابل پیگیری.',
      'home.process.step1Title': 'کشف و مشاوره',
      'home.process.step1Desc': 'برای درک اهداف، کاربران و نیازهای کسب‌وکارتان با شما ملاقات می‌کنیم.',
      'home.process.step2Title': 'طراحی و استراتژی',
      'home.process.step2Desc': 'ساختار و ظاهر را بر اساس داده‌های واقعی طراحی می‌کنیم.',
      'home.process.step3Title': 'توسعه',
      'home.process.step3Desc': 'وب‌سایت شما را با فناوری‌های مدرن می‌سازیم و به‌طور کامل تست می‌کنیم.',
      'home.process.step4Title': 'راه‌اندازی و پشتیبانی',
      'home.process.step4Desc': 'پس از راه‌اندازی، از طریق آموزش و پشتیبانی مستمر در کنار شما می‌مانیم.',

      // Home — Why
      'home.why.eyebrow': 'چرا فرا تِک',
      'home.why.title': 'تفاوت در جزئیات است',
      'home.why.subtitle': 'ما با کسب‌وکار شما مانند کسب‌وکار خودمان رفتار می‌کنیم — با تمرکز بر نتیجه، دقت و تفکر بلندمدت.',
      'home.why.stat1Lbl': 'رضایت مشتری',
      'home.why.stat2Lbl': 'پروژه تحویل داده شده',
      'home.why.stat3Lbl': 'سال تجربه در صنعت',
      'home.why.stat4Lbl': 'برای شروع پروژه',
      'home.why.item1Title': 'تیم داخلی اختصاصی',
      'home.why.item1Desc': 'پروژه شما توسط یک تیم با ثبات و باتجربه مدیریت می‌شود — نه فریلنسرهای پراکنده.',
      'home.why.item2Title': 'سرعت و کیفیت در کنار هم',
      'home.why.item2Desc': 'فرآیندهای آزموده‌شده به ما اجازه می‌دهند سریع تحویل دهیم — بدون قربانی کردن کیفیت.',
      'home.why.item3Title': 'قیمت‌گذاری شفاف',
      'home.why.item3Desc': 'هزینه دقیق و زمان‌بندی را قبل از شروع می‌دانید. بدون هزینه‌های پنهان.',
      'home.why.item4Title': 'پشتیبانی واقعی، نه فقط وعده',
      'home.why.item4Desc': 'پس از راه‌اندازی هم در دسترس هستیم و مشکلات را سریع حل می‌کنیم.',

      // Home — Testimonials
      'home.testimonials.eyebrow': 'نظرات مشتریان',
      'home.testimonials.title': 'مشتریان ما چه می‌گویند',
      'home.testimonials.t1': '«فرا تِک فقط یک وب‌سایت نساخت — کسب‌وکار ما را درک کرد و راهکارهایی ارائه داد که واقعاً فروش ما را افزایش داد.»',
      'home.testimonials.t1Author': 'مریم رضایی',
      'home.testimonials.t1Role': 'مدیر، فروشگاه آرام',
      'home.testimonials.t2': '«توجه به جزئیات و پاسخگویی سریع، کل فرآیند را لذت‌بخش کرد. پلتفرم ما دقیقاً همان‌طور که تصور می‌کردیم شد.»',
      'home.testimonials.t2Author': 'علی محمدی',
      'home.testimonials.t2Role': 'بنیان‌گذار، زرین فین‌تک',
      'home.testimonials.t3': '«از ابتدا همه چیز شفاف بود — هزینه، زمان‌بندی و مراحل. به‌عنوان یک استارتاپ، این شفافیت برای ما فوق‌العاده ارزشمند بود.»',
      'home.testimonials.t3Author': 'سارا کریمی',
      'home.testimonials.t3Role': 'مدیر بازاریابی، سافرین',

      // Home — Contact
      'home.contact.title': 'درباره پروژه‌تان برای ما بگویید',
      'home.contact.subtitle': 'فرم را پر کنید یا مستقیماً تماس بگیرید — در کمتر از ۲۴ ساعت پاسخ می‌دهیم.',
      'home.contact.namePlaceholder': 'مثلاً علی رضایی',
      'home.contact.phonePlaceholder': '+98 912 345 6789',
      'home.contact.emailPlaceholder': 'you@example.com',
      'home.contact.messagePlaceholder': 'درباره پروژه و اهدافتان برای ما بگویید...',

      // Footer
      'footer.about': 'فرا تِک یک استودیوی فناوری دیجیتال است که بر تبدیل ایده‌ها به راهکارهای عملی دیجیتال تمرکز دارد. ما به کسب‌وکارها کمک می‌کنیم از طریق تجربه‌های وب مدرن، توسعه نرم‌افزار سفارشی، راهکارهای مبتنی بر هوش مصنوعی و اتوماسیون هوشمند فرآیندهای کسب‌وکار رشد کنند.',
      'footer.servicesTitle': 'خدمات',
      'footer.companyTitle': 'شرکت',
      'footer.serviceWebDev': 'توسعه وب',
      'footer.serviceCustomSoftware': 'نرم‌افزار سفارشی',
      'footer.serviceAI': 'راهکارهای AI',
      'footer.serviceAutomation': 'اتوماسیون',
      'footer.aboutUs': 'درباره ما',
      'footer.ourTeam': 'تیم ما',
      'footer.portfolio': 'نمونه‌کارها',
      'footer.contact': 'تماس',
      'footer.rights': '© ۲۰۲۵ فرا تِک — تمامی حقوق محفوظ است.',

      // About
      'about.eyebrow': 'درباره فرا تِک',
      'about.title': 'ما محصولات دیجیتال را طراحی، می‌سازیم و مقیاس می‌دهیم',
      'about.headerDesc': 'فرا تِک یک استودیوی فناوری دیجیتال است که بر تبدیل ایده‌ها به راهکارهای عملی دیجیتال تمرکز دارد. ما به کسب‌وکارها کمک می‌کنیم از طریق تجربه‌های وب مدرن، توسعه نرم‌افزار سفارشی، راهکارهای مبتنی بر هوش مصنوعی و اتوماسیون هوشمند فرآیندهای کسب‌وکار رشد کنند.',
      'about.story.eyebrow': 'داستان ما',
      'about.story.title': 'ایده‌ها به راهکارهای دیجیتال',
      'about.story.p1': 'فرا تِک یک استودیوی فناوری دیجیتال است که بر تبدیل ایده‌ها به راهکارهای عملی دیجیتال تمرکز دارد. ما به کسب‌وکارها کمک می‌کنیم از طریق تجربه‌های وب مدرن، توسعه نرم‌افزار سفارشی، راهکارهای مبتنی بر هوش مصنوعی و اتوماسیون هوشمند فرآیندهای کسب‌وکار رشد کنند.',
      'about.story.p2': 'رویکرد ما ترکیبی از طراحی متفکرانه، مهندسی مدرن و فناوری هوشمند برای خلق محصولات دیجیتال مفید، مقیاس‌پذیر و آینده‌نگر است.',
      'about.values.eyebrow': 'آنچه باور داریم',
      'about.values.title': 'ارزش‌هایی که هر پروژه را هدایت می‌کنند',
      'about.values.v1Title': 'کیفیت بر میان‌بر',
      'about.values.v1Desc': 'ترجیح می‌دهیم یک روز بیشتر وقت بگذاریم و کار را درست انجام دهیم تا چیزی تحویل دهیم که به آن افتخار نمی‌کنیم.',
      'about.values.v2Title': 'شفافیت در همه چیز',
      'about.values.v2Desc': 'قیمت شفاف، زمان‌بندی شفاف، ارتباط شفاف. بدون اصطلاحات پیچیده، بدون غافلگیری.',
      'about.values.v3Title': 'نتیجه، نه فقط خروجی',
      'about.values.v3Desc': 'وب‌سایت زیبایی که تبدیل نمی‌کند یک سرگرمی است — ما برای تأثیر قابل اندازه‌گیری می‌سازیم.',
      'about.values.v4Title': 'همکاری بلندمدت',
      'about.values.v4Desc': 'بیشتر مشتریان ما برمی‌گردند. ما هر پروژه را آغاز یک رابطه می‌دانیم.',
      'about.timeline.eyebrow': 'نقاط عطف',
      'about.timeline.title': 'سفر ما تاکنون',
      'about.timeline.t1Year': 'مأموریت',
      'about.timeline.t1Title': 'تبدیل ایده‌ها',
      'about.timeline.t1Desc': 'تمرکز بر تبدیل ایده‌ها به راهکارهای عملی دیجیتال.',
      'about.timeline.t2Year': 'رویکرد',
      'about.timeline.t2Title': 'طراحی متفکرانه',
      'about.timeline.t2Desc': 'ترکیب طراحی متفکرانه، مهندسی مدرن و فناوری هوشمند.',
      'about.timeline.t3Year': 'تمرکز',
      'about.timeline.t3Title': 'محصولات مقیاس‌پذیر',
      'about.timeline.t3Desc': 'خلق محصولات دیجیتال مفید و مقیاس‌پذیر برای کسب‌وکارها.',

      // Services
      'services.eyebrow': 'خدمات',
      'services.title': 'راهکارهای دیجیتال متناسب با نیاز شما',
      'services.headerDesc': 'چهار حوزه اصلی که در آن‌ها به ایده‌ها کمک می‌کنیم به محصولات دیجیتال عملی و مقیاس‌پذیر تبدیل شوند.',
      'services.cta.title': 'مطمئن نیستید به کدام خدمت نیاز دارید؟',
      'services.cta.desc': 'پیامی برای ما بفرستید تا به شما کمک کنیم رویکرد مناسب را پیدا کنید.',
      'services.cta.btn': 'با ما صحبت کنید',

      // Services (company-config)
      'service.webDev.title': 'توسعه وب و طراحی سایت مدرن',
      'service.webDev.desc': 'طراحی و توسعه وب‌سایت‌های مدرن، واکنش‌گرا و باکیفیت با تجربه‌های کاربری متفکرانه، هویت بصری قوی و پایه‌های فنی قابل نگهداری.',
      'service.webDev.item1': 'طراحی واکنش‌گرای سفارشی',
      'service.webDev.item2': 'هویت بصری قوی',
      'service.webDev.item3': 'پایه‌های قابل نگهداری',
      'service.customSoftware.title': 'توسعه نرم‌افزار سفارشی',
      'service.customSoftware.desc': 'ساخت راهکارهای نرم‌افزاری متناسب با نیازها، گردش‌کارها و نیازهای عملیاتی خاص کسب‌وکار.',
      'service.customSoftware.item1': 'گردش‌کارهای اختصاصی',
      'service.customSoftware.item2': 'معماری مقیاس‌پذیر',
      'service.customSoftware.item3': 'طراحی‌شده برای نیاز شما',
      'service.ai.title': 'راهکارهای هوش مصنوعی',
      'service.ai.desc': 'توسعه کاربردهای عملی مبتنی بر هوش مصنوعی، سیستم‌های هوشمند و راهکارهای یادگیری ماشین طراحی‌شده بر اساس موارد استفاده‌ی واقعی.',
      'service.ai.item1': 'AI کاربردی',
      'service.ai.item2': 'موارد استفاده واقعی',
      'service.ai.item3': 'سیستم‌های هوشمند',
      'service.automation.title': 'اتوماسیون فرآیندهای کسب‌وکار',
      'service.automation.desc': 'بهبود گردش‌کارها با کاهش وظایف دستی تکراری و یکپارچه‌سازی اتوماسیون هوشمند در عملیات کسب‌وکار.',
      'service.automation.item1': 'کاهش کار دستی',
      'service.automation.item2': 'گردش‌کارهای هوشمندتر',
      'service.automation.item3': 'یکپارچه‌سازی هوشمند',

      // Portfolio
      'portfolio.eyebrow': 'نمونه‌کارهای منتخب',
      'portfolio.title': 'پروژه‌هایی که با دقت ساختیم',
      'portfolio.headerDesc': 'گزیده‌ای از وب‌سایت‌ها، پلتفرم‌ها و محصولات مبتنی بر AI که با مشتریان‌مان ساخته‌ایم.',
      'portfolio.cta.title': 'پروژه‌ای در ذهن دارید؟',
      'portfolio.cta.desc': 'بیایید ایده شما را به محصولی تبدیل کنیم که کاربران‌تان عاشقش شوند.',
      'portfolio.cta.btn': 'شروع پروژه',
      'portfolio.viewDetails': 'مشاهده جزئیات',
      'portfolio.visitProject': 'مشاهده پروژه',
      'portfolio.noProjects': 'هنوز پروژه‌ای ثبت نشده.',

      // Team
      'team.eyebrow': 'تیم ما',
      'team.title': 'افراد پشت فرا تِک',
      'team.headerDesc': 'تیمی چندتخصصی از طراحان، مهندسان، استراتژیست‌ها و متخصصان AI — که با هم برای ساختن محصولات عالی کار می‌کنند.',
      'team.leadership.eyebrow': 'رهبری',
      'team.leadership.title': 'هدایت کار',
      'team.careers.eyebrow': 'فرصت‌های شغلی',
      'team.careers.title': 'می‌خواهید به تیم بپیوندید؟',
      'team.careers.desc': 'همیشه به دنبال افراد با استعدادی هستیم که عاشق ساختن چیزهای مهم هستند.',
      'team.careers.noPositions': 'در حال حاضر موقعیت شغلی باز نداریم.',
      'team.careers.apply': 'درخواست',
      'team.careers.fullTime': 'تمام‌وقت · دورکاری / تهران',
      'team.cta.title': 'بیایید با هم چیزی بسازیم',
      'team.cta.desc': 'چه مشتری باشید چه هم‌تیمی آینده — خوشحال می‌شویم از شما بشنویم.',
      'team.cta.btn': 'تماس با ما',
      'team.founder1.role': 'هوش مصنوعی و توسعه نرم‌افزار',
      'team.founder1.focus': 'هوش مصنوعی، سیستم‌های هوشمند، مهندسی نرم‌افزار و محصولات دیجیتال مبتنی بر AI.',
      'team.founder2.role': 'نرم‌افزار و راهکارهای دیجیتال',
      'team.founder2.focus': 'توسعه نرم‌افزار، راهکارهای دیجیتال و ساخت محصولات فناوری عملی.',

      // AI Lab
      'aiLab.badge': 'آزمایشگاه AI فرا تِک',
      'aiLab.title': 'هوش مصنوعی کاربردی، برای <span class="ai-accent">مسائل واقعی کسب‌وکار</span>',
      'aiLab.headerDesc': 'از چت‌بات‌های هوشمند و اتوماسیون محتوا تا مدل‌های سفارشی یادگیری ماشین — آزمایشگاه AI ما هوش مصنوعی را به ابزارهایی تبدیل می‌کند که واقعاً نتیجه می‌دهند.',
      'aiLab.ctaPrimary': 'درباره پروژه AI صحبت کنیم',
      'aiLab.ctaSecondary': 'مشاهده قابلیت‌ها',
      'aiLab.capabilities.eyebrow': 'قابلیت‌ها',
      'aiLab.capabilities.title': 'با AI چه می‌سازیم',
      'aiLab.capabilities.desc': 'شش حوزه‌ای که در آن‌ها به مشتریان کمک کرده‌ایم AI را به نتیجه تبدیل کنند.',
      'aiLab.process.eyebrow': 'نحوه کار ما',
      'aiLab.process.title': 'فرآیند پروژه AI ما',
      'aiLab.usecases.eyebrow': 'موارد استفاده',
      'aiLab.usecases.title': 'جایی که کار AI ما می‌درخشد',
      'aiLab.cta.title': 'آماده‌اید AI را در کسب‌وکارتان به کار بگیرید؟',
      'aiLab.cta.desc': 'یک تماس کشف ۳۰ دقیقه‌ای با تیم آزمایشگاه AI ما رزرو کنید.',
      'aiLab.cta.btn': 'رزرو تماس',

      // Contact
      'contact.eyebrow': 'تماس',
      'contact.title': 'درباره پروژه‌تان صحبت کنیم',
      'contact.headerDesc': 'به ما بگویید چه نیاز دارید و در کمتر از ۲۴ ساعت با شما تماس می‌گیریم.',
      'contact.getInTouch': 'با ما در ارتباط باشید',
      'contact.hoursDesc': 'ما دوشنبه تا جمعه، ۹ صبح تا ۶ عصر در دسترس هستیم. همچنین می‌توانید هر زمان از طریق ایمیل با ما در تماس باشید.',
      'contact.formName': 'نام و نام خانوادگی',
      'contact.formPhone': 'تلفن',
      'contact.formEmail': 'ایمیل',
      'contact.formTopic': 'موضوع',
      'contact.formTopicSelect': 'یک موضوع انتخاب کنید',
      'contact.formTopicWeb': 'طراحی و توسعه وب',
      'contact.formTopicUIUX': 'طراحی UI / UX',
      'contact.formTopicSEO': 'سئو و رشد',
      'contact.formTopicEcommerce': 'فروشگاه اینترنتی',
      'contact.formTopicAI': 'پروژه AI',
      'contact.formTopicOther': 'سایر',
      'contact.formMessage': 'جزئیات پروژه',
      'contact.formMessagePlaceholder': 'درباره پروژه، اهداف و زمان‌بندی برای ما بگویید...',
      'contact.sendBtn': 'ارسال پیام',

      // Project detail
      'project.breadcrumbWork': 'نمونه‌کارها',
      'project.overview': 'معرفی پروژه',
      'project.technologiesUsed': 'تکنولوژی‌های استفاده‌شده',
      'project.infoTitle': 'اطلاعات پروژه',
      'project.infoClient': 'کارفرما',
      'project.infoCategory': 'دسته‌بندی',
      'project.infoStatus': 'وضعیت',
      'project.statusPublished': 'منتشرشده',
      'project.backToPortfolio': 'بازگشت به نمونه‌کارها',
      'project.viewLive': 'مشاهده پروژه زنده',
      'project.details': 'جزئیات پروژه',
      'project.noDetails': 'جزئیات بیشتری در دسترس نیست.',
      'project.gallery': 'گالری پروژه',
      'project.noMedia': 'رسانه‌ای برای این پروژه موجود نیست.',
      'project.notFound': 'پروژه یافت نشد',
      'project.notFoundDesc': 'لطفاً به صفحه نمونه‌کارها بازگردید و یک پروژه معتبر انتخاب کنید.',
      'project.errorLoading': 'خطا در بارگذاری پروژه',
      'project.errorLoadingDesc': 'خطا در بارگذاری جزئیات پروژه. لطفاً دوباره تلاش کنید.',
      'project.noDescription': 'توضیحی موجود نیست.',
      'project.loadingDesc': 'در حال بارگذاری...',

      // CTA band
      'ctaBand.contactUs': 'تماس با ما',
      'ctaBand.getInTouch': 'در ارتباط باشید',
      'ctaBand.startProject': 'شروع پروژه',
    },

    // ---------------------------------------------------------------
    // انگلیسی
    // ---------------------------------------------------------------
    en: {
      'brand.name': 'FaraTech',
      'brand.tagline': 'AGENCY',

      'nav.home': 'Home',
      'nav.about': 'About',
      'nav.services': 'Services',
      'nav.work': 'Work',
      'nav.team': 'Team',
      'nav.aiLab': 'AI Lab',
      'nav.contact': 'Contact',
      'nav.startProject': 'Start a Project',

      'lang.fa': 'FA',
      'lang.en': 'EN',

      'common.readMore': 'Read more',
      'common.viewAll': 'View all',
      'common.learnMore': 'Learn more',
      'common.getStarted': 'Get started',
      'common.contactUs': 'Contact us',
      'common.backToHome': 'Back to home',
      'common.loading': 'Loading...',
      'common.noResults': 'No results found.',
      'common.error': 'Error loading. Please try again.',
      'common.sendMessage': 'Send Message',
      'common.sending': 'Sending...',
      'common.messageSent': 'Message sent!',
      'common.fullName': 'Full name',
      'common.phone': 'Phone',
      'common.email': 'Email',
      'common.message': 'Message',
      'common.required': 'Required',
      'common.select': 'Select',
      'common.name': 'Name',
      'common.date': 'Date',
      'common.status': 'Status',
      'common.category': 'Category',
      'common.client': 'Client',
      'common.technologies': 'Technologies',

      'home.hero.badge': 'Ideas into Digital Solutions',
      'home.hero.title': '<span class="hero-title-line1">Your Ideas</span> <span class="hero-title-line2">Our Technology</span> <span class="accent">Infinite Possibilities</span>',
      'home.hero.lead': 'FaraTech is a digital technology studio focused on transforming ideas into practical digital solutions. We help businesses grow through modern web experiences, custom software development, AI-powered solutions, and intelligent business-process automation.',
      'home.hero.ctaPrimary': 'Get a Free Consultation',
      'home.hero.ctaSecondary': 'View Our Work',
      'home.hero.stat1Title': 'Innovation',
      'home.hero.stat1Sub': 'Driven',
      'home.hero.stat2Title': 'Quality',
      'home.hero.stat2Sub': 'Focused',
      'home.hero.stat3Title': 'Results',
      'home.hero.stat3Sub': 'Oriented',

      'home.frame.url': 'faratech/dashboard',
      'home.frame.activeProject': 'ACTIVE PROJECT',
      'home.frame.title': 'Building Digital Solutions',
      'home.frame.webDev': 'Web Development',
      'home.frame.inProgress': 'In Progress',
      'home.frame.aiIntegration': 'AI Integration',
      'home.frame.planning': 'Planning',
      'home.frame.livePreview': 'Live Preview',
      'home.frame.fast': 'Fast',
      'home.frame.quality': 'Quality',
      'home.frame.trusted': 'Trusted',
      'home.scrollHint': 'Scroll to explore',

      'home.trusted.label': 'TRUSTED BY PROFESSIONAL TEAMS',

      'home.services.eyebrow': 'SERVICES',
      'home.services.title': 'Digital solutions tailored to your needs',
      'home.services.subtitle': 'Three core areas we focus on — plus more under the hood.',
      'home.services.viewAll': 'View All Services',

      'home.portfolio.eyebrow': 'SELECTED WORK',
      'home.portfolio.title': "Projects we've carefully crafted",
      'home.portfolio.viewAll': 'View All Projects',

      'home.aiTeaser.tag': 'FARATECH AI LAB',
      'home.aiTeaser.title': "We don't just build websites — we build with AI",
      'home.aiTeaser.description': 'From AI-powered chatbots and content generation to intelligent automation and custom machine learning models — our AI Lab delivers real, business-grade AI solutions.',
      'home.aiTeaser.cta': 'Explore AI Lab',

      'home.process.eyebrow': 'OUR PROCESS',
      'home.process.title': 'From idea to launch, in four steps',
      'home.process.subtitle': 'A transparent, structured and trackable process.',
      'home.process.step1Title': 'Discovery & Consulting',
      'home.process.step1Desc': 'We meet to understand your goals, users and business needs.',
      'home.process.step2Title': 'Design & Strategy',
      'home.process.step2Desc': 'We design the structure and visuals based on real data.',
      'home.process.step3Title': 'Development',
      'home.process.step3Desc': 'We build your website with modern technologies and test it thoroughly.',
      'home.process.step4Title': 'Launch & Support',
      'home.process.step4Desc': 'After launch, we stay with you through training and ongoing support.',

      'home.why.eyebrow': 'WHY FARATECH',
      'home.why.title': 'The difference is in the details',
      'home.why.subtitle': 'We treat your business like our own — driven by results, precision, and long-term thinking.',
      'home.why.stat1Lbl': 'Client satisfaction',
      'home.why.stat2Lbl': 'Projects delivered',
      'home.why.stat3Lbl': 'Industry experience',
      'home.why.stat4Lbl': 'To kick off a project',
      'home.why.item1Title': 'Dedicated in-house team',
      'home.why.item1Desc': 'Your project is handled by a stable, experienced team — not scattered freelancers.',
      'home.why.item2Title': 'Speed and quality together',
      'home.why.item2Desc': 'Battle-tested processes let us deliver fast — without compromising on quality.',
      'home.why.item3Title': 'Transparent pricing',
      'home.why.item3Desc': 'You know the exact cost and timeline before we begin. No hidden fees.',
      'home.why.item4Title': 'Real support, not just promises',
      'home.why.item4Desc': 'We remain available after launch and resolve issues quickly.',

      'home.testimonials.eyebrow': 'TESTIMONIALS',
      'home.testimonials.title': 'What our clients say',
      'home.testimonials.t1': '"FaraTech didn\'t just build a website — they understood our business and delivered solutions that genuinely increased our sales."',
      'home.testimonials.t1Author': 'Maryam Rezaei',
      'home.testimonials.t1Role': 'Manager, Aram Store',
      'home.testimonials.t2': '"Attention to detail and fast responses made the whole process a pleasure. Our platform turned out exactly the way we envisioned it."',
      'home.testimonials.t2Author': 'Ali Mohammadi',
      'home.testimonials.t2Role': 'Founder, Zarrin Fintech',
      'home.testimonials.t3': '"Everything was transparent from the start — cost, timeline and milestones. As a startup, that clarity was incredibly valuable to us."',
      'home.testimonials.t3Author': 'Sara Karimi',
      'home.testimonials.t3Role': 'Marketing Manager, Safarin',

      'home.contact.title': 'Tell us about your project',
      'home.contact.subtitle': 'Fill out the form or reach out directly — we respond in under 24 hours.',
      'home.contact.namePlaceholder': 'e.g. John Smith',
      'home.contact.phonePlaceholder': '+1 555 123 4567',
      'home.contact.emailPlaceholder': 'you@example.com',
      'home.contact.messagePlaceholder': 'Tell us about your project and goals...',

      'footer.about': 'FaraTech is a digital technology studio focused on transforming ideas into practical digital solutions. We help businesses grow through modern web experiences, custom software development, AI-powered solutions, and intelligent business-process automation.',
      'footer.servicesTitle': 'Services',
      'footer.companyTitle': 'Company',
      'footer.serviceWebDev': 'Web Development',
      'footer.serviceCustomSoftware': 'Custom Software',
      'footer.serviceAI': 'AI Solutions',
      'footer.serviceAutomation': 'Automation',
      'footer.aboutUs': 'About Us',
      'footer.ourTeam': 'Our Team',
      'footer.portfolio': 'Portfolio',
      'footer.contact': 'Contact',
      'footer.rights': '© 2025 FaraTech — All rights reserved.',

      'about.eyebrow': 'ABOUT FARATECH',
      'about.title': 'We design, build and scale digital products',
      'about.headerDesc': 'FaraTech is a digital technology studio focused on transforming ideas into practical digital solutions. We help businesses grow through modern web experiences, custom software development, AI-powered solutions, and intelligent business-process automation.',
      'about.story.eyebrow': 'OUR STORY',
      'about.story.title': 'Ideas into Digital Solutions',
      'about.story.p1': 'FaraTech is a digital technology studio focused on transforming ideas into practical digital solutions. We help businesses grow through modern web experiences, custom software development, AI-powered solutions, and intelligent business-process automation.',
      'about.story.p2': 'Our approach combines thoughtful design, modern engineering, and intelligent technology to create useful, scalable digital products.',
      'about.values.eyebrow': 'WHAT WE BELIEVE',
      'about.values.title': 'Values that guide every project',
      'about.values.v1Title': 'Craft over shortcuts',
      'about.values.v1Desc': "We'd rather take one more day to make it right than ship something we're not proud of.",
      'about.values.v2Title': 'Clarity in everything',
      'about.values.v2Desc': 'Clear pricing, clear timelines, clear communication. No jargon, no surprises.',
      'about.values.v3Title': 'Results, not just output',
      'about.values.v3Desc': "A beautiful website that doesn't convert is a hobby — we build for measurable impact.",
      'about.values.v4Title': 'Long-term partnerships',
      'about.values.v4Desc': 'Most of our clients come back. We treat every project as the start of a relationship.',
      'about.timeline.eyebrow': 'MILESTONES',
      'about.timeline.title': 'Our journey so far',
      'about.timeline.t1Year': 'Mission',
      'about.timeline.t1Title': 'Transforming Ideas',
      'about.timeline.t1Desc': 'Focused on transforming ideas into practical digital solutions.',
      'about.timeline.t2Year': 'Approach',
      'about.timeline.t2Title': 'Thoughtful Design',
      'about.timeline.t2Desc': 'Combining thoughtful design, modern engineering, and intelligent technology.',
      'about.timeline.t3Year': 'Focus',
      'about.timeline.t3Title': 'Scalable Products',
      'about.timeline.t3Desc': 'Creating useful, scalable digital products for businesses.',

      'services.eyebrow': 'SERVICES',
      'services.title': 'Digital solutions tailored to your needs',
      'services.headerDesc': 'Four core areas where we help ideas become practical, scalable digital products.',
      'services.cta.title': 'Not sure which service you need?',
      'services.cta.desc': "Send us a message and we'll help you figure out the right approach.",
      'services.cta.btn': 'Talk to us',

      'service.webDev.title': 'Web Development & Modern Website Design',
      'service.webDev.desc': 'Design and develop modern, responsive, high-quality websites with thoughtful user experiences, strong visual identity, and maintainable technical foundations.',
      'service.webDev.item1': 'Custom responsive design',
      'service.webDev.item2': 'Strong visual identity',
      'service.webDev.item3': 'Maintainable foundations',
      'service.customSoftware.title': 'Custom Software Development',
      'service.customSoftware.desc': 'Build software solutions tailored to specific business requirements, workflows, and operational needs.',
      'service.customSoftware.item1': 'Tailored workflows',
      'service.customSoftware.item2': 'Scalable architecture',
      'service.customSoftware.item3': 'Built to fit your needs',
      'service.ai.title': 'Artificial Intelligence Solutions',
      'service.ai.desc': 'Develop practical AI-powered applications, intelligent systems, and machine-learning solutions designed around real use cases.',
      'service.ai.item1': 'Applied AI',
      'service.ai.item2': 'Real use cases',
      'service.ai.item3': 'Intelligent systems',
      'service.automation.title': 'Business Process Automation',
      'service.automation.desc': 'Improve workflows by reducing repetitive manual tasks and integrating intelligent automation into business operations.',
      'service.automation.item1': 'Reduce manual work',
      'service.automation.item2': 'Smarter workflows',
      'service.automation.item3': 'Intelligent integration',

      'portfolio.eyebrow': 'SELECTED WORK',
      'portfolio.title': "Projects we've carefully crafted",
      'portfolio.headerDesc': "A selection of recent websites, platforms and AI-powered products we've built with our clients.",
      'portfolio.cta.title': 'Have a project in mind?',
      'portfolio.cta.desc': "Let's turn your idea into a product your users will love.",
      'portfolio.cta.btn': 'Start a project',
      'portfolio.viewDetails': 'View details',
      'portfolio.visitProject': 'Visit project',
      'portfolio.noProjects': 'No projects yet.',

      'team.eyebrow': 'OUR TEAM',
      'team.title': 'The people behind FaraTech',
      'team.headerDesc': 'A multidisciplinary team of designers, engineers, strategists and AI specialists — working together to ship great products.',
      'team.leadership.eyebrow': 'LEADERSHIP',
      'team.leadership.title': 'Leading the work',
      'team.careers.eyebrow': 'CAREERS',
      'team.careers.title': 'Want to join the team?',
      'team.careers.desc': "We're always looking for talented people who love building things that matter.",
      'team.careers.noPositions': 'No open positions at the moment.',
      'team.careers.apply': 'Apply',
      'team.careers.fullTime': 'Full-time · Remote / Tehran',
      'team.cta.title': "Let's build something together",
      'team.cta.desc': "Whether you're a client or a future teammate — we'd love to hear from you.",
      'team.cta.btn': 'Contact us',
      'team.founder1.role': 'AI & Software Development',
      'team.founder1.focus': 'Artificial intelligence, intelligent systems, software engineering, and AI-powered digital products.',
      'team.founder2.role': 'Software & Digital Solutions',
      'team.founder2.focus': 'Software development, digital solutions, and building practical technology products.',

      'aiLab.badge': 'FARATECH AI LAB',
      'aiLab.title': 'Applied AI, built for <span class="ai-accent">real business problems</span>',
      'aiLab.headerDesc': 'From intelligent chatbots and content automation to custom machine learning models — our AI Lab turns artificial intelligence into tools that actually move the needle.',
      'aiLab.ctaPrimary': 'Discuss an AI project',
      'aiLab.ctaSecondary': 'See capabilities',
      'aiLab.capabilities.eyebrow': 'CAPABILITIES',
      'aiLab.capabilities.title': 'What we build with AI',
      'aiLab.capabilities.desc': "Six areas where we've helped clients turn AI into results.",
      'aiLab.process.eyebrow': 'HOW WE WORK',
      'aiLab.process.title': 'Our AI project process',
      'aiLab.usecases.eyebrow': 'USE CASES',
      'aiLab.usecases.title': 'Where our AI work shines',
      'aiLab.cta.title': 'Ready to put AI to work in your business?',
      'aiLab.cta.desc': 'Book a 30-minute discovery call with our AI Lab team.',
      'aiLab.cta.btn': 'Book a call',

      'contact.eyebrow': 'CONTACT',
      'contact.title': "Let's talk about your project",
      'contact.headerDesc': "Tell us what you need and we'll get back to you within 24 hours.",
      'contact.getInTouch': 'Get in touch',
      'contact.hoursDesc': "We're available Monday–Friday, 9am–6pm. You can also reach us via email any time.",
      'contact.formName': 'Full name',
      'contact.formPhone': 'Phone',
      'contact.formEmail': 'Email',
      'contact.formTopic': 'Topic',
      'contact.formTopicSelect': 'Select a topic',
      'contact.formTopicWeb': 'Web Design & Development',
      'contact.formTopicUIUX': 'UI / UX Design',
      'contact.formTopicSEO': 'SEO & Growth',
      'contact.formTopicEcommerce': 'E-Commerce',
      'contact.formTopicAI': 'AI Project',
      'contact.formTopicOther': 'Other',
      'contact.formMessage': 'Project details',
      'contact.formMessagePlaceholder': 'Tell us about your project, goals and timeline...',
      'contact.sendBtn': 'Send Message',

      'project.breadcrumbWork': 'Work',
      'project.overview': 'Project Overview',
      'project.technologiesUsed': 'Technologies Used',
      'project.infoTitle': 'Project Information',
      'project.infoClient': 'Client',
      'project.infoCategory': 'Category',
      'project.infoStatus': 'Status',
      'project.statusPublished': 'Published',
      'project.backToPortfolio': 'Back to Portfolio',
      'project.viewLive': 'View Live Project',
      'project.details': 'Project Details',
      'project.noDetails': 'No additional details available.',
      'project.gallery': 'Project Gallery',
      'project.noMedia': 'No media available for this project.',
      'project.notFound': 'Project Not Found',
      'project.notFoundDesc': 'Please go back to the portfolio page and select a valid project.',
      'project.errorLoading': 'Error Loading Project',
      'project.errorLoadingDesc': 'There was an error loading the project. Please try again later.',
      'project.noDescription': 'No description available.',
      'project.loadingDesc': 'Please wait while we load the project details.',

      'ctaBand.contactUs': 'Contact us',
      'ctaBand.getInTouch': 'Get in touch',
      'ctaBand.startProject': 'Start a project',
    }
  };

  // =====================================================================
  // وضعیت داخلی
  // =====================================================================
  const state = {
    current: DEFAULT_LANG,
    listeners: []
  };

  // =====================================================================
  // توابع
  // =====================================================================
  function t(key) {
    const lang = state.current;
    if (dict[lang] && Object.prototype.hasOwnProperty.call(dict[lang], key)) {
      return dict[lang][key];
    }
    // fallback به انگلیسی
    if (dict.en && Object.prototype.hasOwnProperty.call(dict.en, key)) {
      return dict.en[key];
    }
    // در نهایت، خود کلید
    return key;
  }

  function apply() {
    // متنهای ساده
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var key = el.getAttribute('data-i18n');
      el.textContent = t(key);
    });

    // متنهای HTML
    document.querySelectorAll('[data-i18n-html]').forEach(function (el) {
      var key = el.getAttribute('data-i18n-html');
      el.innerHTML = t(key);
    });

    // attribute ها (placeholder, title, aria-label, ...)
    document.querySelectorAll('[data-i18n-attr]').forEach(function (el) {
      var spec = el.getAttribute('data-i18n-attr');
      var parts = spec.split(';');
      parts.forEach(function (part) {
        var pair = part.split(':');
        if (pair.length === 2) {
          var attr = pair[0].trim();
          var key = pair[1].trim();
          el.setAttribute(attr, t(key));
        }
      });
    });

    // سوییچرها: کلاس active
    document.querySelectorAll('[data-lang-switch]').forEach(function (btn) {
      if (btn.getAttribute('data-lang-switch') === state.current) {
        btn.classList.add('active');
        btn.setAttribute('aria-current', 'true');
      } else {
        btn.classList.remove('active');
        btn.removeAttribute('aria-current');
      }
    });
  }

  function setLang(lang) {
    if (lang !== 'fa' && lang !== 'en') return;
    state.current = lang;

    try {
      localStorage.setItem(STORAGE_KEY, lang);
    } catch (e) { /* ignore */ }

    // تنظیم lang و dir روی html
    document.documentElement.setAttribute('lang', lang);
    document.documentElement.setAttribute('dir', lang === 'fa' ? 'rtl' : 'ltr');
    document.body.classList.toggle('is-fa', lang === 'fa');
    document.body.classList.toggle('is-en', lang === 'en');

    apply();

    // رویداد
    var evt = new CustomEvent('ft:langchange', { detail: { lang: lang } });
    document.dispatchEvent(evt);
  }

  function init() {
    // خواندن از localStorage
    var saved;
    try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
    var startLang = (saved === 'fa' || saved === 'en') ? saved : DEFAULT_LANG;

    // اتصال دکمههای سوییچ
    document.querySelectorAll('[data-lang-switch]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        setLang(btn.getAttribute('data-lang-switch'));
      });
    });

    // اعمال اولیه
    state.current = startLang;
    document.documentElement.setAttribute('lang', startLang);
    document.documentElement.setAttribute('dir', startLang === 'fa' ? 'rtl' : 'ltr');
    document.body.classList.toggle('is-fa', startLang === 'fa');
    document.body.classList.toggle('is-en', startLang === 'en');
    apply();
  }

  // =====================================================================
  // Public API
  // =====================================================================
  window.FT_I18N = {
    get current() { return state.current; },
    get isRTL() { return state.current === 'fa'; },
    t: t,
    apply: apply,
    setLang: setLang,
    dict: dict
  };

  // راهاندازی
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();