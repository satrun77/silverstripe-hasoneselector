<?php

/**
 * Class HasOneSelectorDataList is data list to manage add/remove managed object from the
 * HasOneSelectorField class
 */
class HasOneSelectorDataList extends DataList
{
    /**
     * @var HasOneSelectorGridField
     */
    protected $gridField;

    /**
     * HasOneSelectorDataList constructor.
     * @param HasOneSelectorGridField $gridField
     */
    public function __construct(HasOneSelectorGridField $gridField)
    {
        $this->gridField = $gridField;

        parent::__construct($gridField->getDataClass());
    }

    /**
     * Set the current selected record into the has one relation
     *
     * @param  DataObject $item
     * @return void
     */
    public function add($item)
    {
        $this->gridField->setRecord($item);
    }

    /**
     * Clear the record within the has one relation
     *
     * @param  DataObject $item
     * @return void
     */
    public function remove($item)
    {
        $this->gridField->setRecord(null);
    }
}
