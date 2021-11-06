<?php

namespace Moo\HasOneSelector\Form;

use Exception;
use Moo\HasOneSelector\ORM\DataList;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField as SSGridField;
use SilverStripe\Forms\GridField\GridField_ActionMenu;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\Requirements;

/**
 * Class GridField instance of grid field.
 */
class GridField extends SSGridField
{
    /**
     * Name of the list data class.
     */
    protected string $dataClass = '';

    /**
     * Instance of data object that contains the has one relation.
     */
    protected ?DataObject $owner = null;

    /**
     * Text to display when no record selected.
     */
    protected string $emptyString = 'No item selected';

    /**
     * Instance of form field that holds the value.
     */
    protected ?FormField $valueField = null;

    /**
     * HasOneSelector GridField constructor.
     */
    public function __construct(string $name, string $title, DataObject $owner, string $dataClass = DataObject::class)
    {
        // Include styles
        Requirements::css('moo/hasoneselector:client/styles/hasoneselector.css');

        // Initiate grid field configuration based on relation editor
        $config = new GridFieldConfig();
        $config->addComponent(new GridFieldButtonRow('before'));
        $config->addComponent(new GridFieldAddNewButton('buttons-before-left'));
        $config->addComponent(new GridFieldAddExistingAutocompleter('buttons-before-right'));
        $config->addComponent(new GridFieldDataColumns());
        $config->addComponent(new GridFieldEditButton());
        $config->addComponent(new GridFieldDeleteAction(true));
        $config->addComponent(new GridField_ActionMenu());
        $config->addComponent(new GridFieldDetailForm());

        // Set the data class of the list
        $this->setDataClass($dataClass);
        // Set the owner data object that contains the has one relation
        $this->setOwner($owner);
        // Load relation value from session
        $this->loadRelationFromSession();

        // Set empty string based on the data class
        $this->setEmptyString(sprintf('No %s selected', mb_strtolower(singleton($dataClass)->singular_name())));

        parent::__construct($name, $title, null, $config);
    }

    /**
     * Set instance of value holder field.
     */
    public function setValueHolderField(FormField $field): self
    {
        $this->valueField = $field;

        return $this;
    }

    /**
     * Defined the columns to be rendered in the field.
     */
    public function setDisplayFields(array $fields): self
    {
        // Get grid field configuration
        $config = $this->getConfig();

        // Define columns to display in grid field
        $component = $config->getComponentByType(GridFieldDataColumns::class);
        assert($component instanceof GridFieldDataColumns);

        $component->setDisplayFields($fields);

        return $this;
    }

    /**
     * Apply transformation of the displayed data within a specific column(s).
     */
    public function setFieldFormatting(array $formatting): self
    {
        // Get grid field configuration
        $config = $this->getConfig();

        // Customise the display of the column
        $component = $config->getComponentByType(GridFieldDataColumns::class);
        assert($component instanceof GridFieldDataColumns);

        $component->setFieldFormatting($formatting);

        return $this;
    }

    /**
     * Set empty string when no record selected.
     */
    public function setEmptyString(string $string): self
    {
        $this->emptyString = $string;

        return $this;
    }

    /**
     * set the name of the data class for current list.
     */
    public function setDataClass(string $class): self
    {
        $this->dataClass = $class;

        return $this;
    }

    /**
     * Get the name of the data class for current list.
     */
    public function getDataClass(): string
    {
        return $this->dataClass;
    }

    /**
     * Get the record of the has one relation for current owner object.
     *
     * @throws Exception
     */
    public function getRecord(): ?DataObject
    {
        return $this->getOwner()->{rtrim($this->getName(), 'ID')}();
    }

    /**
     * Set the record of the has one relation for current owner object.
     *
     * @throws Exception
     */
    public function setRecord(?DataObject $object): void
    {
        $owner      = $this->getOwner();
        $recordName = $this->getRelationName();

        $owner->{$recordName} = is_null($object) ? 0 : $object->ID;

        // Store relation value in session
        $this->storeRelationInSession((int) $owner->{$recordName});
    }

    /**
     * Set instance of data object that has the has one relation.
     */
    public function setOwner(DataObject $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get instance of data object that has the has one relation.
     */
    public function getOwner(): ?DataObject
    {
        return $this->owner;
    }

    /**
     * Get the data source.
     *
     * @return SS_List
     */
    public function getList()
    {
        // Get current record ID
        $id = (int) $this->getOwner()->{$this->getRelationName()};

        // Filter current list to display current record (has one) value
        $list = parent::getList();
        assert($list instanceof DataList);

        return $list->filter('ID', $id);
    }

    /**
     * Get the data source after applying every {@link GridField_DataManipulator} to it.
     */
    public function getManipulatedList(): SS_List
    {
        // Call manipulation from parent class to update current record (has one)
        parent::getManipulatedList();

        // Get list of data based on new record
        return $this->getList();
    }

    /**
     * @return string
     */
    protected function getOptionalTableBody(array $content)
    {
        // Text used by grid field for no items
        $noItemsText = _t('GridField.NoItemsFound', 'No items found');

        // If we have no items text in the body, then replace the text with customised string
        if (mb_strpos($content['body'], $noItemsText) !== false) {
            $content['body'] = str_replace($noItemsText, $this->emptyString, $content['body']);
        }

        // Append field to hold the value of has one relation
        $owner      = $this->getOwner();
        $recordName = $this->getRelationName();
        $content['body'] .= $this->valueField->setValue($owner->{$recordName})->Field();

        return parent::getOptionalTableBody($content);
    }

    /**
     * Get relation name within the owner object. This includes the "ID" at the end.
     */
    protected function getRelationName(): string
    {
        return $this->getName().'ID';
    }

    /**
     * Store relation value in session.
     */
    protected function storeRelationInSession(int $recordId): void
    {
        // Session name for current owner
        $sessionName = $this->getSessionName();

        // Store relation and owner in session
        $session = Controller::curr()->getRequest()->getSession();
        $session->set($sessionName, [
            'Relation'   => $this->getRelationName(),
            'RelationID' => $recordId,
        ]);
    }

    /**
     * Load relation value from data stored in session.
     */
    protected function loadRelationFromSession(): void
    {
        // Session name for current owner
        $sessionName = $this->getSessionName();

        // Store relation value in session
        $session = Controller::curr()->getRequest()->getSession();
        $data    = $session->get($sessionName);
        if (!empty($data['Relation']) && !empty($data['RelationID'])) {
            // Get owner object
            $owner = $this->getOwner();

            // Set relation value
            $owner->{$data['Relation']} = $data['RelationID'];
        }
    }

    /**
     * Get session name for current owner to store relation value.
     */
    protected function getSessionName(): string
    {
        // Get owner object
        $owner = $this->getOwner();

        // Session name for current owner
        return static::formatSessionName($owner);
    }

    /**
     * Get formatted name for session to store relation value.
     */
    public static function formatSessionName(DataObject $owner): string
    {
        return sprintf('%s_%s_%s', self::class, $owner->ClassName, $owner->ID);
    }
}
