<?php

namespace Moo\HasOneSelector\Tests\Object;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class ResourceTest extends DataObject implements TestOnly
{
    private static $db = [
        'Title' => 'Varchar',
    ];
}
