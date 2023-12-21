<?php

namespace App\Services\Article;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;

interface ArticleServiceInterface
{
    public function getAll(): Collection;
    public function getById(int $id): Article;
    public function saveArticle(array $data): Article;
    public function updateArticle($data, $id): Article;
    public function deleteById($id): Article;
}
