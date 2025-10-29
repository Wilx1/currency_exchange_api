<?php
class ImageGenerator {
    public static function generateSummary($conn) {
        // Get stats
        $summaryQuery = "SELECT COUNT(*) AS total, MAX(last_refreshed_at) AS last_refreshed_at FROM countries";
        $summaryResult = $conn->query($summaryQuery);
        $summary = $summaryResult->fetch_assoc();

        $topQuery = "SELECT name, estimated_gdp FROM countries ORDER BY estimated_gdp DESC LIMIT 5";
        $topResult = $conn->query($topQuery);

        $topCountries = [];
        while ($row = $topResult->fetch_assoc()) {
            $topCountries[] = $row;
        }

        // Create image
        $width = 600;
        $height = 400;
        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $blue = imagecolorallocate($image, 50, 100, 255);

        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        $fontSize = 4;
        imagestring($image, $fontSize + 2, 20, 20, "Country Summary Report", $blue);
        imagestring($image, $fontSize, 20, 60, "Total countries: " . $summary['total'], $black);
        imagestring($image, $fontSize, 20, 80, "Last refreshed: " . $summary['last_refreshed_at'], $black);

        $y = 120;
        imagestring($image, $fontSize + 1, 20, $y, "Top 5 Countries by GDP:", $blue);
        $y += 30;
        foreach ($topCountries as $c) {
            imagestring($image, $fontSize, 40, $y, $c['name'] . " - " . number_format($c['estimated_gdp'], 2), $black);
            $y += 20;
        }

        // Save to /cache
        $dir = __DIR__ . '/../cache';
        if (!file_exists($dir)) mkdir($dir, 0777, true);

        $filePath = $dir . '/summary.png';
        imagepng($image, $filePath);
        imagedestroy($image);
    }
}
