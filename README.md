# fast-admin
一个基于新齐低代码开发框架(xqkeji)的后台管理系统，默认只安装xq-app-admin模块，该模块主要用于系统管理，包括管理员管理、角色管理、修改密码、退出登录、系统配置等基本功能。

## 运行环境
该项目需要新齐低代码开发框架(xqkeji)的运行环境，目前只提供docker方式的运行环境。

在window系统中，可以运行在wsl(window系统的Linux子系统)环境下。
### 检查CPU是否开启虚拟化技术
在Window打开“任务管理器”，然后点击“性能选项卡”，再点击进入“CPU”详细页。
在CPU的信息中，有显示“虚拟化：已启用”说明CPU有开启虚拟化技术。
如果没有开启，需要重启电脑进入主板的BIOS管理程序，启用CPU的虚拟机技术。

### 开启WSL功能
右键任务栏的“开始”图标，进入“系统信息”选项页，回退到“系统”页，再点击进入“可选功能”页，然后滚动到页面底部，见到“相关设置”后，点击“更多Window功能”。
点击开启“适用于Linux的Windows子系统”选项和“虚拟机平台”再点击“确定”，系统重启后，就开启了WSL的功能。
### 检查WSL
为了避免不必要的错误，建议使用前先升级wsl,打开一个命令行窗口，并输入命令：
``` shell
wsl --update
```
更新完成后，可以查看wsl的状态。
``` shell
wsl --status
```
状态中的默认版本号需要为0
``` shell
默认版本：2
```
如果默认版本不为2，需要修改默认版本号。
``` shell
wsl --set-default-version 2
```
### 安装ubuntu应用
可以从window“开始”菜单进入“Microsoft Store"，在应用商店中搜索”ubuntu"并安装该应用。
安装完成后可以通过以下命令启动"ubuntu"应用。
``` shell
wsl -d ubuntu
```
也可以在“开始”的“应用列表”直接点击“Ubuntu”图标来进入“ubuntu"。
### 安装xqkeji运行环境
进入"ubuntu"应用后，执行以下命令： 

**【注意：执行完命令后，会在当前目录创建docker目录，并作为xqkeji的运行目录，因此执行前要确定好当前的目录】**

当前的目录一般用window的盘符，例如：/mnt/d/为D盘，/mnt/e/为E盘。

可以使用cd /mnt/d/ 命令让当前目录为window的D盘。

使用window的盘符路径可以使得linux子系统的文件与Window共享，方便开发。

``` shell
cd /mnt/d/
curl -O https://www.xqkeji.cn/docker.tar.gz
tar -xzvf docker.tar.gz
rm docker.tar.gz
cd docker
chmod -R a+w log
chmod +x install.sh
sudo ./install.sh
```

## 创建项目
通过以下命令进入xqkeji容器
``` shell
sudo docker exec -it xqkeji /bin/bash
```
在容器中执行命令
``` shell
cd /home/web
composer create-project xqkeji/fast-admin app1
```
执行该命令会自动下载fast-admin项目代码，并要求配置数据库信息，并自动安装xq-app-admin模块.
**【app1为项目名称，可以根据实际情况修改。】**
项目创建完成后，可以进入项目目录，继续安装其他模块。
例如：
``` shell
cd app1
composer require xqkeji/xq-app-content
```
执行后将安装内容管理模块。

完成程序模块安装后，执行命令：
``` shell
exit
```
退出xqkeji容器，回到linux子系统。

## 在nginx配置一个虚拟机
### 1、添加一个本地的域名解析
在window操作系统下，打开C:\Windows\System32\drivers\etc\hosts文件，在文件底部增加一行内容：
``` shell
127.0.0.1 app1.xqkeji.cn
```
作用是在你的本地电脑添加一个域名解析，后面我们可以通过这个域名访问上面创建的项目。

### 2、添加一个nginx虚拟主机
在nignx的配置目录下docker/etc/nginx/conf.d/增加一个app1.conf的虚拟主机配置文件，内容如下：
``` shell
server {
	listen       80;
	server_name  app1.xqkeji.cn;
	error_log  /home/log/app1.log;
	root    "/home/web/app1/www";
	location / {
		index  index.html index.htm index.php;
		autoindex  off;
		try_files $uri $uri/ /index.php?_url=$uri&$args;
	}
	location ~ \.php(.*)$  {
		try_files		$uri = 404;
		fastcgi_pass   172.0.0.10:9000; #php-fpm的服务地址
		fastcgi_index  index.php;
		fastcgi_split_path_info  ^((?U).+\.php)(/?.+)$;
		fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
		fastcgi_param  PATH_INFO  $fastcgi_path_info;
		fastcgi_param  PATH_TRANSLATED  $document_root$fastcgi_path_info;
		include        fastcgi_params;
	}
}
```
### 3、重启nginx服务
``` shell
sudo docker restart nginx
```
重启后，就可以通过浏览器输入地址：http://app1.xqkeji.cn/admin就可以访问到该管理后台了。
同时http://app1.xqkeji.cn/默认访问管理后台的主页面，如果要修改或取消该路由，请修改app/example/config/router.php的路由配置。