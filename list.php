<?php
if (!defined('SEC')) {
    die('Forbidden path');
}

$result = $db->query("SELECT * FROM category");
$categorylist = [];
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $categorylist[] = $row;
    }
}

if (isset($_REQUEST['action']) == 'create_category' && $GLOBALS['currentuser']['roleid']==='1') {


    $name = mysqli_real_escape_string($db, $_REQUEST['category']);
    $result = mysqli_query($db, "
    INSERT INTO category (name, counttopics)
    VALUES('" . $name . "','0')
    ");
    
    die(header('location: /' . FORUM_DIR));
}

if (isset($_REQUEST['search']) && $_REQUEST['search_type'] === 'topics') {
    $filtervalues = isset($_GET['search']) ? mysqli_real_escape_string($db, $_GET['search']) : '';
    $query = "SELECT * FROM topic WHERE CONCAT(name) LIKE '%$filtervalues%' ";
    $topiclist = mysqli_query($db, $query);
}
if (isset($_REQUEST['search']) && $_REQUEST['search_type'] === 'posts') {
    $filtervalues = isset($_GET['search']) ? mysqli_real_escape_string($db, $_GET['search']) : '';
    $query = "SELECT * FROM post WHERE CONCAT(message) LIKE '%$filtervalues%' ";
    $postlist = mysqli_query($db, $query);
}
?>


<?php include PATH . '/header.php' ?>


<div class="container">
    <div class="container">
        <div class="left-column">
            <div class="block">
                <div>
                <form action="" method="get">
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <select class="custom-select" name="search_type">
                <option value="topics" <?php if (isset($_REQUEST['search_type']) && $_REQUEST['search_type'] == 'topics') echo 'selected'; ?>>Search Topics</option>
                <option value="posts" <?php if (isset($_REQUEST['search_type']) && $_REQUEST['search_type'] == 'posts') echo 'selected'; ?>>Search Posts</option>
            </select>
        </div>
        <input type="text" class="form-control searchTerm" name="search" required value="<?php if (isset($_REQUEST['search'])) { echo $_REQUEST['search']; } ?>" placeholder="Search data">
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary searchButton">Search</button>
        </div>
    </div>
</form>
                </div>
                <tbody>
                    <?php
                    if (isset($_REQUEST['search']) && $_REQUEST['search_type'] === 'topics') {
                        if (mysqli_num_rows($topiclist) > 0 )
                        {
                            foreach ($topiclist as $i => $items) { ?>
                                <tr>
                                    <hr>
                                    <td>
                                        <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $items['id'] ?>">
                                        <?php
                                        $message = $items['name'];
                                        if (strlen($message) > 90) {
                                            $message = substr(htmlspecialchars($message), 0, 87) . '...';
                                        } else {
                                            $message = htmlspecialchars($message);
                                        }
                                        echo $message;
                                        ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                if ($i > 4) { ?>
                                    <tr>
                                        <hr>
                                        <td><a href="/<?= FORUM_DIR ?>/">More...</a></td>
                                    </tr>
                                    <?php
                                    break;
                                }
                            }
                        } 
                        else { ?>
                            <tr>
                                <hr>
                                <td>No Record Found</td>
                            </tr>
                            <?php
                        }
                    } ?>
                    <?php
                    if(isset($_REQUEST['search']) && $_REQUEST['search_type'] === 'posts'){
                        if (mysqli_num_rows($postlist) > 0 )
                        {
                            foreach ($postlist as $i => $items) {
                                $result = $db->query("SELECT * FROM post WHERE topicid = '{$items['topicid']}'");
                                $postlistfull = [];
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $postlistfull[$row['id']] = $row;
                                    }
                                }
                                ?>
                                <tr>
                                    <hr>
                                    <td>
                                        <a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $items['topicid']?>&page_num=<?=ceil(array_search($items['id'],array_column($postlistfull, 'id'))/6)?>#<?= $items['id'] ?>">
                                        <?php
                                        $message = $items['message'];
                                        if (strlen($message) > 90) {
                                            $message = substr(htmlspecialchars($message), 0, 87) . '...';
                                        } else {
                                            $message = htmlspecialchars($message);
                                        }
                                        echo $message;
                                        ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                if ($i > 4) { ?>
                                    <tr>
                                        <hr>
                                        <td><a href="/<?= FORUM_DIR ?>/">More...</a></td>
                                    </tr>
                                    <?php
                                    break;
                                }
                            }
                        }
                        else { ?>
                            <tr>
                                <hr>
                                <td>No Record Found</td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </div>

            <!--
      
<div class="niz">
<tr>
    <br><td>chto-to</td><br>
    <br><td>chto-to</td><br>
    <br><td>chto-to</td><br>
 </tr>
</div>                       
                                //-->
        </div>




        <div class="right-column">
            <h1>Forum_Main_Page</h1>
            <table>
                <thead>
                    <tr>
                        <th>Categoryes</th>
                        <th>Topics</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorylist as $category): ?>
                        <tr>
                            <td><a href="/<?= FORUM_DIR ?>/?page=category&category_id=<?= $category['id'] ?>">
                                    <?= $category['name'] ?>
                                </a></td>
                            <td>
                                <?= $category['counttopics'] ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['login']) && $GLOBALS['currentuser']['roleid']==='1'): ?>
    <table class="reply_form">
        <thead>
            <tr>
                <th>Create new category</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <form class="message_form" action="/<?= FORUM_DIR ?>/?action=create_category" method="post">
                        <input type="text" name="category" placeholder="Category name" pattern="^(?!\s*$).+" required>
                        <input type="submit" name="create" value="Create">
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
<?php endif ?>



<?php include PATH . '/footer.php' ?>