<?php

namespace App\Constants;

class ImportRequests
{
    const NEW = 'new';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED = 'completed';
    const ERROR = 'error';

    const STATUS_LABELS = [
        self::NEW,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::ERROR,
    ];

    const REQUIRED_CSV_HEADERS = [
        'title',
        'content',
        'category'
    ];

    const IMPORT_REQUESTS_CSV_FILES_DIRECTORY = 'import_requests/csv_files';
    const ERRORS_CSV_FILES_DIRECTORY = 'errors/csv_files';

    const CHUNK_SIZE = 500;

    const PAGINATION_PER_PAGE = 10;
}
