<?php

function dbConnect()
{

    $db = new \PDO('mysql:host=127.0.0.1;dbname=portfolio;charset=utf8',
        'sekoume', 'sekoume');
    return $db;
}

function getAll()
{
    $db = dbConnect();
    $all = $db->prepare('SELECT * FROM user');
    $all->execute();

    return $all;
}

$users = getAll();

?>

<html>
    <h1>Liste de mes utilisateurs</h1>

    <table>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo $user[1]; ?></td>
            <td><?php echo $user[2]; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

</html>
