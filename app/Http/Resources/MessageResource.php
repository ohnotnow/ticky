<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isUser = $this->resource->isFromUser();

        return [
            'from' => $isUser ? 'user' : 'assistant',
            'content' => $isUser ? $this->resource->content : null,
            'raw_response' => $isUser ? null : $this->resource->content,
            'recommendations' => $this->resource->recommendationsForView(),
            'created_at' => $this->resource->created_at,
        ];
    }
}
