<?php
/**
 * Created by PhpStorm.
 * User: Elton
 * Date: 2020/6/29
 * Time: 10:32
 */

namespace app\admin\command;

use GuzzleHttp\Client;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Request;

class FollowUser extends Command
{
    //history shopid 400516522
    private $host = "https://shopee.tw";
    private $client;
    private $referer = "https://shopee.tw/shop/400516522/followers";
    private $shopid = "400516522";
    private $num = 0;
    protected function configure()
    {
        $this->setName('FollowUser')
            ->setDescription('关注用户');
    }

    protected function execute(Input $input, Output $output)
    {
        Request::instance()->module('admin');
        $this->getClient();
    }
    public function getClient()
    {
        $config = [
            'base_uri'=>$this->host,
            'connect_timeout' => 3,
            'timeout'         => 30,
            'http_errors'     => true, //抛出异常 true是 false否
            'verify'          => false,
        ];
        $this->client = new Client($config);
        $offset = 200;
        $limit = 20;
        while (true) {
            $res = $this->getUser($offset,$limit);
            $offset = $offset+$limit;
            echo "一共关注粉丝:".$this->num.PHP_EOL;
            if ($res == false){
                break;
            }
            if ($offset == 200){
                break;
            }
        }
        echo "执行结束".PHP_EOL;
    }



