<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TrendResultStore
{
    public function put(array $result): string
    {
        $id = (string) Str::uuid();
        $result['id'] = $id;

        Cache::put($this->key($id), $result, now()->addMinutes(30));

        return $id;
    }

    public function get(string $id): ?array
    {
        $result = Cache::get($this->key($id));

        return is_array($result) ? $result : null;
    }

    private function key(string $id): string
    {
        return 'socialinsight:trend-result:'.$id;
    }
}
