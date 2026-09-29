<?php
/**
 * INTENTIONALLY VULNERABLE APPLICATION — LAB / EDUCATIONAL USE ONLY.
 *
 * This is not production code. It deliberately reproduces:
 *   - a controller with no authentication guard,
 *   - raw string interpolation of request input into SQL (UNION SQLi),
 *   - unescaped output.
 *
 * NEVER expose this to a network. See ../README.md and ../DISCLAIMER.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Database bootstrap (SQLite, created + seeded on first run)
// ---------------------------------------------------------------------------
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}
$dbFile = $dataDir . '/lab.sqlite';

if (!file_exists($dbFile)) {
    $seed = new PDO('sqlite:' . $dbFile);
    $seed->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $seed->exec("CREATE TABLE ta_kib_a (
        id            INTEGER PRIMARY KEY,
        kd_aset_gab   TEXT,
        nm_aset_gab   TEXT,
        kd_upb_gab    TEXT,
        nm_aset_7     TEXT,
        masa_manfaat  TEXT,
        no_register   TEXT,
        nibar         TEXT,
        nm_upb        TEXT,
        kd_urusan     TEXT,
        kd_bidang     TEXT,
        kd_skpd       TEXT,
        kd_unit       TEXT,
        kd_upb        TEXT,
        tgl_perolehan TEXT
    )");

    $seed->exec("CREATE TABLE user_login (
        user_id       INTEGER PRIMARY KEY,
        username      TEXT,
        password      TEXT,
        user_level_id INTEGER
    )");

    $seed->exec("INSERT INTO ta_kib_a
        (kd_aset_gab, nm_aset_gab, kd_upb_gab, nm_aset_7, masa_manfaat,
         no_register, nibar, nm_upb, kd_urusan, kd_bidang, kd_skpd, kd_unit,
         kd_upb, tgl_perolehan)
        VALUES
        ('1.3.1.01.01.01.001', 'Contoh Aset', '1.1.1.1.1', 'Tanah Kosong', '-',
         '1', 'NIBAR-0001', 'UPTD Contoh', '1', '1', '1', '1', '1', '2024-05-01')");

    // Dummy bcrypt hash for demonstration only (per-hash salt, cost 12).
    $seed->exec("INSERT INTO user_login (username, password, user_level_id)
        VALUES ('uptd_demo', '\$2a\$12\$TDSk2P.Tqxvvrz2zArNTIu4.dbwQEWAOM13hGTckf1uLSpFSPWiEW', 3)");

    $seed = null;
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

/**
 * Renders one sticker card. Columns 1, 4, 6, 7, 8 and 9 are displayed, which is
 * why a UNION payload targets those positions.
 */
function sticker_card(array $row): string
{
    $noRegister = str_pad((string) ($row['no_register'] ?? ''), 6, '0', STR_PAD_LEFT);
    $line1 = ($row['kd_aset_gab'] ?? '') . '-' . $noRegister . '-' . ($row['tahun'] ?? '');
    $line2 = $row['nm_aset_7'] ?? '';
    $line3 = $row['nibar'] ?? '';
    $line4 = $row['nm_upb'] ?? '';

    // NOTE: values are echoed WITHOUT escaping on purpose.
    return <<<HTML
    <div class="mb-1">
      <table id="sticker" class="w-100">
        <tbody>
          <tr><td><span>{$line1}</span></td></tr>
          <tr><td><span>{$line2}</span></td></tr>
          <tr><td><span>{$line3}</span></td></tr>
          <tr><td class="fw-bold">{$line4}</td></tr>
        </tbody>
      </table>
    </div>
HTML;
}

function page(string $body): void
{
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
        . '<title>Lab</title></head><body><div id="content">'
        . $body
        . '</div></body></html>';
}

