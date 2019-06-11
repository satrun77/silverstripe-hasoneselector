<?php

namespace Moo\HasOneSelector\Tests\Object;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class PageTest extends DataObject implements TestOnly
{
    private static $table_name = 'PageTest';

    private static $db = [
        'Title' => 'Varchar',
    ];

    private static $has_one = [
        'Resource' => ResourceTest::class,
    ];
}
