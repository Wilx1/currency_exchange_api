<?php
class Country {
    private $conn;
    private $table = "countries";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getConnection() {
        return $this->conn;
    }

    // 🔹 Insert or Update a Country
    public function save($data) {
        $name = $this->conn->real_escape_string($data['name']);
        $capital = isset($data['capital']) ? $this->conn->real_escape_string($data['capital']) : null;
        $region = isset($data['region']) ? $this->conn->real_escape_string($data['region']) : null;
        $population = isset($data['population']) ? intval($data['population']) : 0;
        $currency_code = isset($data['currency_code']) ? $this->conn->real_escape_string($data['currency_code']) : null;
        $exchange_rate = isset($data['exchange_rate']) ? floatval($data['exchange_rate']) : null;
        $estimated_gdp = isset($data['estimated_gdp']) ? floatval($data['estimated_gdp']) : null;
        $flag_url = isset($data['flag_url']) ? $this->conn->real_escape_string($data['flag_url']) : null;

        // Check if country already exists
        $checkQuery = "SELECT id FROM {$this->table} WHERE LOWER(name) = LOWER('$name')";
        $result = $this->conn->query($checkQuery);

        if ($result && $result->num_rows > 0) {
            // Update existing
            $query = "
                UPDATE {$this->table}
                SET capital='$capital', region='$region', population=$population,
                    currency_code='$currency_code', exchange_rate=$exchange_rate,
                    estimated_gdp=$estimated_gdp, flag_url='$flag_url'
                WHERE LOWER(name) = LOWER('$name')
            ";
        } else {
            // Insert new
            $query = "
                INSERT INTO {$this->table} 
                (name, capital, region, population, currency_code, exchange_rate, estimated_gdp, flag_url)
                VALUES ('$name', '$capital', '$region', $population, '$currency_code', $exchange_rate, $estimated_gdp, '$flag_url')
            ";
        }

        if (!$this->conn->query($query)) {
            return [
                "error" => "Database operation failed",
                "details" => $this->conn->error
            ];
        }

        return ["success" => true];
    }

    // 🔹 Get all countries (optional filters)
    public function getAll($filters = []) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";

        if (isset($filters['region'])) {
            $region = $this->conn->real_escape_string($filters['region']);
            $query .= " AND region = '$region'";
        }

        if (isset($filters['currency'])) {
            $currency = $this->conn->real_escape_string($filters['currency']);
            $query .= " AND currency_code = '$currency'";
        }

        if (isset($filters['sort'])) {
            if ($filters['sort'] === 'gdp_desc') $query .= " ORDER BY estimated_gdp DESC";
            elseif ($filters['sort'] === 'gdp_asc') $query .= " ORDER BY estimated_gdp ASC";
        }

        $result = $this->conn->query($query);

        if (!$result) return ["error" => $this->conn->error];

        $countries = [];
        while ($row = $result->fetch_assoc()) {
            $countries[] = $row;
        }

        return $countries;
    }

    // 🔹 Get one country by name
    public function getByName($name) {
        $name = $this->conn->real_escape_string($name);
        $query = "SELECT * FROM {$this->table} WHERE LOWER(name) = LOWER('$name')";
        $result = $this->conn->query($query);

        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return ["error" => "Country not found"];
    }

    // 🔹 Delete a country by name
    public function delete($name) {
        $name = $this->conn->real_escape_string($name);
        $query = "DELETE FROM {$this->table} WHERE LOWER(name) = LOWER('$name')";
        if ($this->conn->query($query)) {
            if ($this->conn->affected_rows > 0) {
                return ["success" => true];
            } else {
                return ["error" => "Country not found"];
            }
        }

        return ["error" => $this->conn->error];
    }

    // 🔹 Get status summary
    public function getStatus() {
        $query = "SELECT COUNT(*) AS total_countries, MAX(last_refreshed_at) AS last_refreshed_at FROM {$this->table}";
        $result = $this->conn->query($query);

        if ($result) {
            return $result->fetch_assoc();
        }

        return ["error" => $this->conn->error];
    }
}
