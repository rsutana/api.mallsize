<?php

namespace App\Http\Resources;

use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every active lookup option list, keyed by table name.
 */
class LookupsResource extends JsonResource
{
    /**
     * @return array<string, list<array{code: string, label: string}>>
     */
    public function toArray(Request $request): array
    {
        $out = [];

        foreach (array_keys(Lookups::TABLES) as $table) {
            $out[$table] = Lookups::options($table);
        }

        return $out;
    }
}
