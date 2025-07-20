<?php

namespace App\Services;

use App\Exceptions\DatabaseException;
use App\Repositories\NewsCategoriesRepository;
use App\Repositories\NewsRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class NewsService
{
    private CategoriesService $categoriesService;
    private DatabaseService $databaseService;
    private NewsRepository $newsRepository;
    private NewsCategoriesRepository $newsCategoriesRepository;

    public function __construct(
        CategoriesService        $categoriesService,
        DatabaseService          $databaseService,
        NewsRepository           $newsRepository,
        NewsCategoriesRepository $newsCategoriesRepository
    )
    {
        $this->categoriesService = $categoriesService;
        $this->databaseService = $databaseService;
        $this->newsRepository = $newsRepository;
        $this->newsCategoriesRepository = $newsCategoriesRepository;
    }

    /**
     * @throws DatabaseException
     */
    public function storeNewsChunk(array $rows, array $parseErrors): array
    {
        $errors = $parseErrors;
        $newsRows = [];
        $newsCategoriesRecords = [];
        foreach ($rows as $colNumber => $row) {
            $validationResult = $this->validateNewsRecord($row);
            if ($validationResult !== true) {
                $errors[] = ['column_number' => $colNumber, 'error' => $validationResult];
                continue;
            }
            $row['id'] = (string) Str::ulid();


            $categories = $this->categoriesService->getOrStoreCategoriesFromCategoryString($row['category']);

            foreach ($categories as $category) {
                $newsCategoriesRecords[] = [
                    'news_id' => $row['id'],
                    'category_id' => $category['id'],
                ];
            }

            $newsRows[] = $row;
        }

        $this->storeBatchNews($newsRows, $newsCategoriesRecords);

        return $errors;
    }

    private function validateNewsRecord(array $row): string|bool
    {
        $validator = Validator::make($row, [
            'title' => 'required|max:2048',
            // Assuming 'content' is a long text field, adjust max length as needed
            'content' => 'required|max:10000',
            'url' => 'nullable|url|max:2048',
            'category' => 'required|max:2048',
        ]);

        if ($validator->fails()) {
            return implode("\n", array_map(
                fn($error) => implode("\n", $error),
                $validator->errors()->toArray()
            ));
        }
        return true;
    }

    private function storeBatchNews($newsRows, $newsCategoriesRows): void
    {
        $this->databaseService->transaction(function () use ($newsRows, $newsCategoriesRows) {
            $this->newsRepository->insertManyNews($newsRows);
            $this->newsCategoriesRepository->insertManyNewsCategories($newsCategoriesRows);
        });
    }

}
