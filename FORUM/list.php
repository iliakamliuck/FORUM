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

if (isset($_REQUEST['search'])) {
    $filtervalues = $_GET['search'];
    $query = "SELECT * FROM topic WHERE CONCAT(name) LIKE '%$filtervalues%' ";
    $query_run = mysqli_query($db, $query);
}
?>


<?php include PATH . '/header.php' ?>


<div class="container">
    <div class="container">
        <div class="left-column">
            <div class="block">
                <div>
                    <form action="" method="get">
                        <input type="text" class="searchTerm" name="search" required
                            value="<?php if (isset($_REQUEST['search'])) {
                                echo $_REQUEST['search'];
                            } ?>"
                            class="form-control" placeholder="Search data">
                        <button type="submit" class="searchButton">Search</button>
                    </form>
                </div>
                <tbody>
                    <?php
                    if (isset($_REQUEST['search'])) {
                        if (mysqli_num_rows($query_run) > 0) {
                            foreach ($query_run as $i => $items) { ?>
                                <tr>
                                    <hr>
                                    <td><a href="/<?= FORUM_DIR ?>/?page=topic&topic_id=<?= $items['id'] ?>">
                                            <?= $items['name'] ?>
                                        </a></td>
                                </tr>
                                <?php
                                if ($i > 3) { ?>
                                    <tr>
                                        <hr>
                                        <td><a href="/<?= FORUM_DIR ?>/">More...</a></td>
                                    </tr>
                                    <?php
                                    break;
                                }
                            }
                        } else { ?>
                            <tr>
                                <hr>
                                <td>No Record Found</td>
                            </tr>
                            <?php
                        }
                    } ?>
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




<?php include PATH . '/footer.php' ?>