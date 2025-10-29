# 🌍 CURRENCY EXCHANGE API

A RESTful PHP API that fetches, stores, and manages country data from external APIs — including names, regions, populations, currencies, and exchange rates.  
It also generates summary visuals and supports filtering, sorting, and status tracking.

---

## 🚀 Features

- Fetches real-time data from:
  - [REST Countries API](https://restcountries.com/)
  - [Open Exchange Rates API](https://open.er-api.com/)
- Automatically computes **estimated GDPs** per country.
- Caches the last refresh timestamp.
- Supports **region and currency filtering**.
- Supports **sorting by GDP (ascending or descending)**.
- Generates **summary image visualizations**.
- Provides API endpoints for full CRUD operations.
- Includes error handling for API/network failures.

---

## 🧱 Tech Stack

- **Language:** PHP 
- **Database:** MySQL
- **Web Server:** Apache / Nginx
- **Libraries:** GD for image generation
- **Environment:** `.env` file for database credentials
- **HTTP Requests:** `file_get_contents()` (no external dependency)

---

## 📂 Folder Structure

```
project-root/
│
├── app/
│   ├── Controllers/
│   │   └── CountryController.php
│   ├── Models/
│   │   └── Country.php
│   ├── utils/
│   │   └── ImageGenerator.php
│   └── cache/
│       └── last_refreshed.txt
│
├── config/
│   └── Database.php
│
├── index.php
│
├── .env
└── README.md
```

---

## ⚙️ Setup Instructions

### 1. Clone the Repository
```bash
git clone https://github.com/Wilx1/currency_exchange_api.git
cd currency_exchange_api
```

### 2. Configure Environment Variables
Create a `.env` file in the root directory:
```
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_NAME=country_db
```

### 3. Import the Database
Run the following SQL command or import your prepared `countries.sql` file:
```sql
CREATE DATABASE country_db;
USE country_db;

CREATE TABLE countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    capital VARCHAR(255),
    region VARCHAR(255),
    population BIGINT,
    currency_code VARCHAR(10),
    exchange_rate DECIMAL(20,6),
    estimated_gdp DECIMAL(30,2),
    flag_url TEXT,
    last_refreshed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 4. Start a Local Server
If you’re using PHP’s built-in server:
```bash
php -S localhost:8000 -t public
```

---

## 🧩 API Endpoints

### 🔹 1. Get All Countries
```
GET /countries
```
Optional query parameters:
- `region` → Filter by region (e.g. `Africa`)
- `currency` → Filter by currency (e.g. `USD`)
- `sort` → Sort by GDP (`gdp_asc` or `gdp_desc`)

Example:
```
GET /countries?region=Africa&sort=gdp_desc
```

### 🔹 2. Get a Specific Country
```
GET /countries/{name}
```

### 🔹 3. Refresh All Countries
Fetches and updates countries from external APIs.
```
POST /countries/refresh
```

### 🔹 4. Delete a Country
```
DELETE /countries/{name}
```

### 🔹 5. Get Status
```
GET /status
```

### 🔹 6. View Summary Image
```
GET /countries/image
```
Displays a summary visual (saved under `/app/cache/summary.png`).

---

## 🪶 Example Response

```json
{
  "name": "Nigeria",
  "capital": "Abuja",
  "region": "Africa",
  "population": 200963599,
  "currency_code": "NGN",
  "exchange_rate": 1575.30,
  "estimated_gdp": 250481112990.32,
  "flag_url": "https://restcountries.com/data/nga.svg"
}
```

---

## 🧰 Troubleshooting

| Issue | Possible Fix |
|-------|---------------|
| `false` from `getenv()` | Ensure `.env` file is loaded before using variables |
| API data not loading | Check internet access or RESTCountries / Open ER API availability |
| GD errors | Enable GD extension in your PHP installation |
| “Route not found” | Verify your `index.php` router includes `/countries` and `/status` |

---

## 📸 Visual Example

When refreshed, the system generates `/app/cache/summary.png`  
A chart summarizing population and GDP data.

---

## 📜 License

This project is open-source under the **MIT License**.

---

## 👨‍💻 Author

**Wilx O.**  
Backend Developer | PHP • Laravel • REST APIs  
GitHub: [@Wilx1](https://github.com/Wilx1)
