<?php

require_once(modification($_SERVER['DOCUMENT_ROOT'] . '/catalog/model/extension/module/ovesio.php'));

class Ovesio
{
    private $registry;
    private $model;
    private $config_language_id;
    private $module_key = 'ovesio';

    private $request_data = [];
    private $data = [];
    private $endpoint = [
        'translate' => '/translate/request',
        'generate_description' => '/ai/generate-description'
    ];
    private $priorities = [];
    private $priority = ['generate_description' => 1, 'translate' => 2];
    private $catalog_lang = null;

    public function __construct($registry)
    {
        $this->registry = $registry;

        $this->config_language_id = $registry->get('config_language_id');

        /**
         * Changes needed for OpenCart v3
         */
        if(version_compare(VERSION, '3.0.0.0') >= 0) {
            $this->module_key = 'module_ovesio';
        }

        $this->model = new ModelExtensionModuleOvesio($registry);

        $from_language_id = $this->config->get($this->module_key . '_catalog_language_id');
        $this->model->setLanguageId($this->config->get($this->module_key . '_catalog_language_id'));

        $languages = $this->config->get($this->module_key . '_language_match');
        $this->catalog_lang = $languages[$from_language_id]['code'];

        $this->db->cache = false; //custom cache query disabled
    }

	public function __get($key) {
		return $this->registry->get($key);
	}

    public function getModuleKey()
    {
        return $this->module_key;
    }

    public function sendData()
    {
        $status = $this->config->get($this->module_key . '_status');
        if(!$status) {
            return;
        }

        array_multisort($this->priorities, SORT_ASC, $this->data);

        while(count($this->data)) {
            foreach($this->data as $event_type => $operations) {
                foreach($operations as $type => $ids) {
                    $ids = array_filter($ids);
                    if($ids) {
                        //reset data
                        $this->request_data = [];


                        if($event_type == 'generate_description') {
                            $status = $this->config->get($this->module_key . '_description_status');
                        } else {
                            $status = $this->config->get($this->module_key . '_translation_status');
                        }

                        if(!$status) {
                            if($event_type == 'generate_description') {
                                $this->add('translate', $type, $ids);
                            }

                            //reset while
                            $this->cleanData($event_type, $type);

                            continue;
                        }

                        //prepare new data per type
                        $this->{$event_type . '_' . $type}($ids);

                        //send request type
                        $this->sendRequest($event_type);
                    }

                    //reset while
                    $this->cleanData($event_type, $type);
                }
            }
        }

        // Reset everyting
        $this->data = [];
        $this->request_data = [];
    }

    /**
     * Ovesio add event on list
     */
    public function product($id)
    {
        $this->add('generate_description', 'product', $id);
    }

    public function category($id)
    {
        $this->add('generate_description', 'category', $id);
    }

    public function attribute($id)
    {
        $this->add('translate', 'attribute', $id);
    }

    public function attribute_group($id)
    {
        $this->add('translate', 'attribute_group', $id);
    }

    public function option($id)
    {
        $this->add('translate', 'option', $id);
    }

    /**
     * Add event data
     *
     * @param string $type | translate
     * @param string $key | product, category, attribute, option
     * @param int $id
     * @return void
     */
    public function add($type, $key, $id)
    {
        foreach ((array)$id as $id) {
            $this->data[$type][$key][$id] = $id;
            $this->priorities[$type] = $this->priority[$type];
        }
    }

    /**
     * Private methods
     */

    /**
     * Send data to Ovesio.com
     *
     * @param string $event_type
     * @return void
     */
    private function sendRequest($event_type)
    {
        if(!isset($this->endpoint[$event_type])) {
            new Exception('Invalid endpoint type');
        }

        $endpoint = $this->endpoint[$event_type];
        $this->config->set('config_language_id', $this->config_language_id); // put it back

        if (empty($this->request_data['data'])) {
            //reset
            $this->request_data = [];
            return;
        }

        // html_entity_decode content
        $this->request_data['data'] = $this->decode($this->request_data['data']);

        $api = $this->config->get($this->module_key . '_api');
        $token = $this->config->get($this->module_key . '_token');

        $ch = curl_init();

        // add token header
        $headers = [
            'X-Api-Key: ' . $token,
            'Content-Type: application/json',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // make post with json data
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->request_data));

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
        curl_setopt($ch, CURLOPT_URL, trim($api, '/') . $endpoint);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $raw_response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $response = json_decode($raw_response, true);

