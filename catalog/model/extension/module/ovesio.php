<?php

class ModelExtensionModuleOvesio extends Model
{
    private $from_language_id;

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function setLanguageId($language_id)
    {
        $this->from_language_id = $language_id;
    }

    public function getCategories($category_ids = [], $status)
    {
        $where = '';
        if (!empty($category_ids)) {
            $where = 'AND c.category_id IN (' . implode(',', $category_ids) . ')';
        }

        if (!$status) {
            $where .= " AND c.status != 0";
        }

        $query = $this->db->query("SELECT cd.* FROM " . DB_PREFIX . "category_description as cd
        JOIN " . DB_PREFIX . "category as c ON c.category_id = cd.category_id
        WHERE cd.language_id = {$this->from_language_id} $where
        ORDER BY c.category_id");

        return $query->rows;
    }

    public function getProducts($product_id = [], $status, $out_of_stock)
    {
        $where = '';
        if (!empty($product_id)) {
            $where = ' AND p.product_id IN (' . implode(',', $product_id) . ')';
        }

        if (!$out_of_stock) {
			$where .= " AND p.quantity > '0'";
		}

        if (!$status) {
            $where .= " AND p.status != 0";
        }

        $query = $this->db->query("SELECT pd.* FROM " . DB_PREFIX . "product_description as pd
        JOIN " . DB_PREFIX . "product as p ON p.product_id = pd.product_id
        WHERE pd.language_id = {$this->from_language_id} $where
        ORDER BY p.product_id");

        return $query->rows;
    }

    public function getProductsAttributes($product_ids = [])
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

    public function getAttributeGroups($attribute_group_ids = [])
    {
        $where = '';
        if (!empty($attribute_group_ids)) {
            $where = 'AND agd.attribute_group_id IN (' . implode(',', $attribute_group_ids) . ')';
        }

        $query = $this->db->query("SELECT agd.attribute_group_id, agd.name FROM " . DB_PREFIX . "attribute_group_description as agd
        JOIN " . DB_PREFIX . "attribute_group as ag ON ag.attribute_group_id = agd.attribute_group_id
        WHERE agd.language_id = {$this->from_language_id} {$where} ORDER BY ag.attribute_group_id");

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

    public function hasDescription(string $resource, array $resource_ids)
    {
        $query = $this->db->query("SELECT resource_id, generate_description_hash FROM " . DB_PREFIX . "ovesio_list WHERE `resource` = '" . $resource . "' AND `resource_id` IN (" . implode(',', $resource_ids) . ") AND generate_description_id >= 0 GROUP BY resource_id");

        $status = array_fill_keys($resource_ids, null);
        foreach($query->rows as $row) {
            $status[$row['resource_id']] = $row['generate_description_hash'];
        }

        return $status;
    }

    public function addList(array $where = [], array $data = [])
    {
        if (empty($where) || empty($data)) {
            return;
        }

        $where_sql = [];
        foreach ($where as $key => $value) {
            $where_sql[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }

        $fields_sql = [];
        foreach ($data as $key => $value) {
            $fields_sql[] = "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }

        // check if exists first
        $query = $this->db->query("SELECT id FROM " . DB_PREFIX . "ovesio_list WHERE " . implode(' AND ', $where_sql));

        if ($query->row) {
            $this->db->query("UPDATE " . DB_PREFIX . "ovesio_list SET " . implode(', ', $fields_sql) . " WHERE id = " . (int) $query->row['id']);

            return $query->row['id'];
        } else {
            $fields_sql = array_merge($where_sql, $fields_sql);

            $this->db->query("INSERT INTO " . DB_PREFIX . "ovesio_list SET " . implode(', ', $fields_sql));

            return $this->db->getLastId();
        }
    }

    public function getProductCategories($product_ids)
    {
        $product_category_data = [];

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id IN (". implode(',', $product_ids) . ")");

        foreach ($query->rows as $result) {
            $product_category_data[$result['product_id']][] = $result['category_id'];
        }

        return $product_category_data;
    }

    public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT c.category_id, cd2.name,
        (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;')
        FROM " . DB_PREFIX . "category_path cp LEFT
        JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id)
        WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'
        GROUP BY cp.category_id) AS path
        FROM " . DB_PREFIX . "category c
        LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id)
        WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

    public function getCronList($language)
    {
        $pwhere = '';
        if (!$this->config->get($this->module_key . '_description_send_disabled')) {
			$pwhere .= " AND p.quantity > '0'";
		}

        if (!$this->config->get($this->module_key . '_description_send_stock_0')) {
            $pwhere .= " AND p.status != 0";
        }

        $cwhere = '';
        if (!$this->config->get($this->module_key . '_send_disabled')) {
            $cwhere .= " AND c.status != 0";
        }

        $sql = " SELECT
                r.`resource`,
                r.resource_id,
                ol.id as list_id,
                ( ol.generate_description_id IS NOT NULL AND ol.generate_description_status = 0 AND ol.generate_description_date < NOW() - INTERVAL 24 HOUR ) AS expired_description,
                ( ol.translate_id IS NOT NULL AND ol.translate_status = 0 AND ol.translate_date < NOW() - INTERVAL 24 HOUR ) AS expired_translation
            FROM (
                SELECT 'attribute_group' as resource, a.attribute_group_id AS resource_id
                FROM " . DB_PREFIX . "attribute_description as ad
                JOIN " . DB_PREFIX . "attribute as a ON a.attribute_id = ad.attribute_id
                WHERE ad.language_id = {$this->from_language_id}
            UNION
                SELECT 'option' as resource, o.option_id as resource_id
                FROM " . DB_PREFIX . "option_description as od
                JOIN " . DB_PREFIX . "option as o ON o.option_id = od.option_id
                WHERE od.language_id = {$this->from_language_id}
            UNION
                SELECT 'category' as resource, cd.category_id as resource_id
                FROM " . DB_PREFIX . "category_description as cd
                JOIN " . DB_PREFIX . "category as c ON c.category_id = cd.category_id
                WHERE cd.language_id = {$this->from_language_id} {$cwhere}
            UNION
                SELECT 'product' as resource, p.product_id as resource_id
                FROM " . DB_PREFIX . "product as p
                JOIN " . DB_PREFIX . "product_description as pd ON p.product_id = pd.product_id
                where pd.language_id = {$this->from_language_id} {$pwhere}
        ) as r
        LEFT JOIN " . DB_PREFIX . "ovesio_list as ol ON ol.resource = r.resource AND ol.resource_id = r.resource_id
        WHERE
            (ol.id IS NULL OR ol.lang = '{$language}') AND
            (
                ( ol.generate_description_id IS NULL AND ol.`resource` IN ('product', 'category')) OR
                ol.translate_id IS NULL OR
                ( ol.generate_description_id IS NOT NULL AND ol.generate_description_status = 0 AND ol.generate_description_date < NOW() - INTERVAL 24 HOUR ) OR
                ( ol.translate_id IS NOT NULL AND ol.translate_status = 0 AND ol.translate_date < NOW() - INTERVAL 24 HOUR )
            )
        LIMIT 40";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function updateExpired($id, $type) {
        $this->db->query("UPDATE " . DB_PREFIX . "ovesio_list SET {$type}_id = NULL, {$type}_hash = NULL, {$type}_date = NULL, {$type}_status = 0 WHERE id = " . (int) $id);
    }
}