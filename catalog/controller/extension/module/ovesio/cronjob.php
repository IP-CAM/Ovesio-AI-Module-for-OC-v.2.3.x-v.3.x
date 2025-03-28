<?php

require_once(modification($_SERVER['DOCUMENT_ROOT'] . '/catalog/model/extension/module/ovesio.php'));

class ControllerExtensionModuleOvesioCronjob extends Controller
{
    private $module_key = 'ovesio';

    public function __construct($registry) {
        parent::__construct($registry);

        /**
         * Changes needed for v3
         */
        if(version_compare(VERSION, '3.0.0.0') >= 0) {
            $this->module_key = 'module_ovesio';
        }

        $this->model = new ModelExtensionModuleOvesio($registry);

        $from_language_id = $this->config->get($this->module_key . '_catalog_language_id');
        $this->model->setLanguageId($from_language_id);

        $this->load->library('ovesio');
    }

    public function index()
    {
        if (!$this->config->get($this->module_key . '_status')) {
            return $this->setOutput(['error' => 'Module is disabled']);
        }

        $status = (int) $this->config->get($this->module_key . '_description_status');
        $status = $status + (int) $this->config->get($this->module_key . '_translation_status');

        if($status == 0) {
            return $this->setOutput(['error' => 'All operations are disabled']);
        }

        $from_language_id = $this->config->get($this->module_key . '_catalog_language_id');

        $languages = $this->config->get($this->module_key . '_language_match');
        $language = $languages[$from_language_id]['code'];

        $list = $this->model->getCronList($language, $this->module_key);

        if(!empty($list))
        {
            foreach($list as $entry) {
                if($entry['expired_description'] || $entry['expired_translation']) {
                    $type = $entry['expired_description'] ? 'description' : 'translate';
                    $this->model->updateExpired($entry['list_id'], $type);
                }

                $this->ovesio->{$entry['resource']}($entry['resource_id']);
            }

            $this->ovesio->sendData();
        }

        $this->ovesio->showDebug();

        echo "Entries found: " . count($list);
    }

    /**
     * Custom response
     */
    private function setOutput($response)
    {
        if(is_array($response))
        {
            $response = json_encode($response);

            $this->response->addHeader('Content-Type: application/json');
        }

        $this->response->setOutput($response);
    }
}