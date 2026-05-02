<?php
header("Content-Type: application/xml; charset=utf-8");

$baseUrl = "https://zpolonius.dk/";
$jsonFile = __DIR__ . "/data/content.json";
$data = [];

if (file_exists($jsonFile)) {
    $json = file_get_contents($jsonFile);
    $data = json_decode($json, true);
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

// 1. STATISKE SIDER
$staticPages = [
    "" => 1.0,
    "about.html" => 0.8,
    "projects.html" => 0.9,
    "insights.html" => 0.9,
    "cv.html" => 0.8,
    "recommendations.html" => 0.7,
    "contact.html" => 0.6
];

foreach ($staticPages as $page => $priority) {
    echo "  <url>" . PHP_EOL;
    echo "    <loc>{$baseUrl}{$page}</loc>" . PHP_EOL;
    echo "    <changefreq>weekly</changefreq>" . PHP_EOL;
    echo "    <priority>{$priority}</priority>" . PHP_EOL;
    echo "  </url>" . PHP_EOL;
}

// 2. DYNAMISKE PROJEKTER
if (!empty($data['projects'])) {
    foreach ($data['projects'] as $p) {
        if (isset($p['visible']) && $p['visible'] === false) continue;
        $id = $p['id'] ?? '';
        if (!$id) continue;
        echo "  <url>" . PHP_EOL;
        echo "    <loc>{$baseUrl}detail.html?id=" . htmlspecialchars($id) . "</loc>" . PHP_EOL;
        echo "    <changefreq>monthly</changefreq>" . PHP_EOL;
        echo "    <priority>0.8</priority>" . PHP_EOL;
        echo "  </url>" . PHP_EOL;
    }
}

// 3. DYNAMISKE ARTIKLER (INSIGHTS)
if (!empty($data['articles'])) {
    foreach ($data['articles'] as $a) {
        if (isset($a['visible']) && $a['visible'] === false) continue;
        $id = $a['id'] ?? '';
        if (!$id) continue;
        echo "  <url>" . PHP_EOL;
        echo "    <loc>{$baseUrl}detail.html?id=" . htmlspecialchars($id) . "</loc>" . PHP_EOL;
        echo "    <changefreq>monthly</changefreq>" . PHP_EOL;
        echo "    <priority>0.8</priority>" . PHP_EOL;
        echo "  </url>" . PHP_EOL;
    }
}

// 4. DYNAMISKE CV POSTER (JOBS)
if (!empty($data['cv']['jobs'])) {
    foreach ($data['cv']['jobs'] as $j) {
        if (isset($j['visible']) && $j['visible'] === false) continue;
        $id = $j['id'] ?? '';
        if (!$id) continue;
        echo "  <url>" . PHP_EOL;
        echo "    <loc>{$baseUrl}detail.html?id=" . htmlspecialchars($id) . "</loc>" . PHP_EOL;
        echo "    <changefreq>monthly</changefreq>" . PHP_EOL;
        echo "    <priority>0.6</priority>" . PHP_EOL;
        echo "  </url>" . PHP_EOL;
    }
}

echo '</urlset>';
