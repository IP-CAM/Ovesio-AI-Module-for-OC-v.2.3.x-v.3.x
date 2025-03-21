<?php

class ModelExtensionModuleOvesio extends Model
{
    private $module_key = 'ovesio';

    public function __construct($registry)
    {
        parent::__construct($registry);

        /**
         * Changes needed for v3
         */
        if(version_compare(VERSION, '3.0.0.0') >= 0) {
            $this->module_key = 'module_ovesio';
        }

        $this->from_language_id = (int)$this->config->get($this->module_key . '_catalog_language_id');
    }

    public function getCategories($category_ids = [])
    {
        $where = '';
        if (!empty($category_ids)) {
            $where = 'AND c.category_id IN (' . implode(',', $category_ids) . ')';
        }

        if (!$this->config->get($this->module_key . '_send_disabled')) {
            $where .= " AND c.status != 0";
        }

        $query = $this->db->query("SELECT cd.* FROM " . DB_PREFIX . "category_description as cd
        JOIN " . DB_PREFIX . "category as c ON c.category_id = cd.category_id
        WHERE cd.language_id = {$this->from_language_id} $where
        ORDER BY c.category_id");

        return $query->rows;
    }

    public function getProducts($product_id = [])
    {
        $where = '';
        if (!empty($product_id)) {
            $where = ' AND p.product_id IN (' . implode(',', $product_id) . ')';
        }

        if (!$this->config->get($this->module_key . '_send_stock_0')) {
			$where .= " AND (p.quantity > '0' OR p.stock_status_id != '" . (int)$this->config->get('config_stock_status_out') . "')";
		}

        if (!$this->config->get($this->module_key . '_send_disabled')) {
            $where .= " AND p.status != 0";
        }

        $query = $this->db->query("SELECT pd.* FROM " . DB_PREFIX . "product_description as pd
        JOIN " . DB_PREFIX . "product as p ON p.product_id = pd.product_id
        WHERE pd.language_id = {$this->from_language_id} $where
        ORDER BY p.product_id");

        return $query->rows;
    }

    public function getProductAttributeIds($product_ids = [])
    {
        $where = '';
        if (!empty($product_ids)) {
            $where = ' AND p.product_id IN (' . implode(',', $product_ids) . ')';
        }

        $query = $this->db->query("SELECT pa.product_id, pa.attribute_id, pa.text
        FROM " . DB_PREFIX . "product_attribute as pa
        JOIN ". DB_PREFIX ."product as p ON p.product_id = pa.product_id
        WHERE pa.language_id = {$this->from_language_id} $where
        ORDER BY pa.attribute_id");


        $product_attributes = [];
        foreach ($query->rows as $pa) {
            if (empty($pa['text']))
                continue;

            $product_attributes[$pa['product_id']][$pa['attribute_id']] = trim($pa['text']);
        }

        return $product_attributes;
    }

    public function getAttributes($attribute_ids = [])
    {
        $where = '';
        if (!empty($attribute_ids)) {
            $where = 'AND a.attribute_id IN (' . implode(',', $attribute_ids) . ')';
        }

        $query = $this->db->query("SELECT a.attribute_id, a.attribute_group_id, ad.name FROM " . DB_PREFIX . "attribute_description as ad
        JOIN " . DB_PREFIX . "attribute as a ON a.attribute_id = ad.attribute_id
        WHERE ad.language_id = {$this->from_language_id} $where ORDER BY a.attribute_id");

        return $query->rows;
    }

    public function getAttributeGroups()
    {
        $query = $this->db->query("SELECT agd.attribute_group_id, agd.name FROM " . DB_PREFIX . "attribute_group_description as agd
        JOIN " . DB_PREFIX . "attribute_group as ag ON ag.attribute_group_id = agd.attribute_group_id
        WHERE agd.language_id = {$this->from_language_id} ORDER BY ag.attribute_group_id");

        return $query->rows;
    }

    public function getGroupsAttributes($attribute_group_ids = [])
    {
        $where = '';
        if (!empty($attribute_group_ids)) {
            $where = 'AND a.attribute_group_id IN (' . implode(',', $attribute_group_ids) . ')';
        }

        $query = $this->db->query("SELECT a.attribute_id, a.attribute_group_id, ad.name FROM " . DB_PREFIX . "attribute_description as ad
        JOIN " . DB_PREFIX . "attribute as a ON a.attribute_id = ad.attribute_id
        WHERE ad.language_id = {$this->from_language_id} $where ORDER BY a.attribute_id");

        return $query->rows;
    }

    public function getOptionValues($option_ids = [])
    {
        $where = '';
        if (!empty($option_ids)) {
            $where = 'AND ovd.option_id IN (' . implode(',', $option_ids) . ')';
        }

        $query = $this->db->query("SELECT ovd.option_id, ovd.option_value_id, ovd.name FROM " . DB_PREFIX . "option_value_description as ovd WHERE ovd.language_id = {$this->from_language_id} $where");

        return $query->rows;
    }

    public function getOptions($option_ids = [])
    {
        $where = '';
        if (!empty($option_ids)) {
            $where = 'AND o.option_id IN (' . implode(',', $option_ids) . ')';
        }

        $query = $this->db->query("SELECT o.option_id, od.name FROM " . DB_PREFIX . "option_description as od
        JOIN " . DB_PREFIX . "option as o ON o.option_id = od.option_id
        WHERE od.language_id = {$this->from_language_id} $where ORDER BY o.option_id");

        return $query->rows;
    }

    public function updateCategoryDescription($category_id, $language_id, $description)
    {
        if (empty($description)) {
            return;
        }

        $fields_sql = [];
        foreach ($description as $key => $value) {
            $fields_sql[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }

        // check if exists first
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "' AND language_id = {$language_id}");

        if ($query->row) {
            $this->db->query("UPDATE " . DB_PREFIX . "category_description SET " . implode(', ', $fields_sql) . " WHERE category_id = {$category_id} AND language_id = {$language_id}");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = {$category_id}, language_id = {$language_id}, " . implode(', ', $fields_sql));
        }
    }

    public function updateAttributeGroupDescription($attribute_group_id, $language_id, $name)
    {
        // check if exists first
        $query = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . (int)$attribute_group_id . "' AND language_id = {$language_id}");

        if ($query->row) {
            $this->db->query("UPDATE " . DB_PREFIX . "attribute_group_description SET name = '" . $this->db->escape($name) . "' WHERE attribute_group_id = {$attribute_group_id} AND language_id = {$language_id}");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = {$attribute_group_id}, language_id = {$language_id}, name = '" . $this->db->escape($name) . "'");
        }
    }

    public function updateAttributeDescription($attribute_id, $language_id, $name)
    {
        // check if exists first
        $query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = '" . (int)$attribute_id . "' AND language_id = {$language_id}");

        if ($query->row) {
            $this->db->query("UPDATE " . DB_PREFIX . "attribute_description SET name = '" . $this->db->escape($name) . "' WHERE attribute_id = {$attribute_id} AND language_id = {$language_id}");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = {$attribute_id}, language_id = {$language_id}, name = '" . $this->db->escape($name) . "'");
        }
    }

    public function updateOptionDescription($option_id, $language_id, $name)
    {
        // check if exists first
        $query = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$option_id . "' AND language_id = {$language_id}");

        if ($query->row) {
            $this->db->query("UPDATE " . DB_PREFIX . "option_description SET name = '" . $this->db->escape($name) . "' WHERE option_id = {$option_id} AND language_id = {$language_id}");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = {$option_id}, language_id = {$language_id}, name = '" . $this->db->escape($name) . "'");
        }
    }

    public function updateProductDescription($product_id, $language_id, $description)
    {
        if (empty($description)) {
            return;
        }

        $fields_sql = [];
        foreach ($description as $key => $value) {
            $fields_sql[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }

        // check if exists first
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "' AND language_id = {$language_id}");

        if ($query->row) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET " . implode(', ', $fields_sql) . " WHERE product_id = {$product_id} AND language_id = {$language_id}");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = {$product_id}, language_id = {$language_id}, " . implode(', ', $fields_sql));
        }
    }

    public function updateAttributeValueDescription($product_id, $attribute_id, $language_id, $text)
    {
        // check if exists first
        $query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$attribute_id . "' AND language_id = {$language_id}");

        if (!empty($query->row['attribute_id'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET text = '" . $this->db->escape($text) . "' WHERE product_id = '" . $product_id . "' AND attribute_id = '" . (int)$attribute_id . "' AND language_id = {$language_id}");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$attribute_id . "', language_id = {$language_id}, text = '" . $this->db->escape($text) . "'");
        }
    }

    public function updateOptionValueDescription($option_value_id, $language_id, $name)
    {
        // check if exists first
        $query = $this->db->query("SELECT option_value_id FROM " . DB_PREFIX . "option_value_description WHERE option_value_id = '" . (int)$option_value_id . "' AND language_id = {$language_id}");

        if ($query->row) {
            $this->db->query("UPDATE " . DB_PREFIX . "option_value_description SET name = '" . $this->db->escape($name) . "' WHERE option_value_id = {$option_value_id} AND language_id = {$language_id}");
        } else {
            $option_id = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "option_value WHERE option_value_id = '" . (int)$option_value_id . "'")->row['option_id'];

            $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = {$language_id}, option_id = {$option_id}, name = '" . $this->db->escape($name) . "'");
        }
    }

    public function getProductForSeo($product_id, $language_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` WHERE product_id = " . (int)$product_id);
        $data = $query->row;

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_description` WHERE product_id = " . (int)$product_id . " AND language_id = " . (int)$language_id);
        $data['product_description'][$language_id] = $query->row ?? [];

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_special` WHERE product_id = " . (int)$product_id);
        $data['product_special'] = $query->rows;

        return $data;
    }

    public function getCategoryForSeo($category_id, $language_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` WHERE category_id = " . (int)$category_id);
        $data = $query->row;

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_description` WHERE category_id = " . (int)$category_id . " AND language_id = " . (int)$language_id);
        $data['category_description'][$language_id] = $query->row ?? [];

        return $data;
    }
}