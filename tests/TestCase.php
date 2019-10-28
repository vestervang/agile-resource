<?php


namespace Vestervang\AgileResource\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp() : void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        $this->setUpRoutes($this->app);
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('cache.prefix', 'tests---');
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
        });

        $app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('body');
            $table->bigInteger('author_id');
        });

        $user = User::create([
            'name' => 'Test',
            'email' => 'test@email.com'
        ]);

        $user->posts()->create([
            'title' => 'dicta eum natus',
            'body' => 'Voluptatibus doloremque optio tenetur neque ex exercitationem omnis. Non expedita vel impedit soluta ut et. Consectetur quo voluptatem quibusdam consequuntur est ut voluptatem. Quam voluptatem quos ad. Qui reprehenderit rem quod possimus cumque dolorum. Illo et voluptas magni. Quidem quas sit unde aut labore enim. Aut et dolorum reprehenderit delectus laborum. Enim cumque dolorum qui sapiente consectetur voluptatum. Quibusdam praesentium optio esse fuga quaerat repellat. Rem repellendus eos commodi voluptatem. Quae delectus maiores ut voluptatem sed autem aperiam nam. Voluptatem hic repudiandae dolore dolore porro ipsum nam dolorem. Corporis error rerum deleniti labore distinctio quibusdam. Est dolore accusantium libero ipsam qui fuga. Vel et corrupti voluptatibus.',
        ]);

        $user->posts()->create([
            'title' => 'rem qui facere',
            'body' => 'Et et expedita dolor omnis adipisci earum. Debitis velit molestiae quibusdam non aliquam fugit aperiam. Voluptas sit cum aspernatur nobis corporis aut voluptatum. Est aut repudiandae quo velit. Et et et quia ab sed sint. Ipsa est molestias ut quae autem error. Debitis et animi eum quis fugiat aut. Sequi fugit cumque sed veniam. Voluptas aut eum et illo. Voluptas facere harum magnam. Enim ut qui ut et deserunt. Odio magnam quo est quis atque odio velit ipsam. Incidunt qui est occaecati ipsam ducimus et voluptatem. Quo necessitatibus ipsam sunt quod consectetur nesciunt. Totam accusamus reprehenderit sequi nam. Est modi ipsam dolores dolorem perferendis aperiam ipsa inventore. Vel nostrum ea labore asperiores. Occaecati doloremque eligendi fugiat ad fugit dignissimos cumque. Qui est cumque ut quasi enim. Molestiae voluptate quibusdam nihil natus adipisci. Voluptate cumque explicabo maiores distinctio deleniti dolorem. Deleniti omnis aliquid quo. Ut quia animi earum voluptas animi. Expedita harum accusamus vitae odit. Similique rerum repudiandae provident reprehenderit fugiat et laudantium voluptatem. Dolore praesentium voluptatem ut consequuntur omnis qui. Quis reiciendis beatae voluptatem ut facere at aut. Nesciunt velit accusamus modi id voluptatem exercitationem harum. Et id velit expedita autem occaecati delectus et dolorem. Quae ut commodi corrupti pariatur porro maxime aliquam debitis.',
        ]);

        $user->posts()->create([
            'title' => 'totam at reiciendis',
            'body' => 'Dolor tempore iste qui et. Accusantium quibusdam nam dolorum beatae aperiam architecto. Voluptatum error numquam est ea repellat ea dolor. Vel est sit sint consequatur. Tempora molestiae et ipsum quia vel id hic hic. Soluta eveniet maxime minus odit iure fugiat id quos. Quam ipsa autem expedita cumque voluptatibus est consequatur. Ad voluptate odit eos possimus numquam dolores. Illum quia repellat placeat dicta ut velit labore. Nam dolore facere veritatis est. Vel dolores quos autem. Sit ab quibusdam sunt et mollitia quae distinctio. Voluptatum est voluptatibus assumenda error voluptas labore quia omnis. Ad optio fugiat sunt explicabo et. Et ut dignissimos assumenda. Sint optio esse est vel distinctio perspiciatis corporis. Magnam ab omnis laborum voluptas. Cumque soluta eaque rem quod illum sit cupiditate. Autem optio fugit adipisci doloremque in eos dolores. Nulla vitae esse minima hic dicta ut. Natus illum quaerat sed repellendus saepe fugiat porro.',
        ]);

    }

    protected function setUpRoutes($app)
    {
        Route::any('/posts/{id}/author', ['as' => 'post.author']);
        Route::any('/user/{id}/posts', ['as' => 'user.posts']);
    }
}
