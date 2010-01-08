# Douban API Package Module

Douban API Package 是一个使用 PHP 开发且基于 Kohana v3 开发的一个扩展（Module）。

它对[豆瓣](http://www.douban.com/) [API](http://www.douban.com/service/apidoc/) 进行重新封包。它使用起来非常方便，可以快速开发一个 web 应用。

## 为什么要另外封包？

对，豆瓣官方提供了一种 PHP 的解决方案，可是需要庞大的支持库：Zend GData（20M左右）以及可能无法修改的环境配置（租用的空间主机会遇到这样的问题）。虽说 Douban API Package 也是基于框架开发，不过 Kohana 是一个纯 PHP5 模式且体积小巧（仅有 478KB），优秀迅捷的框架。**最重要的是**，Douban API Package 提供了目前官方所有功能的支持。并有线上网站：[魔豆](http://modou.us/) 和[豆瓣 API 控制台](http://modou.us/console)长期运营。

**虽然它是基于 Kohana 框架开发，但是并没有使用太多的依赖，稍微熟悉可以轻松分离出来！**

## 安装需求

* PHP 5.2+ with curl module
* [Kohana v3](http://github.com/kohana/kohana) - 一款纯 PHP 5 框架，它的特点就是**高安全性**，**轻量级代码**，**容易使用**。 

## 安装步骤

步骤 0: 部署 Kohana v3

下载并安装 Kohana v3 的过程，请大家参考此教程：[使用 Git 部署 Kohana 系统](http://kohanaphp.cn/guide/tutorials.git)

步骤 1: 下载本扩展!

你可以在部署完毕的 Kohana 系统的根目录执行下面操作：

	$ git submodule add git://github.com/icyleaf/douban.git modules/douban
	
完成！

或者你也可以从本[github 项目](http://github.com/icyleaf/douban) 下载压缩包放置在 modules 文件夹下面。

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

> 文件路径：`classes/controller/douban_demo.php`


## 祝你开发顺利！

如果任何疑问或者 Bugs 反馈，即可以在本项目中提交 Issue 或者给我发邮件：`icyleaf.cn@gmail.com`

01/07/2009