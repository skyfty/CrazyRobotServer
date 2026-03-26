<?php

namespace app\common\library;

use app\common\model\User;
use app\common\model\UserRule;
use fast\Random;
use think\Config;
use think\Db;
use think\Exception;
use think\Hook;
use think\Request;
use think\Validate;
use app\api\model\LevelType as LevelTypeModel;//关卡类型
use app\api\model\Pass as PassModel;//通过记录
use app\api\model\Specialequipments as SpecialequipmentsModel;//特殊装备
use app\api\model\Upgrade as UpgradeModel;//升级记录
use app\api\model\Token as TokenModel;//Toke



class Auth
{
    protected static $instance = null;
    protected $_error = '';
    protected $_logined = false;
    protected $_user = null;
    protected $_token = '';
    //Token默认有效时长
    protected $keeptime = 2592000;
    protected $requestUri = '';
    protected $rules = [];
    //默认配置
    protected $config = [];
    protected $options = [];
    protected $allowFields = ['id', 'username', 'gold', 'diamond', 'magazinelevel', 'equipmentEquipped', 'equipment', 'upgradeData'];

    public function __construct($options = [])
    {
        if ($config = Config::get('user')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->options = array_merge($this->config, $options);
    }

    /**
     *
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }
    

    /**
     * 获取User模型
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 兼容调用user模型的属性
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_user ? $this->_user->$name : null;
    }

    /**
     * 兼容调用user模型的属性
     */
    public function __isset($name)
    {
        return isset($this->_user) ? isset($this->_user->$name) : false;
    }

    /**
     * 根据Token初始化
     *
     * @param string $token Token
     * @return boolean
     */
    public function init($token)
    {
        if ($this->_logined) {
            return true;
        }
        if ($this->_error) {
            return false;
        }
        $data = Token::get($token);
        if (!$data) {
            return false;
        }
        $user_id = intval($data['user_id']);
        if ($user_id > 0) {
            $user = User::get($user_id);
            if (!$user) {
                $this->setError('Account not exist');
                return false;
            }
            if ($user['status'] != 'normal') {
                $this->setError('Account is locked');
                return false;
            }
            $this->_user = $user;
            $this->_logined = true;
            $this->_token = $token;

            //初始化成功的事件
            Hook::listen("user_init_successed", $this->_user);

            return true;
        } else {
            $this->setError('You are not logged in');
            return false;
        }
    }

    /**
     * 注册用户
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param array  $extend   扩展参数
     * @return boolean
     */
    public function register($username, $password, $email = '', $mobile = '', $extend = [])
    {
        // 检测用户名、昵称、邮箱、手机号是否存在
        if (User::getByUsername($username)) {
            $this->setError('Username already exist');
            return false;
        }
        if (User::getByNickname($username)) {
            $this->setError('Nickname already exist');
            return false;
        }
        if ($email && User::getByEmail($email)) {
            $this->setError('Email already exist');
            return false;
        }
        if ($mobile && User::getByMobile($mobile)) {
            $this->setError('Mobile already exist');
            return false;
        }

        $ip = request()->ip();
        $time = time();

        $data = [
            'username' => $username,
            'password' => $password,
            'email'    => $email,
            'mobile'   => $mobile,
            'level'    => 1,
            'score'    => 0,
            'avatar'   => '',
        ];
        $params = array_merge($data, [
            'nickname'  => preg_match("/^1[3-9]{1}\d{9}$/", $username) ? substr_replace($username, '****', 3, 4) : $username,
            'salt'      => Random::alnum(),
            'jointime'  => $time,
            'joinip'    => $ip,
            'logintime' => $time,
            'loginip'   => $ip,
            'prevtime'  => $time,
            'status'    => 'normal'
        ]);
        $params['password'] = $this->getEncryptPassword($password, $params['salt']);
        $params = array_merge($params, $extend);

        //账号注册时需要开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = User::create($params, true);

            $this->_user = User::get($user->id);

            //设置Token
            $this->_token = Random::uuid();
            Token::set($this->_token, $user->id, $this->keeptime);

            //设置登录状态
            $this->_logined = true;

            //注册成功的事件
            Hook::listen("user_register_successed", $this->_user, $data);
            Db::commit();
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
        return true;
    }
     /**
     * 微信登录
     * @param string $code 微信登录凭证（code）
     * @return array|false 成功返回用户信息（含token），失败返回false
     */
    public function loginByWechat($code)
    {
         $appid =  "wx965590a19e99da54";  
         $appsecret ="86737ae6c85afd410954fa4c7c1758ee";  
         $grant_type ="authorization_code";  
    
      $uri = "https://api.weixin.qq.com/sns/jscode2session";  
      // 构造查询字符串  
      $queryParams = [
        'appid' => $appid,
        'secret' => $appsecret,
        'js_code' => $code,
        'grant_type' => $grant_type,
      ];
      $url = $uri . '?' . http_build_query($queryParams);
        // 向微信服务器发送请求获取 openid 和 session_key
       // $wxResp = \fast\Http::get("https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appsecret}&js_code={$code}&grant_type=authorization_code");
        $wxResp = \fast\Http::get($url);
       $wxData = json_decode($wxResp, true);
     return $wxData;
    }
    
     /**
     * 初始化用户数据
     */
    public function initUserData($username)
    {
        $user = User::getByUsername($username);
        if (!$user) {
            $this->setError('User not exist');
            return false;
        }
        //获取用户id
        $userId = $user->id;
        //初始化passmodel
        $passModel = new PassModel();
        //清除用户的所有通过记录
        $passModel->clear($userId);
        //更新level为1
        $user->level = 1;
        //更新score为0
        $user->score = 0;
        $user->gold = 0;
        $user->diamond = 100;
        $user->equipment = '[{"id": "1", "level": 1}]';
        $user->upgradeData = '[{"id": 1, "level": 0}, {"id": 2, "level": 0}, {"id": 3, "level": 0}, {"id": 4, "level": 0}, {"id": 5, "level": 0}, {"id": 6, "level": 0}, {"id": 7, "level": 0}]';
        $user->equipmentEquipped = '[{"id": 0}, {"id": 0}, {"id": 0}, {"id": 0}, {"id": 0}]';
        $user->save();
        return true;
    }
    /**
     * 自定义创建用户
     */
    public function customRegister($username)
    {
        $ip = request()->ip();
        $time = time();
          $data = [
            'username' => $username,
            'password' => $username,
            'email'    => $username,
            'mobile'   => "12345678911",
            'level'    => 1,
            'score'    => 0,
            'avatar'   => '',
            'gold'     => 0,
            'diamond'  => 100,
            'equipment' => '[{"id": "1", "level": 1}]',
            'upgradeData' => '[{"id": 1, "level": 0}, {"id": 2, "level": 0}, {"id": 3, "level": 0}, {"id": 4, "level": 0}, {"id": 5, "level": 0}, {"id": 6, "level": 0}, {"id": 7, "level": 0}]',
            'equipmentEquipped' => '[{"id": 0}, {"id": 0}, {"id": 0}, {"id": 0}, {"id": 0}]'
        ];
         $params = array_merge($data, [
            'nickname'  => preg_match("/^1[3-9]{1}\d{9}$/", $username) ? substr_replace($username, '****', 3, 4) : $username,
            'salt'      => Random::alnum(),
            'jointime'  => $time,
            'joinip'    => $ip,
            'logintime' => $time,
            'loginip'   => $ip,
            'prevtime'  => $time,
            'status'    => 'normal'
        ]);
         $params['password'] = $this->getEncryptPassword($username, $params['salt']);
        $params = array_merge($params);
          //账号注册时需要开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user = User::create($params, true);
            $this->_user = User::get($user->id);
                //设置Token
            $this->_token = Random::uuid();
            Token::set($this->_token, $user->id, $this->keeptime);

            //设置登录状态
            $this->_logined = true;

            //注册成功的事件
            Hook::listen("user_register_successed", $this->_user, $data);
            Db::commit();
        } catch (Exception $e) {
              $this->setError($e->getMessage());
            Db::rollback();
            return false;
        }
        return true;
    }

