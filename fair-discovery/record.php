<?php
header("Content-Type: application/json");
session_start(); 

// --- CONFIGURATION ---
$file = __DIR__ . "/discovery.json";
$max_history_weeks = 20; 

// Get Visitor IP
$ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// 1. OPEN DATABASE
$fp = fopen($file, 'c+');
if (!flock($fp, LOCK_EX)) { exit(); } 
$fileSize = filesize($file);
$jsonContent = $fileSize > 0 ? fread($fp, $fileSize) : "{}";
$log = json_decode($jsonContent, true);

// Ensure structure exists
if (!isset($log["blocked_ips"])) $log["blocked_ips"] = [];
if (!isset($log["visitors"]["history"])) {
    $log["visitors"]["current_week_start"] = date("Y-m-d", strtotime('last saturday')); 
    $log["visitors"]["current_count"] = 0;
    $log["visitors"]["history"] = [];
}

// 2. --- SELF-DEFENSE: BLACKLIST ---
if (in_array($ip, $log["blocked_ips"])) {
    flock($fp, LOCK_UN); fclose($fp);
    exit(); 
}

// 3. --- IDENTIFY BOTS ---
$agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_bot = false;
$bots = [
    'facebookexternalhit', 'Facebot', 'Meta', 
    'AdsBot-Google', 'Google-InspectionTool', 'Googlebot', 
    'bingbot', 'slurp', 'DuckDuckBot', 'Baiduspider', 'YandexBot', 
    'python', 'curl', 'wget', 'ia_archiver'
];

foreach ($bots as $bot) {
    if (stripos($agent, $bot) !== false) {
        $is_bot = true; break;
    }
}

// --- ALGORITHM: WEEKLY ROTATION (SATURDAY TO FRIDAY) ---
// We check if 7 days have passed since the current_week_start
$start_ts = strtotime($log["visitors"]["current_week_start"]);
$now = time();
$one_week_sec = 604800; // 7 days in seconds

// "Catch Up" Loop: Handles if no one visited for multiple weeks
while ($now >= $start_ts + $one_week_sec) {
    // 1. Archive the completed week
    $entry = [
        "date" => date("Y-m-d", $start_ts), // The Sat date
        "count" => $log["visitors"]["current_count"]
    ];
    
    // 2. Add to history
    $log["visitors"]["history"][] = $entry;
    
    // 3. Trim to 20 weeks (FIFO)
    if (count($log["visitors"]["history"]) > $max_history_weeks) {
        array_shift($log["visitors"]["history"]); // Remove oldest
    }
    
    // 4. Reset for new week
    $log["visitors"]["current_count"] = 0;
    
    // 5. Advance start date by exactly 7 days
    $start_ts += $one_week_sec;
    $log["visitors"]["current_week_start"] = date("Y-m-d", $start_ts);
}

// 4. --- BOT BANNING ---
if ($is_bot) {
    if (!in_array($ip, $log["blocked_ips"])) {
        $log["blocked_ips"][] = $ip;
        if (count($log["blocked_ips"]) > 100) array_shift($log["blocked_ips"]);
    }
    // Save and Die
    ftruncate($fp, 0); rewind($fp);
    fwrite($fp, json_encode($log, JSON_PRETTY_PRINT));
    flock($fp, LOCK_UN); fclose($fp);
    exit();
}

// 5. --- PROCESS HUMAN DATA ---
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($data && isset($data["type"])) {
    // VISITOR COUNT
    if ($data["type"] === "visit") {
        if (!isset($_SESSION['counted_week_start']) || $_SESSION['counted_week_start'] !== $log["visitors"]["current_week_start"]) {
            $log["visitors"]["current_count"]++;
            $_SESSION['counted_week_start'] = $log["visitors"]["current_week_start"];
        }
    }
    
    // SCORE UPDATE
    if ($data["type"] === "score" && isset($data["path"]) && isset($data["score"])) {
        $path = filter_var($data["path"], FILTER_SANITIZE_URL);
        $score = (int)$data["score"];
        if ($score > 5000000) $score = 1000; 

        $found = false;
        foreach ($log["pages"] as &$page) {
            if ($page["url"] === $path) {
                $page["score"] += $score;
                $found = true; break;
            }
        }
        if (!$found) $log["pages"][] = ["url" => $path, "score" => $score];
    }
}

// 6. SAVE
ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($log, JSON_PRETTY_PRINT));
flock($fp, LOCK_UN);
fclose($fp);
echo json_encode(["status" => "ok"]);
?>