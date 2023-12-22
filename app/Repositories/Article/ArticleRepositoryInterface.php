<?php

namespace App\Repositories\Article;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ArticleRepositoryInterface
{
    public function getAllBuilder(): Builder;

    public function getAll(): Collection;

    public function getById(int $id): Model|null;

    public function save(array $data): Model;

    public function update(array $data, int $id): Model;

    public function delete(int $id): Model;

    public function deleteMany(array $ids): bool;

    public function restore(int $id): Model;

    public function restoreMany(array $ids): bool;
}
