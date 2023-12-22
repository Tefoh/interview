<?php

namespace Tests\Unit;


use App\Enums\PublicationStatusEnum;
use App\Models\Article;
use App\Models\User;
use App\Repositories\Article\ArticleRepository;
use App\Repositories\Article\ArticleRepositoryInterface;
use App\Services\Article\ArticleService;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ArticleService $articleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            ArticleRepositoryInterface::class,
            ArticleRepository::class
        );

        $this->articleService = app(ArticleService::class);
    }

    /**
     * @test
     */
    public function it_should_include_all_necessary_columns_for_article_and_author_on_get_list_builder()
    {
        $author = User::factory()->create();
        Article::factory()->create([
            'author_id' => $author->id,
        ]);
        $this->actingAs($author);

        $builder = $this->articleService->getArticleListQuery();

        $columns = array_keys($builder->get()->first()->toArray());

        $this->assertEquals([
            "id",
            "title",
            "content",
            "publication_at",
            "publication_status",
            "deleted_at",
            "author",
            "author_id",
        ], $columns);
    }

    /**
     * @test
     */
    public function it_should_include_author_name_with_join_on_get_list_builder()
    {
        $author = User::factory()->create();
        Article::factory()->create([
            'author_id' => $author->id,
        ]);
        $this->actingAs($author);

        $builder = $this->articleService->getArticleListQuery();

        $article = $builder->get()->first()->toArray();

        $this->assertEquals($author->name, $article['author']);
    }

    /**
     * @test
     */
    public function user_should_be_able_to_see_only_its_articles_on_get_list_builder()
    {
        $author = User::factory()->create();
        Article::factory()->create([
            'author_id' => $author->id,
        ]);
        Article::factory()->create();
        $this->actingAs($author);

        $builder = $this->articleService->getArticleListQuery();

        $totalGetArticle = $builder->count();

        $this->assertEquals(1, $totalGetArticle);
    }

    /**
     * @test
     */
    public function admin_should_be_able_to_see_all_articles_get_list_builder()
    {
        $admin = User::factory()->admin()->create();
        Article::factory()->create([
            'author_id' => $admin->id,
        ]);
        Article::factory()->create();
        $this->actingAs($admin);

        $builder = $this->articleService->getArticleListQuery();

        $totalGetArticle = $builder->count();

        $this->assertEquals(2, $totalGetArticle);
    }

    /**
     * @test
     */
    public function it_should_include_trashed_articles_get_list_builder()
    {
        $admin = User::factory()->create();
        $article = Article::factory()->create([
            'author_id' => $admin->id,
        ]);
        $article->delete();
        $this->actingAs($admin);

        $builder = $this->articleService->getArticleListQuery();

        $totalGetArticle = $builder->count();

        $this->assertEquals(1, $totalGetArticle);
    }

    /**
     * @test
     */
    public function get_all_should_return_a_collection_of_all_articles()
    {
        $admin = User::factory()->admin()->create();
        $articles = Article::factory()->count(5)->create();
        $this->actingAs($admin);

        $articles = $articles->load('author')->map->only([
            "id",
            "title",
            "content",
            "publication_at",
            "deleted_at",
        ]);

        $allArticles = $this->articleService->getAll()->map->only([
            "id",
            "title",
            "content",
            "publication_at",
            "deleted_at",
        ]);
        $this->assertEquals($articles->toArray(), $allArticles->toArray());
    }

    /**
     * @test
     */
    public function get_article_by_id_should_return_related_article()
    {
        User::factory()->create();
        $article = Article::factory()->create();

        $foundArticle = $this->articleService->getById(1);

        $this->assertEquals($article->id, $foundArticle->id);
    }

    /**
     * @test
     */
    public function get_article_by_id_should_throw_exception_if_article_not_exists()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->articleService->getById(1);
    }

    /**
     * @test
     */
    public function user_can_create_new_article()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => 'test title',
            'content' => 'test content',
        ];

        $this->articleService->saveArticle($data);

        $this->assertDatabaseHas(
            Article::class,
            array_merge($data, [
                'author_id' => $user->id,
                'publication_at' => null,
                'publication_status' => PublicationStatusEnum::DRAFT->value,
            ])
        );
    }

    /**
     * @test
     */
    public function admin_can_create_new_article()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $data = [
            'title' => 'test title',
            'content' => 'test content',
            'author_id' => User::factory()->create()->id,
            'publication_at' => null,
            'publication_status' => PublicationStatusEnum::DRAFT->value,
        ];

        $this->articleService->saveArticle($data);

        $this->assertDatabaseHas(
            Article::class,
            $data
        );
    }

    /**
     * @test
     */
    public function it_should_send_notification_if_saving_operation_failed()
    {
        $this->expectException(Halt::class);
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $data = [];
        try {
            $this->articleService->saveArticle($data);
        } catch (Halt $e) {

            $this->assertEquals(
                "Unable to save article data, contact IT for support!",
                session('filament.notifications')[0]['title']
            );

            throw $e;
        }
    }

    /**
     * @test
     */
    public function user_can_update_article()
    {
        $user = User::factory()->create();
        $article = Article::factory()->create([
            'author_id' => $user->id
        ]);
        $this->actingAs($user);

        $data = [
            'title' => 'updated',
            'content' => 'updated',
        ];

        $this->articleService->updateArticle($data, $article);

        $this->assertDatabaseHas(
            Article::class,
            $data
        );
    }

    /**
     * @test
     */
    public function admin_can_update_article()
    {
        $admin = User::factory()->admin()->create();
        $article = Article::factory()->create();
        $this->actingAs($admin);

        $data = [
            'title' => 'update title',
            'content' => 'update content',
            'author_id' => User::factory()->create()->id,
            'publication_at' => now(),
            'publication_status' => PublicationStatusEnum::PUBLISH->value,
        ];

        $this->articleService->updateArticle($data, $article);

        $this->assertDatabaseHas(
            Article::class,
            $data
        );
    }

    /**
     * @test
     */
    public function it_should_send_notification_if_update_operation_failed()
    {
        $this->expectException(Halt::class);
        $admin = User::factory()->admin()->create();
        $article = new Article();
        $this->actingAs($admin);

        $data = [];
        try {
            $this->articleService->updateArticle($data, $article);
        } catch (Halt $e) {

            $this->assertEquals(
                "Unable to update article data, contact IT for support!",
                session('filament.notifications')[0]['title']
            );

            throw $e;
        }
    }

    /**
     * @test
     */
    public function admin_can_delete_article()
    {
        $admin = User::factory()->admin()->create();
        $article = Article::factory()->create();
        $this->actingAs($admin);

        $this->assertNull(
            $article->deleted_at
        );

        $this->articleService->deleteById($article->id);

        $this->assertNotNull(
            $article->fresh()->deleted_at
        );
    }

    /**
     * @test
     */
    public function it_should_send_notification_if_delete_operation_failed()
    {
        $this->expectException(Halt::class);
        $article = Article::factory()->create();
        $article->delete();

        try {
            $this->articleService->deleteById($article->id);
        } catch (Halt $e) {

            $this->assertEquals(
                "Unable to delete article data, contact IT for support!",
                session('filament.notifications')[0]['title']
            );

            throw $e;
        }
    }

    /**
     * @test
     */
    public function admin_can_delete_many_article()
    {
        $admin = User::factory()->admin()->create();
        $deletableArticle = Article::factory()->count(3)->create();
        Article::factory()->count(2)->create();
        $this->actingAs($admin);

        $this->articleService->deleteByIds($deletableArticle->pluck('id')->toArray());

        $totalTrashed = Article::onlyTrashed()->count();
        $totalArticle = Article::query()->count();

        $this->assertEquals(3, $totalTrashed);
        $this->assertEquals(2, $totalArticle);
    }

    /**
     * @test
     */
    public function it_should_send_notification_if_delete_many_operation_failed()
    {
        $this->expectException(Halt::class);
        try {
            $this->articleService->deleteById([]);
        } catch (Halt $e) {

            $this->assertEquals(
                "Unable to delete article data, contact IT for support!",
                session('filament.notifications')[0]['title']
            );

            throw $e;
        }
    }

    /**
     * @test
     */
    public function admin_can_restore_article()
    {
        $admin = User::factory()->admin()->create();
        $article = Article::factory()->create();
        $this->actingAs($admin);
        $article->delete();

        $this->assertNotNull(
            $article->deleted_at
        );

        $this->articleService->restoreById($article->id);

        $this->assertNull(
            $article->fresh()->deleted_at
        );
    }

    /**
     * @test
     */
    public function it_should_send_notification_if_restore_operation_failed()
    {
        $this->expectException(Halt::class);
        $articleRepositoryMock = Mockery::mock(ArticleRepository::class);
        $articleRepositoryMock->shouldReceive('restore')->andThrow(Halt::class);

        $articleService = new ArticleService($articleRepositoryMock);

        $article = Article::factory()->create();

        try {
            $articleService->restoreById($article->id);
        } catch (Halt $e) {

            $this->assertEquals(
                "Unable to restore article data, contact IT for support!",
                session('filament.notifications')[0]['title']
            );

            throw $e;
        }
    }

    /**
     * @test
     */
    public function admin_can_restore_many_article()
    {
        $admin = User::factory()->admin()->create();
        $restoredArticles = Article::factory()->count(3)->create();
        Article::factory()->count(2)->create()->each->delete();
        $this->actingAs($admin);
        $restoredArticles->each->delete();

        $totalTrashed = Article::onlyTrashed()->count();

        $this->assertEquals(5, $totalTrashed);

        $this->articleService->restoreByIds($restoredArticles->pluck('id')->toArray());

        $totalTrashed = Article::onlyTrashed()->count();
        $totalArticle = Article::query()->count();

        $this->assertEquals(2, $totalTrashed);
        $this->assertEquals(3, $totalArticle);
    }

    /**
     * @test
     */
    public function it_should_send_notification_if_restore_many_operation_failed()
    {
        $this->expectException(Halt::class);
        try {
            $this->articleService->restoreById([]);
        } catch (Halt $e) {

            $this->assertEquals(
                "Unable to restore article data, contact IT for support!",
                session('filament.notifications')[0]['title']
            );

            throw $e;
        }
    }
}
