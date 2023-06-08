<?php
require_once('common/header.php');
require("common/menu.php");

if(isset($_POST) && !empty($_POST['ids'])){
	foreach($_POST['ids'] as $id){
		$id = (int)$id;
		if($currUser = User::find_by_id($id)){
			$currUser->delete();
		}
	}
}

$allUsers = User::find_all();
$user->allowed('administrators_edit');
$pageTitle = 'All Admins';

?>
<section>
	<div class="content-wrapper">
		<?php require('common/header_page.php');?>
		<form action="administrators.php" method="post" enctype="multipart/form-data">

			<div class="form round drop_shadow" id="list">
			<div class="panel panel-default" >
			<div class="panel-body" id="list" style="overflow: auto;">
				<table class="table data-table table-striped table-hover">
					<thead>

						<tr class="ui-state-disabled">

							<td><h2>ID</h2></td>
							<td><h2>Username</h2></td>
							<td><h2>Password</h2></td>
							<td><h2>Email</h2></td>
							<td><h2>User Level</h2></td>
							<td><h2>Edit</h2></td>
							<td align="center">
								<input type="checkbox" id="select-all" />
							</td>
						</tr>
					</thead>
					<tbody class="content">
						<?php foreach($allUsers as $currentUser) : ?>
							<tr height="30" style="border-bottom:thin solid #eeeee1;line-height:30px;" id="<?php echo $currentUser->id; ?>">

								<td ><?php echo $currentUser->id; ?></td>
								<td ><?php echo h($currentUser->username); ?></td>
								<td ><em>encrypted</em></td>
								<td ><?php echo $currentUser->email; ?></td>
								<td ><?php echo $currentUser->user_level; ?></td>

								<td width="30">
									<a href="administrators_edit.php?id=<?php echo $currentUser->id; ?>"><img src="<?php echo ADMIN_PATH_HTML;?>/images/file_edit.png" width="20" alt="Edit" /></a>
								</td>
								<td  align="center">
									<input type="checkbox" name="ids[]" value="<?php echo $currentUser->id; ?>" />
								</td>

							</tr>
							<?php endforeach; ?>
					</tbody>
				</table>
				<div class="clear"></div>
				<br />

				<input type="hidden" name="table" value="<?php echo $tableName; ?>" />
				<input class="submit round drop_shadow btn btn-primary btn-sm btn-success "  type="button" value="Add" ONCLICK="window.location.href='administrators_edit.php?action=new'"  style="float:left;margin-left:4px;" />

				<input type="submit" name="delete" class="submit round drop_shadow ConfirmDelete btn btn-primary btn-sm btn-danger" value="Delete" style="float:left;margin-left:4px;" />

				<input class="submit round drop_shadow btn btn-primary btn-sm " type="button" value="Inverse" id="inverse" style="float:left;margin-left:4px;" />

				<div class="clear"></div>
			</div>
		</form>
	</div>
</section>

<?php require_once('common/footer.php'); ?>