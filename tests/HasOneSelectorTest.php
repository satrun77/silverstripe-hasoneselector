<?php

namespace Moo\HasOneSelector\Tests;

use InvalidArgumentException;
use Moo\HasOneSelector\Extension\GridFieldItemRequest;
use Moo\HasOneSelector\Form\Field;
use Moo\HasOneSelector\Form\GridField;
use Moo\HasOneSelector\ORM\DataList;
use Moo\HasOneSelector\Tests\Object\PageTest;
use Moo\HasOneSelector\Tests\Object\ResourceTest;
use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

/**
 * @internal
 */
class HasOneSelectorTest extends SapphireTest
{
    public static function tearDownAfterClass(): void
    {
        // Disable teardown to prevent db access
    }

    public function testNoItemSelected(): void
    {
        RequestHandler::config()->set('url_segment', '/');
        $page  = $this->getPage()->setComponent('Resource', null);
        $field = $this->getField($page);
        $field->setEmptyString('No data selected');
        new Form(new RequestHandler(), 'Form', FieldList::create($field), FieldList::create());
        $html = $field->FieldHolder();

        self::assertStringContainsString('No data selected', (string)$html);
        $this->assertEquals(ResourceTest::class, $field->getDataClass());
        $this->assertNull($field->getRecord());
    }

    public function testSelectedItem(): void
    {
        $resource = $this->getResource();
        $page     = $this->getPage();
        $page->setField('ResourceID', $resource->ID);
        $page->setComponent('Resource', $resource);

        $field = $this->getField($page);
        $field->getGridField()->getList()->setList([
            $this->getResource(),
            $this->getResource()->setField('ID', 99),
        ]);
        $resource = $page->Resource();

        $this->assertInstanceOf(ResourceTest::class, $field->getRecord());
        $this->assertInstanceOf(PageTest::class, $field->getOwner());
        $this->assertEquals($field->getList()->first()->ID, $resource->ID);
        $this->assertEquals($field->getList(), $field->getManipulatedList());
        $this->assertEquals($resource->Title, $field->getColumnContent($field->getRecord(), 'Title'));
    }

    public function testSelectingRemovingItem(): void
    {
        RequestHandler::config()->set('url_segment', '/');
        $resource = $this->getResource()->update([
            'ID' => 88,
        ]);
        $page  = $this->getPage();
        $field = $this->getField($page);

        $form = new Form(new RequestHandler(), 'Form', FieldList::create($field), FieldList::create());
        $field->setForm($form);
        $field->getList()->add($resource);
        $page->setComponent('Resource', $resource);

        $this->assertInstanceOf(ResourceTest::class, $field->getRecord());
        $this->assertInstanceOf(PageTest::class, $field->getOwner());
        $this->assertEquals($field->getList(), $field->getManipulatedList());
        $this->assertEquals($resource->Title, $field->getGridField()->getColumnContent($field->getGridField()->getRecord(), 'Title'));

        $field->getList()->remove($resource);
        $html = $field->FieldHolder();

        self::assertStringContainsString('No resource test selected', (string)$html);
        $this->assertEquals(ResourceTest::class, $field->getDataClass());
    }

    public function testManageLinkableComponent(): void
    {
        // Get HasOne Field
        $field = $this->getField($this->getPage());

        // Remove linkable component and assert it is removed
        $components       = $field->removeLinkable()->getConfig()->getComponents()->toArray();
        $componentClasses = array_map('get_class', $components);
        $this->assertNotContains(GridFieldAddExistingAutocompleter::class, $componentClasses);

        // ENable linkable and assert it exists
        $components       = $field->enableLinkable()->getConfig()->getComponents()->toArray();
        $componentClasses = array_map('get_class', $components);
        $this->assertContains(GridFieldAddExistingAutocompleter::class, $componentClasses);
    }

    public function testManageAddableComponent(): void
    {
        // Get HasOne Field
        $field = $this->getField($this->getPage());

        // Remove linkable component and assert it is removed
        $components       = $field->removeAddable()->getConfig()->getComponents()->toArray();
        $componentClasses = array_map('get_class', $components);
        $this->assertNotContains(GridFieldAddNewButton::class, $componentClasses);

        // ENable linkable and assert it exists
        $components       = $field->enableAddable()->getConfig()->getComponents()->toArray();
        $componentClasses = array_map('get_class', $components);
        $this->assertContains(GridFieldAddNewButton::class, $componentClasses);
    }

