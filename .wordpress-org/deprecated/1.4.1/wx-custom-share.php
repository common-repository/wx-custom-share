<?php     
/*
Plugin Name: WX Custom Share
Plugin URI: http://www.qwqoffice.com/article-20.html
Description: Customize infomation in share link, support Wechat and QQ.
Version: 1.4.1
Author: QwqOffice
Author URI: http://www.qwqoffice.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wx-custom-share
Domain Path: /languages
*/

//禁止直接访问
if ( ! defined( 'ABSPATH' ) ) exit;

//获取标题
function wxcs_get_title($post, $meta_info){
	$meta_title = $meta_info['ws_title'];
	$default_title = $post->post_title .' - '. get_bloginfo('name');
	
	$info['display'] = $meta_title != '' ? $meta_title : $default_title;
	$info['meta'] = $meta_title;
	$info['default'] = $default_title;
	return $info;
}

//获取描述
function wxcs_get_desc($post, $meta_info){
	$meta_desc = $meta_info['ws_desc'];
	$default_desc = get_permalink($post);
	
	$info['display'] = $meta_desc != '' ? $meta_desc : $default_desc;
	$info['meta'] = $meta_desc;
	$info['default'] = $default_desc;
	return $info;
}

//获取URL
function wxcs_get_url($psot, $meta_info){
	$meta_url = $meta_info['ws_url'];
	$default_url = get_permalink($post);
	
	$info['display'] = $meta_url != '' ? $meta_url : $default_url;
	$info['meta'] = $meta_url;
	$info['default'] = $default_url;
	return $info;
}

//获取小图标
function wxcs_get_img($post, $meta_info){
	$meta_img = $meta_info['ws_img'];
	$default_img = get_the_post_thumbnail_url($post);
	
	$info['display'] = $meta_img != '' ? $meta_img : ($default_img ? $default_img : '');
	$info['meta'] = $meta_img;
	$info['default'] = $default_img;
	return $info;
}

//输出错误
function wxcs_print_error($result){
	$ws_settings = get_option('ws_settings');
	if( isset($ws_settings['ws_debug']) ){
		echo "<script>console.error(eval('(' + '". json_encode($result) ."' + ')'))</script>";
	}
}

//获取Access Token
function wxcs_get_access_token(){
	if( ($access_token = get_option('ws_access_token')) !== false && time() < $access_token['expire_time']){
		return $access_token['access_token'];
	}
	
	$ws_settings = get_option('ws_settings');
	$api_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='. $ws_settings['ws_appid'] .'&secret='. $ws_settings['ws_appsecret'];
	$result = json_decode(file_get_contents($api_url));
	if( isset($result->errcode) && $result->errcode != 0 ){
		wxcs_print_error($result);
		return false;
	}
	
	$access_token['access_token'] = $result->access_token;
	$access_token['expire_time'] = time() + intval( $result->expires_in );
	update_option( 'ws_access_token', $access_token );
	
	return $access_token['access_token'];
}

//获取JSAPI TICKET
function wxcs_get_jsapi_ticket(){
	if( ($jsapi_ticket = get_option('ws_jsapi_ticket')) !== false && time() < $jsapi_ticket['expire_time']){
		return $jsapi_ticket['jsapi_ticket'];
	}
	
	$ws_settings = get_option('ws_settings');
	if( ($access_token = wxcs_get_access_token()) === false ) return false;
	$api_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='. $access_token .'&type=jsapi';
	$result = json_decode(file_get_contents($api_url));
	if( isset($result->errcode) && $result->errcode != 0 ){
		wxcs_print_error($result);
		return false;
	}
	
	$jsapi_ticket['jsapi_ticket'] = $result->ticket;
	$jsapi_ticket['expire_time'] = time() + intval( $result->expires_in );
	update_option( 'ws_jsapi_ticket', $jsapi_ticket );
	
	return $jsapi_ticket['jsapi_ticket'];
}

