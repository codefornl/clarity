<?php

namespace Cbase;

class Handler {
    
    private $_pdo;
    
    public function __construct(\PDO $pdo) {
        $this->_pdo = $pdo;
    }
    
    public function getCbases() {
        $sql = "
            SELECT
                id,
                name,
                description,
                admin_name,
                admin_email,
                image
            FROM
                cbases
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute();
        $cbases = $stmt->fetchAll();
        return $cbases;
    }
    
    public function getCbaseById($cbaseId) {
        $sql = "
            SELECT
                id,
                name,
                description,
                admin_name,
                admin_email
            FROM
                cbases
            WHERE id = :id
            LIMIT 1
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "id" => (int)$cbaseId
        ]);
        $cbase = $stmt->fetch();
        return $cbase;
    }
    
    public function getUsecases() {
        $sql = "
            SELECT
                *
            FROM
                projects
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute();
        $usecases = $stmt->fetchAll();
        return $usecases;
    }
    
    public function getUsecaseById($usecaseId) {
        $sql = "
            SELECT
                *
            FROM
                projects
            WHERE id = :id
            LIMIT 1
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "id" => (int)$usecaseId
        ]);
        $usecase = $stmt->fetch();
        return $usecase;
    }
    
    public function getUsecasesByCbaseId($cbaseId) {
        $sql = "
            SELECT
                *
            FROM
                projects
            WHERE cbase_id = :cbase_id
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "cbase_id" => (int)$cbaseId
        ]);
        $usecases = $stmt->fetchAll();
        return $usecases;
    }
    
    public function postUsecase($usecase) {
        
        return $usecase;
    }
    
}
