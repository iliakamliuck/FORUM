<?php
if (!defined('SEC')) {
    die('Forbidden path');
}
if (isset($_REQUEST['action']) == 'auth' && !isset($_SESSION['login'])) {

    $login = mysqli_real_escape_string($db, $_REQUEST['login']);
    $password = mysqli_real_escape_string($db, $_REQUEST['password']);
    $result = $db->query("SELECT * FROM user WHERE login = '" . $login . "'");
    $user = [];
    if ($result->num_rows > 0) {

        if ($row = $result->fetch_assoc()) {
            $user = $row;

            if (password_verify($_REQUEST['password'], $user['password'])) {

                $_SESSION['login'] = $user['login'];
                die(header('location: /' . FORUM_DIR . '/?message=auth_success'));
            } else {
                die(header('location: /' . FORUM_DIR . '/?page=auth&message=auth_deny'));
            }

        }
    } else {
        die(header('location: /' . FORUM_DIR . '/?page=auth&message=auth_deny'));
    }
}
?>

<?php include PATH . '/header.php' ?>

<h1>Authorization</h1>
<?php if (!$GLOBALS['currentuser']): ?>
    <form action="/<?= FORUM_DIR ?>/?page=auth&action=auth" method="post">
        <input type="text" name="login" value="" placeholder="Enter login"><br>
        <input type="password" name="password" value="" placeholder="Enter password"><br>
        <input type="submit" value="Log in"><br>
    </form>
<?php else: ?>
    <div>You are already logged in, <a href="/<?= FORUM_DIR ?>/?action=logout">Logout </a></div>
<?php endif ?>
<?php include PATH . '/footer.php' ?>