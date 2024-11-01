<?php

namespace Smartcat\Includes\Services\App\Includes;

trait DocumentsQueryBuilder
{
    protected $wpdb;

    /** @var array */
    protected $where = [];

    protected $limit = null;

    protected function insert(array $data)
    {
        $this->initDB();

        $this->wpdb->insert($this->table(), $data);
    }

    protected function get()
    {
        $this->initDB();

        $result = $this->wpdb->get_results(
            $this->wpdb->prepare(
                $this->getSelectQuery(),
                $this->getSelectData()
            )
        );

        $this->clearBuilderState();

        return $result;
    }

    private function getSelectQuery(): string
    {
        $query = "SELECT * FROM {$this->table()}";

        foreach ($this->where as $index => $item) {
            $whereOrAnd = $index === 0 ? 'WHERE' : 'AND';
            $query .= " $whereOrAnd `{$item['column']}` = {$item['bean']}";
        }

        if (!is_null($this->limit)) {
            $query .= " LIMIT $this->limit";
        }

        return $query;
    }

    private function getSelectData(): array
    {
        return array_map(function ($item) {
            return $item['value'];
        }, $this->where);
    }

    private function clearBuilderState()
    {
        $this->where = [];
    }

    /**
     * @param string $column
     * @param string $bean
     * @param $value
     * @return static
     */
    protected function addWhere(string $column, string $bean, $value)
    {
        $this->where[] = [
            'column' => $column,
            'bean' => $bean,
            'value' => $value
        ];

        return $this;
    }

    protected function limit(int $limit = null)
    {
        $this->limit = $limit;

        return $this;
    }

    protected function update(array $data, array $where)
    {
        $this->initDB();

        $this->wpdb->update($this->table(), $data, $where);
    }

    protected function delete($where)
    {
        $this->initDB();

        $this->wpdb->delete($this->table(), $where);
    }

    protected function distinct($column)
    {
        $this->initDB();

        return $this->wpdb->get_col("SELECT distinct $column FROM {$this->table()}");
    }

    /**
     * @return \wpdb
     */
    public function db()
    {
        $this->initDB();

        return $this->wpdb;
    }

    private function table(): string
    {
        return $this->wpdb->prefix . SMARTCAT_DOCUMENTS_TABLE_NAME;
    }

    protected function initDB()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
}