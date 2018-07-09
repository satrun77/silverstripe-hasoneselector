<?php

/**
 * Class HasOneSelectorDataList is data list to manage add/remove managed object from the
 * HasOneSelectorField class
 */
class HasOneSelectorDataList extends DataList
{
    /**
     * @var HasOneSelectorField
     */
    protected $gridField;

    /**
     * HasOneSelectorDataList constructor.
     * @param HasOneSelectorField $gridField
     */
    public function __construct(HasOneSelectorField $gridField)
    {
        $this->gridField = $gridField;

        parent::__construct($gridField->getDataClass());
    }

    /**
     * Set the current selected record into the has one relation
     *
     * @param  DataObject          $item
     * @return void
     * @throws ValidationException
     */
    public function add($item)
    {
        $this->gridField->setRecord($item);
    }

    /**
     * Clear the record within the has one relation
     *
     * @param  DataObject          $item
     * @return void
     * @throws ValidationException
     */
    public function remove($item)
    {
        $this->gridField->setRecord(null);
    }
}
