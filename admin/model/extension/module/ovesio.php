<?php

class ModelExtensionModuleOvesio extends Model
{
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ovesio_list` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `resource` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
            `resource_id` INT(11) NOT NULL,
            `lang` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `generate_description_id` INT(11) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `generate_description_hash` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
            `generate_description_date` DATETIME NULL,
            `generate_description_status` INT(11) DEFAULT 0,
            `metatags_id` INT(11) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `metatags_hash` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
            `metatags_date` DATETIME NULL,
            `metatags_status` INT(11) DEFAULT 0,
            `translate_id` INT(11) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
            `translate_hash` VARCHAR(50) NULL COLLATE 'utf8mb4_general_ci',
            `translate_date` DATETIME NULL,
            `translate_status` INT(11) DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`) USING BTREE,
            INDEX `resource` (`resource`) USING BTREE,
            INDEX `resource_id` (`resource_id`) USING BTREE,
            INDEX `lang` (`lang`) USING BTREE,
            INDEX `generate_description_id` (`generate_description_id`) USING BTREE,
            INDEX `generate_description_hash` (`generate_description_hash`) USING BTREE,
            INDEX `generate_description_date` (`generate_description_date`) USING BTREE,
            INDEX `generate_description_status` (`generate_description_status`) USING BTREE,
            INDEX `metatags_id` (`metatags_id`) USING BTREE,
            INDEX `metatags_hash` (`metatags_hash`) USING BTREE,
            INDEX `metatags_date` (`metatags_date`) USING BTREE,
            INDEX `metatags_status` (`metatags_status`) USING BTREE,
            INDEX `translate_id` (`translate_id`) USING BTREE,
            INDEX `translate_hash` (`translate_hash`) USING BTREE,
            INDEX `translate_date` (`translate_date`) USING BTREE,
            INDEX `translate_status` (`translate_status`) USING BTREE,
            INDEX `created_at` (`created_at`) USING BTREE
        )
        COLLATE='utf8mb4_general_ci' ENGINE=InnoDB;");
    }

    public function deleteIgnoredList()
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "ovesio_list` SET generate_description_id = NULL WHERE generate_description_id = 0 AND generate_description_hash IS NULL");
        $this->db->query("UPDATE `" . DB_PREFIX . "ovesio_list` SET translate_id = NULL WHERE translate_id = 0 AND translate_hash IS NULL");
        $this->db->query("UPDATE `" . DB_PREFIX . "ovesio_list` SET metatags_id = NULL WHERE metatags_id = 0 AND metatags_hash IS NULL");
    }
}