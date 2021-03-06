<?php include "inc/header.inc.php" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$language;?>" lang="<?=$language;?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $Text['global_title'] . " - " . $Text['head_ti_incidents'];?></title>

	<link rel="stylesheet" type="text/css"   media="screen" href="css/aixada_main.css" />
  	<link rel="stylesheet" type="text/css"   media="print"  href="css/print.css" />
  	<link rel="stylesheet" type="text/css"   media="screen" href="js/fgmenu/fg.menu.css"   />
    <link rel="stylesheet" type="text/css"   media="screen" href="css/ui-themes/<?=$default_theme;?>/jqueryui.css"/>

    <?php if (isset($_SESSION['dev']) && $_SESSION['dev'] == true ) { ?> 
	    <script type="text/javascript" src="js/jquery/jquery.js"></script>
		<script type="text/javascript" src="js/jqueryui/jqueryui.js"></script>
		<script type="text/javascript" src="js/fgmenu/fg.menu.js"></script>
		<script type="text/javascript" src="js/aixadautilities/jquery.aixadaMenu.js"></script>     	 
	   	<script type="text/javascript" src="js/aixadautilities/jquery.aixadaXML2HTML.js" ></script>
	   	<script type="text/javascript" src="js/aixadautilities/jquery.aixadaUtilities.js" ></script>
   	<?php  } else { ?>
	    <script type="text/javascript" src="js/js_for_incidents.min.js"></script>
    <?php }?>

   
	<script type="text/javascript">
	
	$(function(){

		
		
		function switchTo(section){
			resetDetails();
			if (section == 'new_incident'){
				$('#incident_id').val('');
				$('#incidents_listing').hide();
				$('#incidents_id_info').hide();
				$('#h3_new_incident').show(); 
				$('#btn_cancel').show();
			} else {
				$('#incidents_listing').show();
				$('#incidents_id_info').show();
				$('#h3_new_incident').hide(); 
				$('#btn_cancel').hide();
			}
		}
		
		
		function resetDetails(){
			$('#subject, #incidents_text, #ufs_concerned').val('');
			$('#statusSelect option:first').attr('selected',true);
			$('#typeSelect option:last').attr('selected',true);
			$('#prioritySelect option:first').attr('selected',true);
			$('#providerSelect  option:first').attr('selected',true);
			$('#commissionSelect  option:first').attr('selected',true);
			$('#ufs_concerned option:first').attr('selected',true);
			
				
			$('#incident_id').val('');
			$('#incident_id_info').html('');

		}
		
		$('#btn_new_incident')
			.click(function(){
				switchTo('new_incident');
		});
		
		
		$('#btn_cancel')
			.button({
				icons: {
				primary: "ui-icon-close"}
			})
			.click(function(){
				switchTo('listing');
		});


		$('#btn_save')
			.button({
				icons: {
				primary: "ui-icon-check"}
			});

		//DELETE incidents
		$('.btn_del_incident')
			.live("mouseenter", function(){
				$(this).removeClass('ui-icon-close').addClass('ui-icon-circle-close');
			})
			.live("mouseleave", function(){
				$(this).removeClass('ui-icon-circle-close').addClass('ui-icon-close');
			})
			.live("click", function(){
				var incident_id = $(this).parent().next().text();
				$this = $(this).parent().parent();
				$.showMsg({
								msg: '<?php echo $Text['msg_delete_incident'];?>',
								type: 'confirm',
								buttons : {
										"<?php echo $Text['btn_ok'];?>": function() {
											
											$.ajax({
											    type: "POST",
											    url: "smallqueries.php?oper=delIncident&incident_id="+incident_id,
											    success: function(msg){
													$this.empty();
													resetDetails();
											    	
											    },
											    error : function(XMLHttpRequest, textStatus, errorThrown){
												    
											    },
											    complete : function(msg){
											    	
											    }
											}); //end ajax
											$(this).dialog( "close" );
											
									  	}, 
									  	"<?php echo $Text['btn_cancel'];?>":function(){
									  		$(this).dialog( "close" );
											
									  	}
									}
					});
			});

		
		//detect form submit and prevent page navigation
		$('form').submit(function() { 

			var dataSerial = $(this).serialize();
			
			$('button').button( "option", "disabled", true );

			var curUrl = ($('#h3_new_incident').is(':visible'))? "smallqueries.php?oper=newIncident":"smallqueries.php?oper=editIncident";
			
			$.ajax({
				    type: "POST",
				    url: curUrl,
				    data: dataSerial,
				    beforeSend: function(){
				   		$('#editorWrap .loadAnim').show();
					},
				    success: function(msg){
						switchTo('listing');
						resetDetails();
				    },
				    error : function(XMLHttpRequest, textStatus, errorThrown){
				    	 $.updateTips('#incidentsMsg','error','Error: '+XMLHttpRequest.responseText);
				    },
				    complete : function(msg){
				    	$('button').button( "option", "disabled", false );
				    	$('#tbl_incidents tbody').xml2html('reload');
				    	$('#editorWrap .loadAnim').hide();//
				    	
				    	
				    }
			}); //end ajax
			
			return false; 
		});

			
		/**
		 *	incidents
		 */
		$('#tbl_incidents tbody').xml2html('init',{
				url: 'smallqueries.php',
				params : 'oper=latestIncidents&',
				loadOnInit: true,
				paginationNav : '#tbl_incidents tfoot td'
		});


		
		$('#tbl_incidents tbody tr')
			.live('mouseenter', function(){
				$(this).children().addClass('ui-state-highlight');
			})
			.live('mouseleave',function(){
				if (!$(this).hasClass('active_row')){
					$(this).children().removeClass('ui-state-highlight');
				}
			})
			//click on uf table row
			.live("click", function(){
							
				//populate the form
				$(this).closest('tr').find('td:gt(0)').each(function(){
					var input_name = $(this).attr('field_name');
					var value = $(this).text();
	
					if (input_name == 'incident_id') $('#incident_id_info').html('#'+value);
			
					//set the values of the select boxes
					if (input_name == 'type' || input_name == 'status' || input_name == 'priority' || input_name == 'commission' || input_name == 'provider'){
						$("#"+input_name+"Select").val(value).attr("selected",true);
						
					} else if (input_name == 'ufs_concerned'){
						var ufs = value.split(",");
						$('#ufs_concerned').val(ufs);
					} else {
						$('#'+input_name).val(value);
					}
	
				});
		});
		
		

		//build provider select
		$("#providerSelect")
			.xml2html("init", {
				url: 'ctrlShopAndOrder.php',
				params:'oper=listProviders&what=Shop',
				offSet:1,
				loadOnInit:true
		});

		//build ufs select
		$("#ufs_concerned")
			.xml2html("init", {
				url: 'smallqueries.php',
				params:'oper=getActiveUFs',
				offSet:1,
				loadOnInit:true
		});

		//build type select
		$("#typeSelect")
			.xml2html("init", {
				url: 'smallqueries.php',
				params:'oper=getIncidentTypes',
				loadOnInit:true,
				complete: function(){
					$("#typeSelect option:last").attr("selected",true);
				}
		});

		//build commission select
		$("#commissionSelect")
			.xml2html("init", {
				url: 'smallqueries.php',
				params : 'oper=getCommissions',
				offSet : 1,
				loadOnInit: true
		});

		switchTo('listing');
						
			
	});  //close document ready
	</script>
