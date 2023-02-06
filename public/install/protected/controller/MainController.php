<?php

class MainController extends BaseController
{
    /**
     * 首页
     */
    public function actionIndex()
    {
        $agree = request('agree', 0);

        if ($agree == 1) {
            $_SESSION['agree'] = 1;
            $this->redirect('?a=check');
        }
    }

    /**
     * 环境检测
     */
    public function actionCheck()
    {
        if ($_SESSION['agree'] != 1) {
            $this->redirect('?a=index');
        }

        $_SESSION['error'] = false;
        //环境检测
        $this->env = check_env();
        //函数检测
        $this->func = check_func();
        //目录文件读写检测
        $this->dirfile = check_dirfile();
    }

    /**
     * 配置信息
     */
    public function actionSetting()
    {
        if ($_SESSION['error'] || $_SESSION['agree'] != 1) {
            $this->redirect('?a=index');
        }

        $this->timezones = $this->geTimezones();
    }

    public function actionDatabases()
    {
        $db_host = request('db_host', '');
        $db_port = request('db_port', '');
        $db_user = request('db_user', '');
        $db_pass = request('db_pass', '');
        $filter_dbs = ['information_schema', 'mysql', 'performance_schema', 'sys'];

        $pdo = $this->getDb($db_host, $db_port, $db_user, $db_pass);
        if ($pdo === false) {
            die(json_encode(['status' => 'error', 'message' => '数据库连接失败']));
        }

        $result = $pdo->query('show databases;');

        if ($result === false) {
            die(json_encode(['status' => 'error', 'message' => 'query failed']));
        }

        $list = $result->fetchAll();

        $databases = [];
        if ($list) {
            foreach ($list as $key => $row) {
                if (in_array($row['Database'], $filter_dbs)) {
                    continue;
                }
                $databases[] = $row['Database'];
            }
        }

        die(json_encode(['status' => 'success', 'data' => $databases]));
    }

    /**
     * 保存配置
     */
    public function actionSave()
    {
        $db_host = request('db_host', '');
        $db_port = request('db_port', '');
        $db_user = request('db_user', '');
        $db_pass = request('db_pass', '');
        $db_name = request('db_name', '');
        $db_prefix = request('db_prefix', '');
        $appUrl = $_SERVER['REQUEST_SCHEME'] . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);

        // 连接数据库服务器
        $db = $this->getDb($db_host, $db_port, $db_user, $db_pass);
        if ($db === false) {
            die(json_encode(['status' => 'n', 'message' => '数据库连接失败']));
        }

        // 创建数据库
        $sql = "CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4";
        $db->exec($sql) or die(json_encode(['status' => 'n', 'info' => '数据库[' . $db_name . ']创建失败']));

        // 导入数据
        $db = $this->getDb($db_host, $db_port, $db_user, $db_pass, $db_name);
        $sqls = ['structure.sql'];
        foreach ($sqls as $sql) {
            $this->importSql($db, $db_prefix, $sql);
        }

        // 写入配置
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $config = [
            'APP_KEY' => $appKey,
            'APP_URL' => $appUrl,
            'ASSET_URL' => $appUrl,
            'DB_HOST' => $db_host,
            'DB_PORT' => $db_port,
            'DB_DATABASE' => $db_name,
            'DB_USERNAME' => $db_user,
            'DB_PASSWORD' => $db_pass,
            'DB_PREFIX' => $db_prefix,
        ];
        if (!$this->writeConf($config)) {
            die(json_encode(['status' => 'n', 'info' => '创建配置文件失败']));
        }

        // 注册超级管理员
        $admin = [
            'username' => trim(request('admin_name')),
            'password' => trim(request('admin_password')),
            'email' => trim(request('admin_email')),
        ];

        if (empty($admin['username'])) {
            die(json_encode(['status' => 'n', 'message' => '请填写管理员用户名']));
        }

        if (empty($admin['password'])) {
            die(json_encode(['status' => 'n', 'message' => '请填写管理员登录密码']));
        }

        if (empty($admin['email'])) {
            die(json_encode(['status' => 'n', 'message' => '请填写管理员电子邮箱']));
        }

        // 执行seeder数据填充
        Http::get($appUrl . '/api/seeder');

        $this->register_administrator($db, $db_prefix, $admin);

        // 安装完成
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_password'] = $admin['password'];
        $_SESSION['app_url'] = $appUrl;
        $_SESSION['complete'] = true;

        // 生成 install.lock 文件
        file_put_contents(BASE_PATH . $this->lockFile, date('YmdHis'));

