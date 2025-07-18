<?php

namespace App\Repositories;

use App\Models\News;
use App\Models\NewsCategory;

class NewsCategoriesRepository
{
    public function insertManyNewsCategories(array $newsCategoriesArray): bool
    {
        if(empty($newsCategoriesArray)) {
            return false;
        }

        return NewsCategory::insert($newsCategoriesArray);
    }

}
