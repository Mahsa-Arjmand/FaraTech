-- =====================================================================
-- FaraTech — i18n Migration
-- افزودن پشتیبانی دوزبانه (فارسی + انگلیسی)
-- تاریخ: 2025
-- =====================================================================
-- ⚠️ قبل از اجرا از دیتابیس بکاپ بگیرید!
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. جدول content: ستون language
-- ---------------------------------------------------------------------
-- بررسی وجود ستون (برای اجرای امن چندباره)
SET @col_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'content'
    AND COLUMN_NAME = 'language'
);

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE content ADD COLUMN language VARCHAR(5) NOT NULL DEFAULT ''en'' AFTER content_key',
  'SELECT ''column language already exists'' AS msg'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- اطمینان از وجود unique key صحیح شامل language
-- حذف index قدیمی اگر وجود داره
SET @idx_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'content'
    AND INDEX_NAME = 'unique_page_section_key'
);

SET @sql = IF(@idx_exists > 0,
  'ALTER TABLE content DROP INDEX unique_page_section_key',
  'SELECT ''old index not found'' AS msg'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- افزودن unique key جدید
SET @idx_exists = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'content'
    AND INDEX_NAME = 'unique_page_section_key_lang'
);

SET @sql = IF(@idx_exists = 0,
  'ALTER TABLE content ADD UNIQUE KEY unique_page_section_key_lang (page, section, content_key, language)',
  'SELECT ''new index already exists'' AS msg'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 2. جدول projects: فیلدهای فارسی
-- ---------------------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='projects' AND COLUMN_NAME='title_fa');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE projects ADD COLUMN title_fa VARCHAR(255) NULL AFTER title',
  'SELECT ''title_fa exists'' AS msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='projects' AND COLUMN_NAME='description_fa');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE projects ADD COLUMN description_fa TEXT NULL AFTER description',
  'SELECT ''description_fa exists'' AS msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='projects' AND COLUMN_NAME='client_fa');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE projects ADD COLUMN client_fa VARCHAR(255) NULL AFTER client',
  'SELECT ''client_fa exists'' AS msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='projects' AND COLUMN_NAME='category_fa');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE projects ADD COLUMN category_fa VARCHAR(100) NULL AFTER category',
  'SELECT ''category_fa exists'' AS msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 3. جدول team_positions: فیلدهای فارسی
-- ---------------------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='team_positions' AND COLUMN_NAME='name_fa');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE team_positions ADD COLUMN name_fa VARCHAR(255) NULL AFTER name',
  'SELECT ''name_fa exists'' AS msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='team_positions' AND COLUMN_NAME='description_fa');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE team_positions ADD COLUMN description_fa TEXT NULL AFTER description',
  'SELECT ''description_fa exists'' AS msg');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 4. پر کردن محتوای فارسی موجود content (اختیاری)
-- ---------------------------------------------------------------------
-- محتوای فعلی همگی language='en' دارن. برای ترجمه، رکورد جدید با language='fa' درج میشه.
-- نمونه (اجرای دستی در صورت نیاز):
-- INSERT INTO content (page, section, content_key, language, content_value)
-- VALUES ('index', 'hero', 'title', 'fa', 'ایدههای شما. تکنولوژی ما. امکانات بینهایت');

-- ---------------------------------------------------------------------
-- ✅ پایان Migration
-- ---------------------------------------------------------------------
SELECT 'Migration completed successfully' AS status;