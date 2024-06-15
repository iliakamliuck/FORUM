<?php
if (!defined('SEC')) {
    die('Forbidden path');
}

$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
$search = isset($_GET['search']) ? mysqli_real_escape_string($db, $_GET['search']) : '';

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'ban_user' && $GLOBALS['currentuser']) {

    $result = mysqli_query($db, "
    UPDATE user
    SET roleid = '2'
    WHERE id = '{$_REQUEST['user_id']}'
    ");

    die(header('location: /' . FORUM_DIR . '/?page=mod'));
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'unban_user' && $GLOBALS['currentuser']) {

    $result = mysqli_query($db, "
    UPDATE user
    SET roleid = '0'
    WHERE id = '{$_REQUEST['user_id']}'
    ");

    die(header('location: /' . FORUM_DIR . '/?page=mod'));
}

if (isset($_REQUEST['search'])) {

    $result = $db->query("SELECT * FROM user WHERE login LIKE '%$search%'"); 
    $user = $result->fetch_assoc();

    $result = $db->query("SELECT * FROM topic WHERE userid ='{$user['id']}'");
    $topiclist = [];
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            $topiclist[] = $row;
        }
    }

    $result = $db->query("SELECT * FROM post WHERE userid ='{$user['id']}'");
    $postlist = [];
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            $postlist[$row['id']] = $row;
        }
    }

}

$result = $db->query("SELECT * FROM roles");
$rolelist = [];
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $rolelist[$row['id']] = $row;
    }
}
?>

<?php include PATH . '/header.php' ?>

<h1>Moderation</h1>

<form method="get" action="">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search users..." />
    <input type="hidden" name="page" value="<?= $currentPage ?>" />
    <button type="submit">Search</button>
</form>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Role</th>
        </tr>
    </thead>
    <tbody>
    <?php if (isset($user) && is_array($user) && isset($rolelist[$user['roleid']])) { ?>
    <tr>
        <td>
            <?= $user['login'] ?>
            <br>
            <?php
            if ((isset($GLOBALS['currentuser']['id'])) && $GLOBALS['currentuser']['roleid'] === '1') {
                if ($user['roleid'] === '0') {
                    ?>
                    <a href="/<?= FORUM_DIR ?>/?page=mod&action=ban_user&user_id=<?= $user['id']?>">Ban</a>
                    <?php
                } elseif ($user['roleid'] === '2') {
                    ?>
                    <a href="/<?= FORUM_DIR ?>/?page=mod&action=unban_user&user_id=<?= $user['id']?>">Unban</a>
                    <?php
                }
            }
            ?>
        </td>
        <td><?= $rolelist[$user['roleid']]['rolename'] ?></td>
    </tr>
    <?php } else { ?>
    <tr>
        <td colspan="2">No user data available.</td>
    </tr>
    <?php } ?>
</tbody>
</table>




<table>
    <thead>
        <tr>
            <th>Topics</th>
            <th>Messages</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?php if (isset($topiclist) && is_array($topiclist) && !empty($topiclist)) { ?>
                    <?php foreach ($topiclist as $topic): ?>
                        <div><a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?=$topic['id']?>"><?=$topic['name']?></a></div>
                        <hr>
                    <?php endforeach; ?>
                <?php } else { ?>
                    <div>No topics available.</div>
                <?php } ?>
            </td>
            <td>
                <?php if (isset($postlist) && is_array($postlist) && !empty($postlist)) { ?>
                    <?php foreach ($postlist as $post):
                        $result = $db->query("SELECT * FROM post WHERE topicid = '{$post['topicid']}'");
                        $postList = [];
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $postList[$row['id']] = $row;
                            }
                        }
                    ?>
                        <div><a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?=$post['topicid']?>&page_num=<?=ceil(array_search($post['id'],array_column($postList, 'id'))/6)?>#<?= $post['id'] ?>"><?=$post['message']?></a></div>
                        <hr>
                    <?php endforeach; ?>
                <?php } else { ?>
                    <div>No posts available.</div>
                <?php } ?>
            </td>
        </tr>
    </tbody>
</table>


<?php include PATH . '/footer.php' ?>