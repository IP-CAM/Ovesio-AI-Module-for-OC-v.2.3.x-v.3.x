<?php
/**
 * Name: Ovesio
 * Url: https://ovesio.com/
 * Author: Aweb Design SRL
 * Version: 1.4.1
 */

class ControllerExtensionModuleOvesio extends Controller
{
    private $error = [];
    private $events = [];

    private $iso2 = ['en', 'bg', 'hr', 'cs', 'da', 'nl', 'et', 'fi', 'fr', 'de', 'el', 'hu', 'ga', 'it', 'lv', 'lt', 'mt', 'no', 'pl', 'pt', 'ro', 'ru', 'sr', 'sk', 'sl', 'es', 'sv', 'tr'];

    private $live_events = [
        'admin/model/catalog/category/addCategory/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/category/editCategory/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/product/addProduct/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/product/editProduct/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/attribute/addAttribute/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/attribute/editAttribute/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/attribute_group/addAttributeGroup/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/attribute_group/editAttributeGroup/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/option/addOption/after' => 'extension/module/ovesio/event/trigger',
        'admin/model/catalog/option/editOption/after' => 'extension/module/ovesio/event/trigger',
    ];

    private $defaults = [
        'status' => 0,
        'translation_status' => 0,
        'api' => 'https://api.ovesio.com/v1/',
        'language_match' => [],
        'language_status' => [],
        'token' => '',
        // 'live_translate' => 1,
        'send_stock_0' => false,
        'send_disabled' => false,
        'translate_fields' => [
            'category' => [
                'name' => 1,
                'description' => 1,
                'meta_title' => 1,
                'meta_description' => 1,
                'meta_keyword' => 1,
            ],
            'product' => [
                'name' => 1,
                'description' => 1,
                'tag' => 0,
                'meta_title' => 0,
                'meta_description' => 0,
                'meta_keyword' => 0,
                // 'image_title',
                // 'image_alt',
            ]
        ],
        'description_status' => 0,
        'live_description' => 1,
        'generate_product_description' => 1,
        'generate_category_description' => 1,
        'description_send_stock_0' => false,
        'description_send_disabled' => false,
        'minimum_product_descrition' => 500,
        'minimum_category_descrition' => 300,
        'create_description_one_time_only' => 1,
        //'translate_one_time_only' => 0,

        /**
         * Metatags
         */
        'metatags_status' => 0,
        'metatags_product' => 0,
        'metatags_send_stock_0' => 0,
        'metatags_category' => 0,
        'metatags_one_time_only' => 0,
        'metatags_send_disabled' => 0,
    ];

    private $token = 'token';
    private $extensions_path = 'extension/extension';
    private $module_key = 'ovesio';
    private $event_model = 'extension/event';

    public function __construct($registry)
    {
        parent::__construct($registry);

        /**
         * Changes needed for v3
         */
        if(version_compare(VERSION, '3.0.0.0') >= 0) {
            $this->token = 'user_token';
            $this->extensions_path = 'marketplace/extension';
            $this->module_key = 'module_ovesio';
            $this->event_model = 'setting/event';
        }
    }

