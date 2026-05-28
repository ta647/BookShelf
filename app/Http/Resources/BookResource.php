<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'author'         => $this->author,
            'isbn'           => $this->isbn,
            'published_date' => $this->published_date,
            'description'    => $this->description,
            'image_url'      => $this->image_url,
            'genres'         => $this->genres->map(fn($g) => ['id' => $g->id, 'name' => $g->name]),
            'avg_rating'     => $this->reviews_avg_rating ? round($this->reviews_avg_rating, 2) : null,
            'reviews_count'  => $this->reviews_count ?? 0,
            'created_at'     => $this->created_at,
        ];
    }
}
