<?php

namespace Nullform\TelegramGateway;

abstract class AbstractType
{
    /**
     * @param array|object|string|null $data Associative array, object or JSON string.
     */
    public function __construct($data = null)
    {
        if (!empty($data)) {
            $this->map($data);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return \json_encode($this, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Assigns values from $data to the Type's properties.
     *
     * @param array|object|string $data Associative array, object or JSON string.
     * @return $this
     */
    protected function map($data)
    {
        if (\is_string($data)) {
            $data = \json_decode($data, true);
        }

        $reflection = new \ReflectionObject($this);
        $data = (array)$data;
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            if (\array_key_exists($property->getName(), $data)) {
                $property->setValue($this, $data[$property->getName()]);
            }
        }

        return $this;
    }
}
