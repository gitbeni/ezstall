<?php $this->extend('site/common/layout/layout1') ?>

<?php $this->section('content') ?>
<div class="container-fluid">
	<div class="cont-sec">
		<div class="inner-section row" >
			
			<div class="col-sm-12 col-md-9 col-lg-9 right-conten-section my-5">
				<div class="page-action" align="right">
					<a href="<?php echo base_url(); ?>/myaccount/addevent" class="btn btn-danger">Add</a>
				</div>
				<form method="post" id="form" action="<?php echo base_url(); ?>/myaccount/event/action" autocomplete="off">
					<div class="col-md-12">
						<h1>Event List</h1>

						<div class="table-responsive">
							<table class="table table-hover datatables" >
	                        	<thead>
		                            <tr>
		                                <th scope="col" align="justify">Event Name</th>
		                                <th scope="col" >Image</th>
		                                <th scope="col" align="justify">Event on</th>
		                                <th scope="col" align="justify">Location</th>
		                                <th scope="col" align="justify">Mobile</th>
		                                <th scope="col" align="justify">Action</th>
		                            </tr>
	                        	</thead>
	                        	
				            </table>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php $this->endSection(); ?>
<?php $this->section('js') ?>
<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">)


<link href="<?php echo base_url(); ?>/assets/plugins/datatables-1.10.25/css/jquery.dataTables.min.css" rel="stylesheet" />
<link href="<?php echo base_url(); ?>/assets/plugins/datatables-1.10.25/css/responsive.dataTables.min.css" rel="stylesheet" />
<script src="<?php echo base_url(); ?>/assets/plugins/datatables-1.10.25/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/plugins/datatables-1.10.25/js/dataTables.responsive.min.js"></script>
<script>
		$(function(){
			var options = {
				url 		: 	'<?php echo base_url()."/myaccount/DTevents"; ?>',
				data		:	{ 'page' : 'events' },
				columns 	: 	[
									{ 'data' : 'name' },
									{ 'data' : 'image' },
									{ 'data' : 'event_on' },
									{ 'data' : 'location' },
									{ 'data' : 'mobile'},
									{ 'data' : 'action' }
								],
				columndefs	:	[{ 'targets' : 4, 'sortable' : false }]
			};
			ajaxdatatables('.datatables', options);

		});

		
</script>
<?php echo $this->endSection() ?>