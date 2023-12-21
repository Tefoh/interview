<?php

namespace App\Models;

use App\Enums\PublicationStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'author_id',
        'publication_at',
        'publication_status',
    ];

    protected $casts = [
        'publication_at' => 'date',
        'publication_status' => PublicationStatusEnum::class,
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected function authorName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->author->name
        );
    }
}