-- ============================================
-- 1. ATTRIBUTES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `attributes` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `type` VARCHAR(50) NOT NULL DEFAULT 'select',
    `display_type` VARCHAR(50) NOT NULL DEFAULT 'dropdown',
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `is_filterable` TINYINT(1) NOT NULL DEFAULT 1,
    `is_global` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `description` TEXT NULL,
    `validation_rules` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    
    INDEX `attributes_type_index` (`type`),
    INDEX `attributes_is_filterable_index` (`is_filterable`),
    INDEX `attributes_sort_order_index` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. ATTRIBUTE VALUES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `attribute_values` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `attribute_id` CHAR(36) NOT NULL,
    `value` VARCHAR(255) NOT NULL,
    `color_code` VARCHAR(7) NULL,
    `image` VARCHAR(255) NULL,
    `swatch_image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `metadata` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    INDEX `attribute_values_attribute_id_sort_order_index` (`attribute_id`, `sort_order`),
    
    CONSTRAINT `av_attribute_id_foreign` 
        FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. ATTRIBUTE GROUPS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS `attribute_groups` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    
    INDEX `attribute_groups_is_active_sort_order_index` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. ATTRIBUTE GROUP MAPPINGS (Pivot)
-- ============================================
CREATE TABLE IF NOT EXISTS `attribute_group_mappings` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `attribute_group_id` CHAR(36) NOT NULL,
    `attribute_id` CHAR(36) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    UNIQUE KEY `group_attribute_unique` (`attribute_group_id`, `attribute_id`),
    INDEX `agm_group_id_sort_order_index` (`attribute_group_id`, `sort_order`),
    
    CONSTRAINT `agm_attribute_group_id_foreign` 
        FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_groups`(`id`) ON DELETE CASCADE,
    CONSTRAINT `agm_attribute_id_foreign` 
        FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. CATEGORY ATTRIBUTE MAPPINGS
-- ============================================
CREATE TABLE IF NOT EXISTS `category_attribute_mappings` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `category_id` CHAR(36) NOT NULL,
    `attribute_id` CHAR(36) NOT NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `is_filterable` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    UNIQUE KEY `cat_attr_unique` (`category_id`, `attribute_id`),
    INDEX `cam_category_id_sort_order_index` (`category_id`, `sort_order`),
    
    CONSTRAINT `cam_category_id_foreign` 
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    CONSTRAINT `cam_attribute_id_foreign` 
        FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. CATEGORY ATTRIBUTE GROUPS (Pivot)
-- ============================================
CREATE TABLE IF NOT EXISTS `category_attribute_groups` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `category_id` CHAR(36) NOT NULL,
    `attribute_group_id` CHAR(36) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    UNIQUE KEY `category_group_unique` (`category_id`, `attribute_group_id`),
    INDEX `cag_category_id_sort_order_index` (`category_id`, `sort_order`),
    
    CONSTRAINT `cag_category_id_foreign` 
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    CONSTRAINT `cag_attribute_group_id_foreign` 
        FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. PRODUCT VARIANT ATTRIBUTE VALUES
-- ============================================
CREATE TABLE IF NOT EXISTS `product_variant_attribute_values` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `product_variant_id` CHAR(36) NOT NULL,
    `attribute_value_id` CHAR(36) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    UNIQUE KEY `variant_attribute_unique` (`product_variant_id`, `attribute_value_id`),
    INDEX `pvav_product_variant_id_index` (`product_variant_id`),
    INDEX `pvav_attribute_value_id_index` (`attribute_value_id`),
    
    CONSTRAINT `pvav_product_variant_id_foreign` 
        FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `pvav_attribute_value_id_foreign` 
        FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;