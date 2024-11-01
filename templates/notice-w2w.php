<?php
defined( 'ABSPATH' ) || exit;
$extensions = array(
	'w2w-scan-to-login' => '扫码登录',
	'w2w-products-filter-and-orderby' => '筛选排序',
	'w2w-products-favor' => '产品收藏',
	'w2w-advanced-address' => '收货地址',
	'w2w-advanced-coupon' => '优惠券',
	'w2w-home-page-modules' => '模块定制',
	'w2w-products-groupon' => '产品拼团',
	'w2w-wechat-cross-border-payment' => '跨境支付',
);
$url = 'https://www.qwqoffice.com/article.php?mod=view&tid=30&hmsr=wx-custom-share&hmpl=V1.6&hmcu=wp-notice&hmkw=WooCommerce%E5%BE%AE%E4%BF%A1%E5%B0%8F%E7%A8%8B%E5%BA%8F&hmci=%E6%8E%A5%E5%85%A5%E4%BD%A0%E7%9A%84WooCommerce%E5%95%86%E5%9F%8E%E5%88%B0%E5%BE%AE%E4%BF%A1%E5%B0%8F%E7%A8%8B%E5%BA%8F%EF%BC%8C%E4%B8%8E%20WooCommerce%E4%BA%A7%E5%93%81%E3%80%81%E8%AE%A2%E5%8D%95%E3%80%81%E9%85%8D%E9%80%81%E3%80%81%E4%BC%98%E6%83%A0%E5%88%B8%E7%AD%89%E5%8A%9F%E8%83%BD%E6%97%A0%E7%BC%9D%E6%95%B4%E5%90%88%EF%BC%8C%E6%9B%B4%E6%9C%89%E5%A4%9A%E7%A7%8D%E6%89%A9%E5%B1%95%E5%8A%9F%E8%83%BD%E5%8F%AF%E9%80%89';
?>
<div class="notice wxcs-notice">
	<div class="wxcs-notice-logo"><img src="<?php echo WXCS_URL . 'assets/images/logo.png' ?>"></div>
	<div class="wxcs-main">
		<div class="wxcs-preview"><img src="<?php echo WXCS_URL . 'assets/images/preview.png' ?>"></div>
		<div class="wxcs-text-button">
			<div class="wxcs-text">
				<div class="wxcs-title">接入你的<br>WooCommerce 商城<br>到微信小程序</div>
				<div class="wxcs-desc">与 WooCommerce 产品、订单、配送、优惠券<br>等功能无缝整合，更有多种扩展功能可选</div>
			</div>
			<div class="wxcs-button-container">
				<div class="wxcs-button-border">
					<a href="<?php echo $url ?>" class="wxcs-button" target="_blank">立即体验</a>
				</div>
			</div>
		</div>
	</div>
	<div class="wxcs-extensions-container">
		<div class="wxcs-extensions">
			<?php foreach( $extensions as $slug => $title ): ?>
			<div class="wxcs-extension">
				<img class="wxcs-extension-img" src="<?php echo WXCS_URL . "assets/images/$slug.png" ?>" />
				<div class="wxcs-extension-title"><?php echo $title ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<img class="wxcs-extensions-arrow" src="<?php echo WXCS_URL . 'assets/images/arrow.svg' ?>" />
	</div>
	<span class="wxcs-close dashicons dashicons-no-alt"></span>
