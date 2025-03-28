<?php

require_once(modification($_SERVER['DOCUMENT_ROOT'] . '/catalog/model/extension/module/ovesio.php'));

class Ovesio
{
    private $registry;
    private $model;
    private $config_language_id;
    private $module_key = 'ovesio';
    private $debug = [];

    private $request_data = [];
    private $data = [];
    private $endpoint = [
        'translate' => '/translate/request',
        'generate_description' => '/ai/generate-description',
        'metatags' => '/ai/generate-seo'
    ];
    private $priorities = [];
    private $priority = [
        'generate_description' => 1,
        'translate' => 2,
        'metatags' => 3
    ];
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
                foreach($operations as $resource => $resource_ids) {
                    $resource_ids = array_filter($resource_ids);
                    if($resource_ids) {
                        //reset data
                        $this->request_data = [];

                        if($event_type == 'generate_description') {

                            $status = $this->config->get($this->module_key . '_description_status');

                            //Check other status types
                            if($status && in_array($resource, ['product', 'category'])) {
                                $status = $this->config->get($this->module_key . '_generate_' . $resource . '_description');
                            }

                        } elseif($event_type == 'translate') {
                            $status = $this->config->get($this->module_key . '_translation_status');

                            //Check other status types
                            if($status && in_array($resource, ['product', 'category'])) {
                                $translate_fields = (array)$this->config->get($this->module_key . '_translate_fields');
                                if(empty($translate_fields[$resource])){
                                    $status = 0;
                                }
                            }
                        } else {
                            $status = $this->config->get($this->module_key . '_metatags_status');

                            //Check other status types
                            if($status && in_array($resource, ['product', 'category'])) {
                                $status = $this->config->get($this->module_key . '_metatags_' . $resource);
                            }
                        }

                        if(!$status) {
                            $this->ignoreMoveOnNextEvent($resource, $resource_ids, $event_type, "Status disabled");

                            //reset while
                            $this->cleanData($event_type, $resource);

                            continue;
                        }

                        //prepare new data per type
                        $this->{$event_type . '_' . $resource}($resource_ids);

                        //send request type
                        $this->sendRequest($event_type);
                    }

                    //reset while
                    $this->cleanData($event_type, $resource);
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
     * @param string $resource | translate
     * @param string $key | product, category, attribute, option
     * @param int $id
     * @return void
     */
    public function add($resource, $key, $id)
    {
        foreach ((array)$id as $id) {
            $this->data[$resource][$key][$id] = $id;
            $this->priorities[$resource] = $this->priority[$resource];
        }
    }

    /**
     * Add message to debug
     *
     * @return void
     */
    public function debug($resource, $resource_id, $event_type, $message)
    {
        $this->debug[] = "[{$event_type}] {$resource}: " . implode(',', (array) $resource_id) . " - " . $message;
    }

    /**
     * Show debug messages
     *
     * @return void
     */
    public function showDebug()
    {
        echo "<pre>" . print_r($this->debug, true) . "</pre>";
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

                        if($event_type == 'generate_description' && !empty($request_entry['content']['description'])) {
                            //remove description from hash to avoid recreating it everytime
                            unset($request_entry['content']['description']);
                        }
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
        $this->request_data['callback_url'] = $server . 'index.php?route=extension/module/ovesio/callback&type=generate_description&hash=' . $hash;

        $this->request_data['to'] = $this->catalog_lang;
    }

    /**
     * Add metatags conditions
     */
    private function addMetatagsConditions()
    {
        $hash = $this->config->get($this->module_key . '_hash');
        $server = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTPS_SERVER;
        $this->request_data['callback_url'] = $server . 'index.php?route=extension/module/ovesio/callback&type=metatags&hash=' . $hash;

        $this->request_data['to'] = $this->catalog_lang;
    }

    /**
     * Protected methods
     */

    protected function generate_description_category($category_ids)
    {
        $hashList = $this->model->getHashList('category', $category_ids, $this->catalog_lang, 'generate_description');

        $categories = $this->model->getCategories(
            $category_ids,
            $this->config->get($this->module_key . '_description_send_disabled')
        );
        if(empty($categories)){
            $this->ignoreMoveOnNextEvent('category', $category_ids, 'generate_description', "Not found or disabled");
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

            //remove description from hash to avoid recreating it everytime
            $_push = $push;
            if(!empty($_push['content']['description'])) {
                unset($_push['content']['description']);
            }
            $hash = md5(json_encode($_push));
            if(!empty($hashList[$category['category_id']])) {
                if($this->config->get($this->module_key . '_create_description_one_time_only') || $hashList[$category['category_id']] == $hash)
                {
                    $this->ignoreMoveOnNextEvent('category', $category['category_id'], 'generate_description', "One time only or the hash did not changed");
                    continue;
                }
            }

            //The description is longer than minimum, send to translation
            if(strlen($_description) > $this->config->get($this->module_key . '_minimum_category_descrition')) {
                $this->ignoreMoveOnNextEvent('category', $category['category_id'], 'generate_description', "Minimum description length not met");
                continue;
            }

            $this->request_data['data'][] = $push;
        }

        if(!empty($this->request_data['data'])) {
            $this->addDescriptionConditions();

            $this->debug('category', str_replace('category/', '', array_column($this->request_data['data'], 'ref')), 'generate_description', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('category', $category_ids, 'generate_description', "No data left to process");
        }
    }

    protected function generate_description_product($product_ids)
    {
        $hashList = $this->model->getHashList('product', $product_ids, $this->catalog_lang, 'generate_description');

        $products = $this->model->getProducts(
            $product_ids,
            $this->config->get($this->module_key . '_description_send_disabled'),
            $this->config->get($this->module_key . '_description_send_stock_0')
        );
        if(empty($products)){
            $this->ignoreMoveOnNextEvent('product', $product_ids, 'generate_description', "Not found, disabled or out of stock");
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

            //remove description from hash to avoid recreating it everytime
            $_push = $push;
            if(!empty($_push['content']['description'])) {
                unset($_push['content']['description']);
            }
            $hash = md5(json_encode($_push));
            if(!empty($hashList[$product['product_id']])) {
                if($this->config->get($this->module_key . '_create_description_one_time_only') || $hashList[$product['product_id']] == $hash)
                {
                    $this->ignoreMoveOnNextEvent('product', $product['product_id'], 'generate_description', "One time only or the hash did not changed");
                    continue;
                }
            }

            //The description is longer than minimum, send to translation
            if(strlen($_description) > $this->config->get($this->module_key . '_minimum_product_descrition')) {
                $this->ignoreMoveOnNextEvent('product', $product['product_id'], 'generate_description', "Minimum description length not met");
                continue;
            }

            $this->request_data['data'][] = $push;
        }

        if(!empty($this->request_data['data'])) {
            $this->addDescriptionConditions();

            $this->debug('product', str_replace('product/', '', array_column($this->request_data['data'], 'ref')), 'generate_description', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('product', $product_ids, 'generate_description', "No data left to process");
        }
    }

    protected function translate_category($category_ids)
    {
        $translate_fields = (array)$this->config->get($this->module_key . '_translate_fields');

        $categories = $this->model->getCategories(
            $category_ids,
            $this->config->get($this->module_key . '_send_disabled')
        );
        if(empty($categories)){
            $this->ignoreMoveOnNextEvent('category', $category_ids, 'translate', "Not found or disabled");
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

            $this->debug('category', str_replace('category/', '', array_column($this->request_data['data'], 'ref')), 'translate', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('category', $category_ids, 'translate', "No data left to process");
        }
    }

    protected function translate_product($product_ids)
    {
        $translate_fields = (array)$this->config->get($this->module_key . '_translate_fields');

        $products = $this->model->getProducts(
            $product_ids,
            $this->config->get($this->module_key . '_send_disabled'),
            $this->config->get($this->module_key . '_send_stock_0')
        );
        if(empty($products)){
            $this->ignoreMoveOnNextEvent('product', $product_ids, 'translate', "Not found, disabled or out of stock");
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

            $this->debug('product', str_replace('product/', '', array_column($this->request_data['data'], 'ref')), 'translate', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('product', $product_ids, 'translate', "No data left to process");
        }
    }

    protected function translate_attribute($attribute_ids)
    {
        $attribute_groups = $this->model->getAttributeGroups();
        if(empty($attribute_groups)){
            $this->ignoreMoveOnNextEvent('attribute', $attribute_ids, 'translate', "No attribute groups found");
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

            $this->debug('attribute', str_replace('attribute_group/', '', array_column($this->request_data['data'], 'ref')), 'translate', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('attribute', $attribute_ids, 'translate', "No data left to process");
        }
    }

    protected function translate_attribute_group($attribute_group_ids)
    {
        $attribute_groups = $this->model->getAttributeGroups($attribute_group_ids);
        $attribute_groups = array_column($attribute_groups, null, 'attribute_group_id');
        if(empty($attribute_groups)){
            $this->ignoreMoveOnNextEvent('attribute_group', $attribute_group_ids, 'translate', "No attribute groups found");
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

            $this->debug('attribute_group', str_replace('attribute_group/', '', array_column($this->request_data['data'], 'ref')), 'translate', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('attribute_group', $attribute_group_ids, 'translate', "No data left to process");
        }
    }

    protected function translate_option($option_ids)
    {
        $options = $this->model->getOptions($option_ids);
        if(empty($options)){
            $this->ignoreMoveOnNextEvent('option', $option_ids, 'translate', "No options found");
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

            $this->debug('option', str_replace('option/', '', array_column($this->request_data['data'], 'ref')), 'translate', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('option', $option_ids, 'translate', "No data left to process");
        }
    }

    /**
     * Protected methods
     */

    protected function metatags_category($category_ids)
    {
        $hashList = $this->model->getHashList('category', $category_ids, $this->catalog_lang, 'metatags');

        $categories = $this->model->getCategories(
            $category_ids,
            $this->config->get($this->module_key . '_send_disabled')
        );
        if(empty($categories)){
            $this->ignoreMoveOnNextEvent('category', $category_ids, 'metatags', "Not found or disabled");
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
            if(!empty($hashList[$category['category_id']])) {
                if($this->config->get($this->module_key . '_metatags_one_time_only') || $hashList[$category['category_id']] == $hash)
                {
                    $this->ignoreMoveOnNextEvent('category', $category['category_id'], 'metatags', "One time only or the hash did not changed");
                    continue;
                }
            }

            $this->request_data['data'][] = $push;
        }

        if(!empty($this->request_data['data'])) {
            $this->addMetatagsConditions();

            $this->debug('category', str_replace('category/', '', array_column($this->request_data['data'], 'ref')), 'metatags', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('category', $category_ids, 'metatags', "No data left to process");
        }
    }

    protected function metatags_product($product_ids)
    {
        $hashList = $this->model->getHashList('product', $product_ids, $this->catalog_lang, 'metatags');

        $products = $this->model->getProducts(
            $product_ids,
            $this->config->get($this->module_key . '_metatags_send_disabled'),
            $this->config->get($this->module_key . '_metatags_send_stock_0')
        );
        if(empty($products)){
            $this->ignoreMoveOnNextEvent('product', $product_ids, 'metatags', "Not found, disabled or out of stock");
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
            if(!empty($hashList[$product['product_id']])) {
                if($this->config->get($this->module_key . '_metatags_one_time_only') || $hashList[$product['product_id']] == $hash)
                {
                    $this->ignoreMoveOnNextEvent('product', $product['product_id'], 'metatags', "One time only or the hash did not changed");
                    continue;
                }
            }

            $this->request_data['data'][] = $push;
        }

        if(!empty($this->request_data['data'])) {
            $this->addMetatagsConditions();

            $this->debug('product', str_replace('product/', '', array_column($this->request_data['data'], 'ref')), 'metatags', "Prepare data");
        } else {
            $this->ignoreMoveOnNextEvent('product', $product_ids, 'metatags', "No data left to process");
        }
    }

    protected function ignoreMoveOnNextEvent($resource, $resource_ids, $event_type, $message = null)
    {
        foreach((array)$resource_ids as $resource_id)
        {
            $this->model->addList([
                'resource' => $resource,
                'resource_id' => $resource_id,
                'lang' => $this->catalog_lang
            ],[
                $event_type . '_id' => 0,
                $event_type . '_hash' => null, //should not contain hash because on the next update we need to make sure the same conditions are applied
                $event_type . '_date' => date('Y-m-d H:i:s'),
                $event_type . '_status' => 1
            ]);

            if($event_type == 'generate_description') {
                // Send to translate
                $this->add('translate', $resource, $resource_id);
            } elseif($event_type == 'translate') {
                // Send to metatags
                $this->add('metatags', $resource, $resource_id);
            }
        }

        if($message) {
            $this->debug($resource, $resource_ids, $event_type, $message);
        }
    }

    private function cleanData($event_type, $resource)
    {
        unset($this->data[$event_type][$resource]);
        if(!count($this->data[$event_type])) {
            unset($this->data[$event_type]);
        }
    }
}