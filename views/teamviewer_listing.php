<?php $this->view('partials/head'); ?>

<?php //Initialize models needed for the table
new Machine_model;
new Reportdata_model;
new Teamviewer_model;
?>

<div class="container">
  <div class="row">
  	<div class="col-lg-12">
		  <h3><span data-i18n="teamviewer.reporttitle"></span> <span id="total-count" class='label label-primary'>…</span></h3>
		  <table class="table table-striped table-condensed table-bordered">
		    <thead>
		      <tr>
		      	<th data-i18n="listing.computername" data-colname='machine.computer_name'></th>
		        <th data-i18n="serial" data-colname='reportdata.serial_number'></th>
		        <th data-i18n="username" data-colname='reportdata.long_username'></th>
		        <th data-i18n="teamviewer.clientid" data-colname='teamviewer.clientid'></th>	        
		        <th data-i18n="teamviewer.always_online" data-colname='teamviewer.always_online'></th>	        
		        <th data-i18n="teamviewer.version" data-colname='teamviewer.version'></th>	        
		        <th data-i18n="teamviewer.update_available" data-colname='teamviewer.update_available'></th>	        
		        <th data-i18n="teamviewer.updateversion" data-colname='teamviewer.updateversion'></th>	        
		        <th data-i18n="teamviewer.autoupdatemode" data-colname='teamviewer.autoupdatemode'></th>	        
		        <th data-i18n="teamviewer.security_adminrights" data-colname='teamviewer.security_adminrights'></th>	        
		        <th data-i18n="teamviewer.prefpath" data-colname='teamviewer.prefpath'></th>	        
		      </tr>
		    </thead>
		    <tbody>
		      <tr>
		        <td data-i18n="listing.loading" colspan="11" class="dataTables_empty"></td>
		      </tr>
		    </tbody>
		  </table>
    </div> <!-- /span 12 -->
  </div> <!-- /row -->
</div>  <!-- /container -->

<script type="text/javascript">

	$(document).on('appUpdate', function(e){

		var oTable = $('.table').DataTable();
		oTable.ajax.reload();
		return;

	});

	$(document).on('appReady', function(e, lang) {
		// Get column names from data attribute
		var columnDefs = [],
		col = 0; // Column counter
		$('.table th').map(function(){
	             columnDefs.push({name: $(this).data('colname'), targets: col});
	             col++;
		});
	    oTable = $('.table').dataTable( {
	        columnDefs: columnDefs,
	        ajax: {
                url: appUrl + '/datatables/data',
                type: "POST",
                data: function(d){
                     d.mrColNotEmpty = "clientid";
                }
            },
            dom: mr.dt.buttonDom,
            buttons: mr.dt.buttons,
	        createdRow: function( nRow, aData, iDataIndex ) {
	        	// Update name in first column to link
	        	var name=$('td:eq(0)', nRow).html();
	        	if(name == ''){name = "No Name"};
	        	var sn=$('td:eq(1)', nRow).html();
	        	var link = mr.getClientDetailLink(name, sn, '#tab_teamviewer-tab');
	        	$('td:eq(0)', nRow).html(link);
                
	        	// Connect button
	        	var colvar=$('td:eq(3)', nRow).html();
	        	$('td:eq(3)', nRow).html(+colvar+'&nbsp;&nbsp;&nbsp;&nbsp;<a class="btn btn-info btn-xs" style="min-width: 80px;" href="teamviewer10://control?device='+colvar+'"  target="_blank">'+i18n.t('teamviewer.connect')+'</a>')
	        	
	        	// Status
	        	var always_online=$('td:eq(4)', nRow).html();
	        	always_online = always_online == 1 ? '<span class="label label-success">'+i18n.t('yes')+'</span>' :
	        	(always_online === '0' ? '<span class="label label-danger">'+i18n.t('no')+'</span>' : '')
	        	$('td:eq(4)', nRow).html(always_online)
            
	        	var colvar=$('td:eq(6)', nRow).html();
	        	colvar = colvar == 1 ? '<span class="label label-success">'+i18n.t('yes')+'</span>' :
	        	(colvar === '0' ? '<span class="label label-danger">'+i18n.t('no')+'</span>' : '')
	        	$('td:eq(6)', nRow).html(colvar)
            
	        	var colvar=$('td:eq(8)', nRow).html();
	        	colvar = colvar == 1 ? '<span class="label label-success">'+i18n.t('yes')+'</span>' :
	        	(colvar === '0' ? '<span class="label label-danger">'+i18n.t('no')+'</span>' : '')
	        	$('td:eq(8)', nRow).html(colvar)
            
	        	var colvar=$('td:eq(9)', nRow).html();
	        	colvar = colvar == 1 ? '<span class="label label-success">'+i18n.t('yes')+'</span>' :
	        	(colvar === '0' ? '<span class="label label-danger">'+i18n.t('no')+'</span>' : '')
	        	$('td:eq(9)', nRow).html(colvar)
       	        }
	    });
	});
</script>

<?php $this->view('partials/foot'); ?>
