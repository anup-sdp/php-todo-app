<?php
// index.php
require 'db.php';

$todos = $db->query("SELECT * FROM todos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Todo App</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 40px auto; background: azure; }
        li { margin: 8px 0; }
        .done { text-decoration: line-through; color: #888; }
        form.inline { display: inline; }
        input[type=text] { padding: 6px; width: 70%; }
        button { padding: 6px 10px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>My Todos</h1>

    <form action="add.php" method="post"> <!-- add.php -->
        <input type="text" name="title" placeholder="What needs doing?" required>
        <button type="submit">Add</button>
    </form>
    

    <ul>
        <?php foreach ($todos as $todo): ?>
            <li>
                <span class="<?= $todo['done'] ? 'done' : '' ?>"> <!-- short echo tag -->
                    <?= htmlspecialchars($todo['title']) ?>
                    <!-- ^ escapes special characters so user input can't break your HTML or inject scripts -->
                </span>

                <form class="inline" action="toggle.php" method="post">
                    <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                    <button type="submit"><?= $todo['done'] ? 'Undo' : 'Done' ?></button>
                </form>

                <a href="edit.php?id=<?= $todo['id'] ?>">Edit</a>

                <form class="inline" action="delete.php" method="post"
                      onsubmit="return confirm('Delete this todo?');">
                    <input type="hidden" name="id" value="<?= $todo['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>

        <?php if (empty($todos)): ?>
            <li>No todos yet — add one above!</li>
        <?php endif; ?>
    </ul>
</body>
</html>