    /**
     * 用户登录
     *
     * @param string $account  账号,用户名、邮箱、手机号
     * @param string $password 密码
     * @return boolean
     */
    public function login($account, $password)
    {
        $field = Validate::is($account, 'email') ? 'email' : (Validate::regex($account, '/^1\d{10}$/') ? 'mobile' : 'username');
        $user = User::get([$field => $account]);
        if (!$user) {
            $this->setError('Account is incorrect');
            return false;
        }

        if ($user->status != 'normal') {
            $this->setError('Account is locked');
            return false;
        }

        if ($user->loginfailure >= 10 && time() - $user->loginfailuretime < 86400) {
            $this->setError('Please try again after 1 day');
            return false;
        }

        if ($user->password != $this->getEncryptPassword($password, $user->salt)) {
            $user->save(['loginfailure' => $user->loginfailure + 1, 'loginfailuretime' => time()]);
            $this->setError('Password is incorrect');
            return false;
        }

        //直接登录会员
        return $this->direct($user->id);
    }
    /**
     * 只要账号就能登录成功，不存在就创建用户
     */
    public function loginByUsername($username)
    {
        $user = User::getByUsername($username);
        if (!$user) {
            //账号不存在，创建用户
            $this->customRegister($username, $username);
            $user = User::getByUsername($username);
            if (!$user) {
                $this->setError('Account is incorrect');
                return false;
            }

            // $this->setError('Account is incorrect');
            // return false;
        }
        //直接登录会员
        return $this->direct($user->id);
    }
    /**
     * 根据用户id获取用户信息
     */
    public function getInfo($user_id)
    { 
        $user = User::field($this->allowFields)->find($user_id);
        // $user = User::get($user_id, $this->allowFields);
        $levelTypeModel = new LevelTypeModel();
        $levelTypes = $levelTypeModel->getAll();
        //获取用户的所有通过记录
        $passModel = new PassModel();
        $passes = $passModel->getAll($user->id);
          //遍历等级类型，判断是否有符合的
            foreach ($levelTypes as $levelType) {
                  $levelType->type = $levelType->id;
                  //移除id
                  unset($levelType->id);
                  $levelType->index = 0;
                   //判断是否通过了该关卡
                foreach ($passes as $pass) {
                    if ($pass->type == $levelType->type) {
                       $levelType->index = $pass->id;
                    }
                }
            }
            $user->pass = $levelTypes;//通关id
            //获取用户的所有特殊装备
            $specialequipmentsModel = new SpecialequipmentsModel();
            $user->equipmentDetails = $specialequipmentsModel->getAll($user->equipment);//特殊装备详情
            //获取用户的所有升级记录
            $upgradeModel = new UpgradeModel();
            $user->upgradeDatas = $upgradeModel->getAll($user->upgradeData);//升级记录详情
             $user->equipmentEquippedIndex=json_decode($user->equipmentEquipped,true);
        return $user;
    }
    /**
     * 更新用户装备信息
     */
    public function updateEquipmentEquipped($user_id, $equipmentEquippedJson)
    {
        $user = User::get($user_id);
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
        $user->equipmentEquipped = $equipmentEquippedJson;
        $user->save();
        return true;
    }
    /**
     * 更新用户装备信息Equipment
     */
     public function updateEquipment($user_id, $equipmentJson)
    {
        $user = User::get($user_id);
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
        $user->equipment = $equipmentJson;
        $user->save();
        return true;
    }
    
