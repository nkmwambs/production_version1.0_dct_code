<?php 
 $mpesa_short_code_exists=$this->db->select('dct_mpesa_short_code')->get_where('projectsdetails',array('icpno'=>$this->session->center_id))->row_array();

 if($mpesa_short_code_exists>0){
     //if not zero then fcp already provided an dct mpesa short code 
    if($mpesa_short_code_exists['dct_mpesa_short_code']!=0){
      echo 1;
    }
    else{
        echo 0;
    }
 }
 
?>
<div class="row">
	<div class="col-md-12">
    
    	<!------CONTROL TABS START------>
		<ul class="nav nav-tabs bordered">

			<li class="active">
            	<a href="#list" data-toggle="tab"><i class="entypo-user"></i> 
					<?php echo get_phrase('manage_profile');?>
                    	</a>
            </li>
			
			<li class="">
            	<a href="#password" data-toggle="tab"><i class="entypo-lock"></i> 
					<?php echo get_phrase('change_password');?>
                    	</a>
            </li>
                        
           
		</ul>
    	<!------CONTROL TABS END------>
        
	
		<div class="tab-content">
			
			<div class="tab-pane box active" id="list" style="padding: 5px">
                <div class="box-content">
					<?php 
                    foreach($edit_data as $row):
                        ?>
                        <?php echo form_open(base_url() . 'admin.php/partner/manage_profile/update_profile_info' , array('class' => 'form-horizontal form-groups-bordered validate','target'=>'_top' , 'enctype' => 'multipart/form-data'));?>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo get_phrase('full_name');?></label>
                                <div class="col-sm-5">
                                    <input type="text" readonly="readonly" class="form-control" name="username" value="<?php echo $row['username'];?>"/>
                                </div>
                            </div>
                       
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo get_phrase('email');?></label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="email" value="<?php echo $row['email'];?>"/>
                                </div>
                            </div>
                            <!-- added by Onduso on 5/24/2020 Start-->
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo get_phrase('dct_mpesa_short_code');?></label>
                                <div class="col-sm-5">
                                    <input id='dct_mpesa_short_code' type="text"  class="form-control" name="dct_mpesa_short_code" value=""/>
                                </div>
                            </div>
                            <!-- added by Onduso on 5/24/2020  End-->

                            <div class="form-group">
                              <div class="col-sm-offset-3 col-sm-5">
                                  <button type="submit" class="btn btn-info"><?php echo get_phrase('update_profile');?></button>
                              </div>
								</div>
                        </form>
						<?php
                    endforeach;
                    ?>
                </div>
			</div>
			
			<!--End Profile -->
			
			<div class="tab-pane box" id="password" style="padding: 5px">
                <div class="box-content padded">
					<?php 
                    foreach($edit_data as $row):
                        ?>
                        <?php echo form_open(base_url() . 'admin.php/partner/manage_profile/change_password' , array('class' => 'form-horizontal form-groups-bordered validate','target'=>'_top'));?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo get_phrase('current_password');?></label>
                                <div class="col-sm-5">
                                    <input type="password" class="form-control" name="password" value=""/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo get_phrase('new_password');?></label>
                                <div class="col-sm-5">
                                    <input type="password" class="form-control" name="new_password" value=""/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label"><?php echo get_phrase('confirm_new_password');?></label>
                                <div class="col-sm-5">
                                    <input type="password" class="form-control" name="confirm_new_password" value=""/>
                                </div>
                            </div>
                            <div class="form-group">
                              <div class="col-sm-offset-3 col-sm-5">
                                  <button type="submit" class="btn btn-info"><?php echo get_phrase('update_profile');?></button>
                              </div>
								</div>
                        </form>
						<?php
                    endforeach;
                    ?>
                </div>
			</div>
            
            <!-- END PASSWORD --->
 
			
		</div>
	</div>
	
</div>

<script>
	$(document).ready(function(){
		var datatable = $('.table').DataTable();
		
		
		    if (location.hash) {
			        $("a[href='" + location.hash + "']").tab("show");
			    }
			    $(document.body).on("click", "a[data-toggle]", function(event) {
			        location.hash = this.getAttribute("href");
			    });
			});
			$(window).on("popstate", function() {
			    var anchor = location.hash || $("a[data-toggle='tab']").first().attr("href");
			    $("a[href='" + anchor + "']").tab("show");

            
		
	});
    //Added by Onduso on 25/5/2020 start
    $(document).ready(function(){

        //make ajax call and on success assign dct_mpesa_short_code field the short code
        //Then change the field to readonly

        var url="<?=base_url();?>admin.php/partner/check_mpesa_short_code_exists";

        $.get(
            url,
            function(response){
                if(response !=0){
                    $('#dct_mpesa_short_code').attr('value',response);
                    $('#dct_mpesa_short_code').attr('readonly',true);
                }

            }

        );

    });
   //Added by Onduso on 25/5/2020 End
    
</script>