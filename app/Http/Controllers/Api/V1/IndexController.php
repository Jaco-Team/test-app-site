<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\IndexResource;
use Illuminate\Http\Request;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    public function index(): IndexResource
    {
        //$collection = collect([1, 2, 3]);

        $arr = [
            [
                'id' => 1,
                'name' => 'One',
                'date' => '2024-01-01'
            ],
            [
                'id' => 2,
                'name' => 'Two',
                'date' => '2024-04-05'
            ],
            [
                'id' => 3,
                'name' => 'Tree',
                'date' => '2024-02-02'
            ],
            [
                'id' => 4,
                'name' => 'Four',
                'date' => '2024-09-09'
            ],
        ];

        Collection::macro('toUpper', function () {
            return $this->map(function (string $value) {
                return Str::upper($value);
            });
        });

        $collection = collect(['first', 'second']);

        $upper = $collection->toUpper();

        return new IndexResource( $upper );
    }

    public function get_last(): array
    {
        return [
            'id' => 1,
            'name' => 'One',
            'date' => '2024-01-01'
        ];


    }
}
