<?php

namespace App\Services;

use App\Constants\Errors;
use App\Exceptions\DatabaseException;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class CategoriesService
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    const string CATEGORY_SEPARATOR = '-';


    /**
     * @throws DatabaseException
     */
    public function getOrStoreCategoriesFromCategoryString(string $categoriesStr): array
    {
        $categoriesNames = $this->filterCategoriesString($categoriesStr);
        $existingCategories = $this->categoryRepository->findCategoriesInArr($categoriesNames);

        $newCategoriesNames = array_diff($categoriesNames, $existingCategories->pluck('name')->toArray());

        if (empty($newCategoriesNames)) {
            return $existingCategories->toArray();
        }

        $newCategories = $this->storeBatchCategories($newCategoriesNames);

        return array_merge($existingCategories->toArray(), $newCategories);

    }

    /**
     * @throws DatabaseException
     */
    private function storeBatchCategories(array $categoriesNames): array
    {
        $categories = array_map(function ($category) {
            return [
                'id' => (string)Str::ulid(),
                'name' => $category,
            ];
        }, $categoriesNames);

        $res = $this->categoryRepository->insertMany($categories);
        if (!$res) {
            throw new DatabaseException(Errors::DATABASE_ERROR, "Failed to store categories: " . implode(', ', $categoriesNames));
        }

        return $categories;
    }

    private function filterCategoriesString(string $categories): array
    {
        $categories = explode(self::CATEGORY_SEPARATOR, $categories);
        return array_filter($categories, function ($category) {
            return !empty(trim($category));
        });
    }

}
