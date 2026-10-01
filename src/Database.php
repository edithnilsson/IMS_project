<?php
class Database
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Run a prepared query with bound params.
     * Returns array of associative rows.
     */
    public function query(string $sql, string $types = '', array $params = []): array
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . $this->conn->error);
        }
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function connection(): mysqli
    {
        return $this->conn;
    }
}