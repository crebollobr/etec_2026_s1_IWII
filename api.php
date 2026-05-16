<?php
/**
 * API do curso de AJAX — ETEC
 * URL final: https://curso.chr.eti.br/ajax/api.php
 *
 * Recurso único: tarefas (id, aluno_id, titulo, feito, criado_em)
 * Cada aluno escolhe um aluno_id e usa em todas as chamadas.
 * Sem login, sem senha — didático.
 *
 * Endpoints:
 *   GET    api.php?aluno_id=ana123              → lista tarefas do aluno
 *   GET    api.php?aluno_id=ana123&id=7         → uma tarefa
 *   POST   api.php   (corpo: aluno_id, titulo)  → cria
 *   PUT    api.php?id=7  (corpo JSON)           → atualiza (parcial OK)
 *   DELETE api.php?id=7&aluno_id=ana123         → apaga
 *
 * Aceita corpo POST tanto como form-urlencoded quanto JSON.
 * Para PUT, espera JSON no corpo (contentType: application/json).
 *
 * Banco: SQLite em ./tarefas.db (criado automaticamente na 1ª chamada).
 */

// ---------- CORS ----------
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------- Banco ----------
$dbPath = __DIR__ . '/tarefas.db';
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("
        CREATE TABLE IF NOT EXISTS tarefas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            aluno_id TEXT NOT NULL,
            titulo TEXT NOT NULL,
            feito INTEGER NOT NULL DEFAULT 0,
            criado_em TEXT NOT NULL DEFAULT (datetime('now','localtime'))
        )
    ");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_aluno ON tarefas(aluno_id)");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'banco indisponível']);
    exit;
}

// ---------- Helpers ----------
function entrada() {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) return $json;
    // Fallback: form-urlencoded
    parse_str($raw, $parsed);
    if (!empty($parsed)) return $parsed;
    return $_POST;
}

function exige($cond, $msg, $http = 400) {
    if (!$cond) {
        http_response_code($http);
        echo json_encode(['erro' => $msg]);
        exit;
    }
}

function jsonResp($dados, $http = 200) {
    http_response_code($http);
    echo json_encode($dados);
    exit;
}

function tarefaPorId($db, $id, $aluno_id) {
    $st = $db->prepare('SELECT * FROM tarefas WHERE id = ? AND aluno_id = ?');
    $st->execute([$id, $aluno_id]);
    return $st->fetch(PDO::FETCH_ASSOC);
}

// ---------- Roteamento ----------
$metodo = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$alunoIdQuery = isset($_GET['aluno_id']) ? trim($_GET['aluno_id']) : null;

switch ($metodo) {

    case 'GET':
        exige($alunoIdQuery, 'aluno_id é obrigatório');

        if ($id) {
            $t = tarefaPorId($db, $id, $alunoIdQuery);
            exige($t, 'tarefa não encontrada', 404);
            jsonResp($t);
        }

        $st = $db->prepare('SELECT * FROM tarefas WHERE aluno_id = ? ORDER BY id DESC');
        $st->execute([$alunoIdQuery]);
        jsonResp($st->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $body = entrada();
        $alunoId = isset($body['aluno_id']) ? trim($body['aluno_id']) : '';
        $titulo  = isset($body['titulo'])   ? trim($body['titulo'])   : '';

        exige($alunoId !== '', 'aluno_id é obrigatório');
        exige($titulo !== '',  'titulo é obrigatório');
        exige(strlen($titulo) <= 200, 'titulo grande demais (máx 200)');

        $st = $db->prepare('INSERT INTO tarefas (aluno_id, titulo) VALUES (?, ?)');
        $st->execute([$alunoId, $titulo]);
        $novoId = $db->lastInsertId();

        $st2 = $db->prepare('SELECT * FROM tarefas WHERE id = ?');
        $st2->execute([$novoId]);
        jsonResp($st2->fetch(PDO::FETCH_ASSOC), 201);
        break;

    case 'PUT':
        exige($id, 'id é obrigatório (na query string)');

        $body = entrada();
        $alunoId = isset($body['aluno_id']) ? trim($body['aluno_id']) : '';
        exige($alunoId !== '', 'aluno_id é obrigatório no corpo');

        $t = tarefaPorId($db, $id, $alunoId);
        exige($t, 'tarefa não encontrada', 404);

        // Atualização parcial — só os campos enviados
        $novoTitulo = isset($body['titulo']) ? trim($body['titulo']) : $t['titulo'];
        $novoFeito  = isset($body['feito'])  ? (int)$body['feito']   : (int)$t['feito'];

        exige($novoTitulo !== '', 'titulo não pode ficar vazio');
        exige(strlen($novoTitulo) <= 200, 'titulo grande demais');
        $novoFeito = $novoFeito ? 1 : 0;

        $st = $db->prepare('UPDATE tarefas SET titulo = ?, feito = ? WHERE id = ? AND aluno_id = ?');
        $st->execute([$novoTitulo, $novoFeito, $id, $alunoId]);

        jsonResp(tarefaPorId($db, $id, $alunoId));
        break;

    case 'DELETE':
        exige($id, 'id é obrigatório');
        exige($alunoIdQuery, 'aluno_id é obrigatório');

        $t = tarefaPorId($db, $id, $alunoIdQuery);
        exige($t, 'tarefa não encontrada', 404);

        $st = $db->prepare('DELETE FROM tarefas WHERE id = ? AND aluno_id = ?');
        $st->execute([$id, $alunoIdQuery]);

        jsonResp(['ok' => true, 'id' => $id]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'método não suportado']);
}
