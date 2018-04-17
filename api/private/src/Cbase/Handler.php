<?php

namespace Cbase;

class Handler {
    
    private $_pdo;
    //private $_cachedCbases = [];
    
    public function __construct(\PDO $pdo, $rootPass) {
        $this->_pdo = $pdo;
        $this->_rootPass = $rootPass;
    }
    
    public function getCbases($q = "") {
        $sql = "
            SELECT
                id,
                name,
                slug,
                description,
                admin_name,
                admin_email,
                image,
                language,
                promote,
                logo_image
            FROM
                cbases
            WHERE
                NOT disabled
        ";
        $params = [];
        if (!empty($q)) {
            $sql .= "
                AND (
                       name LIKE :q
                    OR description LIKE :q
                    OR admin_name LIKE :q
                )
            ";
            $params['q'] = '%' . $q . '%';
        }
        $sql .= "
            ORDER BY name ASC
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($params);
        $cbases = $stmt->fetchAll();
        foreach ($cbases as &$cbase) {
            $cbase["promote"] = (bool)$cbase["promote"];
        }
        return $cbases;
    }
    
    public function getCbaseById($cbaseId) {
        // if ($this->_cachedCbases[$cbaseId]) {
        //     return $this->_cachedCbases[$cbaseId];
        // }
        $sql = "
            SELECT
                id,
                name,
                slug,
                description,
                admin_name,
                admin_email,
                image,
                language,
                logo_image
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
        //$this->_cachedCbases[$cbaseId] = $cbase;
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
                image,
                language,
                logo_image
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
    
    private function _getCbaseTokenEncrypted($cbase) {
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
        return $cbase["token_encrypted"];
    }
    
    public function createCbase($params) {
        // FIXME make root_pass the second parameter
        if (empty($params["root_pass"]) || $params["root_pass"] !== $this->_rootPass) {
            // A bit ugly using HTTP status codes, since handler has nothing to
            // do with HTTP, but whatever.
            throw new \Exception("missing value for root_pass", 401);
        }
        $token = "";
        $token_alphabet = array_merge(range('A','F'), range(0,9));
        for ($i = 0; $i < 40; ++$i) {
            $token .= $token_alphabet[array_rand($token_alphabet)];
        }
        $token_encrypted = password_hash($token, PASSWORD_BCRYPT);
        $sql = "
            INSERT INTO
                cbases
            SET
                name = :name,
                slug = :slug,
                description = :description,
                admin_name = :admin_name,
                admin_email = :admin_email,
                token_encrypted = :token_encrypted,
                image = :image,
                language = :language
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute([
            "name" => $params["name"],
            "slug" => $this->_slugify($params["name"]),
            "description" => $params["description"],
            "admin_name" => $params["admin_name"],
            "admin_email" => $params["admin_email"],
            "token_encrypted" => $token_encrypted,
            "image" => $params["image"],
            "language" => $params["language"]
        ]);
        $cbase = $this->getCbaseById($this->_pdo->lastInsertId());
        $cbase["token"] = $token;
        return $cbase;
    }
    
    public function updateCbase($cbase, $params, $token) {
        $token_encrypted = $this->_getCbaseTokenEncrypted($cbase);
        if (!password_verify($token, $token_encrypted)) {
            // A bit ugly using HTTP status codes, since handler has nothing to
            // do with HTTP, but whatever.
            throw new \Exception("incorrect token", 401);
        }
        $updateFields = [
            "name",
            "admin_name",
            "admin_email",
            "image",
            "description"
        ];
        $sql = "
            UPDATE
                cbases
            SET
                
        ";
        $values["id"] = $cbase["id"];
        foreach ($updateFields as $field) {
            if (!empty($params[$field])) {
                $parts[] = "{$field} = :{$field}";
                $cbase[$field] = $params[$field];
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
    
    public function createTokenPair() {
        $token = "";
        $token_alphabet = array_merge(range('A','F'), range(0,9));
        for ($i = 0; $i < 40; ++$i) {
            $token .= $token_alphabet[array_rand($token_alphabet)];
        }
        $token_encrypted = password_hash($token, PASSWORD_DEFAULT);
        return [
            "token" => $token,
            "token_encrypted" => $token_encrypted
        ];
    }
    
    public function createCbaseToken($cbase) {
        $email = $cbase["admin_email"];
        $tokenPair = $this->createTokenPair();
        $token = $tokenPair["token"];
        $token_encrypted = $tokenPair["token_encrypted"];
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
    
    public function getUsecases($q = "") {
        $sql = "
            SELECT
                *
            FROM
                projects
        ";
        $params = [];
        if (!empty($q)) {
            $sql .= "
                WHERE
                    name LIKE :q
                OR  teaser LIKE :q
                OR  description LIKE :q
                OR  type LIKE :q
                OR  country LIKE :q
                OR  category LIKE :q
                OR  organisation LIKE :q
                OR  contact_name LIKE :q
            ";
            $params['q'] = '%' . $q . '%';
        }
        $sql .= "
            ORDER BY name ASC
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($params);
        $usecases = $stmt->fetchAll();
        return $usecases;
    }
    
    public function getUsecasesByCbaseId($cbaseId, $q = "") {
        $sql = "
            SELECT
                *
            FROM
                projects
            WHERE cbase_id = :cbase_id
        ";
        $params = [
            "cbase_id" => (int)$cbaseId
        ];
        if (!empty($q)) {
            $sql .= "
                AND (
                    name LIKE :q
                    OR  teaser LIKE :q
                    OR  description LIKE :q
                    OR  type LIKE :q
                    OR  country LIKE :q
                    OR  category LIKE :q
                    OR  organisation LIKE :q
                    OR  contact_name LIKE :q
                )
            ";
            $params['q'] = '%' . $q . '%';
        }
        $sql .= "
            ORDER BY name ASC
        ";
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($params);
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
            return md5(time() . $text);
        }
        
        return $text . "-" . md5(time() . $text);
    }
    
    public function createUsecaseWithinCbase($cbase, $params, $token) {
        $token_encrypted = $this->_getCbaseTokenEncrypted($cbase);
        if (!password_verify($token, $token_encrypted)) {
            // A bit ugly using HTTP status codes, since handler has nothing to
            // do with HTTP, but whatever.
            throw new \Exception("incorrect token", 401);
        }
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
    
    public function updateUsecase(&$usecase, $params, $token) {
        $cbase = $this->getCbaseById($usecase["cbase_id"]);
        $token_encrypted = $this->_getCbaseTokenEncrypted($cbase);
        if (!password_verify($token, $token_encrypted)) {
            // A bit ugly using HTTP status codes, since handler has nothing to
            // do with HTTP, but whatever.
            throw new \Exception("incorrect token", 401);
        }
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
            "download",
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
    
    public function deleteUsecase(&$usecase, $token) {
        $cbase = $this->getCbaseById($usecase["cbase_id"]);
        $token_encrypted = $this->_getCbaseTokenEncrypted($cbase);
        if (!password_verify($token, $token_encrypted)) {
            // A bit ugly using HTTP status codes, since handler has nothing to
            // do with HTTP, but whatever.
            throw new \Exception("incorrect token", 401);
        }
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