    public function getUser($offset,$limit)
    {
        $header = [
            'Charset' => 'UTF-8',
            'Cookie' => "",
            'Referer'=>$this->referer,
            'X-Api-Source'=>"rweb",
            'X-Csrftoken'=>"nmGVmTPzyql6rHpkhRnqv1l5sreXObnk",
            'X-Requested-With'=>"XMLHttpRequest",
            'X-Shopee-Language'=>"zh-Hans",
            'Af-Ac-Enc-Sz-Token'=>"xWN/ukH7fs4BHBY4Xd665g==|kw485zFrZ6tP0pkD+GFVcUvJV8d6WSc5TaumczYlQKrevik3DNPoQIglRVL1pF0JIhBpIgj064RGp3dqmZFSXrwOE7gv+VucwCE=|CdOAXVawp5dkTRvf|06|3"
        ];

        $params = [
            'offset'=>$offset,
            'limit'=>$limit,
            'shopid'=>$this->shopid,
        ];
        $uri = $this->host."/api/v4/pages/get_follower_list?".http_build_query($params);
        echo $uri.PHP_EOL;
        $result = $this->client->request('get', $uri,[
            'headers'         => $header,
        ]);
        $res = $result->getBody()->getContents();
        if (empty($res)){
            return false;
        }
        $content = json_decode($res,true);
        if (empty($content['data'])){
            return false;
        }
        if (isset($content['data']['accounts'])){
            foreach ($content['data']['accounts'] as $user){
                if ($user['is_following'] == false){
                    $this->followUser($user['userid']);
                    usleep(200000);
                }
//                else{
//                    $this->followUser($user['userid'],"unfollow");
//                }
            }
        }else{
            echo $res;
        }

        return true;

    }
    public function followUser($userid,$action="follow")
    {
        $cookie = <<<abc
SPC_F=PfFRzOigKgKvhLFoeOYX903SryN1r8c2; REC_T_ID=3e6d5df6-4fbe-11ed-b2aa-4cd98f65f772; language=zhHans; SPC_IA=-1; SPC_T_ID="MzGRLh+lEcNjEki+rb/6Ka2FnkWhldvLNfJJQsBuGaLDSWcFzbDumr31bn/2PTtiR3a/5OoIojv6SYaW2Hccn9yBaouXn+a8ojdtvwCdS4WEkOjj4EzUnWR/04SjyDNV9eFXwHHKxZWOCzOivydfoqf5DbVHVMnE22q+PXNMaz4="; SPC_T_IV="N01jOG1kY2tmSlhXbXZWNg=="; cto_bundle=faU7J182ZlRQcXJTaVNRZDJwNGpOZ24yNnNXdFNXZFglMkJRNHJmc0h6MzdPUXJxRnE0UkVPc2tRN0JuRmdYQyUyRjV0aEhKNEg1TTBKRERCY0IwTThaUWI1YWZoRlhqOVNMZ3p1eVF2RWhYeCUyQkpMOFl4JTJGbVFzWXdLa2dhbHJ3VkxxNE1EMklETFJ5c1BOZTBubnl6QzlPMFdCVDQzQSUzRCUzRA; _fbp=fb.1.1675261158790.1880980235; SPC_CLIENTID=UGZGUnpPaWdLZ0t2ipsclunrujycodfo; _med=refer; _gcl_au=1.1.1967807284.1683686623; SPC_U=843812769; SPC_T_IV=RVRDM1d4MkswTHBnUkVxbA==; SPC_R_T_ID=fGJ2La03kfqr0XMY8rfc/z7TAyB+IHUrBXTXXflg19THicSzyZaYOOnhA1h8ymDwPQ29zEDKChvPnzWE6LlI3PaIfPWMNhWZwfxDdaLq3F4SwqH9dP1ewS4XqKopAOSRAJVFZlXW6YIPVTSnO7NuKfgPDr4L58YJ/9oOpqtFIkc=; SPC_R_T_IV=RVRDM1d4MkswTHBnUkVxbA==; SPC_T_ID=fGJ2La03kfqr0XMY8rfc/z7TAyB+IHUrBXTXXflg19THicSzyZaYOOnhA1h8ymDwPQ29zEDKChvPnzWE6LlI3PaIfPWMNhWZwfxDdaLq3F4SwqH9dP1ewS4XqKopAOSRAJVFZlXW6YIPVTSnO7NuKfgPDr4L58YJ/9oOpqtFIkc=; __LOCALE__null=TW; csrftoken=nmGVmTPzyql6rHpkhRnqv1l5sreXObnk; SPC_SI=6QZaZAAAAABFdndHMXBlM5Et9QAAAAAAc3dOaXpTUnI=; _QPWSDCXHZQA=d72b3999-2b76-423c-e472-0c1b60dcb2a8; SPC_ST=.NUMxQ2djbzM1UHV6eGRxZA7AdRoHnQkxf+IUX/VDk1WgeE1MT9+ZWiX2gxlX39Ie68deAhRaJoYbCKXdIj+WzUc9vTxnjT/MnJIqj9S56oe3f+FsE2aCL7hBWT5j/xQ2MXAmtZsYDvOPu89TW7bW8djAas06m8p1J5dPFgyOiQsHuZ97xKjfEe4H90mbGLG1wxwHdYyNxYEEj9dCXMc6jQ==; AMP_TOKEN=%24NOT_FOUND; _gid=GA1.2.1335284713.1684117555; _ga=GA1.1.481971394.1666191408; _ga_RPSBE3TQZZ=GS1.1.1684117514.7.1.1684117571.3.0.0; shopee_webUnique_ccd=PGxb9tZmcRyxSmgXRO%2BbQQ%3D%3D%7Ckwo85zFrZ6tP0pkD%2BGFVcUvJV8d6WSc5TaumczYlQKrevik3DNPoQIglRVL1pF0JIhBpIgj064RGp3dqmZJVWLUMEbsg%2BVucwCE%3D%7CCdOAXVawp5dkTRvf%7C06%7C3; ds=3101f821e65ffc477435b142a67af8cf; SPC_EC=em5ScFZoRjRkdE03VjZDdbJQQvCqfdDlFdFBttMuLaHLpuOjVeLcu4AcgP6S52Ltl9Vfh33yPPpRcOQnMGW9N5gGCxcvYdvkAfNlDR1bJhkzmJcawn8JncHNnkh8XLDtg3DEwW7Je68fP2/8sJM4lRogzOdWfSw54vwe1oedHqE=
abc;

        $header = [
            'Charset' => 'UTF-8',
            'Cookie' => $cookie,
            'Referer'=>$this->referer,
            'X-Api-Source'=>"rweb",
            'X-Csrftoken'=>"nmGVmTPzyql6rHpkhRnqv1l5sreXObnk",
            'X-Requested-With'=>"XMLHttpRequest",
            'X-Shopee-Language'=>"zh-Hans",
            'Af-Ac-Enc-Sz-Token'=>"PGxb9tZmcRyxSmgXRO+bQQ==|kwo85zFrZ6tP0pkD+GFVcUvJV8d6WSc5TaumczYlQKrevik3DNPoQIglRVL1pF0JIhBpIgj064RGp3dqmZJVWLUMEbsg+VucwCE=|CdOAXVawp5dkTRvf|06|3"
        ];
        $params = [
            'userid'=>$userid,
        ];
        $uri = $this->host."/api/v4/pages/{$action}";

        $this->client->request('post', $uri,[
            'headers'         => $header,
            'body' => json_encode($params)
        ]);
        $this->num++;
        echo "{$action}-粉丝成功-{$userid}".PHP_EOL;
    }
}
