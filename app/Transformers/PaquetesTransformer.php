<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class PaquetesTransformer extends BaseTransformer
{
    /**
     * Transform the resource into an array.
     *
     * @param mixed $resource
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $resource): array
    {
        return [
            // Add your transformation logic here
        ];
    }
}
