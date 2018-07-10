<?php

namespace Moo\HasOneSelector\Tests\Object;

use SilverStripe\ORM\DataObject;

class ResourceTest extends DataObject
{
    private static $db = [
        'Title' => 'Varchar',
    ];
}
