<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Collection;

class CategoryRepository
{
    public function findCategoriesInArr(array $categoriesNames): Collection
    {
        if(empty($categoriesNames)) {
            return collect();
        }

        return Category::whereIn('name', $categoriesNames)->get();
    }

    public function insertMany(array $categories): bool
    {
        if(empty($categories)) {
            return false;
        }
        $categories = array_map(function($category) {
            return [
                'id' => $category['id'],
                'name' => $category['name'],
            ];
        }, $categories);

        return Category::insert($categories);

    }
}
