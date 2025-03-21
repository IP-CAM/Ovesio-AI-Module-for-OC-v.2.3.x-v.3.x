<?php

class ControllerExtensionModuleOvesioTranslateEvent extends Controller
{
    private $output = [
        'from' => 'ro',
        'delta_mode' => true,
        'to' => [],
        'conditions' => [],
        'data' => []
    ];

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

        if(!$this->config->get($this->module_key . '_translation_status')) {
            return;
        }

        $this->sentKeys = (array)$this->config->get($this->module_key . '_translate_fields');
        $from_language_id = $this->config->get($this->module_key . '_catalog_language_id');
        $languages = $this->config->get($this->module_key . '_language_match');
        $this->output['from'] = $languages[$from_language_id]['code'];

        foreach ($languages as $language) {
            if (empty($language['status']) || $language['code'] === $this->output['from']) continue;

            $this->output['to'][] = $language['code'];

            //apply conditions in case the from language is not the default language
            if($this->output['from'] != $languages[$language['from_language_id']]['code'])
            {
                $this->output['conditions'][$language['code']] = $languages[$language['from_language_id']]['code'];
            }
        }

        $this->load->model('extension/module/ovesio');
    }

    public function index($data)
    {
        foreach ($data as $key => $values) {
            if (method_exists($this, $key)) {
                $this->{$key}($values);
            }
        }

        $this->sendRequest();
    }


    public function admin($route, $data, $resource_id = null)
    {
        $temp = explode('/', $route);
        $resource = $temp[1];
        if (strpos($temp[2], 'edit') === 0) {
            $resource_id = $data[0];
        }

        if (method_exists($this, $resource)) {
            $this->{$resource}([$resource_id]);
        }

        $this->sendRequest();
    }

    protected function category($category_ids)
    {
        $categories = $this->model_extension_module_ovesio->getCategories($category_ids);

        foreach ($categories as $i => $category) {
            $push = [
                'ref' => 'category/' . $category['category_id'],
                'content' => []
            ];

            foreach ($this->sentKeys['category'] as $key => $send) {
                if (!$send || empty($category[$key])) continue;

                $push['content'][] = [
                    'key' => $key,
                    'value' => $category[$key]
                ];
            }

            if (!empty($push['content'])) {
                $this->output['data'][] = $push;
            }
        }
    }

    protected function product($product_ids)
    {
        $products = $this->model_extension_module_ovesio->getProducts($product_ids);

        // chunk get attributes based on product_id
        $product_attributes = $this->model_extension_module_ovesio->getProductAttributeIds($product_ids);

        $attribute_ids = [];
        foreach($product_attributes as $product_id => $attributes) {
            $attribute_ids = array_merge($attribute_ids, array_keys($attributes));
        }

        $attributes = $this->model_extension_module_ovesio->getAttributes($attribute_ids);
        $attributes = array_column($attributes, 'name', 'attribute_id');

        foreach ($products as $i => $product) {
            $push = [
                'ref' => 'product/' . $product['product_id'],
                'content' => []
            ];

            foreach ($this->sentKeys['product'] as $key => $send) {
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
                $this->output['data'][] = $push;
            }
        }
    }

    protected function attribute($attribute_ids)
    {
        $attribute_groups = $this->model_extension_module_ovesio->getAttributeGroups();
        $attribute_groups = array_column($attribute_groups, null, 'attribute_group_id');

        $attributes = $this->model_extension_module_ovesio->getAttributes($attribute_ids);
        $attribute_group_ids = array_column($attributes, 'attribute_group_id');
        $attributes = $this->model_extension_module_ovesio->getGroupsAttributes($attribute_group_ids);

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

        $this->output['data'] = array_merge($this->output['data'], array_values($groups));
    }

    protected function option($option_ids)
    {
        $options = $this->model_extension_module_ovesio->getOptions($option_ids);
        $option_values = $this->model_extension_module_ovesio->getOptionValues($option_ids);

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
                $this->output['data'][] = $push;
            }
        }
    }

    private function sendRequest()
    {
        $this->config->set('config_language_id', $this->config_language_id); // put it back

        if (empty($this->output['data'])) {
            return;
        }

        // html_entity_decode content
        foreach ($this->output['data'] as $i => $data) {
            foreach ($data['content'] as $j => $content) {
                if (!empty($content['context'])) {
                    $this->output['data'][$i]['content'][$j]['context'] = html_entity_decode($content['context'], ENT_QUOTES, 'UTF-8');
                }

                $this->output['data'][$i]['content'][$j]['value'] = html_entity_decode($content['value'], ENT_QUOTES, 'UTF-8');
            }
        }

        $api = $this->config->get($this->module_key . '_api');
        $token = $this->config->get($this->module_key . '_token');
        $hash = $this->config->get($this->module_key . '_hash');

        $server = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTPS_SERVER;
        $this->output['callback_url'] = $server . 'index.php?route=extension/module/ovesio/translate_callback&hash=' . $hash;

        $ch = curl_init();

        // add token header
        $headers = [
            'X-Api-Key: ' . $token,
            'Content-Type: application/json',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // make post with json data
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->output));

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
        curl_setopt($ch, CURLOPT_URL, trim($api, '/') . '/translate/request');
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
        }

        curl_close($ch);
    }
}
