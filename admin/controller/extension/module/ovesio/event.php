<?php

class ControllerExtensionModuleOvesioEvent extends Controller
{
    private $module_key;

    public function __construct($registry)
    {
        parent::__construct($registry);

        /**
         * Ovesio.com Integration
        **/
        if (!$registry->has('ovesio')) {
            try {
                $this->load->library('ovesio');

            } catch (Exception $e) {}
        }

        $this->module_key = $this->ovesio->getModuleKey();
    }

    public function trigger($route, $data, $resource_id = null)
    {
        $status = $this->config->get($this->module_key . '_status');
        if(!$status) {
            return;
        }

        $temp = explode('/', $route);
        $resource = $temp[1];
        if (strpos($temp[2], 'edit') === 0) {
            $resource_id = $data[0];
        }

        if(!in_array($resource, ['product', 'category', 'attribute', 'option'])) {
            return;
        }

        try {
            $this->ovesio->{$resource}($resource_id);
            $this->ovesio->sendData();
        } catch(Throwable $e) {
            dd($e);
        }
    }
}
