<?php

namespace Smartcat\Includes\Services\App\Includes;

trait TranslationRequestFinder
{
    protected function buildSqlQuery($limit = 20, $offset = 0, $search = null, $orderBy = null, $order = 'DESC', $filters = []): string
    {
        $query = "SELECT distinct `translation_request_id` FROM {$this->table()} WHERE `translation_request_id` IS NOT NULL ";

        if (!empty($search)) {
            $search = esc_sql($search);
            $queryForSelectPostsByTitle = "SELECT `ID` FROM {$this->wpdb->posts} WHERE `post_title` LIKE '%$search%'";
            $postIds = $this->db()->get_col($queryForSelectPostsByTitle);
            $query .= "AND `post_id` IN (" . implode(', ', $postIds) . ") ";
        }

        foreach ($filters as $key => $value) {
            if (!empty($value) && $value !== 'all') {
                $v = esc_sql($value);
                if ($key === 'created_at') {
                    $query .= "AND DATE(`$key`) = '$v' ";
                } else {
                    $query .= "AND `$key` = '$v' ";
                }
            }
        }

        if (!empty($orderBy)) {
            $query .= "ORDER BY `$orderBy` $order ";
        }

        if (!is_null($limit) && !is_null($offset)) {
            $query .= "LIMIT $limit OFFSET $offset";
        }

        return $query;
    }
}