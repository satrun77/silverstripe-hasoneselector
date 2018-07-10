<?php

namespace Moo\HasOneSelector\ORM;

use Exception;
use SilverStripe\ORM\DataList as BaseDataList;
use SilverStripe\ORM\DataObject;
use Moo\HasOneSelector\Form\Field;

/**
 * Class DataList is data list to manage add/remove managed object from the
 * HasOneSelectorField class
 */
class DataList extends BaseDataList
{
    /**
     * @var Field
     */
    protected $gridField;

    /**
     * HasOneSelectorDataList constructor.
     * @param Field $gridField
     */
    public function __construct(Field $gridField)
    {
        $this->gridField = $gridField;

        parent::__construct($gridField->getDataClass());
    }

    /**
     * Set the current selected record into the has one relation
     *
     * @param  DataObject $item
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
    public function remove($item)
    {
        $this->gridField->setRecord(null);
    }
}