        if (empty($response['success'])) {
            if ($status == 401) {
                $this->session->data['notify_error'] = "Autentication Error. Check API Token";
            }
            elseif (!empty($response['errors'])) {
                $this->session->data['notify_error'] = "Invalid data sent to AI Tool, check platform for log errors";
            }
            else {
                if (empty($this->session->data['notify_error'])) {
                    $mini_response = substr($raw_response, 0, 500);
                    $mini_response .= " .....". curl_errno($ch) . '----------' . $status;
                    $this->log = new Log($this->config->get('error_filename')); // sometimes log is not available bcz of destructor
                    $this->log->write("Invalid response received from AI Tool: \n" . $mini_response); // do not spam error.log
                }

                $this->session->data['notify_error'] = "Invalid response received from AI Tool. Check Error Logs for more info";
            }
        } else {
            foreach($this->request_data['data'] as $request_entry) {
                foreach($response['data'] as $entry) {
                    if($request_entry['ref'] == $entry['ref']) {
                        list($resource, $resource_id) = explode('/', $entry['ref']);

                        $this->model->addList([
                            'resource' => $resource,
                            'resource_id' => $resource_id,
                            'lang' => $event_type == 'translate' ? $this->request_data['from'] : $this->request_data['to'],
                        ],[
                            $event_type . '_id' => $entry['id'],
                            $event_type . '_hash' => md5(json_encode($request_entry)),
                            $event_type . '_date' => date('Y-m-d H:i:s'),
                            $event_type . '_status' => 0,
                        ]);

                        break;
                    }
                }
            }
        }

        curl_close($ch);

