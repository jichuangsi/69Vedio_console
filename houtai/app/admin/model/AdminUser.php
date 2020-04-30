<?php
// +----------------------------------------------------------------------
// | msvodx[TP5内核]
// +----------------------------------------------------------------------
// | Copyright © 2019-QQ97250974
// +----------------------------------------------------------------------
// | 专业二开仿站定制修改,做最专业的视频点播系统
// +----------------------------------------------------------------------
// | Author: cherish ©2018
// +----------------------------------------------------------------------
namespace app\admin\model;
use app\common\controller\Common;
use think\Db;
use think\Model;
use app\admin\model\AdminMenu as MenuModel;
use app\admin\model\AdminRole as RoleModel;

/**
 * 后台用户模型
 * @package app\admin\model
 */
class AdminUser extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = 'mtime';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 对密码进行加密
    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    // 写入时，将权限ID转成JSON格式
    public function setAuthAttr($value)
    {
        if (empty($value)) return '';
        return json_encode($value);
    }

    // 获取最后登陆ip
    public function setLastLoginIpAttr()
    {
        return get_client_ip();
    }

    /**
     * 删除用户
     * @param string $id 用户ID
     * @author frs whs tcl dreamer ©2016
     * @return bool
     */
    public function del($id = 0) 
    {
        $menu_model = new MenuModel();
        if (is_array($id)) {
            $error = '';
            foreach ($id as $k => $v) {
                if ($v == ADMIN_ID) {
                    $error .= '不能删除当前登陆的用户['.$v.']！<br>';
                    continue;
                }

                if ($v == 1) {
                    $error .= '不能删除超级管理员['.$v.']！<br>';
                    continue;
                }

                if ($v <= 0) {
                    $error .= '参数传递错误['.$v.']！<br>';
                    continue;
                }

                $map = [];
                $map['id'] = $v;
                // 删除用户
                self::where($map)->delete();
                // 删除关联表;
                $menu_model->delUser($v);
            }

            if ($error) {
                $this->error = $error;
                return false;
            }
        } else {
            $id = (int)$id;
            if ($id <= 0) {
                $this->error = '参数传递错误！';
                return false;
            }

            if ($id == ADMIN_ID) {
                $this->error = '不能删除当前登陆的用户！';
                return false;
            }

            if ($id == 1) {
                $this->error = '不能删除超级管理员！';
                return false;
            }

            $map = [];
            $map['id'] = $id;
            // 删除用户
            self::where($map)->delete();
            // 删除关联表
            $menu_model->delUser($id);
        }

        return true;
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $remember 记住登录 TODO
     * @author frs whs tcl dreamer ©2016
     * @return bool|mixed
     */
    public function login($username = '', $password = '', $remember = false)
    {
//  	$member=$this->myDb->name('member');
        $username = trim($username);
        $password = trim($password);
        $map = [];
        $map['status'] = 1;
        $map['username'] = $username;
        if ($this->validateData(input('post.'), 'AdminUser.login') != true) {
            $this->error = $this->getError();
            return false;
        }
        
        $user = self::where($map)->find(); //后台管理员
        $comm=new Common();
        
//      $mydb=Common::x_connect_webdatabase();
//      $users = Db::name('member')->where(['username'=>$username])->find();  //前台vip用户
		$db_config = Db::name('admin_user')->field('db_config')->order('id asc')->find();
		$config = json_decode($db_config['db_config'], true);
                    $db2 = [
                        // 数据库类型
                        'type' => 'mysql',
                        // 服务器地址
                        'hostname' => $config ['hostname'],
                        // 数据库名
                        'database' => $config ['database'],
                        // 数据库用户名
                        'username' => $config ['username'],
                        // 数据库密码
                        'password' => $config ['password'],
                        'hostport' => $config ['hostport'],
                        // 数据库编码默认采用utf8
                        'charset' => 'utf8',
                        // 数据库表前缀
                        'prefix' => 'ms_',
                    ];
        $myDb = Db::connect($db2);
		$member=$myDb->name('member');
		$users = $member->where(['username'=>$username])->find();
        if (!$user && !$users) {
            $this->error = '用户不存在或被禁用！';
            return false;
        }
        
		if($user){
			// 密码校验
	        if (!password_verify($password, $user->password)) {
	            $this->error = '登陆密码错误！';
	            return false;
	        }
	
	        // 检查是否分配角色
	        if ($user->role_id == 0) {
	            $this->error = '禁止访问(原因：未分配角色)！';
	            return false;
	        }
	
	        // 角色信息
	        $role = RoleModel::where('id', $user->role_id)->find()->toArray();
	        if (!$role || $role['status'] == 0) {
	            $this->error = '禁止访问(原因：角色分组可能被禁用)！';
	            return false;
	        }
	
	        // 更新登录信息
	        $user->last_login_time = time();
	        $user->last_login_ip   = get_client_ip();
	        if ($user->save()) {
	            // 执行登陆
	            $login = [];
	            $login['uid'] = $user->id;
	            $login['version'] = $user->version;
	            $login['role_id'] = $user->role_id;
	            $login['role_name'] = $role['name'];
	            $login['nick'] = $user->nick;
	            //获取版本
	            $login['version'] = $user->version;
	            if ($user->iframe == 1) {
	                cookie('hisi_iframe', 'yes');
	            } else {
	                cookie('hisi_iframe', null);
	            }
	            // 缓存角色权限
	            cache('role_auth_'.$user->role_id, $user['auth'] ? json_decode($user['auth']) : json_decode($role['auth']));
	            // 缓存登录信息
	            session('admin_user', $login);
	            session('admin_user_sign', $this->dataSign($login));
	            return $user->id;
	        }
		}
        if($users){
        	if($users['gid'] != 2){
        		$this->error = '您现在不是会员，还不能登陆';
	            return false;
        	}
        	if($users['password'] != md5($password)){
        		$this->error = '登陆密码错误！';
	            return false;
        	}
        	
        	$role = RoleModel::where('id', 4)->find()->toArray();
        		$login = [];
	            $login['uid'] = $users['id'];
	            $login['version'] = 3;
	            $login['role_id'] = 4;
	            $login['role_name'] = 'VIP用户';
	            $login['nick'] = $users['nickname'];
	            //获取版本
	            $login['version'] = 3;
	            cookie('hisi_iframe', 'yes');
	            // 缓存角色权限
	            cache('role_auth_'.$login['role_id'], json_decode($role['auth']));
	            // 缓存登录信息
	            session('admin_user', $login);
	            session('admin_user_sign', $this->dataSign($login));
	            return $users['id'];
        }
        return false;
    }

    /**
     * 判断是否登录
     * @author frs whs tcl dreamer ©2016
     * @return bool|array
     */
    public function isLogin() 
    {
    	$db_config = Db::name('admin_user')->field('db_config')->order('id asc')->find();
		$config = json_decode($db_config['db_config'], true);
                    $db2 = [
                        // 数据库类型
                        'type' => 'mysql',
                        // 服务器地址
                        'hostname' => $config ['hostname'],
                        // 数据库名
                        'database' => $config ['database'],
                        // 数据库用户名
                        'username' => $config ['username'],
                        // 数据库密码
                        'password' => $config ['password'],
                        'hostport' => $config ['hostport'],
                        // 数据库编码默认采用utf8
                        'charset' => 'utf8',
                        // 数据库表前缀
                        'prefix' => 'ms_',
                    ];
        $myDb = Db::connect($db2);
		$member=$myDb->name('member');
        $user = session('admin_user');
//      $member=$this->myDb->name('member');
        if (isset($user['uid'])) {
        	if($user['role_id'] == 4){
        		if (!$member->where('id', $user['uid'])->find()) {
	                return false;
	            }
        	}else{
        		if (!self::where('id', $user['uid'])->find()) {
	                return false;
	            }
        	}
            
            return session('admin_user_sign') == $this->dataSign($user) ? $user : false;
        }
        return false;
    }

    /**
     * 退出登陆
     * @author frs whs tcl dreamer ©2016
     * @return bool
     */
    public function logout() 
    {
        session('admin_user', null);
        session('admin_user_sign', null);
    }

    /**
     * 数据签名认证
     * @param array $data 被认证的数据
     * @author frs whs tcl dreamer ©2016
     * @return string 签名
     */
    public function dataSign($data = [])
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }
        ksort($data);
        $code = http_build_query($data);
        $sign = sha1($code);
        return $sign;
    }

    // /**
    //  * 用户状态设置
    //  * @param string $id 用户ID
    //  * @return bool
    //  */
    // public function status($id = '', $val = 0) {
    //     if (is_array($id)) {
    //         $error = '';
    //         foreach ($id as $k => $v) {
    //             $v = (int)$v;
    //             if ($v == 1) {
    //                 $error .= '禁止更改超级管理员状态['.$v.']<br>';
    //                 continue;
    //             }

    //             $map = [];
    //             $map['id'] = $v;
    //             // 删除用户
    //             self::where($map)->setField('status', $val);
    //         }

    //         if ($error) {
    //             $this->error = $error;
    //             return false;
    //         }
    //     } else {
    //         $id = (int)$id;
    //         if ($id <= 0) {
    //             $this->error = '参数传递错误';
    //             return false;
    //         }

    //         if ($id == 1) {
    //             $this->error = '禁止更改超级管理员状态';
    //             return false;
    //         }

    //         $map = [];
    //         $map['id'] = $id;
    //         // 删除用户
    //         self::where($map)->setField('status', $val);
    //     }

    //     return true;
    // }
}
