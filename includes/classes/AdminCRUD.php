<?php
/**
 * AdminCRUD Base Class
 * SECURITY AUDIT FIX: Refactoring duplicate code in admin pages
 * Created: 2025-11-18
 *
 * Базовый класс для CRUD операций в админ-панели.
 * Уменьшает дублирование кода в domains.php, hosting.php, vps.php
 *
 * Usage:
 * class DomainsCRUD extends AdminCRUD {
 *     public function __construct($pdo) {
 *         parent::__construct($pdo, 'domain_zones');
 *         $this->fields = ['zone', 'price_registration', 'price_renewal', 'is_popular', 'is_active'];
 *         $this->validationRules = [
 *             'zone' => 'required|string',
 *             'price_registration' => 'required|numeric|min:0',
 *             'price_renewal' => 'required|numeric|min:0'
 *         ];
 *     }
 *
 *     protected function validateData(array $data) {
 *         if (empty($data['zone'])) {
 *             throw new ValidationException('Zone is required');
 *         }
 *         if ($data['price_registration'] <= 0) {
 *             throw new ValidationException('Price must be greater than 0');
 *         }
 *     }
 * }
 */

if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

abstract class AdminCRUD {
    protected $pdo;
    protected $table;
    protected $fields = [];
    protected $primaryKey = 'id';
    protected $timestamps = true;
    protected $validationRules = [];

    /**
     * Constructor
     *
     * @param PDO $pdo Database connection
     * @param string $table Table name
     */
    public function __construct($pdo, $table) {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * Get all records
     *
     * @param array $where WHERE conditions ['column' => 'value']
     * @param string $orderBy Order by clause
     * @param int|null $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getAll($where = [], $orderBy = null, $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($where)) {
            $conditions = [];
            foreach (array_keys($where) as $column) {
                $conditions[] = "$column = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if ($orderBy !== null) {
            $sql .= " ORDER BY {$orderBy}";
        } else {
            $sql .= " ORDER BY {$this->primaryKey} DESC";
        }

        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($where));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get record by ID
     *
     * @param int $id Record ID
     * @return array|false
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get record by field value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @return array|false
     */
    public function getByField($field, $value) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE $field = ?");
        $stmt->execute([$value]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new record
     *
     * @param array $data Record data
     * @return int Last insert ID
     * @throws ValidationException
     */
    public function create(array $data) {
        // Validate data
        $this->validateData($data);

        // Filter only allowed fields
        $data = $this->filterFields($data);

        // Add timestamps
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update record
     *
     * @param int $id Record ID
     * @param array $data Update data
     * @return bool Success
     * @throws ValidationException
     */
    public function update($id, array $data) {
        // Validate data
        $this->validateData($data, $id);

        // Filter only allowed fields
        $data = $this->filterFields($data);

        // Add timestamp
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            $setClause,
            $this->primaryKey
        );

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete record
     *
     * @param int $id Record ID
     * @return bool Success
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Count records
     *
     * @param array $where WHERE conditions
     * @return int
     */
    public function count($where = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if (!empty($where)) {
            $conditions = [];
            foreach (array_keys($where) as $column) {
                $conditions[] = "$column = ?";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($where));

        return (int)$stmt->fetchColumn();
    }

    /**
     * Check if record exists
     *
     * @param int $id Record ID
     * @return bool
     */
    public function exists($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Filter data to only allowed fields
     *
     * @param array $data Input data
     * @return array Filtered data
     */
    protected function filterFields(array $data) {
        if (empty($this->fields)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fields));
    }

    /**
     * Validate data before insert/update
     * Override this method in child classes
     *
     * @param array $data Data to validate
     * @param int|null $id Record ID (for updates)
     * @throws ValidationException
     */
    abstract protected function validateData(array $data, $id = null);

    /**
     * Get paginated results
     *
     * @param int $page Page number (1-indexed)
     * @param int $perPage Items per page
     * @param array $where WHERE conditions
     * @param string|null $orderBy Order by clause
     * @return array ['data' => [], 'total' => int, 'pages' => int, 'current_page' => int]
     */
    public function paginate($page = 1, $perPage = 20, $where = [], $orderBy = null) {
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage));
        $offset = ($page - 1) * $perPage;

        $total = $this->count($where);
        $pages = (int)ceil($total / $perPage);
        $data = $this->getAll($where, $orderBy, $perPage, $offset);

        return [
            'data' => $data,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page,
            'per_page' => $perPage,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
}

/**
 * Validation Exception
 */
class ValidationException extends Exception {
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
?>
