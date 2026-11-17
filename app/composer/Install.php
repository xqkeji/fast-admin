<?php
namespace xqkeji\app\composer;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Command;
class Install
{
    public static function random(int $length=24):string
    {
        $text='';
        $pool = array_merge(
            range(0, 9),
            range("a", "z"),
            range("A", "Z")
        );
        $end = count($pool) - 1;

        while(strlen($text) < $length) 
        {
            $text .= $pool[mt_rand(0, $end)];
        }

        return $text;
    }
    public static function getRootPath():string
    {
        return dirname(__DIR__,2);
    }
    public static function getRootConfigPath():string
    {
        return dirname(__DIR__,2).DIRECTORY_SEPARATOR.'config';
    }
    public static function postInstall() : void
    {
        $dirname=basename(getcwd());
        $configPath=self::getRootConfigPath();
        $containerFile=$configPath.DIRECTORY_SEPARATOR.'container.php';
        if(is_dir($configPath))
        {
            //文件不存在，则创建
            if(!is_file($containerFile))
            {
                while(true)
                {
                    $hostname = readline("请输入数据库服务器地址[默认为：localhost]\r\n");
                    if(trim($hostname)=='')
                    {
                        $hostname='localhost';
                    }
                    $hostport = readline("请输入数据库服务器的端口号[默认为：27017]\r\n");
                    if(trim($hostport)=='')
                    {
                        $hostport='27017';
                    }
                    $database = readline("请输入数据库名称[默认为：".$dirname."_db]\r\n");
                    if(trim($database)=='')
                    {
                        $database=$dirname.'_db';
                    }
                    $username = readline("请输入数据库用户名[默认为：root]\r\n");
                    if(trim($username)=='')
                    {
                        $username='root';
                    }
                    $password = readline("请输入数据库密码[默认为：xqkeji.cn]\r\n");
                    if(trim($password)=='')
                    {
                        $password='xqkeji.cn';
                    }
                    if(!empty($username))
                    {
                        $uri='mongodb://'.$username.':'.$password.'@'.$hostname.':'.$hostport;
                    }
                    else
                    {
                        $uri='mongodb://'.$hostname.':'.$hostport;
                    }
                    $manager = new Manager($uri,['serverSelectionTryOnce'=>false,'serverSelectionTimeoutMS'=>500,'connectTimeoutMS'=>500]);
                    $command = new Command(['listCollections' => 1]);
                    try {
                        echo "正在检测数据库链接信息......\r\n";
                        //能够正常读取数据库内的所有集合说明除了能正常连接还具备相应的权限（这里不表示拥有所有权限）
                        $manager->executeCommand($database, $command);
                        break;   
                    } catch(\Exception $e) {
                        echo "数据库链接信息无法连接数据库，请重新设置！\r\n";
                    }
                }
                echo "数据库链接信息检测成功！\r\n";

                $key=self::random();
                $config=[
                    'db'  => [
                        'hostname'		=> $hostname,
                        'hostport'		=> $hostport,
                        'database'		=> $database,
                        'username' 		=> $username,
                        'password'      => $password,
                    ],
                    'crypt'     =>[
                        'key'=>$key,
                    ],
                ];
                file_put_contents($containerFile,"<?php\r\n return ".var_export($config,true).';');
            }
            
        }
        else
        {
            throw new \Exception("the config directory not exists: \"$configPath\"!" , 404);
        }
        $root_path=self::getRootPath();
        $runtime_path=$root_path.DIRECTORY_SEPARATOR.'runtime';
        $session_path=$runtime_path.DIRECTORY_SEPARATOR.'session';
        $upload_path=$root_path.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'upload';
        $assets_path=$root_path.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'asset';
        chmod($runtime_path,0777);
        chmod($session_path,0777);
        chmod($upload_path,0777);
        chmod($assets_path,0777);
    }
    //站点登记脚本由环境仓库提供（wnmmp\bin\add-site.bat、dnmmp 的 win.bat / bin/add-site.sh），
    //项目本身不带；找不到就只提示，不影响 create-project。脚本自身保证幂等。
    public static function postCreate() : void
    {
        $root=self::getRootPath();
        $name=basename($root);
        if(!preg_match('/^[A-Za-z0-9][A-Za-z0-9-]*$/',$name))
        {
            echo "目录名 \"$name\" 不能用作本地域名，跳过站点登记。\r\n";
            return;
        }
        $windows=DIRECTORY_SEPARATOR==='\\';
        $dir=$root;
        //逐级向上直到文件系统根，用最近的那个环境：项目一般建在 www/ 或 www/default/ 下，
        //写死层数会漏掉更深的目录。
        while(($parent=dirname($dir))!==$dir)
        {
            $dir=$parent;
            if($windows)
            {
                $scripts=[
                    [$dir.'\\bin\\add-site.bat',[$name,$root]],
                    [$dir.'\\win.bat',['site',$name,$root]],
                ];
            }
            else
            {
                $scripts=[[$dir.'/bin/add-site.sh',[$name,$root]]];
            }
            foreach($scripts as $script)
            {
                if(is_file($script[0]))
                {
                    echo "正在登记本地站点 $name.xqkeji.dev ...\r\n";
                    self::runAddSite($script[0],$script[1],$windows);
                    return;
                }
            }
        }
        echo "未找到环境侧的站点登记脚本，跳过 nginx 配置与 hosts 登记。\r\n";
        echo "若项目是在容器里创建的，请在宿主机上执行：wnmmp 用 bin\\add-site.bat $name \"项目目录\"，dnmmp 用 win.bat site $name \"项目目录\" 或 ./bin/add-site.sh $name \"项目目录\"。\r\n";
    }
    private static function runAddSite(string $script,array $args,bool $windows):void
    {
        if($windows)
        {
            $cmd='"'.$script.'"';
            foreach($args as $arg)
            {
                $cmd.=' "'.$arg.'"';
            }
            $cmd='cmd /c "'.$cmd.'"';
        }
        else
        {
            $cmd='bash '.escapeshellarg($script);
            foreach($args as $arg)
            {
                $cmd.=' '.escapeshellarg($arg);
            }
        }
        if(!function_exists('passthru'))
        {
            echo "passthru 被禁用，请手动执行：$cmd\r\n";
            return;
        }
        passthru($cmd,$code);
        if($code!==0)
        {
            echo "[警告] 站点登记未完成（退出码 {$code}），请按上面的输出处理；项目代码已创建好，不受影响。\r\n";
        }
    }
}