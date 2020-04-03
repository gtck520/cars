<?php

namespace king\lib;

use king\core\Error;
use king\core\Instance;
use king\lib\Cache;
use king\lib\exception\BadRequestHttpException;

class Weixin extends Instance
{
    private static $instance;
    private static $token;
    protected $app_id;
    protected $app_secret;
    public $obj;
    public $access_token;
    private $wx_url = 'https://api.weixin.qq.com/cgi-bin/';
    private $wx_upload_url = 'http://file.api.weixin.qq.com/cgi-bin/';
    protected $second = 7000;

    public function setWxUrl($new_url)
    {
        $this->wx_url = $new_url;
    }

    public function setAccessToken($app_id, $app_secret)
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $cache_key = md5('weixin:token:' . $app_id);
        $token = Cache::get($cache_key);
        if (!$token) {
            $url = $this->wx_url . 'token?grant_type=client_credential&appid=' . $app_id . '&secret=' . $app_secret;
            $rs = $this->requestUrl($url, '', 'get');
//            if ($rs->errcode == 0) {
                $token = $rs->access_token;
                Cache::set($cache_key, $token, $this->second);
//            } else {
                // throw new BadRequestHttpException('access_token get failed');
//            }
        }
        $this->access_token = $token;
    }

    public function getUserInfo($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->app_id . '&secret=' . $this->app_secret . '&code=' . $code . '&grant_type=authorization_code';
        $rs = $this->requestUrl($url, '', 'get');
        if (isset($rs->openid)) {
            return $this->getOneUser($rs->openid);
        } else {
            throw new BadRequestHttpException('openid get failed');
        }
    }

    public function getSign()
    {
        $timestamp = time();
        $noncestr = $this->getRandChar(16);
        $jsapi_ticket = $this->getTicket($this->app_id, $this->app_secret);
        $sign_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $string = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $jsapi_ticket, $noncestr, $timestamp, $sign_url);
        $signature = sha1($string);
        return ['appid' => $this->app_id, 'timestamp' => $timestamp, 'noncestr' => $noncestr, 'signature' => $signature];
    }

    private function getTicket()
    {
        if (!$this->access_token) {
            throw new BadRequestHttpException('access_token未设置');
        }

        $cache_key = md5('weixin:ticket:' . $this->app_id);
        $ticket = Cache::get($cache_key);
        if (!$ticket) {
            $url = $this->wx_url . 'ticket/getticket?access_token=' . $this->access_token . '&type=jsapi';
            $rs = $this->requestUrl($url, '', 'get');
            if (isset($rs->errcode) && $rs->errcode == 0) {
                Cache::set($cache_key, $rs->ticket, $this->second);
            } else {
                throw new BadRequestHttpException('get ticket failed,error code:' . $rs->errcode);
            }
        }

        return $ticket;
    }

    private function getRandChar($length)
    {
        $str = '';
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];
        }
        return $str;
    }

    /**
     * 删除永久素材
     * @param media_id 要获取的素材的media_id
     */
    public function delMaterial($media_id)
    {
        if (!empty($media_id)) {
            $data['media_id'] = $media_id;
            $json = json_encode($data);
            $url = $this->wx_url . 'material/del_material?access_token=' . $this->access_token;
            return $this->requestUrl($url, $this->turnJson($json));
        } else {
            return false;
        }
    }

    /**
     * @param type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @param media    form-data中媒体文件标识，有filename、filelength、content-type等信息
     *上传永久素材
     */
    public function uploadMaterial($type, $media)
    {
        $json = json_encode($media);
        $url = $this->wx_url . 'material/add_material?access_token=' . $this->access_token . '&type=' . $type;
        return $this->requestUrl($url, $this->turnJson($json));
    }

    public function getMaterialCount()
    {
        $url = $this->wx_url . 'material/get_materialcount?access_token=' . $this->access_token;
        return $this->requestUrl($url, '');
    }

    public function getMaterials($type = 'image', $offset = 0, $count = 10)
    {
        $data['type'] = $type;
        $data['offset'] = $offset;
        $data['count'] = $count;
        $json = json_encode($data);
        $url = $this->wx_url . 'material/batchget_material?access_token=' . $this->access_token;
        return $this->requestUrl($url, $this->turnJson($json));
    }

    public function getOneMaterial($media_id)
    {
        $data['media_id'] = $media_id;
        $json = json_encode($data);
        $url = $this->wx_url . 'material/get_material?access_token=' . $this->access_token;
        return $this->requestUrl($url, $this->turnJson($json));
    }

    public function setObj($requestMsg)
    {
        $obj = simplexml_load_string($requestMsg, 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->obj = $obj;
    }

    /**
     * 响应事件
     * @param object $class
     */
    public function responseFunc($class)
    {
        $type = $this->obj->MsgType;
        $fun = 'set' . ucfirst($type);
        if ($type == 'event') {
            $fun = 'set' . ucfirst(strtolower($this->obj->Event));
        }
        if (!method_exists($class, $fun)) {
            Error::showError('未定义' . $fun, 'fun.log.php');
        } else {
            $class->$fun();
        }
    }

    /**
     * 微信验证
     * @param unknown $signature
     * @param unknown $time
     * @param unknown $nonce
     * @return boolean
     */
    public function checkSignature($signature, $time, $nonce)
    {
        $tmpArr = [self::$token, $time, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 创建菜单
     * @param array $menuArray菜单数组
     */
    public function createMenu($menuArray)
    {
        $url = $this->wx_url . 'menu/create?access_token=' . $this->access_token;
        $json = json_encode($menuArray);
        return $this->requestUrl($url, $this->turnJson($json));
    }

    public function packWxUnicode($match)
    {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }

    /**
     * 取得菜单
     */
    public function getMenu()
    {
        $url = $this->wx_url . 'menu/get?access_token=' . $this->access_token;
        return $this->requestUrl($url, '', 'get');
    }

    /**
     * 删除菜单
     */
    public function deleteMenu()
    {
        $url = $this->wx_url . 'menu/delete?access_token=' . $this->access_token;
        return $this->requestUrl($url, '', 'get');
    }

    /**
     * 发送客服消息
     * @param string $type
     * @param array $params
     */
    public function sendCustomMsg($openId, $type = 'text', $params)
    {
        $array = ['touser' => $openId, 'msgtype' => $type];

        switch ($type) {
            case 'text':
                $array['text']['content'] = isset($params['content']) ? $params['content'] : '';
                break;
            case 'image':
                $array['image']['media_id'] = isset($params['mediaId']) ? $params['mediaId'] : '';
                break;
            case 'voice':
                $array['voice']['media_id'] = isset($params['mediaId']) ? $params['mediaId'] : '';
                break;
            case 'video':
                if (isset($params['mediaId ']) && isset($params['thumbMediaId'])) {
                    $array['video']['media_id '] = $params['mediaId'];
                    $array['video']['media_id '] = $params['thumbMediaId'];
                    $array['video']['title '] = $params['title'];
                    $array['video']['description '] = $params['description'];
                }
                break;
            case 'music':
                if (isset($params['musicUrl ']) && isset($params['hqMusicUrl ']) && isset($params['thumbMediaId'])) {
                    $array['video']['musicurl '] = $params['musicUrl'];
                    $array['video']['hqmusicurl '] = $params['hqMusicUrl'];
                    $array['video']['media_id '] = $params['thumbMediaId'];
                    $array['video']['title '] = $params['title'];
                    $array['video']['description '] = $params['description'];
                }
                break;
            case 'news':
                $count = count($params);
                if ($count > 10) {
                    Error::showError('图文消息不能超过10条');
                }

                if ($count > 0) {
                    foreach ($params as $param) {
                        $array['news']['articles'][]['title'] = $param['title'];
                        $array['news']['articles'][]['description'] = $param['description'];
                        $array['news']['articles'][]['url'] = $param['url'];
                        $array['news']['articles'][]['picurl'] = $param['picUrl'];
                    }
                }
                break;
            default:
                break;
        }
        if ($type != 'news' && count($array[$type]) < 1)//图文消息允许全部字段为空，其他不行
        {
            Error::showError('回复参数不符合要求');
        }

        $url = $this->wx_url . 'message/custom/send?access_token=' . $this->access_token;
        $json = json_encode($array);
        return $this->requestUrl($url, $this->turnJson($json));


    }

    /**
     * 删除群发的消息
     * @param integer $msgId
     * @return Ambigous <string, mixed>
     */
    public function deleteMsg($msgId)
    {
        $url = $this->wx_url . 'message/mass/delete?access_token=' . $this->access_token;
        $array = ['msgid' => $msgId];
        $json = json_encode($array);
        return $this->requestUrl($url, $json);
    }

    /**
     * 分组群发消息
     * @param integer $group分组id
     * @param string $type发送消息类型，有text ,mpnews,image,voice,mpvideo等几种
     * @param string $param type为text类型时$param为文本内容，其它均为mediaId
     */
    public function sendGroupMsg($group, $type = 'text', $param)
    {
        $msgArray = [];
        $msgArray['filter'] = $group;
        $field = 'content';
        if ($type != 'text') {
            $field = 'media_id';
        }
        $msgArray[$type] = [$field => $param];
        $msgArray['msgType'] = $type;
        $url = $this->wx_url . 'message/mass/sendall?access_token=' . $this->access_token;
        $json = json_encode($msgArray);
        return $this->requestUrl($url, $this->turnJson($json));
    }

    /**
     * 向openid列表群发消息
     * @param array $ids
     * @param string $type
     * @param string $param
     */
    public function sendIdsMsg($ids, $type, $param)
    {
        $msgArray = [];
        $msgArray['touser'] = $ids;
        $field = 'content';
        if ($type != 'text') {
            $field = 'media_id';
        }
        $msgArray[$type] = [$field => $param];
        $msgArray['msgtype'] = $type;
        $url = $this->wx_url . 'message/mass/send?access_token=' . $this->access_token;
        $json = json_encode($msgArray);
        return $this->requestUrl($url, $this->turnJson($json));
    }

    /**
     * 获取关注者列表
     * @param string $nextId　起始的关注者id
     * @return string
     */
    public function getSubscribeList($nextId = '')
    {
        $url = $this->wx_url . 'user/get?access_token=' . $this->access_token . '&next_openid=' . $nextId;
        return $this->requestUrl($url, '', 'get');
    }

    /**
     * 获取用户信息
     * @param string $openId　用户openId
     * @return string
     */
    public function getOneUser($openId)
    {
        $url = $this->wx_url . 'user/info?access_token=' . $this->access_token . '&openid=' . $openId . '&lang=zh_CN';
        return $this->requestUrl($url, '', 'get');
    }

    /**
     * json中文转换
     * @param json $json
     * @return mixed
     */
    private function turnJson($json)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', [$this, 'packWxUnicode'], $json);
    }

    /**
     * 创建用户分组（暂不知道用途，先整合接口）
     * @param string $groupName
     */
    public function createGroup($group_name)
    {
        $params = [];
        $params['group'] = ['name' => $group_name];
        $url = $this->wx_url . 'groups/create?access_token=' . $this->access_token;
        $json = json_encode($params);
        return $this->requestUrl($url, $this->turnJson($json));
    }

    /**
     * 修改分组
     * @param integer $id
     * @param string $group_name
     * @return Ambigous <string, mixed>
     */
    public function alterGroupName($id, $group_name)
    {
        $params = [];
        $params['group'] = ['id' => $id, 'name' => $group_name];
        $url = $this->wx_url . 'groups/update?access_token=' . $this->access_token;
        $json = json_encode($params);
        return $this->requestUrl($url, $this->turnJson($json));
    }

    /**
     * 查找用户所在的分组
     * @param string $openId
     * @return Ambigous <string, mixed>
     */
    public function searchUserGroup($openId)
    {
        $params = [];
        $params['openid'] = $openId;
        $url = $this->wx_url . 'groups/getid?access_token=' . $this->access_token;
        $json = json_encode($params);
        return $this->requestUrl($url, $json);
    }

    /**
     * 修改用户所在分组
     * @param string $openId
     * @param integer $group
     */
    public function changeUserGroup($open_Id, $group)
    {
        $params = [];
        $params = ['openid' => $open_Id, 'to_groupid' => $group];
        $url = $this->wx_url . 'groups/members/update?access_token=' . $this->access_token;
        $json = json_encode($params);
        return $this->requestUrl($url, $json);
    }

    /**
     * 发送post,或get请求
     * @param string $url
     * @param json $json
     * @return string
     */
    private function requestUrl($url, $data, $type = 'post')
    {
        $req = Request::getClass($url, $type);
        if (strtolower($type) == 'post') {
            $req->body = $data;
        }
        $req->sendRequest();
        return json_decode($req->getResponseBody());
    }

    /**
     * 发送被动响应消息
     * @param object $obj
     * @param string $type
     * @param array $params
     * @return string
     */
    public function responseMsg($type = 'text', $params)//type为news时$params类似array(0=>array('title'=>''),1=>array('title'=>''));其它时$param为array('title'=>'');
    {
        $param_xml = '';
        $time = time();
        switch ($type) {
            case 'text':
                $tpl = '<Content><![CDATA[%s]]></Content>';
                $param_xml = isset($params['content']) ? sprintf($tpl, $params['content']) : '';
                break;
            case 'image':
                $tpl = '<Image><MediaId><![CDATA[%s]]></MediaId></Image>';
                $param_xml = isset($params['mediaId']) ? sprintf($tpl, $params['mediaId']) : '';
                break;
            case 'voice':
                $tpl = '<Voice><MediaId><![CDATA[%s]]></MediaId></Voice>';
                $param_xml = isset($params['mediaId']) ? sprintf($tpl, $params['mediaId']) : '';
                break;
            case 'video':
                $tpl = '<Video><MediaId><![CDATA[%s]]></MediaId><Title><![CDATA[title]]></Title><Description><![CDATA[description]]></Description></Video>';
                $param_xml = isset($params['mediaId']) ? sprintf($tpl, $params['mediaId'], $params['title'], $params['description']) : '';
                break;
            case 'music':
                $tpl = '<Music>
				<Title><![CDATA[%s]]></Title>
				<Description><![CDATA[%s]]></Description>
				<MusicUrl><![CDATA[%s]]></MusicUrl>
				<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
				<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
				</Music>';
                $param_xml = isset($params['thumbMediaId']) ? sprintf($tpl, $params['title'], $params['description'], $params['musicUrl'], $params['hqMusicUrl'], $params['thumbMediaId']) : '';
                break;
            case 'news':
                $count = count($params);
                if ($count > 10) {
                    Error::showError('消息数量不能超过10条');
                }

                if ($count > 0) {
                    $param_xml = '<ArticleCount>' . $count . '</ArticleCount><Articles>';
                    $tpl = '<item>
					<Title><![CDATA[%s]]></Title>
					<Description><![CDATA[%s]]></Description>
					<PicUrl><![CDATA[%s]]></PicUrl>
					<Url><![CDATA[%s]]></Url>
					</item>';
                    foreach ($params as $param) {
                        $param_xml .= sprintf($tpl, $param['title'], $param['description'], $param['picUrl'], $param['url']);
                    }
                    $param_xml .= '</Articles>';
                }
                break;
            default:
                break;
        }
        if (!empty($param_xml)) {
            $wxTpl = '<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[%s]]></MsgType>' . $param_xml . '</xml>';
            $response = sprintf($wxTpl, $this->obj->FromUserName, $this->obj->ToUserName, $time, $type);
            echo $response;
        } else {
            Error::showError('回复参数不符合要求');
        }
    }

    /**
     * 上传多媒体文件
     * @param string $type
     * @param string $filePath
     */
    public function uploadMedia($type = 'image', $filePath)
    {
        $url = $this->wx_upload_url . 'media/upload?access_token=' . $this->access_token . '&type=' . $type;
        $array = array('filePath' => '@' . $filePath);
        return $this->requestUrl($url, $array, 'post');
    }
}