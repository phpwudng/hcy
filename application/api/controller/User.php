<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\exception\UploadException;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\library\Upload;
use app\common\service\SmsService;
use fast\Random;
use think\Cookie;
use think\Hook;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd','logout'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;
        //监听注册登录退出的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);

            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
        $account = $this->request->request('account');
        $password = $this->request->request('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 修改密码
     */
    public function changepwd()
    {
            $oldpassword = $this->request->post("oldpassword");
            $newpassword = $this->request->post("newpassword");
            $renewpassword = $this->request->post("renewpassword");
            $token = $this->request->post('__token__');
            $rule = [
                'oldpassword'   => 'require|length:6,30',
                'newpassword'   => 'require|length:6,30',
                'renewpassword' => 'require|length:6,30|confirm:newpassword',
                '__token__'     => 'token',
            ];

            $msg = [
                'renewpassword.confirm' => __('Password and confirm password don\'t match')
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return false;
            }

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
    }

    /**
     * 手机验证码登录
     *
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param string $code   验证码
     */
    public function register()
    {
        $username = $this->request->request('username','');
        $password = $this->request->request('password');
        $email = $this->request->request('email','');
        $mobile = $this->request->request('mobile');
        $code = $this->request->request('code');
        if (!$mobile || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $username = empty($username) ? $mobile : '';
//        if ($email && !Validate::is($email, "email")) {
//            $this->error(__('Email is incorrect'));
//        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
//        $ret = Sms::check($mobile, $code, 'register');
        $ret = SmsService::instance()->check($mobile,$code);
//        if (!$ret) {
//            $this->error(__('Captcha is incorrect'));
//        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->request('username');
        $nickname = $this->request->request('nickname');
        $bio = $this->request->request('bio');
        $avatar = $this->request->request('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @param string $email   邮箱
     * @param string $captcha 验证码
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->request('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @param string $mobile   手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->request('mobile');
        $captcha = $this->request->request('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->request("platform");
        $code = $this->request->request("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    public function resetpwd()
    {
        $type = $this->request->request("type",'mobile');
        $mobile = $this->request->request("mobile");
        $email = $this->request->request("email",'');
        $newpassword = $this->request->request("newpassword");
        $captcha = $this->request->request("captcha",'');
        $code = $this->request->request("code");
        if (!$newpassword || !$code) {
            $this->error(__('Invalid parameters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
//            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            $ret = SmsService::instance()->check($mobile,$code);
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            SmsService::instance()->flush($mobile);
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 关注接口
     */
    public function follow()
    {
        $campanyId = $this->request->param('campanyId/d',0);
//        if (! empty(model('app\admin\model\Goods')->checkCampanyById($campanyId))){
//            $this->error('输入错误，公司不存在');
//        }
        $userId = (int)Cookie::get('uid');
        if (empty($userId)){
            $userId = $this->request->post('uid',1);
        }
        $res = model('app\admin\model\UserSubscribe')->follow($campanyId,$userId);
        if (empty($res)){
            $this->error('关注失败');
        }
        $this->success('关注成功');

    }

    /**
     * 取消关注接口
     */
    public function unfollow()
    {
        $campanyId = $this->request->param('campanyId',0);
        $userId = (int)Cookie::get('uid');
        if (empty($userId)){
            $userId = $this->request->post('uid',1);
        }
        model('app\admin\model\UserSubscribe')->unfollow($campanyId,$userId);
        $this->success('取消关注成功');
    }

    /**
     * 关注列表
     */
    public function subscribeCampany()
    {
        $userId = (int)Cookie::get('uid');
        if (empty($userId)){
            $userId = $this->request->post('uid',1);
        }
        $page = $this->request->post('page',1);
        $size = $this->request->post('size',10);
        $data = model('app\admin\model\UserSubscribe')->subscribeCampany($userId,$page,$size);
        $this->success('成功',$data);
    }

    /**
     * 我发布的公司
     */
    public function myCampanyList()
    {
        $userId = (int)Cookie::get('uid');
        if (empty($userId)){
            $userId = $this->request->post('uid',1);
        }
        $page = (int)$this->request->get('page',1);
        $status = (int)$this->request->get('status',0);
        $size = (int)$this->request->get('size',10);
        [$count,$data] = model('app\admin\model\Goods')->myCampanyList($userId,$status,$page,$size);
        $res = [
            'page'=>$page,
            'size'=>$size,
            'total'=>$count,
            'data'=>$data
        ];
        $this->success('成功',$res);
    }
    /**
     * 我发布的公司详情
     */
    public function getMyCampanyInfoById()
    {
        $userId = (int)Cookie::get('uid');
        if (empty($userId)){
            $userId = $this->request->post('uid',1);
        }
        $campanyId = (int)$this->request->param('campanyId');
        if (empty($campanyId)){
            $this->error('参数错误');
        }
        $data = model('app\admin\model\Goods')->getMyCampanyInfoById($userId,$campanyId);
        $this->success('成功',$data);
    }

    /**
     * 上传图片
     */
    public function upload()
    {
        $attachment = null;
        //默认普通上传文件
        $file = $this->request->file('file');
        try {
            $upload = new Upload($file);
            $attachment = $upload->upload();
        } catch (UploadException $e) {
            $this->error($e->getMessage());
        }
        $url = $attachment->url ?? '/uploads/20210701/b349c776cc02a49f72f83ec5254b27d1.png';
        $this->success('上传成功',['url'=>$url]);

    }

    /**
     * 下架公司
     */
    public function offCampany()
    {
        $userId = Cookie::get('uid');
        if (empty($userId)){
            $userId = $this->request->post('uid',1);
        }
        $campanyId = $this->request->get('campanyId',0);
        if (empty($campanyId)){
            $this->error('参数错误');
        }
        //判断是不是自己发布的公司
        $res = model('app\admin\model\Goods')->offCampany($userId,(int)$campanyId);
        if (empty($res)){
            $this->error('异常操作');
        }else{
            $this->success('下架成功');
        }

    }

}
