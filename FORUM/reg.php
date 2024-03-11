<?php
if (!defined('SEC')) {
    die('Forbidden path');
}
if (isset($_REQUEST['action']) == 'reg' && !$_SESSION['login']) {

    $result = $db->query("SELECT * FROM user WHERE login = '{$_REQUEST['login']}' or email = '{$_REQUEST['email']}'");
    $user = [];
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            $user = $row;
            if ($user['login'] === $_REQUEST['login'] || $user['email'] === $_REQUEST['email']) {

                die(header('location:/' . FORUM_DIR . '/?page=reg&message=reg_exists'));

            }
        }
    }

    $_REQUEST['password'] = password_hash($_REQUEST['password'], PASSWORD_BCRYPT, ['cost=>12']);
    $result = mysqli_query($db, "
   INSERT INTO user (email, login, password, name, surname)
   VALUES('{$_REQUEST['email']}','{$_REQUEST['login']}', '{$_REQUEST['password']}','{$_REQUEST['name']}', '{$_REQUEST['surname']}')");

    die(header('location: /' . FORUM_DIR . '/?message=reg_success'));
}
?>

<?php include PATH . '/header.php' ?>

<h1>Registration</h1>
<?php if (!isset($_SESSION['login'])): ?>
    <form action="/<?= FORUM_DIR ?>/?page=reg&action=reg" method="post">
        <input type="email" name="email" value="" placeholder="Enter e-mail" required><br>
        <input type="text" name="login" value="" placeholder="Enter login" required><br>
        <input type="password" name="password" value="" placeholder="Enter password" required><br>
        <input type="text" name="name" value="" placeholder="Enter name" required><br>
        <input type="text" name="surname" value="" placeholder="Enter surname" required><br>
        <input type="submit" value="Sign in"><br>
    </form>
<?php else: ?>
    <div>You are already logged in, <a href="/<?= FORUM_DIR ?>/?action=logout">Logout </a></div>
<?php endif ?>

<?php include PATH . '/footer.php' ?>