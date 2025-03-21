<?php

class Ovesio
{
    private $registry;

    private $data = [];

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

	public function __get($key) {
		return $this->registry->get($key);
	}

    public function __destruct()
    {
        foreach($this->data as $type => $ids) {
            $ids = array_filter($ids);
            if($ids) {
                $this->event->trigger($type, [$ids]);
            }
        }
    }

    /**
     * Add to data
     *
     * @param string $type | ovesio_translate_event
     * @param string $key | product, category, attribute, option
     * @param int $id
     * @return void
     */
    public function add($type, $key, $id)
    {
        foreach ((array)$id as $id) {
            $this->data[$type][$key][$id] = $id;
        }
    }

    public function getAll()
    {
        return $this->data;
    }

    public function translateProduct($id)
    {
        $this->ovesio->add('ovesio_translate_event', 'product', $id);
    }

    public function translateCategory($id)
    {
        $this->ovesio->add('ovesio_translate_event', 'category', $id);
    }

    public function translateAttribute($id)
    {
        $this->ovesio->add('ovesio_translate_event', 'attribute', $id);
    }

    public function translateOption($id)
    {
        $this->ovesio->add('ovesio_translate_event', 'option', $id);
    }
}