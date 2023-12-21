<?php

namespace App\Services\Article;

use App\Enums\PublicationStatusEnum;
use App\Models\Article;
use App\Repositories\Article\ArticleRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ArticleService implements ArticleServiceInterface
{
    public function __construct(protected readonly ArticleRepositoryInterface $articleRepository)
    { }

    public function getAll(): Collection
    {
        return $this->articleRepository->getAll();
    }

    public function getById(int $id): Article
    {
        /** @var Article $article */
        $article = $this->articleRepository->getById($id);

        if (! $article) {
            Log::info('error-at-update-article', [
                'message' => 'Article not found with id of: ' . $id
            ]);
            throw new ModelNotFoundException('Article not found!');
        }

        return $article;
    }

    public function saveArticleData(array $data): Article
    {
        $this->validateData($data);

        try {
            /** @var Article $article */
            $article = $this->articleRepository->save($data);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('error-at-update-article', [
                'message' => $exception->getMessage()
            ]);

            throw new InvalidArgumentException('Unable to update article data');
        }

        return $article;
    }

    public function updateArticle($data, $id): Article
    {
        $this->validateData($data);

        DB::beginTransaction();

        try {
            /** @var Article $article */
            $article = $this->articleRepository->update($data, $id);
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

    private function validateData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'author_id' => ['required', 'integer'],
            'publication_at' => ['nullable', 'date:Y-m-d'],
            'publication_status' => ['required', Rule::enum(PublicationStatusEnum::class)],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
