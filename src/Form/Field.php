<?php

namespace Moo\HasOneSelector\Form;

use Exception;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataObject;

/**
 * Class HasOneSelectorField provides CMS field to manage selecting/adding/editing object within
 * has_one relation of the current object being edited
 */
class HasOneSelectorField extends CompositeField
{
    /**
     * Instance of form field that find and display selected record
     *
     * @var GridField
     */
    protected $gridField;

    /**
     * Instance of form field that holds the value
     *
     * @var FormField
     */
    protected $valueField;

    /**
     * HasOneSelectorField constructor
     *
     * @param string     $name
     * @param string     $title
     * @param DataObject $owner
     * @param string     $dataClass
     */
    public function __construct($name, $title, DataObject $owner, $dataClass = DataObject::class)
    {
        // Create grid field
        $this->initGridField($name, $title, $owner, $dataClass);

        // Create composite field with hidden field holds the value and grid field to find and select has one relation
        parent::__construct([
            $this->getValueHolderField(),
            $this->gridField,
        ]);
    }

    /**
     * Get instance of value holder field that hold the value of has one
     *
     * @return FormField
     */
    protected function getValueHolderField()
    {
        if (is_null($this->valueField)) {
            // Name of the has one relation
            $recordName = $this->gridField->getName() . 'ID';

            // Field to hold the value
            $this->valueField = HiddenField::create($recordName, '', '');
        }

        return $this->valueField;
    }

    /**
     * Initiate instance of grid field. This is a subclass of GridField
     *
     * @param  string     $name
     * @param  string     $title
     * @param  DataObject $owner
     * @param  string     $dataClass
     * @return GridField
     */
    protected function initGridField($name, $title, DataObject $owner, $dataClass = DataObject::class)
    {
        if (is_null($this->gridField)) {
            $this->gridField = GridField::create($name, $title, $owner, $dataClass);
        }
        $this->gridField->setValueHolderField($this->getValueHolderField());

        return $this->gridField;
    }

    /**
     * Proxy any undefined methods to the grid field as this is the main field and the composite is wrapper to manage
     * the field and value of has one
     *
     * @param  string    $method
     * @param  array     $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arguments = [])
    {
        if ($this->gridField instanceof GridField) {
            return $this->gridField->{$method}(...$arguments);
        }

        return parent::__call($method, $arguments);
    }
}
