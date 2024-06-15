<?php
if (!defined('SEC')) {
    die('Forbidden path');
}
if (isset($_REQUEST['action']) == 'reg' && !$_SESSION['login']) {

    $email = mysqli_real_escape_string($db, $_REQUEST['email']);
    $login = mysqli_real_escape_string($db, $_REQUEST['login']);
    $password = mysqli_real_escape_string($db, $_REQUEST['password'] = password_hash($_REQUEST['password'], PASSWORD_BCRYPT, ['cost=>12']));
    $name = mysqli_real_escape_string($db, $_REQUEST['name']);
    $surname = mysqli_real_escape_string($db, $_REQUEST['surname']);
    
    $result = $db->query("SELECT * FROM user WHERE login = '" . $login . "' or email = '" . $email . "'");
    $user = [];
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            $user = $row;
            if ($user['login'] === $login || $user['email'] === $email) {

                die(header('location:/' . FORUM_DIR . '/?page=reg&message=reg_exists'));

            }
        }
    }

    $result = mysqli_query($db, "
   INSERT INTO user (email, login, password, name, surname, roleid)
   VALUES('" . $email . "','" . $login . "', '" . $password . "','" . $name . "', '" . $surname . "','0')");

    die(header('location: /' . FORUM_DIR . '/?message=reg_success'));
}
?>

<?php include PATH . '/header.php' ?>

<h1>Registration</h1>
<?php if (!isset($_SESSION['login'])): ?>
    <form action="/<?= FORUM_DIR ?>/?page=reg&action=reg" method="post">
        <input type="email" name="email" value="" placeholder="Enter e-mail" pattern="^[^\s]+$" required><br>
        <input type="text" name="login" value="" placeholder="Enter login" pattern="^[^\s]+$" required><br>
        <input type="password" name="password" value="" placeholder="Enter password" pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" required><br>
        <input type="text" name="name" value="" placeholder="Enter name" pattern="^[^\s]+$" required><br>
        <input type="text" name="surname" value="" placeholder="Enter surname" pattern="^[^\s]+$" required><br>
        <input type="submit" value="Sign in"><br>
    </form>
<?php else: ?>
    <div>You are already logged in, <a href="/<?= FORUM_DIR ?>/?action=logout">Logout </a></div>
<?php endif ?>

<?php include PATH . '/footer.php' ?>