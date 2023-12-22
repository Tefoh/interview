<?php

namespace App\Services\Article;

use App\Enums\PublicationStatusEnum;
use App\Models\Article;
use App\Repositories\Article\ArticleRepositoryInterface;
use Exception;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                fn (Builder $builder) => $this->articleRepository->setAuthor($builder)
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

            Notification::make()
                ->title('Unable to save article data, contact IT for support!')
                ->danger()
                ->send();
            throw new Halt();
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

            Notification::make()
                ->title('Unable to update article data, contact IT for support!')
                ->danger()
                ->send();
            throw new Halt();
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

            Notification::make()
                ->title('Unable to delete article data, contact IT for support!')
                ->danger()
                ->send();
            throw new Halt();
        }

        DB::commit();

        return $article;
    }

    public function deleteByIds(array $ids): bool
    {
        DB::beginTransaction();

        try {
            $result = $this->articleRepository->deleteMany($ids);

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('error-at-delete-article', [
                'message' => $exception->getMessage()
            ]);

            Notification::make()
                ->title('Unable to delete articles data, contact IT for support!')
                ->danger()
                ->send();
            throw new Halt();
        }

        DB::commit();

        return $result;
    }

    public function restoreById($id): Article
    {
        DB::beginTransaction();

        try {
            /** @var Article $article */
            $article = $this->articleRepository->restore($id);

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('error-at-restore-article', [
                'message' => $exception->getMessage()
            ]);

            Notification::make()
                ->title('Unable to restore article data, contact IT for support!')
                ->danger()
                ->send();
            throw new Halt();
        }

        DB::commit();

        return $article;
    }

    public function restoreByIds(array $ids): bool
    {
        DB::beginTransaction();

        try {
            $result = $this->articleRepository->restoreMany($ids);

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('error-at-restore-article', [
                'message' => $exception->getMessage()
            ]);

            Notification::make()
                ->title('Unable to restore articles data, contact IT for support!')
                ->danger()
                ->send();
            throw new Halt();
        }

        DB::commit();

        return $result;
    }
}
