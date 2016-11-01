<?php
/* For each piece of data you insert:
 * columnName =>
 * value
 * paramName
 * paramType
 */
require_once("../../../db-test.php");
require_once("../DbManager.php");
require_once("../DbTables.php");
require_once("../DbDataFilter.php");
require_once("../utility.php");
require_once("../purifier/HTMLPurifier.auto.php");
$message = new Message();

// Create test tables in test database
/*  Series - table for series of images
 *      id
 *      title
 *      description
 */
$seriesQ = "CREATE TABLE IF NOT EXISTS Series (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            PRIMARY KEY (id),
            INDEX (name)
            ) ENGINE=InnoDB";
$seriesTable = new DbTable("Series", "id");

/* Images - table for basic image characteristics
 *      imgID
 *      title
 *      description
 *      width
 *      height
 *      date
 *      seriesID
 */
$imgQ = "CREATE TABLE IF NOT EXISTS Image (
            id INT(11) NOT NULL AUTO_INCREMENT,
            filename VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            width INT(11),
            height INT(11),
            seriesID INT(11),
            modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (seriesID) REFERENCES Series(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
            INDEX (name)
            ) ENGINE=InnoDB";
$imgTable = new DbTable("Image", "id");
$seriesTable->addAlias("name", "seriesName");
$imgTable->addAssoc("seriesID", $seriesTable);

/* Tags - table holding tags for images
 *      id
 *      title
 */
$tagQ = "CREATE TABLE IF NOT EXISTS Tag (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY (id),
            INDEX (name)
            ) ENGINE=InnoDB";
$tagTable = new DbTable("Tag", "id");

/* ImageTags - image to tag join table
 *      id
 *      imgID
 *      tagID
 */
$imgTagQ = "CREATE TABLE IF NOT EXISTS ImageTag (
            id INT(11) NOT NULL AUTO_INCREMENT,
            imgID INT(11) NOT NULL,
            tagID INT(11) NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (imgID) REFERENCES Image(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
            FOREIGN KEY (tagID) REFERENCES Tag(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
            ) ENGINE=InnoDB";
$imgTagTable = new DbTable("ImageTag", "id");
$tagTable->addJoin($imgTagTable, "tagID", "imgID");

/* Service - list of image service sites
 *      id
 *      name
 */
$serviceQ = "CREATE TABLE IF NOT EXISTS Service (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY (id),
            INDEX (name)
            ) ENGINE=InnoDB";
$serviceTable = new DbTable("Service", "id");

/* ImageUrl - Image to image service join
 *      id
 *      imgID
 *      serviceID
 *      url
 */
