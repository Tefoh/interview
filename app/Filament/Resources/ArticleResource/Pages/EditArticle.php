<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use App\Services\Article\ArticleServiceInterface;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $articleService = app(ArticleServiceInterface::class);

        /** @var Article $record */
        return $articleService->updateArticle($data, $record);
    }

    protected function getHeaderActions(): array
    {
        return [
            ArticleResource\Actions\ArticleDeleteAction::make(),
        ];
    }
}