	public function index() {
		$this->load->language('extension/module/ovesio');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_extension'] = $this->language->get('text_extension');
        $data['text_success'] = $this->language->get('text_success');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_system_language'] = $this->language->get('text_system_language');
        $data['text_iso2_language'] = $this->language->get('text_iso2_language');
        $data['text_translate_status'] = $this->language->get('text_translate_status');
        $data['text_translate_status_helper'] = $this->language->get('text_translate_status_helper');
        $data['text_translate_from'] = $this->language->get('text_translate_from');
        $data['text_language_association'] = $this->language->get('text_language_association');
        $data['text_language_translations'] = $this->language->get('text_language_translations');
        $data['text_translated_fields'] = $this->language->get('text_translated_fields');
        $data['text_products'] = $this->language->get('text_products');
        $data['text_categories'] = $this->language->get('text_categories');

        $data['text_name'] = $this->language->get('text_name');
        $data['text_description'] = $this->language->get('text_description');
        $data['text_tag'] = $this->language->get('text_tag');
        $data['text_meta_title'] = $this->language->get('text_meta_title');
        $data['text_meta_description'] = $this->language->get('text_meta_description');
        $data['text_meta_keyword'] = $this->language->get('text_meta_keyword');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_translate_feeds'] = $this->language->get('text_translate_feeds');
        $data['text_translation_callback'] = $this->language->get('text_translation_callback');
        $data['text_translation_callback_helper'] = $this->language->get('text_translation_callback_helper');
        $data['text_cronjob'] = $this->language->get('text_cronjob');
        $data['text_cronjob_helper'] = $this->language->get('text_cronjob_helper');
        $data['text_description_generator_info'] = $this->language->get('text_description_generator_info');

        $data['text_translate_after_description_generator_info'] = $this->config->get($this->module_key . '_description_status') ||  $this->config->get($this->module_key . '_metatags_status') ? $this->language->get('text_translate_after_description_generator_info') : null;
        $data['text_metatags_generator_info'] = $this->config->get($this->module_key . '_description_status') ? $this->language->get('text_metatags_generator_info') : null;
        $data['text_one_time_only'] = $this->language->get('text_one_time_only');
        $data['text_on_each_update'] = $this->language->get('text_on_each_update');
        $data['text_other_translations'] = $this->language->get('text_other_translations');

        $data['text_translate_after_description_generator_info'] = null;
        if($this->config->get($this->module_key . '_description_status')) {
            $data['text_translate_after_description_generator_info'] = $this->language->get('text_translate_after_description_generator_info');
        }

        $data['tab_general'] = $this->language->get('tab_general');
        $data['tab_description_generator'] = $this->language->get('tab_description_generator');
        $data['tab_translate'] = $this->language->get('tab_translate');
        $data['tab_metatags'] = $this->language->get('tab_metatags');

        // Entry
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_token'] = $this->language->get('entry_token');
        $data['entry_api'] = $this->language->get('entry_api');
        $data['entry_token_helper'] = $this->language->get('entry_token_helper');
        $data['entry_catalog_language'] = $this->language->get('entry_catalog_language');
        // $data['entry_live_translate'] = $this->language->get('entry_live_translate');
        // $data['help_live_translate'] = $this->language->get('help_live_translate');
        // $data['entry_live_description'] = $this->language->get('entry_live_description');
        // $data['help_live_description'] = $this->language->get('help_live_description');
        $data['entry_send_stock_0'] = $this->language->get('entry_send_stock_0');
        $data['entry_send_disabled'] = $this->language->get('entry_send_disabled');
        $data['entry_generate_product_description'] = $this->language->get('entry_generate_product_description');
        $data['entry_generate_category_description'] = $this->language->get('entry_generate_category_description');
        $data['entry_minimum_description_length_category'] = $this->language->get('entry_minimum_description_length_category');
        $data['entry_minimum_description_length_product'] = $this->language->get('entry_minimum_description_length_product');
        $data['entry_create_a_new_description'] = $this->language->get('entry_create_a_new_description');
        $data['entry_create_a_new_translation'] = $this->language->get('entry_create_a_new_translation');
        $data['entry_metatags_product'] = $this->language->get('entry_metatags_product');
        $data['entry_metatags_send_stock_0'] = $this->language->get('entry_metatags_send_stock_0');
        $data['entry_metatags_send_disabled'] = $this->language->get('entry_metatags_send_disabled');
        $data['entry_metatags_category'] = $this->language->get('entry_metatags_category');

        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_save'] = $this->language->get('button_save');

        // Error
        $data['error_permission'] = $this->language->get('error_permission');
        $data['error_code'] = $this->language->get('error_code');
        $data['error_token'] = $this->language->get('error_token');
        $data['error_from_language_id'] = $this->language->get('error_from_language_id');
        $data['error_from_language_id1'] = $this->language->get('error_from_language_id1');
        $data['error_warning'] = $this->language->get('error_warning');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            //OC required format
            $post = [];
            array_walk($this->request->post, function($item, $key) use(&$post) {
                $post[$this->module_key . '_'. $key] = $item;
            });

			$this->model_setting_setting->editSetting($this->module_key, $post);

            // $this->liveTranslate($this->request->post['live_translate']);

            // delete all ignored events in order to be rechecked is matches the new rules
            $this->load->model('extension/module/ovesio');
            $this->model_extension_module_ovesio->deleteIgnoredList();

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link($this->extensions_path, $this->token .'=' . $this->session->data[$this->token] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

        $data['error'] = $this->error;

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->token .'=' . $this->session->data[$this->token], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link($this->extensions_path, $this->token .'=' . $this->session->data[$this->token] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/ovesio', $this->token .'=' . $this->session->data[$this->token], true)
		);

		$data['action'] = $this->url->link('extension/module/ovesio', $this->token .'=' . $this->session->data[$this->token], true);

		$data['cancel'] = $this->url->link($this->extensions_path, $this->token .'=' . $this->session->data[$this->token] . '&type=module', true);

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        $defaults = $this->defaults;
        //Generate Hash if not exists
        $defaults['hash'] = md5(time());

        $defaults['catalog_language_id'] = $this->config->get('config_language_id');
        foreach ($languages as $language) {
            $defaults['language_match'][$language['language_id']]['code'] = '';

            if(in_array($language['code'], $this->iso2)) {
                $defaults['language_match'][$language['language_id']]['code'] = $language['code'];
            } else {
                $lang_exp = explode('-', $language['code']);
                if(in_array($lang_exp[0], $this->iso2)) {
                    $defaults['language_match'][$language['language_id']]['code'] = $lang_exp[0];
                }

            }

            $defaults['language_match'][$language['language_id']]['status'] = $language['language_id'] == $this->config->get('config_language_id') ? 0 : 1;
            $defaults['language_match'][$language['language_id']]['from_language_id'] = $this->config->get('config_language_id');
        }

        foreach ($defaults['translate_fields'] as $type => $fields) {
            foreach ($fields as $field => $val) {
                $data[$type . '_translates'][] = [
                    'label' => $this->language->get('text_' . $field),
                    'key' => $field,
                ];
            }
        }

        $config = [];
        $_config = $this->model_setting_setting->getSetting($this->module_key);
        array_walk($_config, function($item, $key) use(&$config) {
            $key = str_replace($this->module_key . '_', '', $key);
            $config[$key] = $item;
        });

        $data = array_merge($data, $defaults, $config, $this->request->post);

        $data['languages'] = $languages;
        $data['iso2'] = $this->iso2;

        $data['feeds'] = [
            HTTPS_CATALOG . 'index.php?route=extension/module/ovesio/translate_feed&hash=' . $data['hash'] . '&type=category',
            HTTPS_CATALOG . 'index.php?route=extension/module/ovesio/translate_feed&hash=' . $data['hash'] . '&type=product',
            HTTPS_CATALOG . 'index.php?route=extension/module/ovesio/translate_feed&hash=' . $data['hash'] . '&type=attribute',
            HTTPS_CATALOG . 'index.php?route=extension/module/ovesio/translate_feed&hash=' . $data['hash'] . '&type=option',
        ];

        $data['callback'] = HTTPS_CATALOG . 'index.php?route=extension/module/ovesio/callback&hash=' . $data['hash'];

        $data['description_cronjob'] = '* * * * */5 curl -k -L "' . HTTPS_CATALOG . 'index.php?route=extension/module/ovesio/cronjob&hash=' . $data['hash'] . '" > /dev/null 2>&1';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->view('extension/module/ovesio', $data);
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ovesio')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        // if (empty($this->request->post['live_translate'])) {
        //     $this->liveTranslate(0);
        // }

        // check if token
        if (!empty($this->request->post['status'])) {
            if (empty($this->request->post['token'])) { // TODO: validare token
                $this->error['token'] = $this->language->get('error_token');
            }

            if (empty($this->request->post['api'])) { // TODO: validare api
                $this->error['api'] = $this->language->get('error_api');
            }
        }

        foreach ($this->request->post['language_match'] as $key => $value) {
            if (empty($value['code'])) {
                $this->error[$key]['code'] = $this->language->get('error_code');
            }
            if ($key == $value['from_language_id'] && $value['from_language_id'] != $this->request->post['catalog_language_id']) {
                $this->error[$key]['from_language_id'] = $this->language->get('error_from_language_id');
            }
            if (empty($this->request->post['language_match'][$value['from_language_id']]['status'])) {
                if ($this->request->post['catalog_language_id'] != $value['from_language_id']) {
                    $this->error[$key]['from_language_id'] = $this->language->get('error_from_language_id1');
                }
            }
        }

        if (!empty($this->error)) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

		return !$this->error;
	}

    // protected function liveTranslate($status) {
    //     $status = $status ? 1 : 0;

    //     $this->db->query("UPDATE `" . DB_PREFIX . "event` SET status = '" . (int)$status . "' WHERE code = '{$this->module_key}' AND action IN ('" . implode("', '", $this->live_events) . "')");
    // }

    public function install()
    {
        $this->load->model('extension/module/ovesio');
        $this->model_extension_module_ovesio->install();

        $this->load->model($this->event_model);
        $model_name = 'model_' . str_replace('/', '_', $this->event_model);
        $model =  $this->$model_name;

        if(version_compare(VERSION, '3.0.0.0') >= 0) {
            $model->deleteEventByCode($this->module_key);
        } else {

            $model->deleteEvent($this->module_key);
        }

        foreach ($this->events as $key => $value) {
            $model->addEvent($this->module_key, $key, $value);
        }

        foreach ($this->live_events as $key => $value) {
            $model->addEvent($this->module_key, $key, $value);
        }
    }

    /**
     * Custom template view
     */
    private function view($template, $data) {
        if(version_compare(VERSION, '3.0.0.0') >= 0) {
            $this->config->set('template_engine', 'template');
            $this->response->setOutput($this->load->view($template, $data));
            $this->config->set('template_engine', 'twig');
        } else {
            $this->response->setOutput($this->load->view($template, $data));
        }
    }
}