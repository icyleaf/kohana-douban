/**
 * Douban API Console
 *
 * @author icyleaf <icyleaf.cn@gmail.com>
 * @version 0.2
 */
var db_api_console = {
	json_url: null,

	/**
	 * Init
	 */
	init: function(logged_in, uid, json_url)
	{
		this.json_url = json_url;
		
		if (logged_in == 0)
		{
			// not logged in
			$('#user_id').attr('disabled', true).val('尚未登录');
			$('#format').attr('disabled', true);
			$('#method').attr('disabled', true);
			$('#method_submit').attr('disabled', true);
		}
		else
		{
			// logged in
			$('#user_id').val(uid);
		}

		// Load api methods
		db_api_console.load_methods();
		// Add method change linstener
		db_api_console.methods_listener();
		// Added call api linstener
		db_api_console.call_api_listener();
	},

	/**
	 * Load all method of douban api from json file
	 */
	load_methods: function()
	{
		$.getJSON(this.json_url, function(json){
			var output = '<option value="none">请选择</option>';
//			console.log(json);
			$.each(json, function(className, methods){
//				console.log(methods);
				$.each(methods, function(method, value){
//					console.log(method, args);
						output += '<option ref="' + value + '" value="' +
							value.length + '">' + className + '.' + method + '</option>';
				});
			});
			
			$('#method').empty().html(output);
		});
	},

	/**
	 * Added Calling Douban API Listener
	 */
	call_api_listener: function()
	{
		$('#method_submit').bind('click', function(){
			var value =  $('select#method').find('option:selected').val();
			if (value == 'none')
			{
				alert('请选择你要调用的 API 接口');
				return;
			}

			var method = $('select#method').find('option:selected').text().split('.');
			var args =  $('select#method').find('option:selected').attr('label').split(',');
			var className = method[0];
			var methodName = method[1];
			var alt = $('select#format').find('option:selected').val();
			var url = '/douban_console_'+className+'/'+methodName+'/';
            var data = '';

			if (value > 0)
			{
				for (var i = 0;i < value; i++)
				{
					var param_name = $('#label_' + i).html();
					var param_value = $('#val_' + i).val();

					if (param_value == '')
						continue;

					data += param_name + '=' + param_value + '&';
				}
			}

            data += 'alt=' + alt;

			$('#method_submit').addClass('calling').attr('disabled', true).val("调用中...");
			$.ajax({
				type: 'POST',
				url: url,
				data: data,
				success: function(result){
					result = $.trim(result);
					content = result.split('[-icyleaf-]');
					
					$('#method_submit').removeClass('calling').attr('disabled', false).val("调用此方法");
					output = '<div class="'+alt+'"><pre class="pre">'+content[1]+'</pre></div>';
					var id = ($('#label_0').text() == 'id' || $('#label_0').text()=='city')
						? $('#val_0').val() : '';
						
					$('#query_url').html(content[0].replace('%40', '@'));
					$('#trace').html(output);
				},
				error: function(result){
					var code = result.status;
					var message = result.statusText;

					output = '<h1>'+message+' ('+code+')</h1>';
					$('#trace').html(output);
					$('#method_submit').removeClass('calling').attr('disabled', false).val("调用此方法");
				}
			});
		});
	},

	/**
	 * Added Douban API Methods Listener
	 */
	methods_listener: function()
	{
		$('#method').bind('change', function(){
			db_api_console.clear_args();
			var value =  $('select#method').find("option:selected").val();
			if (value == 'none')
			{
				return;
			}

			var method = $('select#method').find("option:selected").text().split('.');
			var args =  $('select#method').find("option:selected").attr('ref').split(',');
			var className = method[0];
			var methodName = method[1];

			if (value > 0)
			{
				for (var i = 0; i < value; i++)
				{
					$('#label_'+i).text(args[i]);
					$('#arg_'+i).css('display', 'block');
				}
			}
		});
	},

	/**
	 * Clear hidden args on HTML codes
	 */
	clear_args: function()
	{
		for(var i = 0; i <= 8; i++)
		{
			$('#arg_'+i).css('display', 'none');
			$('#val_'+i).val('');
		}
	}
};