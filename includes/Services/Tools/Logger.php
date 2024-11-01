<?php

namespace Smartcat\Includes\Services\Tools;

class Logger
{
    private $wpdb;
    private $prefix;

    public function __construct(string $prefix = 'App')
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->prefix = $prefix;
    }

    public function totalLogs()
    {
        return $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->getTable()}");
    }

    public function getLogs($type = NULL, $query = NULL, $fromDate = NULL, $toDate = NULL, $offset = 0, $limit = 100, $orderBy = NULL, $order = 'DESC')
    {
        $sql = "SELECT * FROM {$this->getTable()} WHERE `id` IS NOT NULL ";

        if (!empty($type)) {
            $type = esc_sql($type);
            $sql .= "AND `type` = '$type' ";
        }

        if (!empty($query)) {
            $query = esc_sql("%$query%");
            $sql .= "AND (`message` LIKE '$query' OR `data` LIKE '$query') ";
        }

        if (!empty($fromDate)) {
            $fromDate = esc_sql($fromDate);
            $sql .= "AND `created_at` >= '$fromDate' ";
        }

        if (!empty($toDate)) {
            $toDate = esc_sql($toDate);
            $sql .= "AND `created_at` <= '$toDate' ";
        }

        $offset = esc_sql($offset);
        $limit = esc_sql($limit);

        if (!in_array(strtoupper($order), ['DESC', 'ASC'])) {
            $order = 'DESC';
        }

        $orderBy = $orderBy ?? 'created_at';

        $sql .= "ORDER BY `$orderBy` $order LIMIT $limit OFFSET $offset";

        $result = $this->wpdb->get_results($sql);

        return array_map(function ($item) {
            return [
                'id' => (int)$item->id,
                'type' => $item->type,
                'message' => $item->message,
                'data' => !is_null($item->data) ? json_decode($item->data, true) : $item->date,
                'stackTrace' => $item->exception,
                'createdAt' => (new \DateTime($item->created_at))->format('Y-m-d H:i:s')
            ];
        }, $result);
    }

    public function info($message, $data = [])
    {
        $this->addRow('info', $message, $data);
    }

    public function error($message, $data = [], $stackTrace = NULL, $exception = null)
    {
        sc_sentry()
            ->extra($data)
            ->exception($exception)
            ->message($message)
            ->send();

        $this->addRow('error', $message, $data, $stackTrace);
    }

    public function warn($message, $data = [], $stackTrace = NULL)
    {
        $this->addRow('warning', $message, $data, $stackTrace);
    }

    private function addRow($type, $message, $data = [], $stackTrace = NULL)
    {
        $prefix = $this->prefix ?? 'App';

        $this->wpdb->insert($this->getTable(), [
            'type' => $type,
            'message' => "[$prefix] $message",
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'exception' => $stackTrace,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function getTable(): string
    {
        return $this->wpdb->prefix . SMARTCAT_LOGS_TABLE_NAME;
    }
}