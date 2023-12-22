<?php

namespace App\Filament\Resources\ArticleResource\Actions;

use App\Models\Article;
use App\Services\Article\ArticleServiceInterface;
use Closure;
use Filament\Tables\Actions\DeleteBulkAction;

class ArticleDeleteBulkAction extends DeleteBulkAction
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function process(?Closure $default, array $parameters = []): mixed
    {
        $articleService = app(ArticleServiceInterface::class);

        /** @var Article $article */
        $articles = $this->getRecords();

        return $articleService->deleteByIds(
            $articles->pluck('id')->toArray()
        );
    }
}
