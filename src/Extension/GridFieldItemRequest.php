<?php

namespace Moo\HasOneSelector\Extension;

use Moo\HasOneSelector\Form\GridField;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;

class GridFieldItemRequest extends DataExtension
{
    /**
     * Hook after update breadcrumbs in grid field to ensure not saved item in breadcrumb have
     * correct URL to adding new item.
     */
    public function updateBreadcrumbs(ArrayList $items): void
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

    /**
     * Hook after saving an object.
     */
    public function onAfterSave(DataObject $record): void
    {
        // Close saved object and remove the value of the ID
        $unsavedRecord     = clone $record;
        $unsavedRecord->ID = 0;
        // Get name of session for unsaved object
        $unsavedSessionName = GridField::formatSessionName($unsavedRecord);
        // Current session
        $session = Controller::curr()->getRequest()->getSession();

        // If we have value stored in the session, then clear that value
        if ($session->get($unsavedSessionName)) {
            $session->clear($unsavedSessionName);
        }

        // Remove unsaved recored
        unset($unsavedRecord);
    }
}
