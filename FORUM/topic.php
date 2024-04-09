<?php if (!defined('SEC')) {
    die('Forbidden path');
}

$messagesPerPage = 6;

$page = isset($_GET['puge']) ? intval($_GET['puge']) : 1;

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'add_post' && $GLOBALS['currentuser']) {


    $result = mysqli_query($db, "
    INSERT INTO post (topicid, userid, message, createdate)
    VALUES('{$_REQUEST['topic_id']}','{$GLOBALS['currentuser']['id']}','{$_REQUEST['message']}','" . time() . "')
    ");

    $result = mysqli_query($db, "
    UPDATE topic
    SET countmessages = countmessages +1,
    replydate = '" . time() . "',
    replyuserid = '{$GLOBALS['currentuser']['id']}'
    WHERE id = '{$_REQUEST['topic_id']}'
    ");

    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'reply_post' && $GLOBALS['currentuser']) {

    if (isset($_REQUEST['reply_send'])) {
    $result = mysqli_query($db, "
    INSERT INTO post (topicid, userid, message, createdate, replyid)
    VALUES('{$_REQUEST['topic_id']}','{$GLOBALS['currentuser']['id']}','{$_REQUEST['message']}','" . time() . "','{$_REQUEST['reply_id']}')
    ");

    $result = mysqli_query($db, "
    UPDATE topic
    SET countmessages = countmessages +1,
    replydate = '" . time() . "',
    replyuserid = '{$GLOBALS['currentuser']['id']}'
    WHERE id = '{$_REQUEST['topic_id']}'
    ");
    }

    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete_post' && $GLOBALS['currentuser']) {


    $result = mysqli_query($db, "DELETE FROM post WHERE id = '{$_REQUEST['post_id']}'");

    $result = mysqli_query($db, "
    UPDATE topic
    SET countmessages = countmessages -1
    WHERE id = '{$_REQUEST['topic_id']}'
    ");

    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'edit_post' && $GLOBALS['currentuser']) {

    if (isset($_REQUEST['edit_save'])) {
        $result = mysqli_query($db, "
    UPDATE post
    SET message = '{$_REQUEST['message']}'
    WHERE id = '{$_REQUEST['post_id']}'
    ");
    }
    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'like_post' && $GLOBALS['currentuser']) {

    $result = $db->query("SELECT * FROM favorite WHERE userid = '{$GLOBALS['currentuser']['id']}' and postid = '{$_REQUEST['post_id']}'");
    $like = [];
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            $like = $row;
            if ($like['userid'] === $GLOBALS['currentuser']['id'] && $like['postid'] === $_REQUEST['post_id']) {

                die(header('location:/' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id'] . '&message=like_exists'));

            }
        }
    }

    $result = mysqli_query($db, "
    INSERT INTO favorite (userid, postid, likedate)
    VALUES('{$GLOBALS['currentuser']['id']}','{$_REQUEST['post_id']}','" . time() . "')
    ");

    $result = mysqli_query($db, "
    UPDATE post
    SET countlike = countlike +1
    WHERE id = '{$_REQUEST['post_id']}'
    ");

    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));
}

$result = $db->query("SELECT COUNT(*) as total FROM post WHERE topicid = '{$_REQUEST['topic_id']}'");
$row = $result->fetch_assoc();
$totalMessages = $row['total'];

$totalPages = ceil($totalMessages / $messagesPerPage);

$offset = ($page - 1) * $messagesPerPage;
if ($offset < 0) {
    $offset = 0;
}

$result = $db->query("SELECT * FROM post WHERE topicid ='{$_REQUEST['topic_id']}'");
$postlistfull = [];
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $postlistfull[$row['id']] = $row;
    }
}

$result = $db->query("SELECT * FROM post WHERE topicid = '{$_REQUEST['topic_id']}' LIMIT {$messagesPerPage} OFFSET {$offset}");
$postlist = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $postlist[$row['id']] = $row;
    }
}

$result = $db->query("SELECT * FROM user");
$userlist = [];
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $userlist[$row['id']] = $row;
    }
}
$result = $db->query("SELECT * FROM topic WHERE id = '{$_REQUEST['topic_id']}'");
$topic_name = $result->fetch_assoc();
?>
<?php include PATH . '/header.php' ?>
<h1><a href="/<?= FORUM_DIR ?>/">Forum</a> -
    <?= $topic_name['name'] ?>
