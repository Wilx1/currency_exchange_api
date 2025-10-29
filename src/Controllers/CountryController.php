<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../Models/Country.php';
require_once __DIR__ . '/../../utils/ImageGenerator.php';

class CountryController {
    private $countryModel;
    private $conn;

    // public function __construct($db) {
    //     $this->countryModel = new Country($db);
    // }

   public function __construct() {
        $db = new Database();
        $conn = $db->connect();
        $this->countryModel = new Country($conn);
    }

    // GET /countries
    public function getAllCountries() {
        $filters = [];
        if (isset($_GET['region'])) {
            $filters['region'] = $_GET['region'];
        }
        if (isset($_GET['currency'])) {
            $filters['currency'] = $_GET['currency'];
        }
        if (isset($_GET['sort'])) {
            $filters['sort'] = $_GET['sort'];
        }

        $countries = $this->countryModel->getAll($filters);
        header('Content-Type: application/json');
        echo json_encode($countries);
    }

    // GET /countries/:name
    public function getCountryByName($name) {
        $country = $this->countryModel->getByName($name);

        header('Content-Type: application/json');
        if ($country) {
            echo json_encode($country);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Country not found"]);
        }
    }

    // DELETE /countries/:name
    public function deleteCountry($name) {
        $deleted = $this->countryModel->delete($name);

        header('Content-Type: application/json');
        if ($deleted) {
            echo json_encode(["message" => "Country deleted successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Country not found or could not be deleted"]);
        }
    }

    // POST /countries/refresh
    public function refreshCountries() {
        // Fetch data from APIs
        $countriesApi = "https://restcountries.com/v2/all?fields=name,capital,region,population,flag,currencies";
        $exchangeApi = "https://open.er-api.com/v6/latest/USD";

        $countriesData = @file_get_contents($countriesApi);
        $exchangeData = @file_get_contents($exchangeApi);

        if (!$countriesData || !$exchangeData) {
            http_response_code(503);
            echo json_encode(["error" => "External data source unavailable"]);
            return;
        }

        $countries = json_decode($countriesData, true);
        $exchangeRates = json_decode($exchangeData, true)['rates'] ?? [];

        $total = 0;
        foreach ($countries as $country) {
            $name = $country['name'] ?? null;
            $capital = $country['capital'] ?? null;
            $region = $country['region'] ?? null;
            $population = $country['population'] ?? 0;
            $flag = $country['flag'] ?? null;
            $currencyCode = isset($country['currencies'][0]['code']) ? $country['currencies'][0]['code'] : null;
            $exchangeRate = $currencyCode && isset($exchangeRates[$currencyCode]) ? $exchangeRates[$currencyCode] : null;

            $randomMultiplier = rand(1000, 2000);
            $estimatedGDP = ($exchangeRate && $exchangeRate != 0)
                ? ($population * $randomMultiplier / $exchangeRate)
                : 0;

            $this->countryModel->save([
                "name" => $name,
                "capital" => $capital,
                "region" => $region,
                "population" => $population,
                "currency_code" => $currencyCode,
                "exchange_rate" => $exchangeRate,
                "estimated_gdp" => $estimatedGDP,
                "flag_url" => $flag
            ]);
            $total++;
        }

        header('Content-Type: application/json');
       // Generate summary image

        $conn = $this->countryModel->getConnection();
        ImageGenerator::generateSummary($conn);

        header('Content-Type: application/json');
        echo json_encode([
            "message" => "Countries refreshed successfully",
            "total_updated" => $total,
            "timestamp" => date("Y-m-d H:i:s"),
            "summary_image" => "cache/summary.png"
        ]);
    }
    // GET /status
    public function getStatus() {
        $query = "SELECT COUNT(*) AS total, MAX(last_refreshed_at) AS last_refreshed_at FROM countries";
        $conn = $this->countryModel->getConnection();
        $result = $conn->query($query);
    
        $data = $result->fetch_assoc();
    
        header('Content-Type: application/json');
        echo json_encode([
            "total_countries" => intval($data['total']),
            "last_refreshed_at" => $data['last_refreshed_at'] ?? null
        ]);
    }
}
