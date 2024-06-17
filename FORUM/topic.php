<?php if (!defined('SEC')) {
    die('Forbidden path');
}

function getChildPosts($post_id, $db) {
    $child_posts = array($post_id);
    $result = mysqli_query($db, "SELECT id FROM post WHERE replyid = '$post_id'");
    while ($row = mysqli_fetch_assoc($result)) {
        $child_posts = array_merge($child_posts, getChildPosts($row['id'], $db));
    }
    return $child_posts;
}

$messagesPerPage = 6;

$page = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'add_post' && $GLOBALS['currentuser']) {

    $message = mysqli_real_escape_string($db, $_REQUEST['message']);
   

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $photo = $_FILES['photo'];
        $photoPath = 'uploads/' . date('Y-m-d_H-i-s', time()) . '_' .basename($photo['name']);
        move_uploaded_file($photo['tmp_name'], $photoPath);

        $result = mysqli_query($db, "
        INSERT INTO post (topicid, userid, message, createdate, photo)
        VALUES('{$_REQUEST['topic_id']}','{$GLOBALS['currentuser']['id']}','" . $message . "','" . time() . "', '" . $photoPath . "')
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

    $result = mysqli_query($db, "
    INSERT INTO post (topicid, userid, message, createdate)
    VALUES('{$_REQUEST['topic_id']}','{$GLOBALS['currentuser']['id']}','" . $message . "','" . time() . "')
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
    
    $message = mysqli_real_escape_string($db, $_REQUEST['message']);
    $result = mysqli_query($db, "
    INSERT INTO post (topicid, userid, message, createdate, replyid)
    VALUES('{$_REQUEST['topic_id']}','{$GLOBALS['currentuser']['id']}','" . $message . "','" . time() . "','{$_REQUEST['reply_id']}')
    ");

    $result = mysqli_query($db, "
    UPDATE topic
    SET countmessages = countmessages +1,
    replydate = '" . time() . "',
    replyuserid = '{$GLOBALS['currentuser']['id']}'
    WHERE id = '{$_REQUEST['topic_id']}'
    ");

    $result = mysqli_query($db, "
    INSERT INTO notifications (userid, topicid, postid, message, datecreated)
    VALUES('{$_REQUEST['user_id']}',{$_REQUEST['topic_id']},{$_REQUEST['post_id']},'" . $message . "','" . time() . "')
    ");
    }

    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'delete_post' && $GLOBALS['currentuser']) {
    
    if((isset($GLOBALS['currentuser']['id']) && $GLOBALS['currentuser']['roleid'] === '1')){

    $result = mysqli_query($db, "
    INSERT INTO moderation (userid, targetuserid, topicid, postid, moderationaction, datemoderated)
    VALUES('{$GLOBALS['currentuser']['id']}','{$_REQUEST['user_from']}','{$_REQUEST['topic_id']}','{$_REQUEST['post_id']}','delete','" . time() . "')
    ");
    }

    $post_id = mysqli_real_escape_string($db, $_REQUEST['post_id']);
    
    $all_posts = getChildPosts($post_id, $db);

    $result = mysqli_query($db, "
    DELETE FROM post
    WHERE id IN ('" . implode("','", $all_posts) . "')
    ");

    $deleted_posts = mysqli_affected_rows($db);

    $result = mysqli_query($db, "
    UPDATE topic
    SET countmessages = countmessages -'$deleted_posts'
    WHERE id = '{$_REQUEST['topic_id']}'
    ");

    $result = mysqli_query($db, "
    DELETE n
    FROM notifications n
    WHERE n.postid IN ('" . implode("','", $all_posts) . "')
    ");


    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'edit_post' && $GLOBALS['currentuser']) {

    if (isset($_REQUEST['edit_save'])) {

        if((isset($GLOBALS['currentuser']['id']) && $GLOBALS['currentuser']['roleid'] === '1')){

        $result = mysqli_query($db, "
        INSERT INTO moderation (userid, targetuserid, topicid, postid, moderationaction, datemoderated)
        VALUES('{$GLOBALS['currentuser']['id']}','{$_REQUEST['user_id']}','{$_REQUEST['topic_id']}','{$_REQUEST['post_id']}','edit','" . time() . "')
        ");
        }

    $message = mysqli_real_escape_string($db, $_REQUEST['message']);
    $result = mysqli_query($db, "
    UPDATE post
    SET message = '" . $message . "'
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

                $result = mysqli_query($db, "DELETE FROM favorite WHERE userid = '{$GLOBALS['currentuser']['id']}'
                AND postid = '{$_REQUEST['post_id']}'");

                $result = mysqli_query($db, "
                UPDATE post
                SET countlike = countlike -1
                WHERE id = '{$_REQUEST['post_id']}'
                ");

                die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));

            }
        }
    }

    $result = mysqli_query($db, "
    INSERT INTO favorite (userid, topicid, postid, likedate)
    VALUES('{$GLOBALS['currentuser']['id']}','{$_REQUEST['topic_id']}','{$_REQUEST['post_id']}','" . time() . "')
    ");

    $result = mysqli_query($db, "
    UPDATE post
    SET countlike = countlike +1
    WHERE id = '{$_REQUEST['post_id']}'
    ");

    die(header('location: /' . FORUM_DIR . '/?page=topic&topic_id=' . $_REQUEST['topic_id']));
}

if (isset($GLOBALS['currentuser']['id'])) {
$result = $db->query("SELECT * FROM favorite WHERE userid = '{$GLOBALS['currentuser']['id']}'");
$likelist = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $likelist[] = $row['postid'];
    }
}
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
                <td id=<?= $post['id']?> >
                    <?= $userlist[$post['userid']]['name'] ?>     <?= $userlist[$post['userid']]['surname'] ?></a>
                    <span><?= date('d-m-Y H:i:s', $post['createdate']) ?></span>
                    <?php if (isset($GLOBALS['currentuser']['id'])): ?>
                        <a
                            href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&post=reply&post_id=<?= $post['id']?>&page_num=<?= $page?>">Reply</a>
                    <?php endif ?>
                    <?php if ((isset($GLOBALS['currentuser']['id']) && (int) $post['userid'] === (int) $GLOBALS['currentuser']['id']) || (isset($GLOBALS['currentuser']['id']) && $GLOBALS['currentuser']['roleid'] === '1')):?>
                        <a href="#" class="delete-button" data-post-id="<?= $post['id'] ?>">Delete</a>
                        <div id="deleteModal-<?= $post['id'] ?>" class="modal" style="display: none;">
                            <div class="modal-content">
                                <h2>Delete warning</h2>
                                <p>Are you sure you want to delete this message and all messages associated with it?</p>
                                <div class="modal-buttons">
                                    <button data-post-id="<?= $post['id'] ?>" onclick="deletePost('/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&action=delete_post&post_id=<?= $post['id']?>&user_from=<?= $post['userid']?>&page_num=<?= $page?>')">Yes</button>
                                    <button onclick="closeModal(<?= $post['id'] ?>)">No</button>
                                </div>
                            </div>
                        </div>
                        <a
                            href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&post=edit&post_id=<?= $post['id']?>&page_num=<?= $page?>">Edit</a>
                    <?php endif ?>
                </td>
                <td>
                    <?php if (isset($post['replyid']) && isset($postlistfull[$post['replyid']])): ?>
                        <div class="text-cut">
                            <?= $userlist[$postlistfull[$post['replyid']]['userid']]['name'] ?>
                            <?= $userlist[$postlistfull[$post['replyid']]['userid']]['surname'] ?>:
                            <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&page_num=<?=ceil(array_search($post['replyid'],array_column($postlistfull, 'id'))/6)?>#<?= $post['replyid'] ?>">
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
                            <input type="hidden" name="user_id" value="<?= $post['userid'] ?>">
                            <textarea name="message"><?= $post['message'] ?></textarea>
                            <input type="submit" name="edit_save" value="Save">
                            <input type="submit" value="Cancel">
                        </form>
                        <?php elseif (isset($_REQUEST['post']) && $GLOBALS['currentuser'] && $_REQUEST['post'] === 'reply' && (int) $post['id'] === (int) $_REQUEST['post_id']): ?>
                        <form class="message_form"
                            action="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&action=reply_post&post_id=<?= $post['id'] ?>"
                            method="post">
                            <input type="hidden" name="reply_id" value="<?= $post['id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $post['userid'] ?>">
                            <textarea name="message"></textarea>
                            <input type="submit" name="reply_send" value="Save">
                            <input type="submit" value="Cancel">
                        </form>
                    <?php else: ?>
                        <?= $post['message'] ?>
                        <?php foreach ($postlistfull as $postitem): ?>
                            <?php if (isset($post['id']) && $post['id'] == $postitem['replyid']): ?>
                                <hr style="margin-bottom: 0;">
                                <div class="text-cut">
                                    <?= $userlist[$postitem['userid']]['name'] ?>
                                    <?= $userlist[$postitem['userid']]['surname'] ?> Replys:
                                    <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&page_num=<?=ceil(array_search($postitem['id'],array_column($postlistfull, 'id'))/6)?>#<?= $postitem['id'] ?>">
                                        <?= $postitem['message'] ?>
                                    </a>
                                </div>
    <?php endif ?>
<?php endforeach ?>

                        <div class="containerlike">
                        <div class="like">
    <text style="font-size: 19px; margin-top: 1px;">
        <?= $post['countlike'] ?>
    </text>
    <form
        action="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&action=like_post&post_id=<?= $post['id'] ?>"
        method="POST">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <button type="submit" style="border: none; background: none;">
        <img src="<?= (isset($GLOBALS['currentuser']) && array_key_exists('id', $GLOBALS['currentuser'])) ? (in_array($post['id'], $likelist) ? 'uploads/heart2.jpg' : 'uploads/heart1.jpg') : 'uploads/heart1.jpg' ?>" alt="like" width="22" height="22">
        </button>
    </form>
</div>
                            <?php if (!empty($post['photo'])): ?>
                                <img src="<?= $post['photo'] ?>" alt="photo_src" class="post-photo">
                                <?php endif; ?>
                        </div>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>

    </tbody>
</table>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&page_num=1">«</a>
    <?php endif; ?>

    <?php
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $startPage + 4);
    if ($endPage > $totalPages) {
        $startPage = max(1, $totalPages - 4);
        $endPage = $totalPages;
    }
    ?>

    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="current"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&page_num=<?php echo $i?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $_REQUEST['topic_id'] ?>&page_num=<?php echo $totalPages?>">»</a>
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
                    <form class="message_form" action="/<?= FORUM_DIR ?>/?page=topic&action=add_post" method="post" enctype="multipart/form-data">
                        <input type="file" name="photo" accept="image/*" lang="en">
                        <input type="hidden" name="topic_id" value="<?= $_REQUEST['topic_id'] ?>">
                        <textarea name="message" placeholder="Write answer" required></textarea>
                        <input type="submit" name="reply" value="Send">
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
<?php endif ?>

<?php include PATH . '/footer.php' ?>