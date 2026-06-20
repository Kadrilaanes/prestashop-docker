<?php
/**
 * GitHub Webhook Handler — Auto-pull on push to main
 * 
 * Receives GitHub webhook POSTs, verifies via custom header secret,
 * syncs repo, refreshes CSS cache in PrestaShop.
 * 
 * Uses custom header auth instead of HMAC because Cloudflare's
 * trycloudflare tunnel modifies the request body, breaking HMAC.
 */

// ─── Config ───────────────────────────────────────────────────────────────────
define('WEBHOOK_SECRET', '7debdb315e249cde331af5b8cbfaab678ba4a93c');
define('REPO_PATH', '/repo');
define('DB_HOST', 'mysql-presta');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prestashop');
define('DB_PREFIX', 'ps_');
define('SMARTY_CACHE_DIR', '/var/www/html/var/cache');

// ─── Auth via custom header ────────────────────────────────────────────────────
// X-Webhook-Secret header (set by custom GitHub Actions workflow or manual trigger)
$headerSecret = $_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? '';
if ($headerSecret !== WEBHOOK_SECRET) {
    http_response_code(403);
    die('Forbidden');
}

$payload = file_get_contents('php://input');
if (!$payload) {
    http_response_code(400);
    die('No payload');
}

$data = json_decode($payload, true);
if (!$data) {
    http_response_code(400);
    die('Invalid JSON');
}

$log = [];
$removed = 0;
$shouldProcess = false;

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';

if ($event === 'push') {
    $ref = $data['ref'] ?? '';
    if ($ref === 'refs/heads/main') {
        $shouldProcess = true;
        $log[] = "Push to main by {$data['pusher']['name']}";
        $log[] = "Commits: " . count($data['commits'] ?? []);
    }
} elseif ($event === 'pull_request') {
    $action = $data['action'] ?? '';
    $merged = $data['pull_request']['merged'] ?? false;
    $baseRef = $data['pull_request']['base']['ref'] ?? '';
    
    if ($action === 'closed' && $merged && $baseRef === 'main') {
        $shouldProcess = true;
        $prTitle = $data['pull_request']['title'] ?? '';
        $log[] = "PR merged to main: \"{$prTitle}\"";
    }
}

if (!$shouldProcess) {
    $actionText = isset($data['action']) ? $data['action'] : 'n/a';
    http_response_code(200);
    die("Ignored event: {$event} ({$actionText})");
}

// ─── Git sync (hard reset to origin/main — always matches GitHub exactly) ─────
// Why `fetch + reset --hard` instead of `git pull`:
// git pull fails when local branch diverges from remote (e.g. un-pushed local
// commits). With multiple people pushing (Commander + Kadrilaanes), we must
// always match GitHub's state exactly. This also makes "last commit didn't
// take effect" impossible — the shop always mirrors what's on GitHub.
$log[] = "--- GIT SYNC ---";
exec("cd " . escapeshellarg(REPO_PATH) . " && git fetch origin 2>&1", $fetchOutput, $fetchExit);
$log = array_merge($log, $fetchOutput);

exec("cd " . escapeshellarg(REPO_PATH) . " && git reset --hard origin/main 2>&1", $resetOutput, $resetExit);
$log = array_merge($log, $resetOutput);

if ($resetExit !== 0) {
    $log[] = "ERROR: git sync failed (code {$resetExit})";
    file_put_contents('php://stderr', implode("\n", $log));
    http_response_code(500);
    die('Git sync failed');
}

// Show what commit we're now on
exec("cd " . escapeshellarg(REPO_PATH) . " && git log --oneline -1 2>&1", $shaOut, $shaExit);
$log[] = "Now at: " . ($shaOut[0] ?? 'unknown');

// ─── Bump CSS version in MySQL ────────────────────────────────────────────────
$log[] = "--- BUMP CSS VERSION ---";
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $conn->query("SELECT value FROM `" . DB_PREFIX . "configuration` WHERE name = 'PS_CCCCSS_VERSION'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $newVersion = intval($row['value']) + 1;
        $conn->exec("UPDATE `" . DB_PREFIX . "configuration` SET value = '{$newVersion}' WHERE name = 'PS_CCCCSS_VERSION'");
        $log[] = "CSS version bumped: {$row['value']} → {$newVersion}";
    } else {
        $conn->exec("INSERT INTO `" . DB_PREFIX . "configuration` (name, value) VALUES ('PS_CCCCSS_VERSION', '1')");
        $log[] = "CSS version set to 1 (new entry)";
    }
    $conn = null;
} catch (PDOException $e) {
    $log[] = "ERROR: DB error: " . $e->getMessage();
}

// ─── Clear Smarty cache (nested subdirs) ──────────────────────────────────────
$log[] = "--- CLEAR CACHE ---";
$cacheDirsToClear = [
    SMARTY_CACHE_DIR . '/prod/smarty/compile',
    SMARTY_CACHE_DIR . '/prod/smarty/cache',
    SMARTY_CACHE_DIR . '/dev/smarty/compile',
    SMARTY_CACHE_DIR . '/dev/smarty/cache',
];
$count = 0;
foreach ($cacheDirsToClear as $dir) {
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
}
$log[] = "Cleared {$count} files from Smarty cache";

// ─── Clear combined CSS files from the correct PrestaShop cache dir ──────────
$log[] = "--- CLEAR COMBINED CSS ---";
$cacheDirs = [
    '/var/www/html/themes/etrendlite/assets/cache',
    '/var/www/html/themes/etrendlite/assets/css',
];
foreach ($cacheDirs as $cacheDir) {
    $combinedFiles = glob($cacheDir . '/theme-*.css');
    foreach ($combinedFiles as $f) {
        unlink($f);
        $removed++;
    }
}
$log[] = "Removed {$removed} combined CSS files (will regenerate on next request)";

// ─── Done ─────────────────────────────────────────────────────────────────────
$log[] = "--- DONE ---";
$logStr = "[" . date('Y-m-d H:i:s') . "] " . implode("\n", $log);

// Log to file (in writable smarty cache area)
file_put_contents(SMARTY_CACHE_DIR . '/github-webhook.log', $logStr . "\n\n", FILE_APPEND);

http_response_code(200);
header('Content-Type: text/plain');
echo implode("\n", $log);
