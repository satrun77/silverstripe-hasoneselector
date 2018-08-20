<?php

/**
 * Class HasOneSelectorGridField instance of grid field
 */
class HasOneSelectorGridField extends GridField
{
    /**
     * Name of the list data class
     *
     * @var string
     */
    protected $dataClass;

    /**
     * Instance of data object that contains the has one relation
     *
     * @var DataObject
     */
    protected $owner;

    /**
     * Text to display when no record selected
     *
     * @var string
     */
    protected $emptyString = 'No item selected';

    /**
     * Instance of form field that holds the value
     *
     * @var FormField
     */
    protected $valueField;

    /**
     * HasOneSelectorField constructor.
     * @param string     $name
     * @param string     $title
     * @param DataObject $owner
     * @param string     $dataClass
     */
    public function __construct($name, $title, DataObject $owner, $dataClass = DataObject::class)
    {
        // Include styles
        Requirements::css('hasoneselector/client/styles/hasoneselector.css');

        // Initiate grid field configuration based on relation editor
        $config = GridFieldConfig_RelationEditor::create();
        $config->removeComponentsByType('GridFieldToolbarHeader');
        $config->removeComponentsByType('GridFieldSortableHeader');
        $config->removeComponentsByType('GridFieldFilterHeader');
        $config->removeComponentsByType('GridFieldPageCount');
        $config->removeComponentsByType('GridFieldPaginator');

        // Set the data class of the list
        $this->setDataClass($dataClass);
        // Set the owner data object that contains the has one relation
        $this->setOwner($owner);
        // Load relation value from session
        $this->loadRelationFromSession();

        // Instance of data list that manages the grid field data
        $dataList = HasOneSelectorDataList::create($this);

        // Set empty string based on the data class
        $this->setEmptyString(sprintf('No %s selected', strtolower(singleton($dataClass)->singular_name())));

        parent::__construct($name . '', $title, $dataList, $config);
    }

    /**
     * Set instance of value holder field
     *
     * @param  FormField $field
     * @return $this
     */
    public function setValueHolderField(FormField $field)
    {
        $this->valueField = $field;

        return $this;
    }

    /**
     * Defined the columns to be rendered in the field
     *
     * @param  array $fields
     * @return $this
     */
    public function setDisplayFields(array $fields)
    {
        // Get grid field configuration
        $config = $this->getConfig();

        // Define columns to display in grid field
        $config->getComponentByType('GridFieldDataColumns')->setDisplayFields($fields);

        return $this;
    }

    /**
     * Apply transformation of the displayed data within a specific column(s)
     *
     * @param  array $formatting
     * @return $this
     */
    public function setFieldFormatting(array $formatting)
    {
        // Get grid field configuration
        $config = $this->getConfig();

        // Customise the display of the column
        $config->getComponentByType('GridFieldDataColumns')->setFieldFormatting($formatting);

        return $this;
    }

    /**
     * Set empty string when no record selected
     *
     * @param  string $string
     * @return $this
     */
    public function setEmptyString($string)
    {
        $this->emptyString = $string;

        return $this;
    }

    /**
     * set the name of the data class for current list
     *
     * @param  string $class
     * @return $this
     */
    public function setDataClass($class)
    {
        $this->dataClass = $class;

        return $this;
    }

    /**
     * Get the name of the data class for current list
     *
     * @return string
     */
    public function getDataClass()
    {
        return $this->dataClass;
    }

    /**
     * Get the record of the has one relation for current owner object
     *
     * @return DataObject|null
     * @throws Exception
     */
    public function getRecord()
    {
        return $this->getOwner()->{rtrim($this->getName(), 'ID')}();
    }

    /**
     * Set the record of the has one relation for current owner object
     *
     * @param  DataObject|null $object
     * @return void
     */
    public function setRecord($object)
    {
        $owner      = $this->getOwner();
        $recordName = $this->getRelationName();

        $owner->{$recordName} = is_null($object) ? 0 : $object->ID;

        // Store relation value in session
        $this->storeRelationInSession($owner->{$recordName});
    }

    /**
     * Set instance of data object that has the has one relation
     *
     * @param  DataObject $owner
     * @return $this
     */
    public function setOwner(DataObject $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get instance of data object that has the has one relation
     *
     * @return DataObject
     */
    public function getOwner()
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
        return $this->list->filter('ID', $id);
    }

    /**
     * Get the data source after applying every {@link GridField_DataManipulator} to it.
     *
     * @return SS_List
     */
    public function getManipulatedList()
    {
        // Call manipulation from parent class to update current record (has one)
        parent::getManipulatedList();

        // Get list of data based on new record
        return $this->getList();
    }

    /**
     * @param  array  $content
     * @return string
     */
    protected function getOptionalTableBody(array $content)
    {
        // Text used by grid field for no items
        $noItemsText = _t('GridField.NoItemsFound', 'No items found');

        // If we have no items text in the body, then replace the text with customised string
        if (strpos($content['body'], $noItemsText) !== false) {
            $content['body'] = str_replace($noItemsText, $this->emptyString, $content['body']);
        }

        // Append field to hold the value of has one relation
        $owner      = $this->getOwner();
        $recordName = $this->getRelationName();
        $content['body'] .= $this->valueField->setValue($owner->{$recordName})->Field();

        return parent::getOptionalTableBody($content);
    }

    /**
     * Get relation name within the owner object. This includes the "ID" at the end
     *
     * @return string
     */
    protected function getRelationName()
    {
        return $this->getName() . 'ID';
    }

    /**
     * Store relation value in session
     *
     * @param int $recordId
     */
    protected function storeRelationInSession($recordId)
    {
        // Session name for current owner
        $sessionName = $this->getSessionName();

        // Store relation and owner in session
        Session::set($sessionName, [
            'Relation'   => $this->getRelationName(),
            'RelationID' => (int) $recordId,
        ]);
    }

    /**
     * Load relation value from data stored in session
     */
    protected function loadRelationFromSession()
    {
        // Session name for current owner
        $sessionName = $this->getSessionName();

        // Store relation value in session
        $data = Session::get($sessionName);
        if (!empty($data['Relation']) && !empty($data['RelationID'])) {
            // Get owner object
            $owner = $this->getOwner();

            // Set relation value
            $owner->{$data['Relation']} = $data['RelationID'];
        }
    }

    /**
     * Get session name for current owner to store relation value
     *
     * @return string
     */
    protected function getSessionName()
    {
        // Get owner object
        $owner = $this->getOwner();

        // Session name for current owner
        return sprintf('%s_%s_%s', self::class, $owner->ClassName, $owner->ID);
    }
}
