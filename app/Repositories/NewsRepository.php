<?php

namespace App\Repositories;

use App\Models\News;

class NewsRepository
{
    public function insertManyNews(array $newsArray): bool
    {
        if(empty($newsArray)) {
            return false;
        }
        $newsArray = array_map(function($news) {
            return [
                'id' => $news['id'],
                'title' => $news['title'],
                'content' => $news['content'],
                'url' => $news['url'] ?? null,
            ];
        }, $newsArray);
        \Log::info('Inserting news records: ' . json_encode($newsArray));
        return News::insert($newsArray);
    }

}
