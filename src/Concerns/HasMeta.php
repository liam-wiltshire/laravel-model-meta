<?php

namespace LiamWiltshire\LaravelModelMeta\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasMeta
{

    protected static $tableFields;

    /**
     * Get all the fields for this models DB table
     * @return array|null
     * @codeCoverageIgnore
     */
    public function getTableFields(): array
    {
        if (!self::$tableFields) {
            $connection = $this->getConnection();

            $fields = $connection->getSchemaBuilder()->getColumnListing($this->getTable());

            self::$tableFields = array_flip($fields);
        }

        return self::$tableFields;
    }

    /**
     * Override default getCasts method to add the meta field
     * @return array
     */
    public function getCasts(): array
    {
        $this->casts[$this->getMetaDbField()] = 'json';
        return parent::getCasts();
    }

    /**
     * Get the name of the column holding meta data
     * @return string
     */
    public function getMetaDbField(): string
    {
        return property_exists($this, 'metaDbField') ? $this->metaDbField : 'meta';
    }

    /**
     * Test to see if $key should be considered an attribute (relationship, method, property or attribute)
     * @param $key
     * @return bool
     */
    public function handleAsAttribute(string $key): bool
    {
        if (property_exists($this, $key)) {
            return true;
        }

        if (parent::getAttribute($key) !== null) {
            return true;
        }

        if (array_key_exists($key, $this->getTableFields())) {
            return true;
        }

        if (method_exists($this, $key)) {
            return true;
        }

        return false;
    }

    /**
     * Get the current metadata for this model
     * @return object
     */
    public function getMetaData(): \stdClass
    {
        if (!$metaData = parent::getAttribute($this->getMetaDbField())) {
            $metaData = new \stdClass();
        }

        return (object) $metaData;
    }

    /**
     * Override default getAttribute method to include metadata step
     * @param $key
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        if ($this->handleAsAttribute($key)) {
            return parent::getAttribute($key);
        }

        $meta = $this->getMetaData();
        return $meta->{$key} ?? null;
    }

    /**
     * Overrride default setAttribute method to include metadata step
     * @param $key
     * @param $value
     * @return $this
     * @throws \Exception
     */
    public function setAttribute($key, $value) :Model
    {
        if ($key == $this->getMetaDbField()) {
            throw new \Exception("Field {$key} shouldn't be manipulated directly");
        }

        if ($this->handleAsAttribute($key)) {
            parent::setAttribute($key, $value);
            return $this;
        }

        $meta = $this->getMetaData();

        $meta->{$key} = $value;

        parent::setAttribute($this->getMetaDbField(), $meta);
        return $this;
    }

    public function save(array $options = [])
    {
        if (!parent::getAttribute($this->getMetaDbField())) {
            parent::setAttribute($this->getMetaDbField(), []);
        }

        parent::save($options);
    }
    
    /**
     * Unset a meta field by key
     * @param string $key
     * @return $this
     */

    public function unsetMeta(string $key): Model
    {
        $meta = $this->getMetaData();

        if (isset($meta->{$key})) {
            unset($meta->{$key});

            parent::setAttribute($this->getMetaDbField(), $meta);
            parent::save();
        }

        return $this;
    }
}
