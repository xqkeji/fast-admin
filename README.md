# fast-admin
一个基于新齐低代码开发框架(xqkeji)的后台管理系统，默认只安装xq-app-admin模块，该模块主要用于系统管理，包括管理员管理、角色管理、修改密码、退出登录、系统配置等基本功能。

## 运行环境
该项目需要新齐低代码开发框架(xqkeji)的运行环境，目前只提供docker方式的运行环境。

在window系统中，可以运行在wsl(window系统的Linux子系统)环境下。

### 检查WSL
win10系统高于2004版本的，默认已经支持WSL。
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
### 安装xqkeji运行环境
进入"ubuntu"应用后，执行以下命令： 
**【注意：执行完命令后，会在当前目录创建docker目录，并作为xqkeji的运行目录，因此执行前要确定好当前的目录】**
``` shell
curl -O https://www.xqkeji.cn/docker.tar.gz
tar -xzvf docker.tar.gz
rm docker.tar.gz
cd docker
chmod +x install.sh
sudo ./install.sh
```
