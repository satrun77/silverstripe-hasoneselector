<?php

namespace Moo\HasOneSelector\Extension;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;

class GridFieldItemRequest extends DataExtension
{
    /**
     * Hook after update breadcrumbs in grid field to ensure not saved item in breadcrumb have
     * correct URL to adding new item
     *
     * @param  ArrayList $items
     * @return void
     */
    public function updateBreadcrumbs(ArrayList $items)
    {
        /** @var DataObject $record */
        $record = $this->getOwner()->record;
        // Breadcrumb item title
        $name = _t('SilverStripe\\Forms\\GridField\\GridField.NewRecord', 'New {type}', ['type' => $record->i18n_singular_name()]);

        // Find item in breadcrumb for data object that is not yet saved that has link
        // with value of false
        $find = $items->filterByCallback(function ($item) use ($name) {
            return !$item->Link && $item->Title === $name;
        })->first();

        // If we found and item, then ensure we have valid link
        if ($find) {
            $find->setField('Link', $this->getOwner()->Link());
        }
    }
}
