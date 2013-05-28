<?php

function post_request($url, $data, $referer = '') {

//    Convert the data array into URL Parameters like a=b&foo=bar etc.
//    $data = http_build_query($data);
    // parse the given URL
    $url = parse_url($url);

    if ($url['scheme'] != 'http') {
        return array(
            'status' => 'err',
            'error' => "Host must starts with http:// !!"
        );
    }

    // extract host and path:
    $host = $url['host'];
    $path = $url['path'];

    // open a socket connection on port 80 - timeout: 30 sec
    $fp = fsockopen($host, 80, $errno, $errstr, 30);

    if ($fp) {

        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");

        if ($referer != '')
            fputs($fp, "Referer: $referer\r\n");

//        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-type: text/xml\r\n");
        fputs($fp, "Content-length: " . strlen($data) . "\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        $result = '';
        while (!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }
    } else {
        return array(
            'status' => 'err',
            'error' => "$errstr ($errno)"
        );
    }

    // close the socket connection:
    fclose($fp);

    // split the result header from the content
    $result = explode("\r\n\r\n", $result, 2);

    $header = isset($result[0]) ? $result[0] : '';
    $content = isset($result[1]) ? $result[1] : '';

    // return as structured array:
    return array(
        'status' => 'ok',
        'header' => $header,
        'content' => $content
    );
}

if ('POST' == $_SERVER['REQUEST_METHOD']) {
// Submit those variables to the server
    $post_data = "<xml>
        <ToUserName><![CDATA[$_POST[ToUserName]]]></ToUserName>
        <FromUserName><![CDATA[$_POST[FromUserName]]]></FromUserName> 
        <CreateTime>$_POST[CreateTime]</CreateTime> 
        <MsgType><![CDATA[$_POST[MsgType]]]></MsgType> 
        <Content><![CDATA[$_POST[Content]]]></Content> 
        <MsgId>$_POST[MsgId]</MsgId> </xml>";

// Send a request to example.com 
    $result = post_request($_POST['Host'], $post_data);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>虚拟微信调用</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="papa">

        <!-- Le styles -->
        <link href="assets/css/bootstrap.css" rel="stylesheet">
        <link href="assets/css/responsive.css" rel="stylesheet">

        <style type="text/css">
            /* Footer
            -------------------------------------------------- */

            .footer {
                text-align: center;
                padding: 20px 0;
                margin-top: 50px;
                border-top: 1px solid #e5e5e5;
                background-color: #f5f5f5;
            }
            .footer p {
                margin-bottom: 0;
                color: #777;
            }
            .footer-links {
                margin: 10px 0;
            }
            .footer-links li {
                display: inline;
                padding: 0 2px;
            }
            .footer-links li:first-child {
                padding-left: 0;
            }

        </style>

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
          <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="assets/ico/favicon.png">

        <script type="text/javascript">
      
        </script>
    </head>

    <body data-spy="scroll" data-target=".bs-docs-sidebar">

        <div style="height:50px;"></div>

        <div class="container">

            <div class="row">
                <div class="span3 offset1">
                    <h3>虚拟微信调用</h3>

                    <form method="post" action="/post.php" class="">
                        <div class="control-group">
                            <label class="control-label" for="Host">Host</label>
                            <div class="controls">
                                <input type="text" name="Host" placeholder="Host" value="<?= isset($_POST['Host']) ? $_POST['Host'] : 'http://wx.tisa7.com/index.php' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="FromUserName">FromUserName</label>
                            <div class="controls">
                                <input type="text" name="FromUserName" placeholder="FromUserName" value="tisa007">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="ToUserName">ToUserName</label>
                            <div class="controls">
                                <input type="text" name="ToUserName" placeholder="ToUserName" value="alan">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="MsgType">MsgType</label>
                            <div class="controls">
                                <input type="text" name="MsgType" placeholder="text|voice|location" value="text">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="Content">Content</label>
                            <div class="controls">
                                <input type="text" name="Content" placeholder="Content" value="<?= isset($_POST['Content']) ? $_POST['Content'] : 'test content' ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="CreateTime">CreateTime</label>
                            <div class="controls">
                                <input type="text" name="CreateTime" placeholder="CreateTime" value="<?= time() ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="MsgId">MsgId</label>
                            <div class="controls">
                                <input type="text" name="MsgId" placeholder="MsgId" value="1234567890">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <button type="submit" class="btn">提交</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="span8">
                    <section id="forms">
                        <?php if (isset($result)) { ?>
                            <h3>调用结果</h3>

                            <?php if ($result['status'] == 'ok') { ?>
                                <pre class="prettyprint"><?= $result['header'] ?></pre>
                                <pre class="prettyprint"><?= htmlentities($result['content'], ENT_NOQUOTES, "utf-8") ?></pre>
                            <?php } else { ?>
                                <pre class="prettyprint"><?= $result['error'] ?></pre>
                            <?php } ?>

                        <?php } ?>
                    </section>
                </div>
            </div>
        </div>
        <!-- Footer
        ================================================== -->
        <footer class="footer">
            <div class="container">
                <p>Code licensed under <a href="http://www.apache.org/licenses/LICENSE-2.0" target="_blank">Apache License v2.0</a></p>
                <ul class="footer-links">
                    <li><a href="http://blog.tisa7.com">小猪爬爬</a></li>
                    <li class="muted">&middot;</li>
                    <li><a href="http://weibo.com/u/2101388255">Alan微博</a></li>
                </ul>
            </div>
        </footer>
        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="assets/js/jquery.js"></script>
        <script src="assets/js/bootstrap-transition.js"></script>
        <script src="assets/js/bootstrap-alert.js"></script>
        <script src="assets/js/bootstrap-modal.js"></script>
        <script src="assets/js/bootstrap-dropdown.js"></script>
        <script src="assets/js/bootstrap-scrollspy.js"></script>
        <script src="assets/js/bootstrap-tab.js"></script>
        <script src="assets/js/bootstrap-tooltip.js"></script>
        <script src="assets/js/bootstrap-popover.js"></script>
        <script src="assets/js/bootstrap-button.js"></script>
        <script src="assets/js/bootstrap-collapse.js"></script>
        <script src="assets/js/bootstrap-carousel.js"></script>
        <script src="assets/js/bootstrap-typeahead.js"></script>
        <script src="assets/js/bootstrap-affix.js"></script>
        <script src="assets/js/holder/holder.js"></script>
        <script src="assets/js/google-code-prettify/prettify.js"></script>
        <script src="assets/js/application.js"></script>

    </body>
</html>
