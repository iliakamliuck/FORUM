<?php if (!defined('SEC')) {
    die('Forbidden path');
}

if (isset($_REQUEST['message'])) {
    $systemText = '';
    switch ($_REQUEST['message']) {
        case 'reg_success';
            $systemText = 'Success registration';
            break;
        case 'logout_success';
            $systemText = 'Success logout';
            break;
        case 'auth_deny';
            $systemText = 'Wrong login or password';
            break;
        case 'auth_success';
            $systemText = 'Success authorization';
            break;
        case 'reg_exists';
            $systemText = 'That login or e-mail already exists';
            break;
    }
}


if ($GLOBALS['currentuser']) {
    $result = $db->query("SELECT * FROM notifications WHERE userid = '{$GLOBALS['currentuser']['id']}' AND isread = false");
    $notificationlist = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $notificationlist[] = $row;
            }
        }
}
?>

<!DOCTYPE html>
<html lang="en" style="background: #f5f5f5">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <title>Forum</title>
    <style>
        .container {
            display: flex;
            flex-direction: row;

            width: 100%;

        }

        .container>* {
            flex: 1;
            margin: 0;
            padding: 0;
        }



        .niz {

            height: 200px;
            width: 25%;
            position: fixed;
            bottom: 0;
            z-index: 1;
        }



        .block {
            width: 250px;
            height: 250px;
            position: absolute;
            top: 20%;
            right: 0;
            bottom: 0;
            left: 22%;



        }

        .text-cut {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 700px;
        }


        .like {
            text-align: left;
            display: flex;
            position: absolute;
            bottom: 0px;
            right: 0;
        }


        .containerlike {
            position: relative;
            margin-top: 50px;
        }

        .left-column {

            padding: 1em;
            height: 65%;
            position: sticky;
            max-width: 30%;
            top: 0;


        }

        .right-column {
            background-color: #e3e3e3;
            padding: 1em;

        }

        body {
            font-size: 14px;
            font-family: Roboto, Arial;
        }

        .wrapper {
            display: grid;
            padding: 1%;
        }

        h1 {
            font-size: 30px;
            padding: 0;
            margin: 20px 0 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: #aaa 1px solid;
            padding: 10px;
            vertical-align: top;
        }

        table th {
            background: #eee;
        }

        a {
            color: #006696;
        }

        a:hover {
            color: #CF82B1;
        }

        .messages tbody tr td:first-child {
            width: 25%;
        }

        .messages tbody tr td:first-child a {
            display: inline-block;
        }

        .messages tbody tr td:first-child span {
            display: block;
            margin: 10px 0;
        }

        .message_form textarea {
            width: 95%;
            padding: 3px;
            height: 200px;
            outline: none;
            border: #aaa 1px solid;
            font-size: 12px;
            background: #eee;
        }

        .message_form input[type="text"] {
            width: 95%;
            padding: 3px;
            border: #aaa 1px solid;
            font-size: 16px;
            background: #eee;
            outline: none;
        }

        .message_form input[type="submit"] {
            padding: 10px 40px;
            border: #aaa 1px solid;
            font-size: 12px;
            background: #eee;
            cursor: pointer;
        }

        .message_form input[type="submit"]:hover {
            border: #aaa 1px solid;
            font-size: 12px;
            background: #bdfdaf;
        }

        .reply_form {
            margin-top: 20px;
            margin-bottom: 50px;
        }


        .searchTerm {
            width: 70%;
            border: 3px solid #00B4CC;
            border-right: none;
            padding: 5px;
            height: 100%;
            border-radius: 5px;
            outline: none;
            color: #9DBFAF;
        }

        .searchButton {
            width: 22%;
            height: 33px;
            border: 1px solid #00B4CC;
            background: #00B4CC;
            text-align: center;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;

        }

        hr {
            margin-bottom: 2%;
        }

        .scale {
    width: 200px;
    height: 10px;
    border: 1px solid #ccc;
    background-color: #f2f2f2;
    position: relative;
    margin-bottom: 10px;
}

.fill {
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    width: 0;
}

.fill.negative {
    background-color: #990000; 
}

.fill.positive {
    background-color: #006600; 
}

.post-photo {
    max-width: 400px;
    height: auto;
    margin-bottom: 10px;
}

    </style>
</head>

<body>
    <div class="wrapper">
        <div class="sidebar_left"></div>
        <div class="content">




            <div>
                <a href="/<?= FORUM_DIR ?>/">Forum</a>
                <?php if (!(isset($_SESSION['login']))): ?>
                    <a href="/<?= FORUM_DIR ?>/?page=auth">Authorization</a>
                    <a href="/<?= FORUM_DIR ?>/?page=reg">Registration</a>
                <?php else: ?>
                    <a href="/<?= FORUM_DIR ?>/?action=logout">Logout(<?= $_SESSION['login'] ?>)</a>
                <?php endif ?>
                <?php if ((isset($_SESSION['login'])) && $GLOBALS['currentuser']['roleid'] === '1'): ?>
                    <a href="/<?= FORUM_DIR ?>/?page=mod">Moderation</a>
                <?php endif ?>
                |
                <span>Time: <?= date('d-m-Y H:i', time()) ?></span>
                |
                <?php if (!empty($notifications)): ?>
  <span class="badge"><?= count($notifications) ?></span>
<?php endif; ?>
<span class="icon" onclick="toggleNotifications()">🔔</span>
<ul class="notifications-list" style="display: none;">
  <?php if ($GLOBALS['currentuser']) foreach ($notificationlist as $notification):
    $result = $db->query("SELECT * FROM post WHERE topicid = '{$notification['topicid']}'");
    $postList = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $postList[$row['id']] = $row;
        }
    } ?>
    <li data-read="<?= $notification['isread'] ? 'true' : 'false' ?>">
    <div style="display: flex; align-items: center;">
      <span style="margin-right: 10px;"><?= $notification['datecreated'] ? date('d-m-Y H:i', $notification['datecreated']) : ''  ?></span>
      <span class="text-cut" style="margin-right: 10px;"><a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?=$notification['topicid']?>&page_num=<?=ceil(array_search($notification['postid'],array_column($postList, 'id'))/6)?>#<?= $notification['postid'] ?>"><?=$notification['message']?></a></span>
      <form method="POST" action="/<?= FORUM_DIR ?>/?action=notif_read">
      <input type="hidden" name="notif_id" value="<?= $notification['id'] ?>">
      <button type="submit" class="mark-as-read">Mark as Read</button>
    </form></div>
   </li>
  <?php endforeach; ?>
</ul>
            </div> 
            <hr>

            <?php if (isset($systemText)): ?>
                <div>
                    <b>System message:</b><br>
                    <?= $systemText ?>
                </div>
            <?php endif ?>