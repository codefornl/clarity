<?php

namespace Cbase;

class Handler {
    
    private $_pdo;
    
    public function __construct(\PDO $pdo, $rootPass) {
        $this->_pdo = $pdo;
        $this->_rootPass = $rootPass;
    }
    
    public function getCbases() {
        $sql = "
            SELECT
                id,
                name,
                slug,
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
                slug,
                description,
                admin_name,
                admin_email,
                image
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
    
    public function getCbaseBySlug($cbaseSlug) {
        $sql = "
            SELECT
                id,
                name,
                slug,
                description,
                admin_name,
                admin_email,
                image
            FROM
                cbases
            WHERE slug = :slug
            LIMIT 1
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "slug" => $cbaseSlug
        ]);
        $cbase = $stmt->fetch();
        return $cbase;
    }
    
    public function createCbase($params) {
        if (empty($params["root_pass"]) || $params["root_pass"] !== $this->_rootPass) {
            // A bit ugly using HTTP status codes, since handler has nothing to
            // do with HTTP, but whatever.
            throw new \Exception("missing value for root_pass", 401);
        }
        $sql = "
            INSERT INTO
                cbases
            SET
                name = :name,
                slug = :slug,
                description = :description,
                admin_name = :admin_name,
                admin_email = :admin_email,
                image = :image
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "name" => $params["name"],
            "slug" => $this->_slugify($params["name"]),
            "description" => $params["description"],
            "admin_name" => $params["admin_name"],
            "admin_email" => $params["admin_email"],
            "image" => $params["image"]
        ]);
        return $this->getCbaseById($this->_pdo->lastInsertId());
    }
    
    public function getCbaseTokenIfValid($cbase, $token) {
        $sql = "
            SELECT
                token_encrypted
            FROM
                cbases
            WHERE
                id = :id
            LIMIT 1
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "id" => $cbase["id"]
        ]);
        $cbase = $stmt->fetch();
        if (password_verify($token, $cbase["token_encrypted"])) {
            return $token;
        } else {
            return null;
        }
    }
    
    public function createCbaseToken($cbase) {
        $email = $cbase["admin_email"];
        $token = "";
        $token_alphabet = array_merge(range('A','F'), range(0,9));
        for ($i = 0; $i < 40; ++$i) {
            $token .= $token_alphabet[array_rand($token_alphabet)];
        }
        $token_encrypted = password_hash($token, PASSWORD_DEFAULT);
        $sql = "
            UPDATE
                cbases
            SET
                token_encrypted = :token_encrypted
            WHERE
                id = :id
            LIMIT 1
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "id" => $cbase["id"],
            "token_encrypted" => $token_encrypted
        ]);
        return $token;
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
    
    public function getUsecaseBySlug($usecaseSlug) {
        $sql = "
            SELECT
                *
            FROM
                projects
            WHERE slug = :slug
            LIMIT 1
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "slug" => $usecaseSlug
        ]);
        $usecase = $stmt->fetch();
        return $usecase;
    }
    
    static private function _slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // trim
        $text = trim($text, '-');
        
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        
        // lowercase
        $text = strtolower($text);
        
        if (empty($text)) {
        return 'n-a';
        }
        
        return $text . "-" . md5(time() . $text);
    }
    
    public function createUsecaseWithinCbase($cbase, $params) {
        $slug = $this->_slugify($params["name"]);
        $sql = "
            INSERT INTO
                projects
            SET
                cbase_id = :cbase_id,
                name = :name,
                slug = :slug
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "cbase_id" => $cbase["id"],
            "name" => $params["name"],
            "slug" => $slug
        ]);
        return $this->getUsecaseBySlug($slug);
    }
    
    public function updateUsecase(&$usecase, $params) {
        $updateFields = [
            "name",
            "organisation",
            "country",
            "image",
            "teaser",
            "description",
            "website",
            "category",
            "type",
            //"tool",
            "contact_name",
            "contact_image",
            "contact_email"
        ];
        $sql = "
            UPDATE
                projects
            SET
                
        ";
        $values["id"] = $usecase["id"];
        foreach ($updateFields as $field) {
            if (!empty($params[$field])) {
                $parts[] = "{$field} = :{$field}";
                $usecase[$field] = $params[$field];
                $values[$field] = $params[$field];
            }
        }
        $sql .= implode($parts, ", ");
        $sql .= "
            WHERE id = :id
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($values);
    }
    
    public function deleteUsecase(&$usecase) {
        $sql = "
            DELETE FROM projects
            WHERE id = :id
            LIMIT 1
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "id" => $usecase["id"]
        ]);
        $usecase = null;
    }
    
}
