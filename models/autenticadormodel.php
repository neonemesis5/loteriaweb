<?php
use Core\BaseModel;

require_once __DIR__ . '/../core/basemodel.php';
require_once __DIR__ . '/../core/Database.php';

class AutenticadorModel extends BaseModel {
    public function __construct() {
        parent::__construct();
        $this->table = 'persona';
    }

    /**
     * Buscar usuario por login
     */
    public function findByLogin(string $login): ?array {
        $result = $this->query(
            "SELECT * FROM {$this->table} WHERE login = :login AND status = :status LIMIT 1",
            ['login' => $login, 'status' => 'A']
        );
        return $result[0] ?? null;
    }
}
