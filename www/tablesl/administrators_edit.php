<?php
require_once('common/header.php');
require("common/menu.php");


if(isset($_GET['id']) && !empty($_GET['id'])){
	$currUser = User::find_by_id($_GET['id']);
	//Check if the user to be edited is more important than the logged in user
	if($currUser->user_level > $user->user_level){
		$session->message("You have no access.");
		redirect_to("administrators.php");
	}
}
if(!empty($_POST)){


	try {

		$token = isset($_POST['token']) ? $_POST['token'] : NULL;
		unset($_POST['token']);
		CSRF::validate($token,false);


		if(!empty($_POST['id'])){
			$userToUpdate = User::find_by_id($_POST['id']);
			//Check if the user to be edited is more important than the logged in user
			if($userToUpdate->user_level > $user->user_level){
				$session->message("You have no access.");
				redirect_to("administrators.php");
			}
		}else{
			$userToUpdate = new User();
		}
		$userToUpdate->setAttributes($_POST);
		if($userToUpdate->save()){

			if(isset($_POST['email_credentials'])){
				$userToUpdate->emailCredentials();
			}

			$session->message($userToUpdate->username . " was saved!");
			redirect_to("administrators.php");
		}else{
			$session->message($userToUpdate->username . " could not be saved!");
			redirect_to("administrators.php");
		}



	} catch (Exception $e) {

		$session->message("Invalid Request.");
		redirect_to("administrators.php");
	}
}

$user->allowed('administrators_edit');

//Unset the password for security reasons
if(isset($currUser)){
	unset($currUser->password);
}
?>
<?php
include("js/passwordmeter.php");
?>
<style>
td h2 {
	font-size: 14px;
}
td input {
	width:100%;
	padding:5px 5px;
	border-radius: 4px;
	border:1px solid #ccc;
}
td label { 
	font-size: 14px;
}
</style>
<section>
	<div class="content-wrapper tab-content	">
		<?php require('common/header_page.php');?>
		<div class="content-heading">
			Admins <em class="fa fa-chevron-circle-right"></em>

			Updating Entry #<?=$_POST['id'];?> <span id="result" style="color:#6e6;font-weight: bold;"></span>

		</div>
		<div class=''>
			<form action="" method="post" enctype="multipart/form-data" id="AdministratorsEdit">
				<input type="hidden" name="token" value="<?php echo CSRF::generateToken(); ?>" />

				<div class="form round drop_shadow create active" id="list">

<!--					<div class="form-group">
						<label class="col-md-2 control-label ">Question</label>
						<div class="col-md-10">
							<input type="text" name="data[faq][question]" placeholder="Question" data-bvalidator="" value="q1">
						</div>
					</div>
-->


					<table cellpadding="4" width="100%" style="font-size:12px;">
						<tr>
							<td><h2>Username</h2></td>
							<td><input type="text" name="username" data-bvalidator="required" value="<?php echo (isset($currUser) && !empty($currUser->username) ? h($currUser->username) : '' )?>" /></td>
						</tr>
						<tr>
							<td><h2>Password</h2></td>
							<td><input type="text" name="password" id="password" data-bvalidator="required"  value="<?php echo (isset($currUser) && !empty($currUser->password) ? $currUser->password : '' )?>" />

								<div id="passwordStrengthDiv" class="is0"></div>
								<input type="hidden" name="pwdStrength" id="pwdStrengthVal" value="0" /></td>
						</tr>
						<tr>
							<td><h2>Email</h2></td>
							<td><input type="text" name="email" data-bvalidator="email" value="<?php echo (isset($currUser) && !empty($currUser->email) ? $currUser->email : '' )?>" /></td>
						</tr>
						<tr>
							<td><h2>Userlevel</h2></td>
							<td>
								<select name="user_level"  data-bvalidator="required" >
									<option value="">Select User Level</option>
									<?php /* Users cannot add a higher level user */ ?>
									<?php if($user->user_level >= 9) : ?>
										<option value="9" <?php echo ((isset($currUser) && !empty($currUser->user_level) && $currUser->user_level == 9) ? 'selected' : '' )?>>Super Admin</option>
										<?php endif; ?>
									<?php if($user->user_level >= 5) : ?>
										<option value="5" <?php echo ((isset($currUser) && !empty($currUser->user_level) && $currUser->user_level == 5) ? 'selected' : '' )?>>Admin</option>
										<?php endif; ?>
									<?php if($user->user_level >= 3) : ?>
										<option value="3" <?php echo ((isset($currUser) && !empty($currUser->user_level) && $currUser->user_level == 3) ? 'selected' : '' )?>>Editor</option>
										<?php endif; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td width="180"><h2>Do not allow:</h2>Restrict the user from editing the following pages</td>
							<td>
								<?php foreach($allTables as $tableName=>$index): ?>
									<label><input type="checkbox" name="disallow[]" value="<?php echo $tableName; ?>" <?php if(!empty($currUser) && strpos($currUser->disallow,$tableName) !== false){ echo 'checked'; } ?> />&nbsp;<?php echo field_name($tableName); ?></label><br />
									<?php endforeach; ?>

								<?php
								if(($user->user_level >= 5) && (!isset($currUser) || (isset($currUser) && $currUser->user_level<= $user->user_level)) && $user->isAllowed('cmsgen_cpanel_editor_links')) : ?>
									<label><input type="checkbox" name="disallow[]" value="<?php echo 'cmsgen_cpanel_editor_links'; ?>" <?php if(!empty($currUser) && strpos($currUser->disallow,'cmsgen_cpanel_editor_links') !== false){ echo 'checked'; } ?> /> User cannot view cPanel links</label><br />
									<?php endif; ?>

								<?php
								if($user->isAllowed('administrators_edit')) : ?>
									<label><input type="checkbox" name="disallow[]" value="<?php echo 'administrators_edit'; ?>" <?php if(!empty($currUser) && strpos($currUser->disallow,'administrators_edit') !== false){ echo 'checked'; } ?> /> User cannot add administrators</label>
									<?php endif; ?>


							</td>
						</tr>
						<tr><td>&nbsp;</td><td></td></tr>

						<tr><td>&nbsp;</td>
							<td>
								<label class="round email_credentials">
									Email user his login credentials <input type="checkbox" name="email_credentials" />
								</label>
							</td></tr>
					</table>
					<div class="clear"></div>
					<div>

					</div>
					<br />

					<input type="hidden" name="id" value="<?php echo (!empty($currUser->id) ? $currUser->id : ''); ?>" />
					<input type="submit" name="save" class="submit round drop_shadow" value="Save" style="float:left;margin-left:4px;" /><br><br><br><br>
					<a href="<?php echo ADMIN_PATH_HTML.DS; ?>administrators.php">List all records</a>



					<div class="clear"></div>
				</div>

			</form>
		</div>
	</div>
</section>

<script type="text/javascript">
	$(document).ready(function() {


		$("#AdministratorsEdit").submit(function(event){
			var pswdStrength =  $("#pwdStrengthVal").val();
			if(pswdStrength < 55 && pswdStrength != 0){
				alert('Password strength must be at least yellow!');
				event.preventDefault();
			}
		});

		<?php if(!empty($_GET['id'])) : ?>
			$("#password").removeAttr('data-bvalidator');
			<?php endif; ?>
	});

</script>

<?php require_once('common/footer.php'); ?>