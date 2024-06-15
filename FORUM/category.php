<?php
if (!defined('SEC')) {
    die('Forbidden path');
}


require_once 'sentiment-analysis-master/src/autoload.php';

$analyzer = SentimentAnalysis\Analyzer::withDefaultConfig();

if (isset($_REQUEST['action']) == 'create_topic' && $GLOBALS['currentuser']) {


    $name = mysqli_real_escape_string($db, $_REQUEST['topic']);
    $result = mysqli_query($db, "
    INSERT INTO topic (name, countmessages, userid, createdate, categoryid)
    VALUES('" . $name . "','0', '{$GLOBALS['currentuser']['id']}','" . time() . "','{$_REQUEST['category_id']}')
    ");

    $topic_id = $db->insert_id;

    $message = mysqli_real_escape_string($db, $_REQUEST['message']);
    $result = mysqli_query($db, "
    INSERT INTO post (topicid, userid, message, createdate)
    VALUES('{$topic_id}','{$GLOBALS['currentuser']['id']}','" . $message . "','" . time() . "')
    ");

    $result = mysqli_query($db, "
    UPDATE topic
    SET countmessages = countmessages +1,
    replydate = '" . time() . "',
    replyuserid = '{$GLOBALS['currentuser']['id']}'
    WHERE id = '{$topic_id}'
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
            <th>Mood</th>
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
                <td><div class="scale">
                    <?php
                    $result = $db->query("SELECT message FROM post WHERE topicid ='{$topic['id']}'");
                    $messagelist = [];
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $messagelist[] = $row['message'];
                        }
                    }
                    $totalSentiment = 0;
                    $count = 0;
                    foreach ($messagelist as $message){
        
                        $result = $analyzer->analyze($message);
                        $sentimentClass = $result->category();
                        if ($sentimentClass === 'positive') {
                            $totalSentiment += 1;
                        } elseif ($sentimentClass === 'negative') {
                            $totalSentiment -= 1;
                        }
                        $count += 1;
                    }
                    $averageSentiment = $totalSentiment / $count * 100;
                    ?>
                    <div class="scale">
                        <div class="fill <?php echo $averageSentiment < 0 ? 'negative' : 'positive'; ?>" style="width: <?php echo abs($averageSentiment); ?>%;"></div>
                </td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php if (isset($_SESSION['login'])): ?>
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
                        <input type="text" name="topic" placeholder="Theme name" pattern="^(?!\s*$).+" required>
                        <textarea name="message" placeholder="Write message" required></textarea>
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