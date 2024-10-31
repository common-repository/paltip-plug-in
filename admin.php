<?php
	require_once plugin_dir_path(__FILE__).'publicFunctions.php';
	
	$user_action = $_REQUEST['user_action'];
	$show_login_form = false;
	$error = NULL;
	
	if($user_action=='submit'){
		$show_login_form = true;
		$arr = publicFunctions::login($_REQUEST['paltip_email'],$_REQUEST['paltip_password'],$error);
		// save id and user_name
		if($error==NULL && count($arr)>1){
			publicFunctions::setPTID($arr['id']);
			publicFunctions::setPTUserName($arr['user_name']);
			publicFunctions::setPTEmail($arr['email']);
			publicFunctions::setPTActive('1');
			$link_changer = new LinkChanger( publicFunctions::getPTID(), NULL , publicFunctions::getPTUserName() );
			$link_changer->updateLinksToNewUser();
			$show_login_form = false;
		}
	}
	
	if( strlen(publicFunctions::getPTID())>0 ){
		if($arr==NULL){
		$arr = publicFunctions::getUserInfo(publicFunctions::getPTID());
		}
		$links_arr = publicFunctions::getUserLinks(publicFunctions::getPTID());
		$tips_count = LinkChanger::getNumberOfTipsAndPosts();
	}
	wp_enqueue_script("jquery");
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
	function openLoginForm(){
		jQuery('#paltip_login_form').slideDown();
		//document.getElementById('paltip_login_form').style.dispaly = 'inline';
	}
