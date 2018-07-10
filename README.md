# HasOne Selector

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6e6bbf47-1ea0-4169-94fb-850bf9baccb1/mini.png)](https://insight.sensiolabs.com/projects/6e6bbf47-1ea0-4169-94fb-850bf9baccb1)
[![Code Coverage](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/satrun77/silverstripe-hasoneselector/?branch=master)
[![Build Status](https://travis-ci.org/satrun77/silverstripe-hasoneselector.svg?branch=master)](https://travis-ci.org/satrun77/silverstripe-hasoneselector)

HasOneSelector is a module that provides CMS field to manage data object defined in a has_one relation.

## Installation via Composer
	composer require satrun77/hasoneselector

## Usage

```php
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

        $resource = HasOneSelectorField::create('Resource', 'Resource', $this, Resource::class);
        $fields->addFieldToTab('Root.Main', $resource);

        return $fields;
    }

    //...
}
```

## License

This module is under the MIT license. View the [LICENSE](LICENSE.md) file for the full copyright and license information.
