<?php

namespace App\Repositories\Article;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(protected Article $article)
    {}

    public function getAllBuilder(): Builder
    {
        return $this->article
            ->newQuery()
            ->select([
                'articles.id',
                'articles.title',
                'articles.content',
                'articles.publication_at',
                'articles.publication_status',
                'articles.deleted_at',
                DB::raw('users.name as author'),
                DB::raw('users.id as author_id')
            ])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->join('users', 'users.id', '=', 'articles.author_id');
    }

    public function setAuthor(Builder $builder): Builder
    {
        return $builder->where('author_id', auth()->id());
    }

    public function getAll(): Collection
    {
        return $this->getAllBuilder()
            ->get();
    }

    public function getById(int $id): Model|null
    {
        return $this->article
            ->newQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('id', $id)
            ->first();
    }

    public function save($data): Model
    {
        /** @var Article $article */
        $article = new $this->article;

        $article->title = $data['title'];
        $article->content = $data['content'];
        $article->author_id = $data['author_id'];
        $article->publication_at = $data['publication_at'];
        $article->publication_status = $data['publication_status'];

        $article->save();

        return $article->fresh();
    }

    public function update($data, $id): Model
    {
        /** @var Article $article */
        $article = $this->article->newQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->find($id);

        $article->title = $data['title'];
        $article->content = $data['content'];
        $article->author_id = $data['author_id'];
        $article->publication_at = $data['publication_at'];
        $article->publication_status = $data['publication_status'];

        $article->update();

        return $article->fresh();
    }

    public function delete(int $id): Model
    {
        $article = $this->article->newQuery()->find($id);
        $article->delete();

        return $article;
    }


    public function deleteMany(array $ids): bool
    {
        return $this->article
            ->newQuery()
            ->whereIn('id', $ids)
            ->delete();
    }

    public function restore(int $id): Model
    {
        /** @var Article $article */
        $article = $this->article
            ->newQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->find($id);
        $article->restore();

        return $article;
    }


    public function restoreMany(array $ids): bool
    {
        return $this->article
            ->newQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->whereIn('id', $ids)
            ->update(['deleted_at' => null]);
    }
}
