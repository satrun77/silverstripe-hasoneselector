<?php

namespace Moo\HasOneSelector\Form;

use Exception;
use Moo\HasOneSelector\ORM\DataList;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldComponent;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\SS_List;

/**
 * Class Field provides CMS field to manage selecting/adding/editing object within
 * has_one relation of the current object being edited.
 */
class Field extends CompositeField
{
    /**
     * Instance of form field that find and display selected record.
     */
    protected ?GridField $gridField = null;

    /**
     * Instance of form field that holds the value.
     */
    protected ?FormField $valueField = null;

    /**
     * HasOneSelector Field constructor.
     *
     * @param string $name
     * @param string $title
     * @param string $dataClass
     */
    public function __construct($name, $title, DataObject $owner, $dataClass = DataObject::class)
    {
        // Create grid field
        $this->initGridField($name, $title, $owner, $dataClass);

        $this->addExtraClass('b-hasoneselector-field');

        // Ensure there is a left label to allow for field to be aligned with others
        $this->leftTitle = ' ';

        // Create composite field with hidden field holds the value and grid field to find and select has one relation
        parent::__construct([
            $this->getValueHolderField(),
            $this->getGridField(),
        ]);
    }

    /**
     * Returns a "field holder" for this field.
     *
     * Forms are constructed by concatenating a number of these field holders.
     *
     * The default field holder is a label and a form field inside a div.
     *
     * @see FieldHolder.ss
     *
     * @param array $properties
     *
     * @return DBHTMLText
     */
    public function FieldHolder($properties = [])
    {
        // Set title based on left title property
        $properties['Title'] = $this->leftTitle;

        // Render field holder
        return parent::FieldHolder($properties);
    }

    /**
     * Get instance of value holder field that hold the value of has one.
     */
    protected function getValueHolderField(): FormField
    {
        if (is_null($this->valueField)) {
            // Name of the has one relation
            $recordName = $this->getGridField()->getName().'ID';

            // Field to hold the value
            $this->valueField = HiddenField::create($recordName, '', '');
        }

        return $this->valueField;
    }

    /**
     * Get instance of grid field embed in wrapper field.
     */
    public function getGridField(): GridField
    {
        return $this->gridField;
    }

    /**
     * Initiate instance of grid field. This is a subclass of GridField.
     */
    protected function initGridField(
        string $name,
        string $title,
        DataObject $owner,
        string $dataClass = DataObject::class
    ): GridField {
        if (is_null($this->gridField)) {
            $this->gridField = GridField::create($name, $title, $owner, $dataClass);
            // Instance of data list that manages the grid field data
            $this->gridField->setList($this->createList());
        }
        $this->gridField->setValueHolderField($this->getValueHolderField());

        return $this->gridField;
    }

    /**
     * Create data list for grid field.
     */
    protected function createList(): SS_List
    {
        return DataList::create($this->getGridField());
    }

    /**
     * Remove the linkable grid field component.
     */
    public function removeLinkable(): self
    {
        // Remove grid field linkable component
        $this->getGridField()->getConfig()->getComponents()->each(function ($component) {
            if ($component instanceof GridFieldAddExistingAutocompleter) {
                $this->getGridField()->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
            }
        });

        return $this;
    }

    /**
     * Add linkable grid field component.
     */
    public function enableLinkable(GridFieldComponent $component = null): self
    {
        // Use default linkable grid field component
        if (is_null($component)) {
            $component = new GridFieldAddExistingAutocompleter('buttons-before-right');
        }

        // Add grid field component
        $this->getGridField()->getConfig()->addComponent($component);

        return $this;
    }

    /**
     * Remove the addable grid field component.
     */
    public function removeAddable(): self
    {
        // Remove grid field addable component
        $this->getGridField()->getConfig()->getComponents()->each(function ($component) {
            if ($component instanceof GridFieldAddNewButton) {
                $this->getGridField()->getConfig()->removeComponentsByType(GridFieldAddNewButton::class);
            }
        });

        return $this;
    }

    /**
     * Add addable grid field component.
     */
    public function enableAddable(GridFieldComponent $component = null): self
    {
        // Use default addable grid field component
        if (is_null($component)) {
            $component = new GridFieldAddNewButton('buttons-before-left');
        }

        // Add grid field component
        $this->getGridField()->getConfig()->addComponent($component);

        return $this;
    }

    /**
     * Proxy any undefined methods to the grid field as this is the main field and the composite is wrapper to manage
     * the field and value of has one.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __call($method, $arguments = [])
    {
        return $this->getGridField()->{$method}(...$arguments);
    }
}