</h1>
<table class="messages">
    <thead>
        <tr>
            <th>Author</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($postlist as $post): ?>
            <tr>
                <td id=<?= $post['id']?> page=<?=$page?>>
                    <?= $userlist[$post['userid']]['name'] ?>     <?= $userlist[$post['userid']]['surname'] ?></a>
                    <span><?= date('d-m-Y H:i:s', $post['createdate']) ?></span>
                    <?php if (isset($GLOBALS['currentuser']['id'])): ?>
                        <a
                            href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&post=reply&post_id=<?= $post['id'] ?>">Reply</a>
                    <?php endif ?>
                    <?php if (isset($GLOBALS['currentuser']['id']) && (int) $post['userid'] === (int) $GLOBALS['currentuser']['id']): ?>
                        <a
                            href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&action=delete_post&post_id=<?= $post['id'] ?>">Delete</a>
                        <a
                            href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&post=edit&post_id=<?= $post['id'] ?>">Edit</a>
                    <?php endif ?>
                </td>
                <td>
                    <?php if (isset($post['replyid'])): ?>
                        <div class="text-cut">
                            <?= $userlist[$postlistfull[$post['replyid']]['userid']]['name'] ?>
                            <?= $userlist[$postlistfull[$post['replyid']]['userid']]['surname'] ?>:
                            <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>#<?= $post['replyid'] ?>">
                                <?= $postlistfull[$post['replyid']]['message'] ?>
                            </a>
                        </div>
                        <hr style="margin-bottom: 0;">
                    <?php endif ?>
                    <?php if (isset($_REQUEST['post']) && $GLOBALS['currentuser'] && $_REQUEST['post'] === 'edit' && (int) $post['id'] === (int) $_REQUEST['post_id']): ?>
                        <form class="message_form"
                            action="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&action=edit_post&post_id=<?= $post['id'] ?>"
                            method="post">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <textarea name="message"><?= $post['message'] ?></textarea>
                            <input type="submit" name="edit_save" value="Save">
                            <input type="submit" value="Cancel">
                        </form>
                        <?php elseif (isset($_REQUEST['post']) && $GLOBALS['currentuser'] && $_REQUEST['post'] === 'reply' && (int) $post['id'] === (int) $_REQUEST['post_id']): ?>
                        <form class="message_form"
                            action="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&action=reply_post&post_id=<?= $post['id'] ?>"
                            method="post">
                            <input type="hidden" name="reply_id" value="<?= $post['id'] ?>">
                            <textarea name="message"></textarea>
                            <input type="submit" name="reply_send" value="Save">
                            <input type="submit" value="Cancel">
                        </form>
                    <?php else: ?>
                        <?= $post['message'] ?>
                        <div class="containerlike">
                            <div class="like">
                                <text style="font-size: 19px; margin-top: 1px;">
                                    <?= $post['countlike'] ?>
                                </text>
                                <form
                                    action="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&action=like_post&post_id=<?= $post['id'] ?>"
                                    method="post">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" style="border: none; background: none;">
                                        <img src="https://img.favpng.com/8/22/14/computer-icons-like-button-heart-symbol-png-favpng-0UiUdvnN2R0Sd6BAUpmSjMB2Z.jpg"
                                            alt="like" width="22" height="22">
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>

    </tbody>
</table>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&puge=<?php echo ($page - 1) ?>">&laquo; </a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="current"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&puge=<?php echo $i?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&puge=<?php echo ($page + 1)?>"> &raquo;</a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['login'])): ?>
    <table class="reply_form">
        <thead>
            <tr>
                <th>Reply to theme</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <form class="message_form" action="/<?= FORUM_DIR ?>/?page=topic&action=add_post" method="post">
                        <input type="hidden" name="topic_id" value="<?= $_REQUEST['topic_id'] ?>">
                        <textarea name="message" placeholder="Write answer"></textarea>
                        <input type="submit" name="reply" value="Send">
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
<?php endif ?>

<?php include PATH . '/footer.php' ?>