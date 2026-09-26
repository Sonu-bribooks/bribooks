<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ImportLiveBookPageDataUat {
    private function _importLiveBookPageDataUat($rows = [], $map = [], $job_id = 0) {
        $this->load->model('book/Page_model', 'page_model');
        $skipped = 0;
        $uploaded = 0;
        $books_created = 0;

        log_kb([
            'IMPORT_BOOK_PAGE_START' => [
                'rows'   => count($rows),
                'map'    => $map,
                'job_id' => $job_id
            ]
        ]);

        /*
         * ---------------------------------------------------------
         * Prepare rows according to map
         * ---------------------------------------------------------
         */

        $theme_options      = [452, 453, 352, 390];
        $grouped_pages_by_book = [];
        foreach ($rows as $index => $row) {

            self::_updateCounter($job_id);

            $data = array_combine(
                array_keys($map),
                array_map(function ($i) use ($row) {
                    return @$row[$i];
                }, array_values($map))
            );

            if (empty($data['book_id'])) {
                self::_updateCounter($job_id, true);

                $skipped++;
                continue;
            }

            $original_book_id = $data['book_id'];
            // Handle texts formatting condition
            $raw_texts = $data['texts'] ?? '';
            $decoded = json_decode($raw_texts, true);
            
            if (is_array($decoded)) {
                // Agar pehle se hi JSON array format me hai ["..."], to waise hi rakhein
                $formatted_texts = $raw_texts;
            } else {
                // Agar plain text hai, to use ["<p>...</p>"] format me convert karein
                $formatted_texts = json_encode(['<p>' . $raw_texts . '</p>']);
            }

            $page_data = [
                'theme_id'         => $theme_options[array_rand($theme_options)],
                'custom_theme_id'  => isset($data['custom_theme_id']) ? $data['custom_theme_id'] : 0,
                'texts'            => $formatted_texts ?? '',
                'sort_order'       => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
                'status'           => 1,
                'date_added'       => date('Y-m-d H:i:s'),
                'date_modified'    => date('Y-m-d H:i:s'),
                '_deleted'         => 0,
                'date_deleted'     => null,
            ];

            // Grouping pages by their original book_id from sheet
            $grouped_pages_by_book[$original_book_id][] = $page_data;

        }

        /*
         * ---------------------------------------------------------
         * Step 2: Create New Book for each group and Insert Pages
         * ---------------------------------------------------------
         */
        // Category options array for random selection (ELT equivalent in PHP)
        $category_options   = [53, 46, 18, 46, 54, 31, 20];
        $genre_options      = [55, 61, 59];

        foreach ($grouped_pages_by_book as $old_book_id => $pages) {

            // Book data array prepare kar rahe hain
            $book_data = [
                'unique_id'         => 0, // Pehle 0 rakhenge, insert ke baad update hoga
                'version'           => 0,
                'site_id'           => 1,
                'user_id'           => 166117,
                'temp_user_id'      => '',
                'category_id'       => $category_options[array_rand($category_options)],
                'cover_id'          => 0,
                'user_cover_id'     => 0,
                'reviewer_id'       => 0,
                'genre_id'          => $genre_options[array_rand($genre_options)],
                'isbn'              => '',
                'isbn_country_code' => null,
                'name'              => '',
                'author_name'       => '',
                'author_bio'        => '',
                'author_image'      => '',
                'cover_image'       => '',
                'featured'          => 0,
                'back_color'        => '#9a388f',
                'slug'              => '',
                'preview_token'     => '',
                'status'            => 0,
                'editing'           => 1,
                'can_be_published'  => 0,
                'amazon_url'        => '',
                'amazon_price'      => 0.00,
                'views'             => 0,
                'reading_count'     => 0,
                'reviewer_rating'   => 0,
                'date_published'    => null,
                'date_approved'     => null,
                'date_added'        => date('Y-m-d H:i:s'),
                'date_modified'     => date('Y-m-d H:i:s'),
                '_deleted'          => 0,
                'date_deleted'      => null
            ];

            // 1. Book model ke through insert karein
            $new_book_id = $this->book_model->add($book_data);

            if ($new_book_id) {                
                $books_created++;

                log_kb([
                    'IMPORT_BOOK_PAGE_data' => [
                        'pages_count'   => count($pages),
                        // 'pages'         => $pages
                    ]
                ]);
                // Step 3: Replace sheet's book_id with new book id and insert pages
                foreach ($pages as $page) {
                    $page['book_id'] = $new_book_id; // Replacing with new book ID

                    $page_id = $this->page_model->add($page);
                    if ($page_id) {
                        $uploaded++;
                    }
                }
            } else {
                // Agar book create nahi ho paayi toh unhe skipped ya fail count kar sakte hain
                $skipped += count($pages);
            }
        }


        /*
         * ---------------------------------------------------------
         * Complete job
         * ---------------------------------------------------------
         */

        self::_updateCompleted($job_id);

        log_kb([
            'IMPORT_BOOK_PAGE_END' => [
                'rows'          => count($rows),
                'uploaded'      => $uploaded,
                'skipped'       => $skipped,
                'job_id'        => $job_id,
                'books_created' => $books_created,
            ]
        ]);

        return [
            'skipped'       => $skipped,
            'uploaded'      => $uploaded,
            'books_created' => $books_created,
        ];
    
    }
}