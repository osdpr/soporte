<?php
require_once __DIR__ . '/../app/db.php';

try {
    $pdo = db();
    echo "<h2>✅ Conexión OK a la base: " . htmlspecialchars(DB_NAME) . "</h2>";
    // Muestra las bases disponibles (solo para probar)
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    if (!$tables) {
        echo "<p>No hay tablas todavía (es normal en este punto).</p>";
    } else {
        echo "<pre>"; print_r($tables); echo "</pre>";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo "<h2>❌ Error de conexión</h2>";
    if (APP_DEBUG) {
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    } else {
        echo "<p>Revisa credenciales en app/config.php</p>";
    }
}
