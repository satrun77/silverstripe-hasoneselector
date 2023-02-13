<?php

namespace Moo\HasOneSelector\Tests\Object;

use Exception;
use Moo\HasOneSelector\Form\GridField;
use SilverStripe\Dev\TestOnly;
use stdClass;

class DataList extends \Moo\HasOneSelector\ORM\DataList implements TestOnly
{
    protected array $list       = [];
    protected array $filterArgs = [];

    public function __construct(GridField $gridField)
    {
        try {
            parent::__construct($gridField);
        } catch (Exception $e) {
            // Disable db exception from parent controller
            $this->dataQuery = new stdClass();
            $this->gridField = $gridField;
            $this->dataClass = $gridField->getDataClass();
        }
    }

    public function setList($list): self
    {
        $this->list = $list;

        return $this;
    }

    public function toArray()
    {
        return $this->list;
    }

    public function count(): int
    {
        return count($this->list);
    }

    public function first()
    {
        return current($this->list);
    }

    public function filter()
    {
        $this->filterArgs = func_get_args();
        foreach ($this->list as $item) {
            if ((int) $item->ID === (int) $this->filterArgs[1]) {
                $list = clone $this;
                $list->setList([
                    $item,
                ]);

                return $list;
            }
        }

        return $this;
    }

    public function getFilterArgs(): array
    {
        return $this->filterArgs;
    }
}
