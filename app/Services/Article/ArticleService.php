<?php

namespace App\Services\Article;

use App\Enums\PublicationStatusEnum;
use App\Models\Article;
use App\Repositories\Article\ArticleRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ArticleService implements ArticleServiceInterface
{
    public function __construct(protected readonly ArticleRepositoryInterface $articleRepository)
    { }

    public function getArticleListQuery(): Builder
    {
        return $this->articleRepository
            ->getAllBuilder()
            ->when(
                ! auth()->user()?->isAdmin(),
                fn (Builder $builder) => $builder->where('author_id', auth()->id())
            );
    }

    public function getAll(): Collection
    {
        return $this->articleRepository->getAll();
    }

    public function getById(int $id): Article
    {
        /** @var Article $article */
        $article = $this->articleRepository->getById($id);

        if (! $article) {
            Log::info('error-at-get-article', [
                'message' => 'Article not found with id of: ' . $id
            ]);
            throw new ModelNotFoundException('Article not found!');
        }

        return $article;
    }

    public function saveArticle(array $data): Article
    {
        try {
            if (! auth()->user()->isAdmin()) {
                $data['author_id'] = auth()->id();
                $data['publication_status'] = PublicationStatusEnum::DRAFT;
                $data['publication_at'] = null;
            }

            /** @var Article $article */
            $article = $this->articleRepository->save($data);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('error-at-save-article', [
                'message' => $exception->getMessage()
            ]);

            throw new InvalidArgumentException('Unable to save article data');
        }

        return $article;
    }

    public function updateArticle($data, Article $article): Article
    {
        DB::beginTransaction();

        try {
            $data = array_merge($article->toArray(), $data);
            /** @var Article $article */
            $article = $this->articleRepository->update($data, $article->id);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('error-at-update-article', [
                'message' => $exception->getMessage()
            ]);

            throw new InvalidArgumentException('Unable to update article data');
        }

        DB::commit();

        return $article;

    }

    public function deleteById($id): Article
    {
        DB::beginTransaction();

        try {
            /** @var Article $article */
            $article = $this->articleRepository->delete($id);

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('error-at-delete-article', [
                'message' => $exception->getMessage()
            ]);

            throw new InvalidArgumentException('Unable to delete article data');
        }

        DB::commit();

        return $article;
    }
}