        die(json_encode(['status' => 'y', 'info' => '数据已成功提交']));
    }

    /**
     * 完成
     */
    public function actionDone()
    {
        if (!isset($_SESSION['complete'])) {
            $this->redirect('../');
        }
    }

    /**
     * 时区
     */
    private function geTimezones()
    {
        return array(
            'UTC' => 'UTC',
            'PRC' => '中华人民共和国',
            'Asia/Shanghai' => '亚洲，中国，上海',
            'Asia/Taipei' => '亚洲，中国，台北',
            'Asia/Chongqing' => '亚洲，中国，重庆',
            'Asia/Chungking' => '亚洲，中国，重庆',
            'Asia/Harbin' => '亚洲，中国，哈尔滨',
            'Asia/Urumqi' => '亚洲，中国，乌鲁木齐',
            'Asia/Hong_Kong' => '亚洲，中国，香港',
            'Hongkong' => '亚洲，中国，香港',
            'Asia/Macau' => '亚洲，中国，澳门',
            'Asia/Macao' => '亚洲，中国，澳门',
            'Asia/Singapore' => '亚洲，新加坡',
            'Singapore' => '亚洲，新加坡',
            'Asia/Seoul' => '亚洲，韩国，首尔',
            'Asia/Tokyo' => '亚洲，日本，东京',
            'Europe/Berlin' => '欧洲，德国，柏林',
            'Europe/Dublin' => '欧洲，德国，都柏林',
            'Europe/Paris' => '欧洲，法国，巴黎'
        );
    }

    /**
     * DB实例
     * @param $db_host
     * @param $db_port
     * @param $db_user
     * @param $db_pass
     * @param $db_name
     * @return PDO
     */
    private function getDb($db_host, $db_port, $db_user, $db_pass, $db_name = '')
    {
        $config = [
            'MYSQL_HOST' => trim($db_host),
            'MYSQL_PORT' => trim($db_port),
            'MYSQL_USER' => trim($db_user),
            'MYSQL_PASS' => trim($db_pass),
            'MYSQL_CHARSET' => 'utf8',
        ];

        try {
            $dsn = 'mysql:host=' . $config['MYSQL_HOST'] . ';port=' . $config['MYSQL_PORT'];
            $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $config['MYSQL_CHARSET'] . '\'');
            // 有具体数据库时采用长连接
            if (!empty($db_name)) {
                $dsn .= ';dbname=' . trim($db_name);
                $options[PDO::ATTR_PERSISTENT] = true;
            }
            $pdo = new PDO($dsn, $config['MYSQL_USER'], $config['MYSQL_PASS'], $options);
        } catch (PDOException $e) {
            return false;
        }

        return $pdo;
    }

    /**
     * 导入sql文件
     * @param $db
     * @param string $prefix
     * @param string $file
     */
    private function importSql($db, $prefix = '', $file)
    {
        //读取SQL文件
        $sql = file_get_contents(APP_DIR . '/data/' . $file);
        $sql = str_replace(["\r\n", "\n\r", "\r"], "\n", $sql);
        $sql = explode(";\n", $sql);

        //替换表前缀
        $prefix = trim($prefix);
        $sql = str_replace(" `dsc_", " `{$prefix}", $sql);

        // 开始安装数据库
        foreach ($sql as $value) {
            $value = trim($value);
            if (empty($value)) {
                continue;
            }

            if (substr($value, 0, 12) == 'CREATE TABLE') {
                $name = preg_replace("/^CREATE TABLE .*`(\w+)` .*/s", "\\1", $value);
                if (false === $db->exec($value)) {
                    die(json_encode(['status' => 'n', 'info' => '创建数据表[' . $name . ']失败']));
                }
            } else {
                $db->exec($value);
            }
        }
    }

    /**
     * 生成配置文件
     * @param $config
     * @return bool
     */
    private function writeConf($config)
    {
        if (is_array($config)) {
            //读取配置内容
            $conf = file_get_contents(BASE_PATH . '/.env.example');
            //替换配置项
            foreach ($config as $k => $v) {
                $conf = str_replace('[' . $k . ']', $v, $conf);
            }
            //写入应用配置文件
            if (file_put_contents(BASE_PATH . '/.env', $conf)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 创建超级管理员账号
     * @param $db
     * @param $prefix
     * @param $admin
     */
    private function register_administrator($db, $prefix, $admin)
    {
        $password = md5($admin['password']);

        $sql = "SELECT `user_id` FROM `{$prefix}admin_user` WHERE `user_name` = '{$admin['username']}' LIMIT 1";
        $result = $db->query($sql);
        if ($result->rowCount() > 0) {
            $sql = "UPDATE `{$prefix}admin_user` SET `user_name` = '{$admin['username']}', `email` = '{$admin['email']}', `password` = '{$password}', `ec_salt` = '' WHERE `user_name` = '{$admin['username']}'";
            $db->exec($sql);
        } else {
            $sql = "INSERT INTO `{$prefix}admin_user` (`user_id`, `user_name`, `parent_id`, `ru_id`, `rs_id`, `email`, `password`, `ec_salt`, `add_time`, `last_login`, `last_ip`, `action_list`, `nav_list`, `lang_type`, `agency_id`, `suppliers_id`, `todolist`, `role_id`, `major_brand`, `admin_user_img`, `recently_cat`) VALUES ('1', '{$admin['username']}', '0', '0', '0', '{$admin['email']}', '{$password}', '', '0', '0', '127.0.0.1', 'all', '', '', '0', '0', NULL, '0', '0', '', '');";
            $db->exec($sql);
        }
    }
}
