<?php

namespace App\Filament\Resources\ArticleResource\Actions;

use App\Models\Article;
use App\Services\Article\ArticleServiceInterface;
use Closure;
use Filament\Actions\DeleteAction;

class ArticleDeleteAction extends DeleteAction
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function process(?Closure $default, array $parameters = []): mixed
    {
        $articleService = app(ArticleServiceInterface::class);

        /** @var Article $article */
        $article = $this->getRecord();

        return $articleService->deleteById($article->id);
    }
}