    public function testUpdateGridFieldDisplayFields(): void
    {
        // Get HasOne Field
        $field = $this->getField($this->getPage());

        $fieldsToDisplay = [
            'One'   => 'One',
            'Title' => 'Title',
        ];
        $field->setDisplayFields($fieldsToDisplay);

        foreach ($fieldsToDisplay as $name) {
            $fields = $field->getGridField()->getColumnMetadata($name);
            $this->assertArrayHasKey('title', $fields);
        }

        try {
            $field->getGridField()->getColumnMetadata('NotColumn');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Bad column "NotColumn"', $exception->getMessage());
        }
    }

    public function testUpdateGridFieldFieldFormatting(): void
    {
        // Get HasOne Field
        $page  = $this->getPage();
        $field = $this->getField($page);

        $formatting = [
            'Title' => static function ($val) {
                return 'Cool '.$val;
            },
        ];
        $field->setFieldFormatting($formatting);

        $title = $field->getGridField()->getColumnContent($page, 'Title');
        $this->assertEquals($formatting['Title']($page->Title), $title);
    }

    public function testLoadDataObjectFromSession(): void
    {
        $session = Controller::curr()->getRequest()->getSession();
        $page    = $this->getPage();

        $resource = $this->getResource()->update([
            'ID' => 99,
        ]);

        $session->set(GridField::formatSessionName($page), [
            'Relation'   => 'ResourceID',
            'RelationID' => $resource->ID,
        ]);

        $field = $this->getField($page);

        $filterArguments = $field->getList()->getFilterArgs();
        $this->assertEquals($resource->ID, $filterArguments[1]);

        $session->set(GridField::formatSessionName($page), []);

        $resource->ID = 1;
        $page         = $this->getPage();
        $page->setField('ResourceID', $resource->ID);
        $page->setComponent('Resource', $resource);

        $field = $this->getField($page);

        $this->assertInstanceOf(ResourceTest::class, $field->getRecord());
        $this->assertEquals($resource->Title, $field->getRecord()->Title);

        $filterArguments = $field->getGridField()->getList()->getFilterArgs();
        $this->assertEquals($resource->ID, $filterArguments[1]);
    }

    public function testBreadcrumb(): void
    {
        // Get item request extension
        $extension = $this->getGridFieldItemRequest();

        // Function to generate a link item
        $linkData = static function ($title = 'Link 1', $link = '/') {
            return ArrayData::create([
                'Title' => $title,
                'Link'  => $link,
            ]);
        };
        // List of items for breadcrumb
        $list = new ArrayList([
            $linkData(),
            $breadcrumbItem = $linkData('New Test', ''),
        ]);
        // Execute hook to update breadcrumb
        $extension->updateBreadcrumbs($list);

        // Assert that the breadcrumb item updated to match extension owner
        $this->assertEquals(
            $extension->getOwner()->Link(),
            $breadcrumbItem->Link
        );
    }

    public function testAfterSave(): void
    {
        // Instance of unsaved data object (page)
        $page      = $this->getPage()->update(['ID' => 0]);

        // Get item request extension
        $extension = $this->getGridFieldItemRequest();

        // Set session value
        $session   = Controller::curr()->getRequest()->getSession();
        $session->set(GridField::formatSessionName($page), [
            'Relation'   => 'ResourceID',
            'RelationID' => 1,
        ]);

        // Execute after save hook
        $extension->onAfterSave($page);

        // Assert that the session value is cleared
        $this->assertNull($session->get(GridField::formatSessionName($page)));
    }

    /**
     * Get instance of grid field item request with owner defined.
     */
    protected function getGridFieldItemRequest(): GridFieldItemRequest
    {
        $extension = new GridFieldItemRequest();
        $extension->setOwner(new class() {
            public $record;

            public function __construct()
            {
                $this->record = new class() {
                    public function i18n_singular_name()
                    {
                        return 'Test';
                    }
                };
            }

            public function Link()
            {
                return '/TextLink';
            }
        });

        return $extension;
    }

    /**
     * Get instance of owner data object (Page).
     */
    protected function getPage(): PageTest
    {
        return new PageTest();
    }

    /**
     * Get instance of data object (Resource).
     */
    protected function getResource(): ResourceTest
    {
        return ResourceTest::create([
            'ID'    => 1,
            'Title' => 'Resource 1',
        ]);
    }

    /**
     * Get instance of field for page test data object.
     */
    protected function getField(PageTest $page): Field
    {
        // Replace module DataList with test subclass
        Injector::inst()->load([
            DataList::class => [
                'class' => Object\DataList::class,
            ],
        ]);

        return new Field('Resource', 'Resource', $page, ResourceTest::class);
    }
}