</div>
<style>
.wxcs-notice {
	display: flex;
	display: -webkit-flex;
	height: 200px;
	box-sizing: border-box;
	position: relative;
	background-color: #ffc03f;
	padding: 0 12px;
	border: 0;
	overflow: hidden;
}
.wxcs-notice {
	-webkit-user-select:none;
	-moz-user-select:none;
	-ms-user-select:none;
	user-select:none;
}
.wxcs-notice-logo {
	width: 0;
	height: 0;
	border-top: 80px solid #fff;
	border-left: 50px solid transparent;
	border-right: 50px solid transparent;
	position: absolute;
	top: 0;
	left: -20px;
}
.wxcs-notice-logo img {
	width: 30px;
	height: 30px;
	position: absolute;
	top: -66px;
	left: 0;
	transform: translateX(-50%);
}
.wxcs-main {
	height: 100%;
	display: flex;
	display: -webkit-flex;
}
.wxcs-preview {
	width: 238px;
	position: relative;
	top: -20px;
	margin-left: 38px;
}
.wxcs-preview img {
	width: 100%;
}
.wxcs-text-button {
	display: flex;
	display: -webkit-flex;
}
.wxcs-text {
	height: 100%;
	letter-spacing: 0.02em;
	margin-left: 38px;
	padding: 23px 0;
	display: flex;
	display: -webkit-flex;
	flex-direction: column;
	-webkit-flex-direction: column;
	justify-content: space-between;
	-webkit-justify-content: space-between;
	box-sizing: border-box;
}
.wxcs-title {
	font-size: 28px;
	line-height: 1.2;
	font-weight: bold;
}
.wxcs-button-container {
	font-size: 17px;
	align-self: flex-start;
	-webkit-align-self: flex-start;
	box-sizing: border-box;
	display: inline-block;
	background: #ffd37a;
	padding: 5px;
	-moz-border-radius: 2px;
	border-radius: 2px;
	-webkit-border-radius: 2px;
	width: 195px;
	margin-top: 123px;
	margin-left: 25px;
}
.wxcs-button-border {
	border: 1px solid #874f7b;
	-moz-border-radius: 2px;
	border-radius: 2px;
	-webkit-border-radius: 2px;
	background: #ac72a0;
	background-image: -webkit-gradient(linear, 0% 0, 0% 100%, from(#ac72a0), to(#97598b));
	background-image: -moz-linear-gradient(0% 100% 90deg, #97598b, #ac72a0);
}
.wxcs-button {
	text-decoration: none;
	display: block;
	text-align: center;
	font-weight: bold;
	border: 1px solid #ac72a0;
	border-top: 1px solid #bd8fb3;
	font-size: 15px;
	text-align: center;
	padding: 10px 0;
	color: #FFF;
	background: #ac72a0;
	background-image: -webkit-gradient(linear, 0% 0, 0% 100%, from(#ac72a0), to(#97598b));
	background-image: -moz-linear-gradient(0% 100% 90deg, #97598b, #ac72a0);
	-moz-border-radius: 2px;
	border-radius: 2px;
	-webkit-border-radius: 2px;
	width: 100%;
	cursor: pointer;
	margin: 0;
	box-sizing: border-box;
}
.wxcs-button:hover {
	text-decoration: none !important;
	border: 1px solid #ac72a0;
	border-bottom: 1px solid #b582a9;
	font-size: 15px;
	text-align: center;
	color: #F0F8FB;
	background: #97598b;
	background-image: -webkit-gradient(linear, 0% 0, 0% 100%, from(#97598b), to(#a36298));
	background-image: -moz-linear-gradient(0% 100% 90deg, #a36298, #97598b);
	-moz-border-radius: 2px;
	border-radius: 2px;
	-webkit-border-radius: 2px;
}
.wxcs-button:focus,
.wxcs-button:active {
	color: #fff;
	outline: none;
	box-shadow: none;
}
.wxcs-extensions-container {
	display: flex;
	display: -webkit-flex;
	flex-direction: column;
	-webkit-flex-direction: column;
	position: relative;
	margin-left: 45px;
	background-color: #fff;
	padding: 5px;
	border-radius: 0 0 15px 15px;
	width: 272px;
	height: 158px;
}
.wxcs-extensions-arrow {
	width: 26px;
	position: absolute;
	bottom: 7px;
	left: 50%;
	transform: translateX(-50%);
}
.wxcs-extension {
	width: 25%;
	float: left;
	padding: 0 9px;
	box-sizing: border-box;
}
.wxcs-extension-img {
	display: flex;
	display: -webkit-flex;
	width: 100%;
	padding: 5px;
	box-sizing: border-box;
}
.wxcs-extension-title {
	font-size: 12px;
	text-align: center;
	line-height: 1;
	margin-bottom: 7px;
}
.wxcs-close {
	color: #555;
	position: absolute;
	top: 5px;
	right: 5px;
	cursor: pointer;
}
@media (max-width: 1400px) {
	.wxcs-extensions-container {
		display: none;
	}
}
@media (max-width: 1050px) {
	.wxcs-text-button {
		flex-direction: column;
		-webkit-flex-direction: column;
	}
	.wxcs-text {
		height: 140px;
		line-height: 1.3;
		padding-bottom: 10px;
	}
	.wxcs-title {
		font-size: 19px;
	}
	.wxcs-button-container {
		width: 180px;
		margin: 0;
		margin-left: 38px;
	}
	.wxcs-button {
		padding: 7px 0;
	}
}
@media (max-width: 670px) {
	.wxcs-notice {
		height: auto;
	}
	.wxcs-notice-logo img {
		width: 25px;
		height: 25px;
	}
	.wxcs-main {
		flex-direction: column;	
	}
	.wxcs-preview {
		width: 90%;
		margin: 0;
	}
	.wxcs-text-button {
		margin-top: -30px;
		position: relative;
		padding: 8px;
		box-sizing: border-box;
		width: 100%;
	}
	.wxcs-text {
		padding: 0 5px;
		padding-bottom: 10px;
		padding-top: 0;
		margin: 0;
		height: auto;
	}
	.wxcs-title {
		padding-bottom: 5px;
	}
	.wxcs-button-container {
		margin: 0;
	}
}
</style>
<script>
jQuery(function($){
	$('.wxcs-close').click(function(){
		$.ajax({
			url: '<?php echo admin_url('admin-ajax.php') ?>',
			type: 'POST',
			data: {
				action: 'wxcs_close_notice',
				nonce: '<?php echo wp_create_nonce('wxcs-close-notice') ?>',
			}
		});
		$(this).closest('.wxcs-notice').fadeOut();
	});
});
</script>