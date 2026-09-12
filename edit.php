<?php
// edit.php
require 'db.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM todos WHERE id = :id");
$stmt->execute(['id' => $id]);
$todo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$todo) {
    header('Location: index.php');
    exit;
}

// Handle the update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    if ($title !== '') {
        $stmt = $db->prepare("UPDATE todos SET title = :title WHERE id = :id");
        $stmt->execute(['title' => $title, 'id' => $id]);
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Todo</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 40px auto; }
        input[type=text] { padding: 6px; width: 70%; }
        button { padding: 6px 10px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Edit Todo</h1>
    <form method="post">
        <input type="hidden" name="id" value="<?= $todo['id'] ?>">
        <input type="text" name="title" value="<?= htmlspecialchars($todo['title']) ?>" required>
        <button type="submit">Save</button>
        <a href="index.php">Cancel</a>
    </form>
</body>
</html>