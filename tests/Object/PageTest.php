<?php

namespace Moo\HasOneSelector\Tests\Object;

use SilverStripe\ORM\DataObject;

class PageTest extends DataObject
{
    private static $db = [
        'Title' => 'Varchar',
    ];

    private static $has_one = [
        'Resource' => ResourceTest::class,
    ];
}
