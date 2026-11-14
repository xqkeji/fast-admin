# fast-admin
一个基于新齐低代码开发框架(xqkeji)的后台管理系统，默认只安装xq-app-admin模块，该模块主要用于系统管理，包括管理员管理、角色管理、修改密码、退出登录、系统配置等基本功能。

## 运行环境

fast-admin 依赖新齐低代码开发框架（xqkeji）的 PHP 扩展，并需要 MySQL 与 MongoDB。运行环境可按需三选一：

| 方式 | 适用平台 | 说明 | 仓库 |
| --- | --- | --- | --- |
| **wnmmp** | Windows | nginx + MySQL + MongoDB + PHP 绿色集成包，免安装、解压即用 | [xqkeji/wnmmp](https://github.com/xqkeji/wnmmp) |
| **dnmmp** | Linux（也支持 WSL2、macOS、Windows + Docker Desktop） | Docker 集成环境，一条脚本自动安装 Docker、拉取镜像并启动 nginx、mysql、mongodb、xqkeji(PHP) 四个容器 | [xqkeji/dnmmp](https://github.com/xqkeji/dnmmp) |
| **php-xqkeji** | 任意系统 | 已有 PHP + MySQL + MongoDB 环境时，只自行安装框架的 PHP 扩展（提供 .so 与 .dll） | [xqkeji/php-xqkeji](https://github.com/xqkeji/php-xqkeji) |

### 一、Windows：wnmmp 绿色集成包

1、下载 [wnmmp](https://github.com/xqkeji/wnmmp)，解压到路径不含中文与空格的目录（例如 `D:\wnmmp`）。

2、运行 `install.bat` 完成初始化，该脚本会自动处理 Visual C++ 运行库等依赖；初始化后 `php.bat`、`composer.bat` 已默认写入系统环境变量，在任意目录打开命令提示符即可直接使用 `php`、`composer` 命令。

3、日常启动与停止：`start.bat` 启动服务，`stop.bat` 停止服务。需要开机自启时，用 `install_service.bat` 注册为 Windows 服务，`uninstall_service.bat` 卸载。

4、站点目录为 `www\default\`，项目创建到该目录下即可（见下方“创建项目”）。

5、常用配置文件：

- `etc\nginx\nginx.conf`：nginx 主配置
- `etc\php\php.ini`、`etc\php\php-cgi.conf`：PHP 与多进程 CGI 配置
- `etc\mysql\my.ini`、`etc\mongodb\mongo.conf`：数据库配置
- `etc\nginx\ssl.conf`、`bin\make-ssl.bat`：HTTPS 证书配置与生成
- `etc\nginx\php_no_exec.conf`：禁止指定目录执行 PHP 的安全规则

6、MySQL 默认账号 `root`，密码 `xqkeji.cn`，创建项目时按提示填写。

### 二、Linux：dnmmp（Docker 集成环境）

1、下载 [dnmmp](https://github.com/xqkeji/dnmmp) 并解压，移动到磁盘空间充足、路径不含中文的目录。

2、按系统执行对应脚本：

- Ubuntu / 其他 Linux：`./ubuntu.sh`
- WSL2（Windows 的 Linux 子系统）：进入子系统后 `cd /mnt/d/dnmmp`，再执行 `./wsl.sh`
- MacOS：`./mac.sh`
- Windows（Docker Desktop）：`win.bat`

WSL2 方式需要 CPU 已开启虚拟化（任务管理器“性能-CPU”显示“虚拟化：已启用”），并将 WSL 默认版本设为 2（`wsl --set-default-version 2`）。

3、脚本会自动拉取镜像并启动四个容器，端口与容器内网地址如下：

| 容器 | 对外端口 | 容器内网 IP |
| --- | --- | --- |
| nginx | 80 | — |
| mysql | 3306 | 172.0.0.200 |
| mongo | 27017 | 172.0.0.100 |
| xqkeji（PHP / php-fpm） | 9000 | 172.0.0.10 |

dnmmp 根目录下的 `www` 已挂载进容器（容器内为 `/home/www`），代码放在这里即可与宿主机共享；容器设置为跟随 Docker 自动启动，用 `docker ps` 可确认四个容器状态。启动失败多为端口被占用，关闭占用程序后重启相应容器即可。

### 三、自行安装框架 PHP 扩展

服务器已有 nginx/Apache + php-fpm + MySQL + MongoDB 时，只需安装框架扩展：

1、从 [php-xqkeji](https://github.com/xqkeji/php-xqkeji) 取得与本机 PHP 版本、线程模型相匹配的 `xqkeji.so`（Linux）或 `php_xqkeji.dll`（Windows）。

2、把文件放入 PHP 的扩展目录（`php -i` 查看 `extension_dir`）。

3、在 `php.ini` 中加载该扩展，并确认框架依赖的 `pdo_mysql`、`mongodb` 等扩展也已开启。

4、重启 php-fpm 或 Web 服务，用 `php -m` 确认 xqkeji 已加载。

> 该扩展为闭源程序，同样采用双重授权模式，条款见其仓库的 README 与 LICENSE.md。

## 创建项目

**Windows（wnmmp）**：composer 已在环境变量中，进入站点目录后直接创建项目：

``` bat
cd /d D:\wnmmp\www\default
composer create-project xqkeji/fast-admin app1
```

**Docker（dnmmp）**：先进入 PHP 容器，在挂载目录中创建项目：

``` shell
sudo docker exec -it xqkeji /bin/bash
cd /home/www
composer create-project xqkeji/fast-admin app1
```

执行该命令会自动下载fast-admin项目代码，并要求配置数据库信息，并自动安装xq-app-admin模块。
**【app1为项目名称，可以根据实际情况修改。】**
项目创建完成后，可以进入项目目录，继续安装其他模块。
例如：
``` shell
cd app1
composer require xqkeji/xq-app-content
```
执行后将安装内容管理模块。

完成程序模块安装后，容器方式下执行命令：
``` shell
exit
```
退出xqkeji容器。

## 配置nginx虚拟主机
### 1、添加一个本地的域名解析
在window操作系统下，打开C:\Windows\System32\drivers\etc\hosts文件，在文件底部增加一行内容：
``` shell
127.0.0.1 app1.xqkeji.cn
```
作用是在你的本地电脑添加一个域名解析，后面我们可以通过这个域名访问上面创建的项目。

Linux 服务器请直接配置 DNS 解析或同样修改 hosts。

### 2、添加一个nginx虚拟主机
在nginx的配置目录（dnmmp 为 `etc/nginx/conf.d/`，wnmmp 为 `etc\nginx\conf.d\`）增加一个app1.conf的虚拟主机配置文件，内容如下：
``` shell
server {
	listen       80;
	server_name  app1.xqkeji.cn;
	error_log  /home/log/app1.log;
	root    "/home/www/app1/www";
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
**【root 指向项目的 www 目录】** 容器方式为 `/home/www/app1/www`；wnmmp 改为实际的 Windows 路径，例如 `D:/wnmmp/www/default/app1/www`，并把 `fastcgi_pass` 指向本机的 php-cgi 地址（一般为 `127.0.0.1:9000`），最终请以 `etc\nginx\nginx.conf` 中默认站点的写法为准。

### 3、重启nginx服务
容器环境：
``` shell
sudo docker restart nginx
```
wnmmp：先执行 `stop.bat` 停止服务，再执行 `start.bat` 启动。

重启后，就可以通过浏览器输入地址：http://app1.xqkeji.cn/admin就可以访问到该管理后台了。
同时http://app1.xqkeji.cn/默认访问管理后台的主页面，如果要修改或取消该路由，请修改app/example/config/router.php的路由配置。

## 许可协议（双重授权）

本项目采用**双重授权**模式，您可任选其一：

1. **开源免费授权**：将本项目（含您基于本项目开发的用户代码）以
   [AGPL-3.0-only](https://www.gnu.org/licenses/agpl-3.0.html) 条款公开发布，
   即通过网络提供服务时也向用户提供相应源码，即可**免费使用**，无需购买商业授权。
2. **商业授权**：若您的代码**未**以 [OSI 认证的开源许可证](https://opensource.org/licenses) 开源
   （例如闭源商业软件、SaaS/云服务、嵌入式产品、内部工具等任意场景），
   则必须事先与授权方签署《商业许可协议》并支付费用后方可使用。

> 开源授权的完整条款以仓库根目录的 [LICENSE](./LICENSE)（AGPL-3.0 全文）为准；
> 商业授权的范围、期限与费用以双方签署的《商业许可协议》为准。

## 如何获取商业授权

如需商业授权，请联系开发者（xqkeji）洽询授权范围、期限与费用。官网：https://xqkeji.cn/

> **提示：** 本项目运行所依赖的 `php-xqkeji`（新齐低代码开发框架的 PHP 扩展）为**闭源程序**，
> 不在本项目 AGPL-3.0 授权范围内，使用它须遵循该扩展自身的许可条款（同样采用双重授权，详见其仓库 README）。