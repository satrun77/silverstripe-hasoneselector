<?php

namespace Moo\HasOneSelector\Tests\Object;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

/**
 * @internal
 */
class PageTest extends DataObject implements TestOnly
{
    private static $table_name = 'PageTest';

    private static $db = [
        'Title' => 'Varchar',
    ];

    private static $has_one = [
        'Resource' => ResourceTest::class,
    ];

    public function __construct()
    {
        parent::__construct([
            'ID'    => 1,
            'Title' => 'Page one',
        ]);
    }

    public function setComponent($componentName, $item)
    {
        $this->components[$componentName] = $item;

        return $this;
    }

    public function Resource()
    {
        return $this->components['Resource'] ?? null;
    }
}
