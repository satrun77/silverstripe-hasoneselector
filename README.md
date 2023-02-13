# HasOne Selector

[![Build Status](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/badges/build.png?b=master)](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/moo/hasoneselector/v/stable?format=flat)](https://packagist.org/packages/moo/hasoneselector)
[![License](https://poser.pugx.org/moo/hasoneselector/license?format=flat)](https://packagist.org/packages/moo/hasoneselector)

HasOneSelector is a module that provides CMS field to manage data object defined in a has_one relation.

## Requirements

* SilverStripe CMS ^5.0

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