    /*
    * 更新金钱
    */
    public function updateGold($user_id, $gold)
    {
        $user = User::get($user_id);
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
        $user->gold = $gold;
        $user->save();
        return true;
    }
     /*
    * 更新添加金钱
    */
    public function updateAddGold($user_id, $gold)
    {
        $user = User::get($user_id);
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
        $user->gold += $gold;
        $user->save();
        return true;
    }
    /**
     * 减少用户砖石数量
     */
    public function reduceDiamond($user_id, $num){
       $expenditure = $num*1; 
         $user = User::get($user_id);
         if (!$user) {
            $this->setError('User not found');
            return false;
        }
        $user->diamond = $user->diamond - $expenditure;
        $user->save();
        return true;
    }
    /*
     * 更新升级数据upgradeData
     */
      public function updateUpgradeData($user_id, $upgradeDataJson,$listDataName)
    {
        $user = User::get($user_id);
        if (!$user) {
            $this->setError('User not found');
            return false;
        }
        $user->$listDataName = $upgradeDataJson;
        $user->save();
        return true;
    }
  /**
     * 获取会员基本信息
     */
    public function getUserinfo()
    {
        $data = $this->_user->toArray();
        $allowFields = $this->getAllowFields();
        $userinfo = array_intersect_key($data, array_flip($allowFields));
        $userinfo = array_merge($userinfo, Token::get($this->_token));
        return $userinfo;
    }
    /**
     * 退出
     *
     * @return boolean
     */
    public function logout()
    {
        if (!$this->_logined) {
            $this->setError('You are not logged in');
            return false;
        }
        //设置登录标识
        $this->_logined = false;
        //删除Token
        Token::delete($this->_token);
        //退出成功的事件
        Hook::listen("user_logout_successed", $this->_user);
        return true;
    }

    /**
     * 修改密码
     * @param string $newpassword       新密码
     * @param string $oldpassword       旧密码
     * @param bool   $ignoreoldpassword 忽略旧密码
     * @return boolean
     */
    public function changepwd($newpassword, $oldpassword = '', $ignoreoldpassword = false)
    {
        if (!$this->_logined) {
            $this->setError('You are not logged in');
            return false;
        }
        //判断旧密码是否正确
        if ($this->_user->password == $this->getEncryptPassword($oldpassword, $this->_user->salt) || $ignoreoldpassword) {
            Db::startTrans();
            try {
                $salt = Random::alnum();
                $newpassword = $this->getEncryptPassword($newpassword, $salt);
                $this->_user->save(['loginfailure' => 0, 'password' => $newpassword, 'salt' => $salt]);

                Token::delete($this->_token);
                //修改密码成功的事件
                Hook::listen("user_changepwd_successed", $this->_user);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->setError($e->getMessage());
                return false;
            }
            return true;
        } else {
            $this->setError('Password is incorrect');
            return false;
        }
    }

