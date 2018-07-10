<?php

class HasOneSelectorTest extends FunctionalTest
{
    protected static $fixture_file = 'HasOneSelectorTest.yml';

    public function testNoItemSelected()
    {
        $page  = $this->getPage('page-1');
        $field = $this->getField($page)->setEmptyString('No data selected');
        $form  = new Form(Controller::curr(), 'Form', FieldList::create($field), FieldList::create());
        $html  = $field->FieldHolder();

        $this->assertContains('No data selected', $html->RAW());
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
        $page     = $this->getPage('page-1');
        $field    = $this->getField($page);

        $resource = $this->getResource('resource-1');
        $field->getList()->add($resource);
        $form = new Form(Controller::curr(), 'Form', FieldList::create($field), FieldList::create());

        $this->assertInstanceOf(ResourceTest::class, $field->getRecord());
        $this->assertInstanceOf(PageTest::class, $field->getOwner());
        $this->assertEquals($field->getList()->first()->ID, $resource->ID);
        $this->assertEquals($field->getList(), $field->getManipulatedList());
        $this->assertEquals($resource->Title, $field->getColumnContent($field->getRecord(), 'Title'));

        $field->getList()->remove($resource);
        $html  = $field->FieldHolder();

        $this->assertContains('No resource test selected', $html->RAW());
        $this->assertEquals(ResourceTest::class, $field->getDataClass());
        $this->assertFalse($field->getRecord()->exists());
    }

    /**
     * @param $name
     * @return PageTest
     */
    protected function getPage($name)
    {
        return $this->objFromFixture(PageTest::class, $name);
    }

    /**
     * @param $name
     * @return ResourceTest
     */
    protected function getResource($name)
    {
        return $this->objFromFixture(ResourceTest::class, $name);
    }

    /**
     * @return HasOneSelectorField
     * @param  mixed               $page
     */
    protected function getField($page)
    {
        return HasOneSelectorField::create('Resource', 'Resource', $page, ResourceTest::class);
    }
}

class ResourceTest extends DataObject
{
    private static $db = [
        'Title' => 'Varchar',
    ];
}

class PageTest extends DataObject
{
    private static $db = [
        'Title' => 'Varchar',
    ];

    private static $has_one = [
        'Resource' => ResourceTest::class,
    ];
}
