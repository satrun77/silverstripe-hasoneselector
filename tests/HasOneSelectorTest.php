<?php

namespace Moo\HasOneSelector\Tests;

use Moo\HasOneSelector\Form\Field;
use Moo\HasOneSelector\Tests\Object\ControllerTest;
use Moo\HasOneSelector\Tests\Object\PageTest;
use Moo\HasOneSelector\Tests\Object\ResourceTest;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;

/**
 * @internal
 * @coversNothing
 */
class HasOneSelectorTest extends FunctionalTest
{
    protected static $fixture_file = 'HasOneSelectorTest.yml';

    protected static $extra_dataobjects = [
        PageTest::class,
        ResourceTest::class,
    ];

    public function testNoItemSelected()
    {
        $page  = $this->getPage('page-1');
        $field = $this->getField($page)->setEmptyString('No data selected');
        $form  = new Form(new ControllerTest(), 'Form', FieldList::create($field), FieldList::create());
        $html  = $field->FieldHolder();

        $this->assertContains('No data selected', $html);
        $this->assertEquals(ResourceTest::class, $field->getDataClass());
        $this->assertFalse($field->getRecord()->exists());
    }

    public function testSelectedItem()
    {
        $page     = $this->getPage('page-1');
        $field    = $this->getField($page)->setEmptyString('No data selected');
        $resource = $this->getResource('resource-1');
        $page->setField('ResourceID', $resource->ID);
        $form = new Form(Controller::curr(), 'Form', FieldList::create($field), FieldList::create());

        $this->assertInstanceOf(ResourceTest::class, $field->getRecord());
        $this->assertInstanceOf(PageTest::class, $field->getOwner());
        $this->assertEquals($field->getList()->first()->ID, $resource->ID);
        $this->assertEquals($field->getList(), $field->getManipulatedList());
        $this->assertEquals($resource->Title, $field->getColumnContent($field->getRecord(), 'Title'));
    }

    public function testSelectingRemovingItem()
    {
        $page  = $this->getPage('page-1');
        $field = $this->getField($page);

        $resource = $this->getResource('resource-1');
        $field->getList()->add($resource);
        $form = new Form(new ControllerTest(), 'Form', FieldList::create($field), FieldList::create());

        $this->assertInstanceOf(ResourceTest::class, $field->getRecord());
        $this->assertInstanceOf(PageTest::class, $field->getOwner());
        $this->assertEquals($field->getList()->first()->ID, $resource->ID);
        $this->assertEquals($field->getList(), $field->getManipulatedList());
        $this->assertEquals($resource->Title, $field->getColumnContent($field->getRecord(), 'Title'));

        $field->getList()->remove($resource);
        $html = $field->FieldHolder();

        $this->assertContains('No resource test selected', $html);
        $this->assertEquals(ResourceTest::class, $field->getDataClass());
        $this->assertFalse($field->getRecord()->exists());
    }

    /**
     * @param $name
     *
     * @return PageTest
     */
    protected function getPage($name)
    {
        return $this->objFromFixture(PageTest::class, $name);
    }

    /**
     * @param $name
     *
     * @return ResourceTest
     */
    protected function getResource($name)
    {
        return $this->objFromFixture(ResourceTest::class, $name);
    }

    /**
     * @param mixed $page
     *
     * @return Field
     */
    protected function getField($page)
    {
        return Field::create('Resource', 'Resource', $page, ResourceTest::class);
    }
}
