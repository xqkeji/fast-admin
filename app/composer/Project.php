<?php
namespace xqkeji\app\composer;
use xqkeji\helper\Str;

class Project
{
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
        $configPath=self::getRootConfigPath();
        $containerFile=$configPath.DIRECTORY_SEPARATOR.'container.php';
        if(is_dir($configPath))
        {
            //文件不存在，则创建
            if(!is_file($containerFile))
            {
                $hostname = readline("请输入数据库服务器地址[默认为：172.172.172.100]\n");
                if(trim($hostname)=='')
                {
                    $hostname='172.172.172.100';
                }
                $database = readline("请输入数据库名称[默认为：xqkeji_db]\n");
                if(trim($database)=='')
                {
                    $database='xqkeji_db';
                }
                $username = readline("请输入数据库用户名[默认为：空]\n");
                if(trim($username)=='')
                {
                    $username='';
                }
                $password = readline("请输入数据库密码[默认为：空]\n");
                if(trim($password)=='')
                {
                    $password='';
                }
                $key=Str::random(Str::RANDOM_ALNUM,24);
                $config=[
                    'db'  => [
                        "hostname"		=>	$hostname,
                        "database"		=>	$database,
                        "username" 		=> $username,
                        "password"      => $password,
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
    }
    
}