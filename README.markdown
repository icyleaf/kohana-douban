# Douban API Package Module (ONLY for Kohana v3.2)

Douban API Package 是一个使用 PHP 开发且基于 Kohana v3 开发的一个扩展（Module）。

它对[豆瓣](http://www.douban.com/) [API](http://www.douban.com/service/apidoc/) 进行重新封包。它使用起来非常方便，可以快速开发一个 web 应用。

## 为什么要另外封包？

对，豆瓣官方提供了一种 PHP 的解决方案，可是需要庞大的支持库：Zend GData（20M左右）以及可能无法修改的环境配置（租用的空间主机会遇到这样的问题）。虽说 Douban API Package 也是基于框架开发，不过 Kohana 是一个纯 PHP5 模式且体积小巧（仅有 478KB），优秀迅捷的框架。**最重要的是**，Douban API Package 提供了目前官方所有功能的支持。并有线上网站：[魔豆](http://modou.us/) 和[豆瓣 API 控制台](http://modou.us/console)长期运营。

**虽然它是基于 Kohana 框架开发，但是并没有使用太多的依赖，稍微熟悉可以轻松分离出来！**


## 支持 API 列表

目前 Douban API Package 支持豆瓣网提供 API 的全部功能，列表如下：

* People - 获取用户信息，搜索用户
* Book - 获取图书信息（subject id，ibsn）和标签，搜索图书
* Movie - 获取电影信息（subject id，imdb）和标签，搜索电影
* Music - 获取音乐信息和标签，搜索音乐
* Broadcast - 获取广播信息（自己或友邻），发布广播，回复广播，删除广播
* Collection - 获取收藏信息（条目或用户），创建收藏，删除收藏
* Doumail - 获取豆邮信息（未读邮件，收件箱，发件箱），发送豆邮，删除豆邮
* Event - 获取同城活动信息（通过用户或城市），获取同城详情（活动信息，GEO信息，参加人数详情，感兴趣人数详情），同城活动操作（参加/感兴趣/不参加），发布同城活动，更新同城活动，删除同城活动
* Note - 获取用户日记信息，发布日记，更新日记，删除日记
* Recommendation - 获取用户推荐信息，发布推荐，回应推荐，删除推荐，删除回应
* Review - 获取条目评论信息（书影音），获取用户评论信息，发布评论，更新评论，删除评论
* Album - 获取相册信息（目前只能获取同城活动的相册，偶然在 Event 的 API 字段中发现此接口）

## 已发现 API （受限）

* Host - `http://api.douban.com/host/{id}` 访问会提示 `access page limit`.


## 安装需求

* PHP 5.2.8+ with curl module
* [Kohana v3](http://github.com/kohana/kohana) - 一款纯 PHP 5 框架，它的特点就是**高安全性**，**轻量级代码**，**容易使用**。 

## 安装步骤

步骤 0: 部署 Kohana

下载并安装 Kohana 的过程，请大家参考此教程：[使用 Git 部署 Kohana 系统](http://kohanaframework.org/3.2/guide/kohana/tutorials/git)

步骤 1: 下载本扩展!

你可以在部署完毕的 Kohana 系统的根目录执行下面操作：

	$ git submodule add git://github.com/icyleaf/douban.git modules/douban

完成！

或者你也可以从本 [github 项目](http://github.com/icyleaf/douban)下载压缩包放置在 modules 文件夹下面。

步骤 2: 在 `bootstrap.php` 文件中启用该模块(默认情况下，存储在 `application' 文件夹)

	/**
	 * Enable modules. Modules are referenced by a relative or absolute path.
	 */
	Kohana::modules(array(
	  'douban'        => MODPATH.'douban',     // Douban API Module
		// 'database'   => MODPATH.'database',   // Database access
		// 'image'      => MODPATH.'image',      // Image manipulation
		// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping (not complete)
		// 'pagination' => MODPATH.'pagination', // Paging of results
		// 'paypal'     => MODPATH.'paypal',     // PayPal integration (not complete)
		// 'todoist'    => MODPATH.'todoist',    // Todoist integration
		// 'unittest'   => MODPATH.'unittest',   // Unit testing
		// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
		));

## 目录结构
	
	douban
	  +--- classes
	         +--- controller               # 豆瓣样例
	         +--- douban
		            +--- api               # Douban API
						  +--- book.php
						  +--- .
						  +--- .
						  +--- .
					+--- core.php          # Douban Core
					+--- oauth.php         # Douban OAuth
					+--- request.php       # Douban Requset 
					+--- response.php      # Douban Response
	         +--- douban.php               # Douban API Class
	  +--- config
	         +--- douban.php               # 豆瓣 API 配置文件
			 +--- user_agents.php          # 奉送给大家一些手机 User agent 配置文件
	  +--- media
	         +--- images                   # 奉送给大家豆瓣 API 用到的图像		
	  +--- vendor
	         +--- OAuth.php                # OAuth 官方推荐 PHP 库
	  +--- LICENSE
	  +--- README.markdown
	
## 快速上手

* Douban API Package 提供一些演示用例，系统部署完毕后可以通过 `http://host/demo_douban` 访问。 

> 文件路径：`classes/controller/demo_douban.php`


## 祝你开发顺利！

如果任何疑问或者 Bugs 反馈，即可以在本项目中提交 Issue 或者给我发邮件：`icyleaf.cn@gmail.com`

12/4/2011
