<?php


namespace Vestervang\AgileResource\Test;

use stdClass;
use Vestervang\AgileResource\Resources\Resource;
use Vestervang\AgileResource\Test\Models\User;

class StdObjectTest extends TestCase
{
    /** @test */
    public function genericObjectNoModels()
    {
        $obj = new stdClass();

        $obj->bear = 'claw';
        $obj->muffin = 'sesame snaps';

        $actual = (new Resource($obj))->toArray(request());

        $expected = [
            'bear' => 'claw',
            'muffin' => 'sesame snaps'
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /** @test */
    public function genericObjectNoModelsFiltered()
    {
        $obj = new stdClass();

        $obj->bear = 'claw';
        $obj->muffin = 'sesame snaps';

        $actual = (new Resource($obj, ['bear']))->toArray(request());

        $expected = [
            'bear' => 'claw',
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /** @test */
    public function genericObjectNested()
    {
        $obj1 = new stdClass();
        $obj1->bear = 'claw';
        $obj1->muffin = 'sesame snaps';

        $obj2 = new stdClass();
        $obj2->cupcake = 'chups';

        $obj1->gingerbread = $obj2;

        $actual = (new Resource($obj1))->toArray(request());

        $expected = [
            'bear' => 'claw',
            'muffin' => 'sesame snaps',
            'gingerbread' => [
                'cupcake' => 'chups'
            ],
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /** @test */
    public function genericObjectNestedFiltered()
    {
        $obj1 = new stdClass();
        $obj1->bear = 'claw';
        $obj1->muffin = 'sesame snaps';

        $obj2 = new stdClass();
        $obj2->cupcake = 'chups';
        $obj2->soufflé = 'gummies';

        $obj1->gingerbread = $obj2;

        $actual = (new Resource($obj1, [
            'muffin',
            'gingerbread' => []
        ]))->toArray(request());

        $expected = [
            'muffin' => 'sesame snaps',
            'gingerbread' => [
                'cupcake' => 'chups',
                'soufflé' => 'gummies',
            ],
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /** @test */
    public function genericObjectWithModel()
    {
        $obj = new stdClass();
        $obj->bear = 'claw';
        $obj->muffin = 'sesame snaps';
        $obj->user = User::find(1);

        $actual = (new Resource($obj))->toArray(request());

        $expected = [
            'bear' => 'claw',
            'muffin' => 'sesame snaps',
            'user' => [
                'Name' => 'Test',
                'Email' => 'test@email.com',
                'Posts' => 'http://resource.local/user/1/posts'
            ]
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }
}