$imgUrlQ = "CREATE TABLE IF NOT EXISTS ImgUrl (
            id INT(11) NOT NULL AUTO_INCREMENT,
            imgID INT(11),
            serviceID INT(11),
            url VARCHAR(255),
            PRIMARY KEY (id),
            FOREIGN KEY (imgID) REFERENCES Image(id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
            FOREIGN KEY (serviceID) REFERENCES Service(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
            ) ENGINE=InnoDB";
$imgUrlTable  = new DbTable("ImgUrl", "id");
$imgUrlTable->displayCols = ["url"];
$serviceTable->addJoin($imgUrlTable, "serviceID", "imgID");
// Make data preparer for titles

$dbm = new DbManager($dbHost, $dbUser, $dbPass, $dbName);
function delTable($tableName) {
    return "DROP TABLE IF EXISTS $tableName ";
}
try {
    $dbm->connect();
    $message->add("Connected to the database.");
    try {
        $dbm->con->exec($seriesQ);
        $dbm->con->exec($imgQ);
        $dbm->con->exec($tagQ);
        $dbm->con->exec($imgTagQ);
        $dbm->con->exec($serviceQ);
        $dbm->con->exec($imgUrlQ);
        $message->add("Created tables");

        $tables = [$imgUrlTable->name, $imgTagTable->name, $imgTable->name,
                                $seriesTable->name, $tagTable->name, $serviceTable->name];

        $series1 = array("name" => "Cute Animals",
                                         "description" => "Photos of animals that are cute.");

        $series2 = array("name" => "Stunning Landscapes",
                                         "description" => "Photographs or paintings of beautiful landscapes.");

        $series3 = array("name" => "Awesome Architecture",
                                         "description" => "Photos, drawings, blueprints, etc. of buildings real and imagined.");

        $series3b = array("description" => "Pictures of buildings real and imagined");
        $seriesData = [];
        // Test the QueryBuilder class
        $qb = new QueryBuilder("insert", $seriesTable);
        // Initialized sql
        //$message->add("Query Builder's command is $qb->cmd");
        $message->add("Query Builder initialized sql is: $qb->sql");
        // Add the columns and string values
        $qb->addVals($series1);
        $qb->addInsert();
        $message->add("QB query is now: $qb->sql");
        try {
            $id = 0;
            $seriesIds = [];
            foreach([$series1, $series2, $series3] as $series) {
                $message->add("Binding " . $series["name"] . " and " . $series["description"]);
                $id = $dbm->insertData("insert", $seriesTable, $series);
                $message->add("Added series #$id.");
                $seriesIds[] = $id;
            }
            try {
                $dbm->insertData("update", $seriesTable, $series3b, $id);
                $message->add("Query was: " . $dbm->query);
                $message->add("Updated series #$id");
                $message->add("Trying to delete series id# $seriesIds[1].");
                try {
                    $dbm->deleteData($seriesTable, $seriesIds[1]);
                    $message->add("Query was: " . $dbm->query);
                } catch (Exception $e) {
                    $message->add("Delete failed.");
                    $message->add("Query was: " . $dbm->query);
                    $message->except("Exception on deleting series.", $e);
                }
                try {
                    $seriesQB = new QueryBuilder("select", $seriesTable);
                    $qb->addOrder("name");
                    $seriesData = $dbm->getReq($seriesQB);
                } catch (Exception $e) {
                    $message->add("Tried to get series data.");
                    $message->add("Query was: " . $dbm->query);
                    $message->except("Exception on retrieving series data.", $e);
                }
            } catch (Exception $e) {
                $message->add("Tried to series update row #: $id");
                $message->add("Query was: " . $dbm->query);
                $message->except("Exception on updating series data.", $e);
            }
            try {
                // Add deleted series back
                $newId = $dbm->insertData("insert", $seriesTable, $series2);
                $message->add("Inserted deleted series again.");

            } catch (Exception $e) {
                $message->add("Query was: " . $dbm->query);
                $message->except("Exception on inserting series2.", $e);
            }
            // Make up image data (as if from $_POST)
            $img1Dirty =    ["filename" => "image1.jpg",
                                            "name" => "Snow Monkeys in Onsen",
                                            "description" => "Several Japanese snow monkeys soaking in a natural hot spring surrounded by snow-covered rocks.",
                                            "width" => 3486,
                                            "height" => 2313,
                                            "seriesID" => $seriesIds[0]
                                        ];
            $img2Dirty = ["filename" => "image2.jpg",
                                        "name" => "Snowy Mountain Landscape",
                                        "description" => "Mountain covered in snow with stratified rock showing through and clouds around its peak.",
                                        "width" => 1920,
                                        "height" => 1080,
                                        "seriesID" => $newId
                                        ];
            $img3Dirty = ["filename" => "image3.jpg",
                                        "name" => "Castello di Sammezzano",
                                        "description" => "An ornate, rainbow-hued Moorish castle with mosaics on the wails and floor and colored glass windows in the ceiling.",
                                        "width" => 853,
                                        "height" => 1280,
                                        "seriesID" => $seriesIds[2],
                                        ];

            // Test the DataFilter with "good" data
            $fnPrep = new FNFilter();
            $textPrep = new TextFilter();
            $intPrep = new IntFilter();
            $imgData = [];
            try {
                foreach([$img1Dirty, $img2Dirty, $img3Dirty] as $dirty) {
                    $clean = ["filename" => $fnPrep->filter($dirty["filename"], "jpg"),
                                    "name" => $textPrep->filter($dirty["name"]),
                                    "description" => $textPrep->filter($dirty["description"]),
                                    "width" => $intPrep->filter($dirty["width"]),
                                    "height" => $intPrep->filter($dirty["height"]),
                                    "seriesID" => $intPrep->filter($dirty["seriesID"]),
                                ];
                    $imgData[] = $clean;
                }
                try {
                    $id = 0;
                    $imgIds = $dbm->insertRows($imgTable, $imgData);
                    $message->add("Added images");
                    try {
                        $message->add("Begin adding tags.");
                        $tagDH = new DataHolder("name");
                        $tagDH->addRow($textPrep->filter("snow"));
                        $tagDH->addRow($textPrep->filter("impressive"));
                        $tagDH->addRow($textPrep->filter("cute"));
                        $tagDH->addRow($textPrep->filter("photo"));
                        $tagDH->addRow($textPrep->filter("rainbow"));
                        $tagIds = $dbm->insertRows($tagTable, $tagDH->rows);
                        $message->add("Tags added to the database.");
                        $message->add("Tag ids: " . implode(", ", $tagIds));
                    } catch (Exception $e) {
                        $message->except("Problem filtering and uploading tags.", $e);
                    }
                    try {
                        $itDH = new DataHolder("imgID, tagID");
                        $message->add("Columns are: " . implode(", ", $itDH->cols));
                        $message->add("Count of columns: " . count($itDH->cols));
                        $str = "Verifying image id $imgIds[0]: ";
                        $str .= showProps($dbm->getAssoc($imgTable, $imgIds[0]));
                        $message->add($str);
                        $message->add("Image retrieval query was: " . $dbm->query);
                        $str = "Verifing tag id $tagIds[0]: ";
                        $str .= showProps($dbm->getRow($tagTable, $tagIds[0]));
                        $message->add($str);
                        $itDH->addRow([$imgIds[0], $tagIds[0]]);
                        $message->add("Row is: " . showArr($itDH->rows[0]));
                        $itDH->addRow([$imgIds[0], $tagIds[2]]);
                        $itDH->addRow([$imgIds[0], $tagIds[3]]);
                        $itDH->addRow([$imgIds[1], $tagIds[0]]);
                        $itDH->addRow([$imgIds[1], $tagIds[2]]);
                        $itDH->addRow([$imgIds[1], $tagIds[3]]);
                        $itDH->addRow([$imgIds[2], $tagIds[1]]);
                        $itDH->addRow([$imgIds[2], $tagIds[3]]);
                        $itDH->addRow([$imgIds[2], $tagIds[4]]);
                        $dbm->insertRows($imgTagTable, $itDH->rows);
                        $message->add("Tagged images.");
                    } catch (Exception $e) {
                        $message->except("Could not tag images.", $e);
                        $message->add("Query was: " . $dbm->query);
                        $message->add("Values were: " . implode(", ", $idDH->rows[0]));
                    }
                    try {
                        $servicesDH = new DataHolder("name");
                        $servicesDH->addRow($textPrep->filter("Flickr"));
                        $servicesDH->addRow($textPrep->filter("Google Photos"));
                        $serviceIds = $dbm->insertRows($serviceTable, $servicesDH->rows);
                    } catch (Exception $e) {
                        $message->except("Problem filtering and uploading services.", $e);
                    }
                    try {
                        $imgUrlDH = new DataHolder("imgID, serviceID, url");
                        $urlPrep = new URLFilter();
                        $imgUrlDH->addRow([$imgIds[0], $serviceIds[0], $urlPrep->filter("http://www.flickr.com/image1.jpg")]);
                        $imgUrlDH->addRow([$imgIds[1], $serviceIds[0], $urlPrep->filter("http://www.flickr.com/username/image2.jpg")]);
                        $imgUrlDH->addRow([$imgIds[1], $serviceIds[1], $urlPrep->filter("http://www.google.com/photos/username/image1.jpg")]);
                        $imgUrlDH->addRow([$imgIds[2], $serviceIds[1], $urlPrep->filter("http://www.google.com/photos/username/image3.jpg")]);
                        $dbm->insertRows($imgUrlTable, $imgUrlDH->rows);
                        $message->add("Added image service URLs to images.");
                    } catch (Exception $e) {
                        $message->except("Could not add image service URLs to images.", $e);
                    }
                    try {
                        // Get all of the images back, with their associated series information
                        $allImgs = $dbm->getAssoc($imgTable, 0, $imgTable->name . ".modified DESC");
                        // For each image
                        foreach ($allImgs as $img) {
                            // Get its tags
                            $img->tags = $dbm->getJoined($img->id, $tagTable, $tagTable->name . ".name ASC");
                            // Get its service URLS
                            $img->services = $dbm->getJoined($img->id, $serviceTable, $serviceTable->name . ".name ASC");
                        }
                    } catch (Exception $e) {
                        $message->except("Could not retrieve image data", $e);
                        $message->add("Query was: " . $dbm->query);
                    }
                } catch (Exception $e) {
                    $message->except("Could not upload image data.", $e);
                }
            } catch (Exception $e) {
                $message->except("Invalid image data. Cannot add to database.", $e);
            }
        } catch (Exception $e) {
            $message->add("Query was: " . $dbm->query);
            $message->except("Exception on inserting series data.", $e);
            $message->add("Tried to insert: " . $dbm->message);
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>DbManager Tester</title>
    <meta name="generator" content="Geany 1.28" />
    <style>
        body, p, li, td {
            font-family: "Helvetica Neue", "Helvetica", "Arial", sans-serif;
            font-size: 14px;
        }
        h1, h2, h3, h4, h5, h6, th {
            font-family: Georgia, serif;
            font-weight: bold;
        }
        h1 {
            font-size: 42px;
        }
        h2 {
            font-size: 36px;
        }
        h3 {
            font-size: 28px;
        }
        h4 {
            font-size: 24px;
        }
        h5, th {
            font-size: 18px;
        }
        table {
            border: 1px solid grey;
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        td, th {
            width: 50%;
            border: 1px dotted #ddd;
            padding: 5px;
        }
    </style>
</head>

<body>
    <h1>Database Manager Test Page</h1>
    <p><?php echo $message->out(); ?></p>
    <h2>Results</h2>
    <h3>Series</h3>
    <?php if (count($seriesData) > 0) {
        $heads = array_keys(get_object_vars($seriesData[0]));
    ?>
        <table>
            <thead>
                <tr>
                <th scope="col">Name</th>
                <th scope="col">Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($seriesData as $series) { ?>
                <tr>
                    <td><?php echo $series->name; ?></td>
                    <td><?php echo $series->description; ?></td>
                </tr>
                <?php }?>
            </tbody>
        </table>
        <?php } else { ?>
            <p>No results.</p>
        <?php } ?>
    <h3>Images</h3>
    <ol>
    <?php
    foreach ($allImgs as $img) { ?>
        <li>
            <?php
                line($img["name"]);
                line($img["filename"]);
                line($img["description"]);
                line("Width: " . $img["width"]);
                line("Height: " . $img["height"]);
                line("Series: " . $img["seriesName"]);
                echo("Tags: ");
                foreach ($img->tags as $tag) {
                    echo $tag->name;
                    if (next($img->tags)) {
                        echo ", ";
                    }
                }
                if (count($img->services) > 0) {
                    echo"<br><strong>Links</strong><br>";?>
                    <ul>
                        <?php foreach($img->services as $s) { ?>
                        <li><a href="<?php echo $s->url; ?>"><?php echo $s->name; ?></a></li>
                        <?php } ?>
                    </ul>

            <?php } ?>
        </li>
    <?php }     ?>
    </ol>   
</body>
<?php
        // Clean up tables
        try {
            $dbm->con->exec(delTable(implode(", ", $tables)));
            $message->add("Deleted tables.");
        } catch (Exception $e) {
            $message->add("Caught exception.");
            $message->except("Could not delete tables.", $e);
        }
    } catch (Exception $e) {
        $message->add("Caught exception.<br>");
        $message->except("Could not create tables.", $e);
    }
    $dbm->disconnect();
    $message->add("Disconnected from the database.");
} catch (Exception $e) {
    $message->add("Caught exception.<br>");
    $message->except("Could not connect to database.", $e);
}
?>
</html>

