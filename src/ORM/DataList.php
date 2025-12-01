<?php

namespace Moo\HasOneSelector\ORM;

use Exception;
use Moo\HasOneSelector\Form\GridField;
use SilverStripe\ORM\DataList as BaseDataList;
use SilverStripe\ORM\DataObject;

/**
 * Class DataList is data list to manage add/remove managed object from the
 * HasOneSelectorField class.
 */
class DataList extends BaseDataList
{
    /**
     * HasOneSelectorDataList constructor.
     */
    public function __construct(protected ?GridField $gridField)
    {
        parent::__construct($this->gridField->getDataClass());
    }

    /**
     * Set the current selected record into the has one relation.
     *
     * @param DataObject $item
     *
     * @throws Exception
     */
    public function add($item): void
    {
        $this->gridField->setRecord($item);
    }

    /**
     * Clear the record within the has one relation.
     *
     * @param DataObject $item
     *
     * @throws Exception
     */
    public function remove($item): void
    {
        $this->gridField->setRecord(null);
    }
}
