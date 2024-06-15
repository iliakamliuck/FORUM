<?php
define('SEC',true);
define('PATH',__DIR__);
$path = explode('\\', PATH);
define('FORUM_DIR',end($path));

include PATH.'/db_conn.php';

session_start();

$GLOBALS['currentuser'] = [];
if(isset($_SESSION['login'])){
    $login = mysqli_real_escape_string($db, $_SESSION['login']);
    $result = $db -> query("SELECT * FROM user WHERE login = '" . $login . "'");
    if($result->num_rows > 0) {
    
        if($row = $result->fetch_assoc()){

            $GLOBALS['currentuser'] = $row;
          
        }
    }
}


if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'notif_read' && $GLOBALS['currentuser']) {
    $result = mysqli_query($db, "
    UPDATE notifications
    SET isread = true
    WHERE id = '{$_REQUEST['notif_id']}'
    ");
    die(header('location: /' . FORUM_DIR . '/'));
}
if((isset($_REQUEST['action']) && $_REQUEST['action'] === 'logout' && $_SESSION['login']) || (isset($_SESSION['login']) && $GLOBALS['currentuser']['roleid']==='2')){
    unset($_SESSION['login']);
    die(header('location: /'.FORUM_DIR.'?message=logout_success'));
}
if(!(isset($_REQUEST['page']))){
    include PATH.'/list.php';
    exit;
}
if($_REQUEST['page'] === 'mod' && isset($_SESSION['login']) && $GLOBALS['currentuser']['roleid']==='1'){
    include PATH.'/mod.php';
    exit;
}
if($_REQUEST['page'] === 'reg'){
    include PATH.'/reg.php';
    exit;
}
if($_REQUEST['page'] === 'auth'){
    include PATH.'/auth.php';
    exit;
}
if($_REQUEST['page'] === 'topic'){
    include PATH.'/topic.php';
    exit;
}
if($_REQUEST['page'] === 'category'){
    include PATH.'/category.php';
    exit;
}
echo 'Error 404'
?>

