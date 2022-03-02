<?php $this->extend('site/common/layout/layout1') ?>
<?php $this->section('content') ?>
<section class="signInFlex">
	<div class="signInLeft">
		<img class="signInImage" src="<?php echo base_url()?>/assets/images/login.jpg" alt="Horse Image">
	</div>
	<div class="signInRight">
		<div class="signInFormPanel">
			<center>
				<h1 class="topPad">Let's Sign In</h1>
				<p>Enter details to signin</p>
			</center>
			<form class="signInForm" id="form" method="post" action="">
				<span>
					<input type="email" class="signInEmail" placeholder="Enter email" name="email" required value="">
					<center></center>
				</span>
				<span>
					<input type="password" class="signInPassword" placeholder="Enter password" required name="password" value="">
					<center></center>
				</span>
				<button type="submit" class="signInSubmitBtn">Sign In</button>
				<p>Don't have an account ? <a href="<?php echo base_url() ?>/register" class="signUpLink">Sign Up</a>
				</p>
			</form>
		</div>
	</div>
</section>
<?php $this->endSection(); ?>
<?php $this->section('js') ?>
	<script>
		$(function(){
			validation(
				'#form',
				{
					email		: {
						required  : true,
						email  	  : true
					},
					password	: {
						required  : true
					}
				},
				{
					email		: {
						required  : "Email field is required.",
						email  	  : "Enter valid email address."
					},
					password	: {
						required  : "Password field is required."
					}
				}
			);
		});
	</script>
<?php echo $this->endSection() ?>