//生成随机字符串
function wxcs_generate_noncestr( $length = 16 ){
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	for( $i = 0; $i < $length; $i++ ){
		$noncestr .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	return $noncestr;
}

//获取#前面的完整URL
function wxcs_get_signature_url(){
	$protocol = is_ssl() ? 'https://' : 'http://';
	$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$url = explode('#', $url);
	$url = $url[0];
	
	return $url;
}

//生成签名
function wxcs_generate_signature($jsapi_ticket, $noncestr, $timestamp, $url){
	$str = 'jsapi_ticket='. $jsapi_ticket .'&noncestr='. $noncestr .'&timestamp='. $timestamp .'&url='. $url;
	return sha1($str);
}

//获取所有自定义类型
function wxcs_get_all_post_types(){
	$args = array('public' => true, '_builtin' => false);
	$builtInArgs = array('public' => true, '_builtin' => true);
	$output = 'objects';
	$operator = 'and';
	
	$builtin_post_types = get_post_types( $builtInArgs, $output, $operator );
	$custom_post_types = get_post_types( $args, $output, $operator );
	$all_post_type = array_merge( $builtin_post_types, $custom_post_types );
	foreach( $all_post_type as $type ){
		$types[ $type->name ] = $type->label;
	}
	return $types;
}

//API配置通知
add_action( 'admin_notices', 'wxcs_api_config_notice' );
function wxcs_api_config_notice(){
	$current_screen = get_current_screen();
	$ws_settings = get_option('ws_settings');
	if( ($ws_settings['ws_appid'] == '' || $ws_settings['ws_appsecret'] == '') &&
		(
		//$current_screen -> base != 'edit' && array_key_exists($current_screen->post_type, $ws_settings['ws_display_types']) ||
		$current_screen -> id == 'settings_page_wxcs-settings'
		) ){
?>
		<div class="notice notice-warning">
			<p>
			<?php
			printf(
				__( 'If you want to share link in Wechat directly, please <strong>enter AppID and AppSecret</strong>, and <strong>add <code>%s</code> to JSAPI Secure Domain</strong> on WeChat Admin Platform.', 'wx-custom-share' ),
				$_SERVER['HTTP_HOST']
			)
			?>
			<br>
			<?php _e( 'Otherwise you must share link to Wechat via QQ to custom share info, or share in QQ directly.', 'wx-custom-share' ) ?>
			</p>
		</div>
<?php
	}
}

//设置按钮
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wxcs_add_action_links');
function wxcs_add_action_links( $links ) {
	$mylinks = array(
		'<a href="' . admin_url('options-general.php?page=wxcs-settings') . '">'. __('Settings','wx-custom-share'). '</a>'
	);
	return array_merge( $mylinks, $links );
}

//设置菜单
if( is_admin() ) add_action( 'admin_menu', 'wxcs_menu' );
function wxcs_menu(){
	//页名称，菜单名称，访问级别，菜单别名，点击该菜单时的回调函数（用以显示设置页面）
	add_options_page( __('Wechat Share Settings','wx-custom-share'), __('Wechat Share','wx-custom-share'), 'administrator', 'wxcs-settings', 'wxcs_html_page' );
}

//后台设置页面
function wxcs_html_page(){
	$ws_settings = get_option('ws_settings');
?>
    <div class="wrap">
    	<h2><?php _e('Wechat Share Settings','wx-custom-share') ?></h2>
		
        <form method="post" action="options.php">
            <?php wp_nonce_field('update-options');?>
        	<table class="form-table">
            <tr><th><label for="ws_settings[ws_appid]"><?php _e('Wechat AppID','wx-custom-share') ?></label></th>
			<td>
                <p>
                	<input type="text" id="ws_settings[ws_appid]" name="ws_settings[ws_appid]" value="<?php echo $ws_settings['ws_appid'] ?>" class="regular-text" autocomplete="off">
                </p>
            </td></tr>
            <tr><th><label for="ws_settings[ws_appsecret]"><?php _e('Wechat AppSecret','wx-custom-share') ?></label></th>
			<td>
                <p>
                	<input type="text" id="ws_settings[ws_appsecret]" name="ws_settings[ws_appsecret]" value="<?php echo $ws_settings['ws_appsecret'] ?>" class="regular-text" autocomplete="off">
                </p>
            </td></tr>
            <tr><th><?php _e('Post types to custom','wx-custom-share') ?></th>
			<td>
            
            <?php foreach( wxcs_get_all_post_types() as $k => $v ): ?>
                <p>
                	<input type="checkbox" id="ws_settings[ws_display_types][<?php echo $k ?>]" name="ws_settings[ws_display_types][<?php echo $k ?>]" <?php checked(isset($ws_settings['ws_display_types'][$k])) ?>>
                    <label for="ws_settings[ws_display_types][<?php echo $k ?>]"><?php echo $v ?> (<?php echo $k ?>)</label>
                </p>
            <?php endforeach; ?>
            </td></tr>
            <tr><th><?php _e('Other','wx-custom-share') ?></th>
            <td>
            	<p>
                	<input type="checkbox" id="ws_settings[ws_del_data]" name="ws_settings[ws_del_data]" <?php checked(isset($ws_settings['ws_del_data'])) ?>>
                    <label for="ws_settings[ws_del_data]"><?php _e('Clear plugin data when uninstall','wx-custom-share') ?></label>
                </p>
            	<p>
                	<input type="checkbox" id="ws_settings[ws_debug]" name="ws_settings[ws_debug]" <?php checked(isset($ws_settings['ws_debug'])) ?>>
                    <label for="ws_settings[ws_debug]"><?php _e('Debug mode','wx-custom-share') ?></label>
					<p class="description"><?php _e('Error will be print to console','wx-custom-share') ?></p>
                </p>
            </td>
            </tr>
            </table>
            <p class="submit">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="page_options" value="ws_settings">
                <input type="submit" value="<?php _e('Save','wx-custom-share') ?>" class="button-primary">
            </p>
        </form>
    </div>
<?php   
}

//添加图片meta box
if( is_admin() ) add_action( 'add_meta_boxes', 'wxcs_add_box' );
function wxcs_add_box(){
	$ws_meta_box = array(
		'id' => 'ws-meta-box',
		'title' => __('Wechat Share','wx-custom-share'),
		'context' => 'normal',
		'priority' => 'low'
	);
	$ws_settings = get_option('ws_settings');
	if( '' !== $ws_settings['ws_display_types'] && array_key_exists( get_post_type(), $ws_settings['ws_display_types'] ) ){
		add_meta_box( $ws_meta_box['id'], $ws_meta_box['title'], 'wxcs_show_box', get_post_type(), $ws_meta_box['context'], $ws_meta_box['priority'] );
	}
}
function wxcs_show_box() {
    global $post;
	
	$meta_info = get_post_meta( $post->ID, 'ws_info', true );
	$pngdata = 'data:image/png;base64,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';
	
	$title = wxcs_get_title($post, $meta_info);
	$desc = wxcs_get_desc($post, $meta_info);
	$url = wxcs_get_url($post, $meta_info);
	$img = wxcs_get_img($post, $meta_info);
?>
	
    <input type="hidden" name="ws_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>">
    <table class="form-table">
        <tr>
        	<th style="width:20%"><label for="ws-title"><?php _e('Title','wx-custom-share') ?></label></th>
            <td>
            	<input type="text" name="ws-title" id="ws-title" value="<?php echo $title['meta'] ?>" style="width:80%" autocomplete="off" placeholder="<?php echo $title['default'] ?>">
                <p class="description" style="font-size:12px">
                    <?php _e('Default is {post title} - {site name}.','wx-custom-share') ?>
                </p>
            </td>
        </tr>
        <tr>
        	<th style="width:20%"><label for="ws-desc"><?php _e('Description','wx-custom-share') ?></label></th>
            <td>
            	<input type="text" name="ws-desc" id="ws-desc" value="<?php echo $desc['meta'] ?>" style="width:80%" autocomplete="off" placeholder="<?php echo $desc['default'] ?>">
                <p class="description" style="font-size:12px">
                    <?php _e('Default is the post permalink.','wx-custom-share') ?>
                </p>
            </td>
        </tr>
        <tr>
        	<th style="width:20%"><label for="ws-url"><?php _e('Link URL','wx-custom-share') ?></label></th>
            <td>
            	<input type="text" name="ws-url" id="ws-url" value="<?php echo esc_url($url['meta']) ?>" style="width:80%" autocomplete="off" placeholder="<?php echo $url['default'] ?>">
                <p class="description" style="font-size:12px">
                    <?php _e('Default is the post permalink. The domain must match the page domain.','wx-custom-share') ?>
                </p>
            </td>
        </tr>
        <tr>
        	<th style="width:20%"><label for="ws-img"><?php _e('Icon','wx-custom-share') ?></label></th>
            <td>
            	<input type="text" name="ws-img" id="ws-img" value="<?php echo esc_url( $img['meta'] ) ?>" style="width:80%" autocomplete="off" placeholder="<?php echo $img['default'] ?>">
                <button type="button" id="ws_upload_btn" class="button">
					<span class="ws-media-icon dashicons dashicons-admin-media"></span>
					<?php _e('Media','wx-custom-share') ?>
				</button>
                <p class="description" style="font-size:12px">
                	<?php _e('Enter a URL, Upload or choose from media library. The image you selected should be square.','wx-custom-share') ?>
					<br>
					<?php _e('Default is the post featured image.','wx-custom-share') ?>
                </p>
            </td>
        </tr>
        <tr>
        	<th style="20%"><label><?php _e('Preview','wx-custom-share') ?></label></th>
            <td>
                <div class="ws-preview">
                	<p class="description"><?php _e('Timeline','wx-custom-share') ?></p>
                	<div class="ws-timeline clearfix">
                    	<div class="ws-timeline-left">
                        	<img width="50" height="50" style="background-color:#CCC">
                        </div>
                        <div class="ws-timeline-right">
                        	<div class="ws-timeline-name"></div>
                            <div class="ws-timeline-content"></div>
                            <a class="ws-url" href="<?php echo $url['display'] ?>" target="_blank">
                				<div class="ws-timeline-link"><table><tr>
                    			<td>
									<div class="ws-attachment">
										<div class="ws-attachment-preview">
											<div class="ws-thumbnail">
												<div class="ws-centered">
													<img class="ws-img" src="<?php echo $img['display'] != '' ? esc_url($img['display']) : $pngdata ?>" alt="<?php _e('cannot get the image','wx-custom-share') ?>">
												</div>
											</div>
										</div>
									</div>
								</td>
                    			<td><div class="ws-timeline-title-div">
                        			<span class="ws-title ws-timeline-title"><?php echo $title['display'] ?></span>
                        		</div></td>
                    			</tr></table></div>
                            </a>
                            <div class="ws-timeline-meta clearfix">
                            	<span class="ws-timeline-time" style="color:#AAAAAA;font-size:12px"><?php _e('1 min ago','wx-custom-share') ?></span>
                                <div class="ws-comment-btn">
                                	<div class="ws-comment-triangle-div"><div class="ws-comment-triangle"></div></div>
                                	<div class="ws-comment-circle"></div>
                                	<div class="ws-comment-circle"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="description"><?php _e('Chat','wx-custom-share') ?></p>
                    <div class="ws-chat clearfix">
                    	<div><img width="50" height="50" style="background-color:#CCC"></div>
                        <a class="ws-url" href="<?php echo $url['display'] ?>" target="_blank">
						<div class="ws-chat-link">
                        <div class="ws-chat-triangle-div">
                        	<div class="ws-chat-triangle"></div>
                            <div class="ws-chat-triangle-shade"></div>
                        </div>
                    	<div class="ws-chat-main">
                        	<p class="ws-title ws-chat-title"><?php echo $title['display'] ?></p>
                            <div class="ws-chat-desc-div">
                            	<div class="ws-chat-desc"><?php echo $desc['display'] ?></div>
								<div class="ws-attachment">
									<div class="ws-attachment-preview">
										<div class="ws-thumbnail">
											<div class="ws-centered">
												<img class="ws-img" src="<?php echo $img['display'] != '' ? esc_url($img['display']) : $pngdata ?>" alt="<?php _e('cannot get the image','wx-custom-share') ?>">
											</div>
										</div>
									</div>
								</div>
                            </div>
                        </div>
                        </div>
						</a>
                    </div>
                </div>
            </td>
        </tr>
    </table>
<style>
.ws-preview *{
	font-family:Microsoft YaHei;
}
.ws-preview > .description{
	font-style: normal;
}
.ws-preview{
	width:90%;
}
.ws-preview div{
	box-sizing:border-box;
}
.ws-timeline{
	background-color:#F8F8F8;
	margin-bottom:15px;
	padding:10px;
}
.ws-timeline > div{
	float:left;
}
.ws-timeline-left{
	width:50px;
}
.ws-timeline-right{
	padding-left:10px;
	width:calc(100% - 50px);
}
.ws-timeline-right > a{
	display:block;
	text-decoration:none;
}
.ws-timeline-name,
.ws-timeline-content{
	margin:7px 0;
	border-radius:8px;
	height:15px;
}
.ws-timeline-name{
	width:20%;
	background-color:#8599C1;
}
.ws-timeline-content{
	width:100%;
	background-color:#A2A2A2;
}
.ws-timeline td{
	display:table-cell;
	padding:0;
}
.ws-timeline-link{
	margin:7px 0;
	padding:4px;
	background-color:#ECECEC;
}
.ws-timeline-link:hover{
	cursor:pointer;
	background-color:#D0D0D0;
}
.ws-attachment{
	width:50px;
}
.ws-attachment-preview{
	position:relative;
}
.ws-attachment-preview:before {
	content:"";
	display:block;
	padding-top:100%;
}
.ws-thumbnail{
	overflow:hidden;
	position:absolute;
	top:0;
	right:0;
	bottom:0;
	left:0;
}
.ws-centered{
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:100%;
	-webkit-transform:translate(50%,50%);
	-ms-transform:translate(50%,50%);
	transform:translate(50%,50%);
}
.ws-timeline-link .ws-img,
.ws-chat-main .ws-img{
	position:absolute;
	top:0;
	left:0;
	max-height:100%;
	-webkit-transform:translate(-50%,-50%);
	-ms-transform:translate(-50%,-50%);
	transform:translate(-50%,-50%);
}
.ws-media-icon{
	color:#82878c;
	vertical-align:text-top;
	font:400 18px/1 dashicons;
}
.ws-timeline-title-div{
	height:50px;
	padding-left:8px;
	overflow:hidden;
}
.ws-timeline-title-div .ws-timeline-title{
	color:#000;
	line-height:50px;
	word-break:break-all;
	display:inline-block;
	vertical-align:middle;
	display:-webkit-box;
	-webkit-box-orient:vertical;
	-webkit-line-clamp:1;
}
.ws-timeline-meta{
	margin-top:15px;
}
.ws-timeline-meta .ws-timeline-time{
	display:inline-block;
	float:left;
}
.ws-timeline-meta .ws-comment-btn{
	float:right;
	position:relative;
	width:20px;
	height:16px;
	background-color:#8694B1;
}
.ws-timeline-meta .ws-comment-circle{
	width:4px;
	height:4px;
	border-radius:4px;
	background-color:#FFF;
	float:left;
	margin-top:6px;
	margin-left:4px;
}
.ws-timeline-meta .ws-comment-triangle-div{
	width:6px;
	height:100%;
	position:absolute;
	left:-6px;
	overflow:hidden;
}
.ws-timeline-meta .ws-comment-triangle{
	width:10px;
	height:10px;
	position:absolute;
	top:3px;
	left:4px;
	background-color:#8694B1;
	transform:rotate(45deg);
	-ms-transform:rotate(45deg); 	/* IE 9 */
	-moz-transform:rotate(45deg); 	/* Firefox */
	-webkit-transform:rotate(45deg); /* Safari 和 Chrome */
	-o-transform:rotate(45deg); 
}
.vertical-middle{
	height:100%;
	vertical-align:middle;
	display:inline-block;
}
.clearfix:after{
	content:" ";
	display:block;
	clear:both;
	height:0;
}

.ws-chat{
	padding:10px;
	background-color:#EBEBEB;
}
.ws-chat div{
	float:left;
}
.ws-chat > a{
	display:inline-block;
}
.ws-chat .ws-chat-main{
	background-color:#FFF;
	padding:8px;
	border:1px solid #CECECE;
	border-radius:5px;
}
.ws-chat-main .ws-chat-title{
	width:250px;
	max-height:40px;
	line-height:20px;
	overflow:hidden;
	display:-webkit-box;
	-webkit-box-orient:vertical;
	-webkit-line-clamp:2;
	word-break:break-all;
	margin-top:0;
	font-size:16px;
	color:#353535;
}
.ws-chat-link:hover .ws-chat-triangle-div .ws-chat-triangle-shade,
.ws-chat-link:hover .ws-chat-triangle-div .ws-chat-triangle,
.ws-chat-link:hover .ws-chat-main{
	cursor:pointer;
	background-color:#F7F7F7;
}
.ws-chat-main .ws-chat-desc-div{
	position:relative;
	margin-top:5px;
}
.ws-chat-desc-div .ws-chat-desc{
	width:200px;
	max-height:48px;
	line-height:16px;
	overflow:hidden;
	display:-webkit-box;
	-webkit-box-orient:vertical;
	-webkit-line-clamp:3;
	font-size:12px;
	color:#999999;
	word-break:break-all;
	padding-right:10px;
	float:left;
}
.ws-chat .ws-chat-triangle-div{
	width:10px;
	height:100%;
	position:relative;
	top:20px;
	left:1px;
	overflow:hidden;
}
.ws-chat .ws-chat-triangle-div .ws-chat-triangle{
	width:10px;
	height:10px;
	position:relative;
	top:0;
	left:5px;
	background-color:#FFF;
	border:1px solid #CECECE;
	transform:rotate(45deg);
	-ms-transform:rotate(45deg); 	/* IE 9 */
	-moz-transform:rotate(45deg); 	/* Firefox */
	-webkit-transform:rotate(45deg); /* Safari 和 Chrome */
	-o-transform:rotate(45deg); 
}
.ws-chat .ws-chat-triangle-div .ws-chat-triangle-shade{
	width:1px;
	height:10px;
	background-color:#FFF;
	position:absolute;
	top:0;
	right:0;
}
.ws-chat .ws-attachment{
	float:right;
}
.ws-chat .ws-attachment *{
	float:none;
}
</style>
<script>
	var mediaUploader;
	jQuery('#ws_upload_btn').click(function(e){
		e.preventDefault();
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}
		mediaUploader = wp.media.frames.file_frame = wp.media({
			title: '<?php _e('Choose Icon','wx-custom-share') ?>',
			button: {
				text: '<?php _e('Insert','wx-custom-share') ?>'
			}, multiple: false });
			
		mediaUploader.on('select', function(){
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			jQuery('.ws-img').attr('src', attachment.url);
			jQuery('#ws-img').val(attachment.url);
		});
		mediaUploader.open();
	});
	
	var df_title = '<?php echo $title['default'] ?>',
	    df_desc = '<?php echo $desc['default'] ?>';
		df_url = '<?php echo $url['default'] ?>',
		df_img = '<?php echo $img['default'] != '' ? esc_url($img['default']) : $pngdata ?>';
	
	function wxcsBindValue(source, target, type, attr, defaultValue){
		jQuery(source).bind('input propertychange', function(){
			if('' !== jQuery(source).val()){
				if( type == 'text' ){
					jQuery(target).text(jQuery(source).val());
				}else if( type == 'attr' ){
					jQuery(target).attr(attr, jQuery(source).val());
				}
			}else{
				if( type == 'text' ){
					jQuery(target).text(defaultValue);
				}else if( type == 'attr' ){
					jQuery(target).attr(attr, defaultValue);
				}
			}
		});
	}
	wxcsBindValue('#ws-title', '.ws-title', 'text', '', df_title);
	wxcsBindValue('#ws-desc', '.ws-chat-desc', 'text', '', df_desc);
	wxcsBindValue('#ws-url', '.ws-url', 'attr', 'href', df_url);
	wxcsBindValue('#ws-img', '.ws-img', 'attr', 'src', df_img);
</script>
<?php
}