</head>
<body>
<div id="wrap">

	<div id="headwrap">
		<?php include "inc/menu2.inc.php" ?>
	</div>
	<!-- end of headwrap -->
	
	
	<div id="stagewrap">
	
		<div id="titlewrap" class="ui-widget">
			<div id="titleLeftCol">
		    	<h1><?=$Text['ti_incidents']; ?></h1>
		    </div>
		</div>
		
		<div id="incidents_listing" class="ui-widget">
			<div class="ui-widget-content ui-corner-all">
					<h2 class="ui-widget-header ui-corner-all hideInPrint"><?php echo $Text['overview'];?> <a href="javascript:void(null)" class="floatRight" id="btn_new_incident"><span class="ui-icon ui-icon-plus floatLeft"></span><?php echo $Text['btn_new_incident'];?></a> &nbsp;&nbsp;<span class="loadAnim floatRight hidden"><img src="img/ajax-loader.gif"/></span></h2>
					<div id="tbl_div">
					<table id="tbl_incidents">
					<thead>
						<tr>
							<th class="mwidth-50 hideInPrint"></th>
							<th class="mwidth-30"><?php echo $Text['id'];?></th>
							<th class="width-280 hideInPrint"><?php echo $Text['subject'];?></th>
							<th><?php echo $Text['priority'];?></th>
							<th class="mwidth-150"><?php echo $Text['created_by'];?></th>
							<th class="mwidth-150"><?php echo $Text['created'];?></th>
							<th><?php echo $Text['status'];?></th>
							<th class="hidden"><?php echo $Text['incident_type'];?></th>
							<th class="hidden"><?php echo $Text['provider_name'];?></th>
							<th class="hidden"><?php echo $Text['ufs_concerned'];?></th>
							<th class="hidden"><?php echo $Text['comi_concerned'];?></th>
							<th class="hidden hideInPrint">Details</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="hideInPrint"><span style="float:left" class="btn_del_incident ui-icon ui-icon-close"></span>&nbsp;&nbsp;</td>
							<td field_name="incident_id">{id}</td>
							<td field_name="subject" class="hideInPrint"><p class="incidentsSubject">{subject}</p></td>
							
							<td field_name="priority">{priority}</td>
							<td field_name="operator">{uf} {user}</td>
							<td field_name="date_posted">{date_posted}</td>
							<td field_name="status">{status}</td>
							<td field_name="type" class="hidden">{type}</td>
							<td field_name="provider" class="hidden">{provider_concerned}</td>
							<td field_name="ufs_concerned" class="hidden">{ufs_concerned}</td>
							<td field_name="commission" class="hidden">{commission_concerned}</td>
							<td field_name="incidents_text" class="hidden hideInPrint">{details}</td>
						</tr>
						<tr class="hidden">
							<td class="noBorder"></td>
							<td colspan="11" class="noBorder">{subject}</td>
						</tr>
						<tr class="hidden">
							<td class="noBorder"></td>
							<td colspan="11" class="hidden noBorder">{details}</td>
							
						</tr>
						<tr><td colspan="12" class="spacingEnd"></td></tr>
					</tbody>
					<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
				</tfoot>
					</table>
					</div>
					
					
			</div>	
		</div>

		<div id="editorWrap" class="ui-widget hideInPrint">
			<div class="ui-widget-content ui-corner-all">
				<h3 class="ui-widget-header ui-corner-all">&nbsp;<span id="h3_new_incident"><?php echo $Text['create_incident'];?></span> <span id="incident_id_info">#</span><span class="loadAnim floatRight hidden"><img src="img/ajax-loader.gif"/></span></h3>
				<p id="incidentsMsg" class="user_tips"></p>
				<form>
					<input type="hidden" id="incident_id" name="incident_id" value=""/>
					<table>
						<tr>
							<td><label for="subject"><?php echo $Text['subject'];?>:</label></td>
							<td><input type="text" name="subject" id="subject" class="inputTxtLarge inputTxt ui-corner-all" value=""/></td>
							
							
						</tr>
						<tr>
							<td><label for="incidents_text"><?php echo $Text['message'];?>:</label></td>
							<td rowspan="5"><textarea id="incidents_text" name="incidents_text" class="textareaLarge inputTxt ui-corner-all"></textarea></td>
							
							<td><label for="prioritySelect"><?php echo $Text['priority'];?></label></td>
							<td><select id="prioritySelect" name="prioritySelect" class="mediumSelect"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></td>
						</tr>
						<tr>
						
							<td></td>
							<td><label for="statusSelect"><?php echo $Text['status'];?></label></td>
							<td><select id="statusSelect" name="statusSelect" class="mediumSelect"><option value="open"> <?php echo $Text['status_open'];?></option><option value="closed"> <?php echo $Text['status_closed'];?></option></select></td>
						</tr>
						<tr>
							
							<td></td>
							<td><label for="typeSelect"><?php echo $Text['incident_type'];?></label></td>
							<td>
								<select id="typeSelect" name="typeSelect" class="mediumSelect">
									<option value="{id}">{description}</option>
								</select></td>
						</tr>
						<tr>
							
							<td></td>
							<td><label for="ufs_concerned"><?php echo $Text['ufs_concerned']; ?></label></td>
							<td>
								<select id="ufs_concerned" name="ufs_concerned[]" multiple size="6">
								 	<option value="-1" selected="selected"><?php echo $Text['sel_none'];?></option>  
									<option value="{id}"> {id} {name}</option>	
								</select>
							</td>
						</tr>
						<tr>
							
							<td></td>
							<td><label for="providerSelect"><?php echo $Text['provider_concerned'];?></label></td>
							<td>
								<select id="providerSelect" name="providerSelect" class="mediumSelect">
	                    			<option value="-1" selected="selected"><?php echo $Text['sel_none'];?></option>                     
	                    			<option value="{id}"> {id} {name}</option>
								</select>
							</td>
						</tr>
						<tr>
						<td></td>
							<td></td>
							<td><label for="commissionSelect"><?php echo $Text['comi_concerned'];?></label></td>
							<td><select id="commissionSelect" name="commissionSelect" class="mediumSelect">
									<option value="-1" selected="selected"><?php echo $Text['sel_none'];?></option>
									<option value="{description}"> {description}</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2" class="textAlignRight">
								<button id="btn_cancel" type="reset"><?php echo $Text['btn_cancel'];?></button>
								<button id="btn_save" type="submit"><?php echo $Text['btn_save'];?></button>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>


		
	</div>
	<!-- end of stage wrap -->
</div>
<!-- end of wrap -->

<!-- / END -->
</body>
</html>