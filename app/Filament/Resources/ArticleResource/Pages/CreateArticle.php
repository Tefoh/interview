<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Services\Article\ArticleServiceInterface;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $articleService = app(ArticleServiceInterface::class);

        return $articleService->saveArticle($data);
    }
}
