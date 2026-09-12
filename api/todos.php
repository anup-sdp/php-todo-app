<?php
// api/todos.php  ->  http://localhost:8000/api/todos.php
require __DIR__ . '/../db.php';

// Allow requests from any origin (fine for learning; lock this down in production)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight request support (needed for browsers calling PUT/DELETE with JSON)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); 
    // ^ 204: indicates that the server successfully processed the request, but there is no content to return in the response body.
    exit;
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function send(int $status, $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {

    // ---- READ ----
    case 'GET':
        if ($id) {
            $stmt = $db->prepare('SELECT * FROM todos WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $todo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$todo) {
                send(404, ['error' => 'Todo not found']);
            }
            send(200, $todo);
        } else {
            $todos = $db->query('SELECT * FROM todos ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
            send(200, $todos);
        }
        break;

    // ---- CREATE ----
    case 'POST':
        $body = json_input();
        $title = trim($body['title'] ?? '');

        if ($title === '') {
            send(422, ['error' => 'Field "title" is required']);
        }

        $stmt = $db->prepare('INSERT INTO todos (title) VALUES (:title)');
        $stmt->execute(['title' => $title]);

        $newId = $db->lastInsertId();
        $stmt = $db->prepare('SELECT * FROM todos WHERE id = :id');
        $stmt->execute(['id' => $newId]);

        send(201, $stmt->fetch(PDO::FETCH_ASSOC));
        break;

    // ---- UPDATE ----
    case 'PUT':
        if (!$id) {
            send(400, ['error' => 'Missing "id" query parameter, e.g. todos.php?id=1']);
        }

        $stmt = $db->prepare('SELECT * FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $todo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$todo) {
            send(404, ['error' => 'Todo not found']);
        }

        $body = json_input();
        $title = array_key_exists('title', $body) ? trim($body['title']) : $todo['title'];
        $done  = array_key_exists('done', $body) ? (int)!!$body['done'] : $todo['done'];

        if ($title === '') {
            send(422, ['error' => 'Field "title" cannot be empty']);
        }

        $stmt = $db->prepare('UPDATE todos SET title = :title, done = :done WHERE id = :id');
        $stmt->execute(['title' => $title, 'done' => $done, 'id' => $id]);

        $stmt = $db->prepare('SELECT * FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        send(200, $stmt->fetch(PDO::FETCH_ASSOC));
        break;

    // ---- DELETE ----
    case 'DELETE':
        if (!$id) {
            send(400, ['error' => 'Missing "id" query parameter, e.g. todos.php?id=1']);
        }

        $stmt = $db->prepare('SELECT * FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            send(404, ['error' => 'Todo not found']);
        }

        $stmt = $db->prepare('DELETE FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);

        send(200, ['message' => 'Todo deleted', 'id' => $id]);
        break;

    default:
        send(405, ['error' => 'Method not allowed']);
}
/*
http://localhost:8000/api/todos.php
get /api/todos.php
get /api/todos.php?id=1
post /api/todos.php  {"title": "New Todo"}
put /api/todos.php?id=1  {"title": "Updated Todo", "done": true}
delete /api/todos.php?id=1

curl -X POST http://localhost:8000/api/todos.php -d '{"title":"Learn PHP"}'
curl http://localhost:8000/api/todos.php
curl -X PUT "http://localhost:8000/api/todos.php?id=1" -d '{"done":true}'
curl -X DELETE "http://localhost:8000/api/todos.php?id=1"
*/

?>