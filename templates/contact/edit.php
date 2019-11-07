<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Contacts - Edit'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'contacts'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Modification contacts
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-user"></i> <a href="<?php echo \descartes\Router::url('Contact', 'list'); ?>">Contacts</a>
						</li>
						<li class="active">
							<i class="fa fa-edit"></i> Modifier
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-edit fa-fw"></i> Modification de contacts</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Contact', 'update', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
                            <?php foreach ($contacts as $contact) } ?>
									<div class="form-group">
                                        <input name="contacts[<?php $this->s($contact['id']); ?>][id]" type="hidden" value="<?php $this->s($contact['id']); ?>">
										<label>Nom contact</label>
										<div class="form-group input-group">
											<span class="input-group-addon"><span class="fa fa-user"></span></span>
											<input name="contacts[<?php $this->s($contact['id']); ?>][name]" class="form-control" type="text" placeholder="Nom contact" autofocus required value="<?php $this->s($contact['name']); ?>">
										</div>
									</div>	
									<div class="form-group">
										<label>Numéro de téléphone du contact</label>
										<div class="form-group">
											<input name="" class="form-control phone-international-input" type="tel" contact-id="<?php $this->s($contact['id']); ?>" value="<?php $this->s($contact['number']); ?>">
											<input name="contacts[<?php $this->s($contact['id']); ?>][number]" type="hidden" id="phone-hidden-input-<?php $this->s($contact['id']); ?>" required>
										</div>
									</div>
									<hr/>
                                <?php } ?>
                                <a class="btn btn-danger" href="<?php echo \descartes\Router::url('Contact', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le contact" /> 	
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery('document').ready(function($)
	{
		jQuery('.phone-international-input').intlTelInput({
			defaultCountry: '<?php $this->s(RASPISMS_SETTINGS_DEFAULT_PHONE_COUNTRY); ?>',
			preferredCountries: <?php $this->s(json_encode(explode(',', RASPISMS_SETTINGS_PREFERRED_PHONE_COUNTRY)), false, false); ?>,
			nationalMode: true,
			utilsScript: '<?php echo HTTP_PWD; ?>/js/intlTelInput/lib/libphonenumber/utils.js'
		});

		jQuery('form').on('submit', function(e)
		{
			e.preventDefault();
			jQuery('.phone-international-input').each(function(key, value)
			{
				jQuery('#phone-hidden-input-' +  jQuery(this).attr('contact-id')).val(jQuery(this).intlTelInput("getNumber"));
			});
			
			this.submit();
		});
	});
</script>
<?php
	$this->render('incs/footer');
