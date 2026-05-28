<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookDetailResource extends JsonResource
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
            'reviews'        => $this->reviews->map(fn($r) => [
                'id'         => $r->id,
                'user_name'  => $r->user->name,
                'rating'     => $r->rating,
                'comment'    => $r->comment,
                'created_at' => $r->created_at,
            ]),
            'created_at'     => $this->created_at,
        ];
    }
}
