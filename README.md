# HasOne Selector

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6e6bbf47-1ea0-4169-94fb-850bf9baccb1/mini.png)](https://insight.sensiolabs.com/projects/6e6bbf47-1ea0-4169-94fb-850bf9baccb1)
[![Code Coverage](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/?branch=master)
[![Build Status](https://travis-ci.org/satrun77/silverstripe-hasoneselector.svg?branch=master)](https://travis-ci.org/satrun77/silverstripe-hasoneselector)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/?branch=master)

HasOneSelector is a module that provides CMS field to manage data object defined in a has_one relation.

## Requirements

* SilverStripe CMS ^4.1

For a SilverStripe 3.x compatible version, please see the [3.x branch, or 1.x releases](https://github.com/satrun77/silverstripe-hasoneselector/tree/3.x).

## Installation via Composer
	composer require moo/hasoneselector

## Usage

```php

use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;
use Moo\HasOneSelector\Form\Field;

class Resource extends DataObject
{
    //...
}

class Page extends SiteTree
{
    //...
    private static $has_one = [
        'Resource' => Resource::class,
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $resource = Field::create('Resource', 'Resource', $this, Resource::class);
        $fields->addFieldToTab('Root.Main', $resource);

        return $fields;
    }

    //...
}
```

## License

This module is under the MIT license. View the [LICENSE](LICENSE.md) file for the full copyright and license information.
