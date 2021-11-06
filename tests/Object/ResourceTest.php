<?php

namespace Moo\HasOneSelector\Tests\Object;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * @internal
 */
class ResourceTest extends DataObject implements TestOnly
{
    private static $table_name = 'ResourceTest';

    private static $db = [
        'Title' => 'Varchar',
    ];
}