    /**
     * 直接登录账号
     * @param int $user_id
     * @return boolean
     */
    public function direct($user_id)
    {
        $user = User::get($user_id);
        if ($user) {
            Db::startTrans();
            try {
                $ip = request()->ip();
                $time = time();

                //判断连续登录和最大连续登录
                if ($user->logintime < \fast\Date::unixtime('day')) {
                    $user->successions = $user->logintime < \fast\Date::unixtime('day', -1) ? 1 : $user->successions + 1;
                    $user->maxsuccessions = max($user->successions, $user->maxsuccessions);
                }

                $user->prevtime = $user->logintime;
                //记录本次登录的IP和时间
                $user->loginip = $ip;
                $user->logintime = $time;
                //重置登录失败次数
                $user->loginfailure = 0;

                $user->save();

                $this->_user = $user;

                $this->_token = Random::uuid();
                Token::set($this->_token, $user->id, $this->keeptime);

                $this->_logined = true;

                //登录成功的事件
                Hook::listen("user_login_successed", $this->_user);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->setError($e->getMessage());
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检测是否是否有对应权限
     * @param string $path   控制器/方法
     * @param string $module 模块 默认为当前模块
     * @return boolean
     */
    public function check($path = null, $module = null)
    {
        if (!$this->_logined) {
            return false;
        }

        $ruleList = $this->getRuleList();
        $rules = [];
        foreach ($ruleList as $k => $v) {
            $rules[] = $v['name'];
        }
        $url = ($module ? $module : request()->module()) . '/' . (is_null($path) ? $this->getRequestUri() : $path);
        $url = strtolower(str_replace('.', '/', $url));
        return in_array($url, $rules);
    }

    /**
     * 判断是否登录
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->_logined) {
            return true;
        }
        return false;
    }

    /**
     * 获取当前Token
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

  

    /**
     * 获取会员组别规则列表
     * @return array|bool|\PDOStatement|string|\think\Collection
     */
    public function getRuleList()
    {
        if ($this->rules) {
            return $this->rules;
        }
        $group = $this->_user->group;
        if (!$group) {
            return [];
        }
        $rules = explode(',', $group->rules);
        $this->rules = UserRule::where('status', 'normal')->where('id', 'in', $rules)->field('id,pid,name,title,ismenu')->select();
        return $this->rules;
    }

    /**
     * 获取当前请求的URI
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * 设置当前请求的URI
     * @param string $uri
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * 获取允许输出的字段
     * @return array
     */
    public function getAllowFields()
    {
        return $this->allowFields;
    }

    /**
     * 设置允许输出的字段
     * @param array $fields
     */
    public function setAllowFields($fields)
    {
        $this->allowFields = $fields;
    }

    /**
     * 删除一个指定会员
     * @param int $user_id 会员ID
     * @return boolean
     */
    public function delete($user_id)
    {
        $user = User::get($user_id);
        if (!$user) {
            return false;
        }
        Db::startTrans();
        try {
            // 删除会员
            User::destroy($user_id);
            // 删除会员指定的所有Token
            Token::clear($user_id);

            Hook::listen("user_delete_successed", $user);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 获取密码加密后的字符串
     * @param string $password 密码
     * @param string $salt     密码盐
     * @return string
     */
    public function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     * @return boolean
     */
    public function match($arr = [])
    {
        $request = Request::instance();
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }
        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($request->action()), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * 设置会话有效时间
     * @param int $keeptime 默认为永久
     */
    public function keeptime($keeptime = 0)
    {
        $this->keeptime = $keeptime;
    }

    /**
     * 渲染用户数据
     * @param array  $datalist  二维数组
     * @param mixed  $fields    加载的字段列表
     * @param string $fieldkey  渲染的字段
     * @param string $renderkey 结果字段
     * @return array
     */
    public function render(&$datalist, $fields = [], $fieldkey = 'user_id', $renderkey = 'userinfo')
    {
        $fields = !$fields ? ['id', 'nickname', 'level', 'avatar'] : (is_array($fields) ? $fields : explode(',', $fields));
        $ids = [];
        foreach ($datalist as $k => $v) {
            if (!isset($v[$fieldkey])) {
                continue;
            }
            $ids[] = $v[$fieldkey];
        }
        $list = [];
        if ($ids) {
            if (!in_array('id', $fields)) {
                $fields[] = 'id';
            }
            $ids = array_unique($ids);
            $selectlist = User::where('id', 'in', $ids)->column($fields);
            foreach ($selectlist as $k => $v) {
                $list[$v['id']] = $v;
            }
        }
        foreach ($datalist as $k => &$v) {
            $v[$renderkey] = $list[$v[$fieldkey]] ?? null;
        }
        unset($v);
        return $datalist;
    }

    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return Auth
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? __($this->_error) : '';
    }
}