        //reset
        $this->request_data = [];
    }

    /**
     * Decode HTML content
     */
    private function decode($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->decode($value);
			}
		} else {
			$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		}

		return $data;
	}

    /**
     * Add translation conditions
     */
    private function addTranslationConditions()
    {
        //$this->request_data['delta_mode'] = true; //ignores duplicates

        $this->request_data['from'] = $this->catalog_lang;
        $languages = $this->config->get($this->module_key . '_language_match');
        foreach ($languages as $language) {
            if (empty($language['status']) || $language['code'] === $this->request_data['from']) continue;

            $this->request_data['to'][] = $language['code'];

            //apply conditions in case the from language is not the default language
            if($this->request_data['from'] != $languages[$language['from_language_id']]['code'])
            {
                $this->request_data['conditions'][$language['code']] = $languages[$language['from_language_id']]['code'];
            }
        }

        $hash = $this->config->get($this->module_key . '_hash');
        $server = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTPS_SERVER;
        $this->request_data['callback_url'] = $server . 'index.php?route=extension/module/ovesio/callback&hash=' . $hash;
    }

    /**
     * Add description conditions
     */
    private function addDescriptionConditions()
    {
        $hash = $this->config->get($this->module_key . '_hash');
        $server = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTPS_SERVER;
        $this->request_data['callback_url'] = $server . 'index.php?route=extension/module/ovesio/callback&type=description&hash=' . $hash;

        $this->request_data['to'] = $this->catalog_lang;
    }

    /**
     * Protected methods
     */

    protected function generate_description_category($category_ids)
    {
        $hasDescription = $this->model->hasDescription('category', $category_ids);

        $categories = $this->model->getCategories(
            $category_ids,
            $this->config->get($this->module_key . '_description_send_disabled')
        );
        if(empty($categories)){
            // maybe needs to be translated
            $this->add('translate', 'category', $category_ids);

            return;
        }

        foreach ($categories as $i => $category) {
            $push = [
                'ref' => 'category/' . $category['category_id'],
                'content' => [
                    'name' => $category['name']
                ]
            ];

            // only if is different from name...usual mistake
            $_description = strip_tags($this->decode($category['description']));
            if(strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $_description)) != strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', strip_tags($this->decode($category['name']))))) {
                $push['content']['description'] = $category['description'];
            }

            $hash = md5(json_encode($push));
            if(!empty($hasDescription[$category['category_id']])) {
                if($this->config->get($this->module_key . '_create_description_one_time_only') || $hasDescription[$category['category_id']] == $hash)
                {
                    $this->add('translate', 'category', $category['category_id']);
                    continue;
                }
            }

            //The description is longer than minimum, send to translation
            if(strlen($_description) > $this->config->get($this->module_key . '_minimum_category_descrition')) {
                $this->model->addList([
                    'resource' => 'category',
                    'resource_id' => $category['category_id'],
                    'lang' => $this->catalog_lang
                ],[
                    'generate_description_id' => 0,
                    'generate_description_hash' => $hash,
                    'generate_description_date' => date('Y-m-d H:i:s'),
                    'generate_description_status' => 1
                ]);

                // Send to translate
                $this->add('translate', 'category', $category['category_id']);

                continue;
            }

            $this->request_data['data'][] = $push;
        }

        if(!empty($this->request_data['data'])) {
            $this->addDescriptionConditions();
        } else {
            // maybe needs to be translated
            $this->add('translate', 'category', $category_ids);
        }
    }

    protected function generate_description_product($product_ids)
    {
        $hasDescription = $this->model->hasDescription('product', $product_ids);

        $products = $this->model->getProducts(
            $product_ids,
            $this->config->get($this->module_key . '_description_send_disabled'),
            $this->config->get($this->module_key . '_description_send_stock_0')
        );
        if(empty($products)){
            // maybe needs to be translated
            $this->add('translate', 'product', $product_ids);

            return;
        }

        // chunk get attributes based on product_id
        $product_attributes = $this->model->getProductsAttributes($product_ids);

        $attribute_ids = [];
        foreach($product_attributes as $attributes) {
            $attribute_ids = array_merge($attribute_ids, array_keys($attributes));
        }

        $attributes = $this->model->getAttributes($attribute_ids);
        $attributes = array_column($attributes, 'name', 'attribute_id');

        $categories_ids = $this->model->getProductCategories($product_ids);

        foreach ($products as $i => $product) {
            $push = [
                'ref' => 'product/' . $product['product_id'],
                'content' => [
                    'name' => $product['name']
                ]
            ];

            // only if is different from name...usual mistake
            $_description = strip_tags($this->decode($product['description']));
            if(strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $_description)) != strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', strip_tags($this->decode($product['name']))))) {
                $push['content']['description'] = $product['description'];
            }

            foreach (($categories_ids[$product['product_id']] ?? []) as $category_id) {
                $category_info = $this->model->getCategory($category_id);

                if ($category_info) {
                    $push['content']['categories'][] = ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
                }
            }

            foreach (($product_attributes[$product['product_id']] ?? []) as $attribute_id => $attribute_text) {
                $push['content']['additional'][] = $attributes[$attribute_id] . ': ' . $attribute_text;
            }

            $hash = md5(json_encode($push));
            if(!empty($hasDescription[$product['product_id']])) {
                if($this->config->get($this->module_key . '_create_description_one_time_only') || $hasDescription[$product['product_id']] == $hash)
                {
                    $this->add('translate', 'product', $product['product_id']);
                    continue;
                }
            }

            //The description is longer than minimum, send to translation
            if(strlen($_description) > $this->config->get($this->module_key . '_minimum_product_descrition')) {
                $this->model->addList([
                    'resource' => 'product',
                    'resource_id' => $product['product_id'],
                    'lang' => $this->catalog_lang
                ],[
                    'generate_description_id' => 0,
                    'generate_description_hash' => $hash,
                    'generate_description_date' => date('Y-m-d H:i:s'),
                    'generate_description_status' => 1
                ]);

                // Translation update
                $this->add('translate', 'product', $product['product_id']);

                continue;
            }

            $this->request_data['data'][] = $push;
        }

        if(!empty($this->request_data['data'])) {
            $this->addDescriptionConditions();
        } else {
            // maybe needs to be translated
            $this->add('translate', 'product', $product_ids);
        }
    }

    protected function translate_category($category_ids)
    {
        $translate_fields = (array)$this->config->get($this->module_key . '_translate_fields');
        if(empty($translate_fields)){
            return;
        }

        $categories = $this->model->getCategories(
            $category_ids,
            $this->config->get($this->module_key . '_send_disabled')
        );
        if(empty($categories)){
            return;
        }

        foreach ($categories as $i => $category) {
            $push = [
                'ref' => 'category/' . $category['category_id'],
                'content' => []
            ];

            foreach ($translate_fields['category'] as $key => $send) {
                if (!$send || empty($category[$key])) continue;

                $push['content'][] = [
                    'key' => $key,
                    'value' => $category[$key]
                ];
            }

            if (!empty($push['content'])) {
                $this->request_data['data'][] = $push;
            }
        }

        if(!empty($this->request_data['data'])) {
            $this->addTranslationConditions();
        }
    }

    protected function translate_product($product_ids)
    {
        $translate_fields = (array)$this->config->get($this->module_key . '_translate_fields');
        if(empty($translate_fields)){
            return;
        }

        $products = $this->model->getProducts(
            $product_ids,
            $this->config->get($this->module_key . '_send_disabled'),
            $this->config->get($this->module_key . '_send_stock_0')
        );
        if(empty($products)){
            return;
        }

        // chunk get attributes based on product_id
        $product_attributes = $this->model->getProductsAttributes($product_ids);

        $attribute_ids = [];
        foreach($product_attributes as $attributes) {
            $attribute_ids = array_merge($attribute_ids, array_keys($attributes));
        }

        $attributes = $this->model->getAttributes($attribute_ids);
        $attributes = array_column($attributes, 'name', 'attribute_id');

        foreach ($products as $i => $product) {
            $push = [
                'ref' => 'product/' . $product['product_id'],
                'content' => []
            ];

            foreach ($translate_fields['product'] as $key => $send) {
                if (!$send || empty($product[$key])) continue;

                $push['content'][] = [
                    'key' => $key,
                    'value' => $product[$key]
                ];
            }

            foreach (($product_attributes[$product['product_id']] ?? []) as $attribute_id => $attribute_text) {
                $push['content'][] = [
                    'key' => 'a-' . $attribute_id,
                    'value' => $attribute_text,
                    'context' => $attributes[$attribute_id]
                ];
            }

            if (!empty($push['content'])) {
                $this->request_data['data'][] = $push;
            }
        }

        if(!empty($this->request_data['data'])) {
            $this->addTranslationConditions();
        }
    }

    protected function translate_attribute($attribute_ids)
    {
        $attribute_groups = $this->model->getAttributeGroups();
        if(empty($attribute_groups)){
            return;
        }

        $attribute_groups = array_column($attribute_groups, null, 'attribute_group_id');

        $attributes = $this->model->getAttributes($attribute_ids);
        $attribute_group_ids = array_column($attributes, 'attribute_group_id');
        $attributes = $this->model->getGroupsAttributes($attribute_group_ids);

        $groups = [];

        foreach ($attribute_group_ids as $attribute_group_id) {
            if (!isset($attribute_groups[$attribute_group_id])) continue;

            $groups[$attribute_group_id] = [
                'ref' => 'attribute_group/' . $attribute_group_id,
                'content' => [
                    [
                        'key' => 'ag-' . $attribute_group_id,
                        'value' => $attribute_groups[$attribute_group_id]['name']
                    ]
                ]
            ];
        }

        foreach ($attributes as $attribute) {
            if (!isset($groups[$attribute['attribute_group_id']])) continue;

            $groups[$attribute['attribute_group_id']]['content'][] = [
                'key' => 'a-' . $attribute['attribute_id'],
                'context' => $attribute_groups[$attribute['attribute_group_id']]['name'],
                'value' => $attribute['name'],
            ];
        }

        $this->request_data['data'] = array_merge($this->request_data['data'] ?? [], array_values($groups));

        if(!empty($this->request_data['data'])) {
            $this->addTranslationConditions();
        }
    }

    protected function translate_attribute_group($attribute_group_ids)
    {
        $attribute_groups = $this->model->getAttributeGroups($attribute_group_ids);
        $attribute_groups = array_column($attribute_groups, null, 'attribute_group_id');
        if(empty($attribute_groups)){
            return;
        }

        $attributes = $this->model->getGroupsAttributes($attribute_group_ids);

        $groups = [];

        foreach ($attribute_group_ids as $attribute_group_id) {
            if (!isset($attribute_groups[$attribute_group_id])) continue;

            $groups[$attribute_group_id] = [
                'ref' => 'attribute_group/' . $attribute_group_id,
                'content' => [
                    [
                        'key' => 'ag-' . $attribute_group_id,
                        'value' => $attribute_groups[$attribute_group_id]['name']
                    ]
                ]
            ];
        }

        foreach ($attributes as $attribute) {
            if (!isset($groups[$attribute['attribute_group_id']])) continue;

            $groups[$attribute['attribute_group_id']]['content'][] = [
                'key' => 'a-' . $attribute['attribute_id'],
                'context' => $attribute_groups[$attribute['attribute_group_id']]['name'],
                'value' => $attribute['name'],
            ];
        }

        $this->request_data['data'] = array_merge($this->request_data['data'] ?? [], array_values($groups));

        if(!empty($this->request_data['data'])) {
            $this->addTranslationConditions();
        }
    }

    protected function translate_option($option_ids)
    {
        $options = $this->model->getOptions($option_ids);
        if(empty($options)){
            return;
        }

        $option_values = $this->model->getOptionValues($option_ids);

        $_option_values = [];
        foreach ($option_values as $option_value) {
            $_option_values[$option_value['option_id']][] = $option_value;
        }

        $option_values = $_option_values;
        unset($_option_values);

        foreach ($options as $option) {
            $push = [
                'ref' => 'option/' . $option['option_id'],
                'content' => []
            ];

            $push['content'][] = [
                'key' => 'o-' . $option['option_id'],
                'value' => $option['name'],
            ];

            foreach (($option_values[$option['option_id']] ?? []) as $option_value) {
                $push['content'][] = [
                    'key' => 'ov-' . $option_value['option_value_id'],
                    'context' => $option['name'],
                    'value' => $option_value['name'],
                ];
            }

            if (!empty($push['content'])) {
                $this->request_data['data'][] = $push;
            }
        }

        if(!empty($this->request_data['data'])) {
            $this->addTranslationConditions();
        }
    }

    private function cleanData($event_type, $type)
    {
        unset($this->data[$event_type][$type]);
        if(!count($this->data[$event_type])) {
            unset($this->data[$event_type]);
        }
    }
}