//-->
</script>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php echo publicFunctions::$PLUGIN_NAME; ?> Options</h2>
	<br/>
	<img src="<?php echo WP_PLUGIN_URL;?>/paltip-plug-in/banner.png" width="772" height="255"/>
	<div id="poststuff" class="metabox-holder">
		<?php if(count($arr)>0){ ?>
		<div class="postbox">
			<h3 class="hndle"><span>User info</span></h3>
			<div class="inside">
				<h2>Hello <?php echo ucfirst($arr['first_name']); ?>, How much $ did you make?</h2>
				<h1>The more you post, the more you Earn</h1>
				<font size="+1">
				<table style="text-align: center;">
					<tr>
						<td># of posts</td>
						<td/>
						<td># of affiliate links</td>
						<td></td>
						<td>Affiliate $ Earned</td>
					</tr>
					<tr>
						<td><img src="<?php echo WP_PLUGIN_URL;?>/paltip-plug-in/img/post.png" /></td>
						<td style="padding: 0px 15px;">X</td>
						<td><img src="<?php echo WP_PLUGIN_URL;?>/paltip-plug-in/img/link.jpg" /></td>
						<td style="padding: 0px 15px;">=</td>
						<td><img src="<?php echo WP_PLUGIN_URL;?>/paltip-plug-in/img/pig.jpg" /></td>
					</tr>
					<tr>
						<td><?php if($tips_count!=NULL  ){ echo $tips_count['number_of_posts'];}else{ echo 0;}  ?><br/> (posts to date)</td>
						<td/>
						<td><?php if($tips_count!=NULL  ){ echo $tips_count['number_of_paltip_links'];}else{ echo 0;}  ?><br/> (tips to date)</td>
						<td/>
						<td>$<?php echo $arr['total_balance'];?></td>
					</tr>
				</table>
				<br/>
				</font>
				<p style="font-size: 17px;">Making $ with <?php echo publicFunctions::$PLUGIN_NAME;?> is easy.<br/></p>
				<ul style="padding-left: 15px;">
					<li type=circle>When you post links, and your readers click and purchase, you earn money.
					<li type=circle>We've already changed your old posts links to earn you money.
					<li type=circle>Oh, one more thing, for every new blog you introduce to our plug-in, we'll put $1 to your piggy bank. <a href="http://paltip.com/<?php echo $arr['user_name'];?>/friendsList"> Invite other bloggers</a>
				</ul>
				
				<?php if(publicFunctions::getPTActive()=='1'){?>
					<br/>For more profile details click <a href="http://paltip.com/<?php echo $arr['user_name'];?>" target="_blank">here</a> 
				<?php }?>
				<br/>
				need more help getting started? Watch one minute <a href="http://www.youtube.com/watch?v=fiqvrrYVNtg" target="_blank">getting started video for wordpress plugin</a>, or <a href="http://paltip.com/contactUs">contact us</a>				
			</div>
		</div>
		<div class="postbox">
			<h3 class="hndle"><span>User Tips</span></h3>
			<div class="inside">
				<?php if(count($links_arr)>0){?>
				<table class="widefat">
					<thead>
					<tr>
							<th colspan="2">Tip title</th>
							<th width="120">Number of Views</th>
							<th width="120">Number of Buys</th>
							<th width="120">Commision</th>
							<th width="120">Total Revenue</th>
					</tr>
					</thead>
					<tbody>
					<?php for($i=0; $i<count($links_arr);$i++){ ?>
						<tr>
							<td>
								<img src="<?php echo $links_arr[$i]['product_pic']; ?>" width="50" height="50"/>
							</td>
							<td>
								<a href="<?php echo $links_arr[$i]['link']; ?>" target="_blank"><?php echo $links_arr[$i]['url_title']; ?></a>
							</td>
							<td align="center">
								<?php echo $links_arr[$i]['view_count']; ?>
							</td>
							<td align="center">
								<?php echo $links_arr[$i]['purches_count']; ?>
							</td>
							<td align="center">
								$<?php echo $links_arr[$i]['product_commision']; ?>
							</td>
							
							<td align="center">
								$<?php echo $links_arr[$i]['product_commision']*$links_arr[$i]['purches_count']; ?>
							</td>
						</tr>		
					<?php }?>
					</tbody>
				 </table>
				<?php if(publicFunctions::getPTActive()=='1'){?>
					<br/><a class=button-secondary href="http://paltip.com/<?php echo $arr['user_name'];?>" target="_blank">View All Tips</a>
				<?php }
				}else{?>
				 	You don't have any tips that can earn you money.<br/>
				 	Add some product links in to your post, and start earn money.
				<?php }?>
			</div>
		</div>
		<?php }?>
		<?php if(publicFunctions::getPTActive()!='1'){?>
		<div class="postbox">
			<h3 class="hndle"><span>Getting Started</span></h3>
			<div class="inside">
			
				<h2>You are 90 seconds <img src="<?php echo WP_PLUGIN_URL;?>/paltip-plug-in/img/clock.png" /> away from starting your automatic money making engine</h2>
				<b>Here how easy it is to put the most profitable affiliate network on the blogs to work for you.</b>
				<table style="padding-left:15px;">
					<tr><td>1. Choose the product you want to blog about (eg. The New Ipad)</td><td> <img alt="the new ipad" src="<?php echo WP_PLUGIN_URL;?>/paltip-plug-in/img/ipad.jpg"></td></tr>
				</table>
				<table style="padding-left:15px;">	
					<tr><td>2. Add the link from any E-commerce site to you post (we auto affiliate the link) </td><td><img alt="the new ipad" src="<?php echo WP_PLUGIN_URL;?>/paltip-plug-in/img/link.jpg"></td></tr>
				</table>
				<table style="padding-left:15px;">
					<tr><td>3. Publish your post and Earn! </td><td><img alt="the new ipad" src="<?php echo WP_PLUGIN_URL;?>/paltip-plug-in/img/publish.jpg"></td></tr>
				</table>
				
				<h2>PalTip is the easiest and fastest way to turn your links into affiliate links. Start using the PalTip plug-in now and register later.</h2><br/>
				<input type="button" value="get started with my 1st PalTip Post" onClick="javascript:document.location='post-new.php';" class="button-primary" style="height:20px;"/>&nbsp; &nbsp; &nbsp;
				<br/><br/>
				<b>Please note:</b> The first time you save a post it may take a few seconds to save, our plug-in is working magic with your older posts too.
				<br/>
				need more help getting started? Watch one minute <a href="http://www.youtube.com/watch?v=fiqvrrYVNtg" target="_blank">getting started video for wordpress plugin</a>, or <a href="http://paltip.com/contactUs">contact us</a>				
				
				<br/><br/>   
			<?php if(count($arr)>0){ ?>
				<input type="button" value="I have a PalTip login" onClick="javascript:openLoginForm();" class="button-primary"/>&nbsp; &nbsp; &nbsp;
				<?php if(publicFunctions::getPTActive()!='1'){?>
				<input type="button" value="To complete your registration and to arrange payment click here" onClick="javascript:window.open('http://paltip.com/register?token=ui_<?php echo $arr['api_token'];?>','paltip');" class="button-primary"/>
				<?php }else{?>
					<input type="button" value="Register to PalTip now" class="button-primary" onClick="javascript:window.open('http://paltip.com/register?token=pr_<?php echo publicFunctions::$PLUGIN_REFERRER;?>','paltip');"/>
				<?php }?>
			<?php }else{?>
				<input type="button" value="I have PalTip login" onClick="javascript:openLoginForm();" class="button-secondary" />&nbsp; &nbsp; &nbsp;
				<input type="button" value="Register to PalTip now" class="button-secondary" onClick="javascript:window.open('http://paltip.com/register?token=pr_<?php echo publicFunctions::$PLUGIN_REFERRER;?>','paltip');"/>
							
			<?php }?>
			
			<form method="post" <?php if(!$show_login_form){?>style="display:none"<?php }?> action="admin.php?page=PalTip_options" id="paltip_login_form">
				<input type="hidden" name="user_action" value="submit"> 
				<h4><u>Login to PalTip:</u></h4>
				<span style="color:red"><?php echo $error;?></span>
				<table width="510">
					<tr valign="top">
						<th width="150" scope="row" style="text-align:left;">User Email:</th>
						<td width="*" >
							<input name="paltip_email" type="text" id="paltip_email" value="<?php echo publicFunctions::getPTEmail();?>" style="width:200px" />
						</td>
					</tr>
					<tr valign="top">
						<th width="150" scope="row" style="text-align:left;">PalTip password:</th>
						<td width="*">
							<input name="paltip_password" type="password" id="paltip_password" value="" style="width:200px" /><span class="submit"><input type="submit" value="<?php _e('Login'); ?>" /></span>
						</td>
					</tr>
				</table>
			</form>
			</div>
		</div>
		<?php }else{?>
			<div class="postbox">
				<h3 class="hndle"><span>Options</span></h3>
				<div class="inside">
					
					<input type="button" value="Change user" onClick="javascript:openLoginForm();" class="button-primary"/>&nbsp; &nbsp; &nbsp;

					<div <?php if(!$show_login_form){?>style="display:none"<?php }?> id="paltip_login_form">
						<form method="post"  action="admin.php?page=PalTip_options">
							<input type="hidden" name="user_action" value="submit"> 
							<h4><u>Login to PalTip:</u></h4>
							<span style="color:red"><?php echo $error;?></span>
							<table width="510">
								<tr valign="top">
									<th width="150" scope="row" style="text-align:left;">User Email:</th>
									<td width="*" >
										<input name="paltip_email" type="text" id="paltip_email" value="<?php echo publicFunctions::getPTEmail();?>" style="width:200px" />
									</td>
								</tr>
								<tr valign="top">
									<th width="150" scope="row" style="text-align:left;">PalTip password:</th>
									<td width="*">
										<input name="paltip_password" type="password" id="paltip_password" value="" style="width:200px" /><span class="submit"><input type="submit" value="<?php _e('Login'); ?>" /></span>
									</td>
								</tr>
							</table>
						</form>
						<h4>OR</h4>
						<?php if(publicFunctions::getPTActive()!='1'){?>
						<input type="button" value="To complete your registration and to arrange payment click here" onClick="javascript:window.open('http://paltip.com/register?token=ui_<?php echo $arr['api_token'];?>','paltip');" class="button-primary"/>
						<?php }else{?>
						<input type="button" value="Register to PalTip now" class="button-primary" onClick="javascript:window.open('http://paltip.com/register?token=pr_<?php echo publicFunctions::$PLUGIN_REFERRER;?>','paltip');"/>
						<?php }?>
					</div>
			</div>
		</div>		
		<?php }?>		
	</div>
</div>