//数据保存
add_action( 'save_post', 'wxcs_save_data' );
function wxcs_save_data($post_id){
	if( ! isset($_POST['ws_meta_box_nonce']) ){
		return $post_id;
	}
	
    //验证
    if( ! wp_verify_nonce($_POST['ws_meta_box_nonce'], basename(__FILE__)) ){
        return $post_id;
    }

    //自动保存检查
    if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
        return $post_id;
    }

    //检查权限
    if( 'page' == $_POST['post_type'] ){
        if (!current_user_can('edit_page', $post_id) ){
            return $post_id;
        }
    }
	elseif( ! current_user_can('edit_post', $post_id) ){
        return $post_id;
    }
	
	//保存
	$wxcs_url_field = ['ws-url', 'ws-img'];
	foreach( $_POST as $post_key => $post_value ){
		if( strpos( $post_key, 'ws-' ) === 0 ){
			$meta_key = str_replace( '-', '_', $post_key );
			
			$info[$meta_key] = sanitize_text_field($_POST[$post_key]);
			if( in_array( $post_key, $wxcs_url_field ) ){
				$info[$meta_key] = esc_url( $info[$meta_key] );
			}
		}
	}
	
	update_post_meta( $post_id, 'ws_info', $info );
}

//前端嵌入JS
add_action( 'wp_enqueue_scripts', 'wxcs_add_share_js' );
function wxcs_add_share_js(){
	wp_enqueue_script( 'wx-custom-share', '//qzonestyle.gtimg.cn/qzone/qzact/common/share/share.js' );
}

