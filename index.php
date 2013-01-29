<?php

define("TOKEN", "wsXzPPwaiyy");
define("DIVIDER", "\n-------------------------\n");
define("DB_HOST", "127.0.0.1");
define("DB_USER", "root");
define("DB_PWD", "312821");
define("DB_DATABASE", "papa_bookstore");
define("DB_CHARSET", "utf8");

include_once 'common.php';
include_once 'cmd_list.php';
include_once 'cmd_add.php';
include_once 'cmd_add_qq.php';
include_once 'cmd_buy.php';
include_once 'cmd_help.php';

$app = new App;
$app->dispatch();

class App {

    public function dispatch() {
        //获取原始表单数据
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)) {
            $link = mysqli_connect(DB_HOST, DB_USER, DB_PWD, DB_DATABASE);
            if (!$link) {
            } else {
                $link->set_charset(DB_CHARSET);
                
                // 保留日志
                // $s = $link->real_escape_string($postStr);
                // $link->query("insert into logs set req='$s'");
                saveLog($postStr);
                //保留用户
                $xmlData = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUserName = $xmlData->FromUserName;
                //判断是否有联系方式
                $result = $link->query("select * from users where user_name='$fromUserName'");
                
                if ($result->num_rows == 0) {
                    $result = $link->query("insert into users set user_name='$fromUserName'");
                }
                $link->close();
            }
            
            $xmlData = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $msgType = $xmlData->MsgType;

            switch ($msgType) {
                case 'text':
                    $this->processTextMsg($xmlData);
                    break;
                case 'image':
                case 'voice':
                case 'location':
                    $data = array(
                        'from_user_name' => (string) $xmlData->FromUserName,
                        'to_user_name' => (string) $xmlData->ToUserName,
                        'time' => time(),
                        'content' => '这是一条' . $msgType . '消息，但是我们目前只能处理文字消息！'
                    );
                    echo genMsgAsText($data);
                    exit();

                default:
                    $data = array(
                        'from_user_name' => (string) $xmlData->FromUserName,
                        'to_user_name' => (string) $xmlData->ToUserName,
                        'time' => time(),
                        'content' => 'unknown message!'
                    );
                    echo genMsgAsText($data);
                    exit();
            }
        } else {
            exit();
        }
    }

    /**
     * 处理文字消息
     * 
     * @param type $xmlData
     */
    private function processTextMsg($xmlData) {
        $data = array(
            'from_user_name' => (string) $xmlData->FromUserName,
            'to_user_name' => (string) $xmlData->ToUserName,
            'content' => trim((string) $xmlData->Content),
            'time' => time()
        );
        $arr = explode(" ", $data['content']);
        switch ($arr[0]) {
            case '搜':
            case 'ls':
                cmdListBooks($data);
                exit();
            case '买':
            case 'buy':
                cmdBuyBook($data);
                exit();
            case '卖':
            case 'sell':
            case 'add':
                cmdAddBook($data);
                exit();
            case 'qq':
            case 'QQ':
                cmdAddQQ($data);
                exit();

            default:
                cmdHelp($data);
                break;
        }
    }

}

?>