// ---------------------------------------------------------------------------
// Routing
// ---------------------------------------------------------------------------
switch (true) {

    case $path === '/':
        header('Content-Type: text/html; charset=UTF-8');
        echo <<<HTML
        <!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
        <title>Intentionally Vulnerable Lab</title></head><body>
        <h1>Intentionally Vulnerable Lab</h1>
        <p><strong>Local use only.</strong> This app is deliberately insecure.</p>
        <ul>
          <li><code>/bmd/common/view_bulk_sticker?kib=kib_a&amp;upb=1.1.1.1.1&amp;tahun=2024</code></li>
          <li><code>/bmd/common/get_ref_by_search?search=Aset</code></li>
          <li><code>POST /bmd/common/mass_approve</code> (no auth)</li>
        </ul>
        </body></html>
        HTML;
        break;

    // -----------------------------------------------------------------------
    // VULNERABLE: raw interpolation into a LIKE clause, no auth guard.
    // Mirrors Common.php:104/133 in the real engagement.
    // -----------------------------------------------------------------------
    case $path === '/bmd/common/view_bulk_sticker':
        $kib   = $_GET['kib']   ?? 'kib_a';
        $upb   = $_GET['upb']   ?? '1.1.1.1.1';
        $tahun = $_GET['tahun'] ?? '';

        $p          = explode('.', $upb);
        $kd_urusan  = $p[0] ?? '';
        $kd_bidang  = $p[1] ?? '';
        $kd_skpd    = $p[2] ?? '';
        $kd_unit    = $p[3] ?? '';
        $kd_upb     = $p[4] ?? '';

        $sql = "SELECT A.kd_aset_gab AS kd_aset_gab, A.nm_aset_gab AS nm_aset_gab, "
             . "A.kd_upb_gab AS kd_upb_gab, A.nm_aset_7 AS nm_aset_7, "
             . "A.masa_manfaat AS masa_manfaat, A.no_register AS no_register, "
             . "A.nibar AS nibar, A.nm_upb AS nm_upb, "
             . "substr(A.tgl_perolehan,1,4) AS tahun "
             . "FROM ta_{$kib} A "
             . "WHERE A.kd_urusan='{$kd_urusan}' AND A.kd_bidang='{$kd_bidang}' "
             . "AND A.kd_skpd='{$kd_skpd}' AND A.kd_unit='{$kd_unit}' "
             . "AND A.kd_upb='{$kd_upb}' AND A.tgl_perolehan LIKE '{$tahun}%'";

        try {
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            http_response_code(500);
            // Verbose error (like debug mode enabled) aids the attacker.
            page('<pre>SQL error: ' . $e->getMessage() . '</pre>' . '<pre>' . $sql . '</pre>');
            break;
        }

        $body = '';
        foreach ($rows as $row) {
            $body .= sticker_card($row);
        }
        page($body);
        break;

    // -----------------------------------------------------------------------
    // VULNERABLE: raw interpolation in a search filter.
    // Mirrors the get_ref_*_by_search having() injection.
    // -----------------------------------------------------------------------
    case $path === '/bmd/common/get_ref_by_search':
        $search = $_GET['search'] ?? '';

        $sql = "SELECT nm_aset_gab FROM (SELECT nm_aset_gab FROM ta_kib_a) "
             . "WHERE nm_aset_gab LIKE \"%{$search}%\"";

        try {
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            http_response_code(500);
            page('<pre>SQL error: ' . $e->getMessage() . '</pre>');
            break;
        }

        header('Content-Type: application/json');
        echo json_encode($rows);
        break;

    // -----------------------------------------------------------------------
    // VULNERABLE: privileged action with no authentication.
    // Mirrors the commented-out guard at Common.php:16-19.
    // -----------------------------------------------------------------------
    case $path === '/bmd/common/mass_approve':
        $kib = $_POST['kib'] ?? 'kib_a';
        header('Content-Type: application/json');
        echo json_encode([
            'status' => true,
            'msg'    => 'records approved (lab stub)',
            'kib'    => $kib,
        ]);
        break;

    default:
        http_response_code(404);
        page('<p>Not found.</p>');
}