//前端嵌入分享代码
add_action( 'wp_footer', 'wxcs_add_share_info' );
function wxcs_add_share_info(){
	$ws_settings = get_option('ws_settings');
	if( is_singular() && '' !== $ws_settings['ws_display_types'] && array_key_exists( get_post_type(), $ws_settings['ws_display_types'] ) ){
		global $post;
		$meta_info = get_post_meta( $post->ID, 'ws_info', true );
		
		$title = wxcs_get_title($post, $meta_info);
		$desc = wxcs_get_desc($post, $meta_info);
		$url = wxcs_get_url($post, $meta_info);
		$img = wxcs_get_img($post, $meta_info);
		
		if( $ws_settings['ws_appid'] != '' && $ws_settings['ws_appsecret'] != '' && ($jsapi_ticket = wxcs_get_jsapi_ticket()) !== false ){
			$noncestr = wxcs_generate_noncestr();
			$timestamp = time();
			$signature_url = wxcs_get_signature_url();
			$signature = wxcs_generate_signature($jsapi_ticket, $noncestr, $timestamp, $signature_url);
		}
?>
		<script id="wx-custom-share-script">
		setShareInfo({
			title: '<?php echo $title['display'] ?>',
			summary: '<?php echo $desc['display'] ?>',
			pic: '<?php echo $img['display'] ?>',
			url: '<?php echo $url['display'] ?>',
<?php
			if( $ws_settings['ws_appid'] != '' && $ws_settings['ws_appsecret'] != '' && $jsapi_ticket !== false ){				
?>
			WXconfig:{
				swapTitleInWX: <?php echo json_encode(apply_filters( 'wxcs_wechat_timeline_swap_title', false )) ?>,
				appId: '<?php echo $ws_settings['ws_appid'] ?>',
				timestamp: '<?php echo $timestamp ?>',
				nonceStr: '<?php echo $noncestr ?>',
				signature: '<?php echo $signature ?>'
			}
<?php
			}
?>
		});
		</script>
<?php
	}
}

//本地化
add_action( 'init', 'wxcs_load_textdomain' );
function wxcs_load_textdomain(){
  load_plugin_textdomain( 'wx-custom-share', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

//激活插件
register_activation_hook( __FILE__,'wxcs_activation' );

//停用插件
register_deactivation_hook( __FILE__,'wxcs_deactivation' );

//删除插件
register_uninstall_hook( __FILE__,'wxcs_uninstall' );

function wxcs_activation(){
	add_option( 'ws_settings', array('ws_display_types' => array('post' => 'on', 'page' => 'on', 'attachment' => 'on')) );
}

function wxcs_deactivation(){

}

function wxcs_uninstall(){
	$ws_settings = get_option('ws_settings');
	if(isset($ws_settings['ws_del_data'])){
		global $wpdb;
		$wpdb->query( "delete from $wpdb->postmeta where meta_key = 'ws_info'" );
		delete_option('ws_settings');
		delete_option('ws_access_token');
		delete_option('ws_jsapi_ticket');
	}
}
?>