<?php
if (!defined('SEC')) {
    die('Forbidden path');
}

if (isset($_REQUEST['action']) == 'create_topic' && $GLOBALS['currentuser']) {


    $result = mysqli_query($db, "
    INSERT INTO topic (name, countmessages, userid, createdate, categoryid)
    VALUES('{$_REQUEST['topic']}','1', '{$GLOBALS['currentuser']['id']}','" . time() . "','{$_REQUEST['category_id']}')
    ");

    $topic_id = $db->insert_id;

    $result = mysqli_query($db, "
    INSERT INTO post (topicid, userid, message, createdate)
    VALUES('{$topic_id}','{$GLOBALS['currentuser']['id']}','{$_REQUEST['message']}','" . time() . "')
");

    $result = mysqli_query($db, "
    UPDATE category
    SET counttopics = counttopics +1
    WHERE id = '{$_REQUEST['category_id']}'
    ");

    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $topic_id));

}

$result = $db->query("SELECT * FROM topic WHERE categoryid ='{$_REQUEST['category_id']}'");
$topiclist = [];
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $topiclist[] = $row;
    }
}

$result = $db->query("SELECT * FROM user");
$userlist = [];
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $userlist[$row['id']] = $row;
    }
}
?>


<?php include PATH . '/header.php' ?>



<table>
    <thead>
        <tr>
            <th>Theme name</th>
            <th>Number of messages</th>
            <th>Author</th>
            <th>Date of creation</th>
            <th>Last answer</th>
            <th>Response date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($topiclist as $topic): ?>
            <tr>
                <td><a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $topic['id'] ?>">
                        <?= $topic['name'] ?>
                    </a></td>
                <td>
                    <?= $topic['countmessages'] ?>
                </td>
                <td>
                    <?= $userlist[$topic['userid']]['name'] ?>
                    <?= $userlist[$topic['userid']]['surname'] ?></a>
                </td>
                <td><?= date('d-m-Y H:i:s', $topic['createdate']) ?></td>
                <td>
                    <?= $topic['replyuserid'] ? $userlist[$topic['replyuserid']]['name'] : '' ?>
                    <?= $topic['replyuserid'] ? $userlist[$topic['replyuserid']]['surname'] : '' ?>
                </td>
                <td>
                    <?= $topic['replydate'] ? date('d-m-Y H:i:s', $topic['replydate']) : '' ?>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php if ($GLOBALS['currentuser']): ?>
    <table class="reply_form">
        <thead>
            <tr>
                <th>Create new theme</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <form class="message_form" action="/<?= FORUM_DIR ?>/?page=category&action=create_topic" method="post">
                        <input type="text" name="topic" placeholder="Theme name">
                        <textarea name="message" placeholder="Write message"></textarea>
                        <input type="submit" name="create" value="Create">
                        <input type="hidden" name="category_id" value="<?= $_REQUEST['category_id'] ?>">
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
<?php endif ?>
</div>


<?php include PATH . '/footer.php' ?>