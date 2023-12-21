<?php

namespace App\Filament\Resources;

use App\Enums\PublicationStatusEnum;
use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Repositories\Article\ArticleRepositoryInterface;
use App\Services\Article\ArticleServiceInterface;
use App\Tables\Columns\PublicationDate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        $articleRepository = app(ArticleRepositoryInterface::class);

        return $articleRepository->getAllBuilder();
    }

    public static function resolveRecordRouteBinding(int | string $key): ?Model
    {
        $articleService = app(ArticleServiceInterface::class);

        return $articleService->getById($key);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->minLength(5)
                            ->maxLength(255)
                            ->string(),
                        Forms\Components\Select::make('author_id')
                            ->relationship(name: 'author', titleAttribute: 'name')
                            ->visible(auth()->user()->isAdmin())
                            ->required()
                            ->exists('users', 'id'),
                        Forms\Components\Select::make('publication_status')
                            ->options([
                                PublicationStatusEnum::DRAFT->value => 'Draft',
                                PublicationStatusEnum::PUBLISH->value => 'Published',
                            ])
                            ->visible(auth()->user()->isAdmin())
                            ->default(PublicationStatusEnum::DRAFT->value)
                            ->label('Status')
                            ->required()
                            ->enum(PublicationStatusEnum::class),
                        Forms\Components\DatePicker::make('publication_at')
                            ->visible(auth()->user()->isAdmin())
                            ->label('Publication date')
                            ->date('d/m/Y'),
                        Forms\Components\RichEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->minLength(10)
                            ->string()
                            ->columnSpan('full'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('author')->label('Author'),
                Tables\Columns\TextColumn::make('publication_status')->label('Status'),
                PublicationDate::make('publication_at')->label('Publication date'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
