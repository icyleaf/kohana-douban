<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noodp, noydir" />
	<meta name="description" content="Douban API Console" />
	<?php echo $header; ?>
</head>
<body class="dev_site_page">
	<input type="hidden" id="post_form_id" name="post_form_id" value="<?php echo md5(rand(100,999)); ?>" />
	<div id="header_bar">
		<div id="header_logo">
			<a href="<?php echo url::site('douban_console'); ?>"><h1>豆瓣 API 测试控制台</h1></a>
		</div>
		
		<div id="devsite_menubar" class="clearfix">
			<div class="center_box">
				<div id="devsite_menubar_core">
					<ul>
						<li class="fb_menu">
							<div class="fb_menu_title">
								<a href="<?php echo URL::site('douban_console'); ?>">豆瓣 API 测试控制器</a>
							</div>
						</li>
						<li class="fb_menu">
							<div class="fb_menu_title">
								<a href="http://www.douban.com/service/apidoc/" target="_blank">豆瓣API参考文档</a>
							</div>
						</li>
						<li class="fb_menu" id="fb_menu_community">
							<div class="fb_menu_title">
								<a href="http://www.douban.com/group/dbapi/" target="_blank">豆瓣API小组</a>
							</div>

						</li>
						<li class="fb_menu" id="fb_menu_resources">
							<div class="fb_menu_title">
								<a href="http://www.douban.com/service/apikey/" target="_blank">申请API Key</a>
							</div>
						</li>
						<li class="fb_menu">
							<div class="fb_menu_title">
								<a href="<?php echo URL::site('douban_console/about'); ?>">关于</a>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="middle-container clearfix">
		<?php if ($action == 'home'): ?>
			<div class="content_header clearfix"><h2>豆瓣 API 测试控制台</h2></div>
			<div class="content clearfix">
				<div id="tools_content" class="clearfix">
					<p>
						这是一个在线的豆瓣 API 测试控制台，你可以在下列左侧选择函和返回格式，
						点击“调用此方法”。在右侧就会显示请求的地址及参数信息和返回的结果。
					</p>
					<?php if ( ! $people): ?>
					<div id="standard_status" class="status">
						<h2>
							<span id="status_title">
								<div id="warning_text">用户必须登录验证后才能使用控制台。 
									<?php echo HTML::anchor('douban_console/auth', '登录验证'); ?>
								</div>
							</span>
						</h2>
					</div>
					<?php endif; ?>
					<div id="warn_user" class="hide_warning"></div>
					<div class="form_bg clearfix">
						<div class="controls">
							<label>用户 ID</label>
							<input type="text" class="inputtext disabled" id="user_id" 
								readonly="readonly" value="尚未登录" />
							<div class="control" id="response_format">
								<label>返回格式</label>
								<select class="select" id="format" name="format">
									<option value="atom">XML</option>
									<option value="json">JSON</option>
								</select>
							</div>
							<div class="control">
								<label id="doc_url">方法</label>
								<select class="select" id="method" name="method">
									<option value="none">加载中...</option>
								</select>
							</div>
							<div id="arg_0" style="display: none;" class="control">
								<label id="label_0"></label>
								<input type="text" class="inputtext" id="val_0" />
								<textarea class="textarea" style="width: 172px; height: 56px; display: none;" id="val_fql"></textarea>
							</div>
							<div id="arg_1" style="display: none;" class="control">
								<label id="label_1"></label>
								<input type="text" class="inputtext" id="val_1" />
							</div>
							<div id="arg_2" style="display: none;" class="control">
								<label id="label_2"></label>
								<input type="text" class="inputtext" id="val_2" />
							</div>
							<div id="arg_3" style="display: none;" class="control">
								<label id="label_3"></label>
								<input type="text" class="inputtext" id="val_3" />
							</div>
							<div id="arg_4" style="display: none;" class="control">
								<label id="label_4"></label>
								<input type="text" class="inputtext" id="val_4" />
							</div>
							<div id="arg_5" style="display: none;" class="control">
								<label id="label_5"></label>
								<input type="text" class="inputtext" id="val_5" />
							</div>
							<div id="arg_6" style="display: none;" class="control">
								<label id="label_6"></label>
								<input type="text" class="inputtext" id="val_6" />
							</div>
							<div id="arg_7" style="display: none;" class="control">
								<label id="label_7"></label>
								<input type="text" class="inputtext" id="val_7" />
							</div>
							<div id="arg_8" style="display: none;" class="control">
								<label id="label_8"></label>
								<input type="text" class="inputtext" id="val_8" />
							</div>
							<div id="submit">
								<input type="submit" class="inputsubmit"
									id="method_submit" name="method_submit"
									value="调用此方法" />
							</div>
							
							<div class="notice">
								如果长时间处于“调用中”，可能是由于程序问题，请刷新后重试。<br /><br />
								如果还再出现此问题，请<a href="mailto:icyleaf.cn@gmail.com">联系我</a>。<br />
                                Twitter: @<a href="http://twitter.com/icyleaf">icyleaf</a><br />
                                新浪微博: @<a href="http://weibo.com/icyleaf">icyleaf</a>
							</div>
						</div>
						<div id="query_url">请求的地址及参数在这里显示。</div>
						<div id="trace">返回的结果在这里显示。</div>
					</div>
				</div>
			</div>
		<?php elseif($action=='about'): ?>
			<div class="content_header clearfix"><h2>关于</h2></div>
			<div class="content clearfix">
				<div id="tools_content" class="clearfix">
					<p>
						豆瓣 API 测试控制台的创作来源于 Facebook Developers Tools 中的 Facebook API Console，
						由于自己在使用 PHP 重新封装 Douban API，每次想查看某个 API 接口返回的数据都要写一个 test 跑一下。
						麻烦不说，还容易出错，于是，使用自己封装的 Douban API，复制 Facebook API Console 
						的界面和类似的功能显示，终于小有所成，其中为了实现此控制台，封包的库类经过两次大改，虽然改的很辛苦，
						但从中学到了很多开发经验。
					</p>

					<p>
						本工具经测试可用于大多数主流浏览器(Firefox/IE/Chrome)。
					</p>
					
					<p>
						最后更新: 12/04/2011.
					</p>
				</div>
			</div>
		<?php elseif($action == 'error'): ?>
			<div class="content_header clearfix"><h2>错误</h2></div>
			<div class="content clearfix">
				<div id="tools_content" class="clearfix">
					<p>
						抱歉，目前由于访问者请求次数过多，超出豆瓣每分钟请求限制，请过 30 分钟后重新尝试。
					</p>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<div id="footer">
		<div class="footer_border_bottom clearfix">
			<div class="copyright">
				<a href="http://icyleaf.com">icyleaf</a> 制造
			</div>
			<div class="info">
				界面设计参考 <a href="http://developers.facebook.com/tools.php">Facebook Developers</a>
			</div>
		</div>
	</div>
<script type="text/javascript">
$(document).ready(function(){
	var base = '<?php echo URL::base(); ?>';
	var method_file = base+'douban_console/json/methods';
	var logged_id = <?php echo $people ? '1' : '0'; ?>;
	var uid = '<?php echo $people ? $people->id : ''; ?>';

	db_api_console.init(logged_id, uid, method_file);
});
</script>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-2570916-14");
pageTracker._trackPageview();
} catch(err) {}
</script>
</body>
</html>
