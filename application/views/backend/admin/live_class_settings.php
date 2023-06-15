<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('live_class_settings'); ?></h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?php echo get_phrase('zoom_account_settings');?></h4>

                    <form class="required-form" action="<?php echo site_url('addons/liveclass/settings/update'); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="zoom_api_key"><?php echo get_phrase('zoom_api_key'); ?><span class="required">*</span></label>
                            <input type="text" name = "zoom_api_key" id = "zoom_api_key" class="form-control" value="<?php echo get_settings('zoom_api_key');  ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="zoom_secret_key"><?php echo get_phrase('zoom_secret_key'); ?><span class="required">*</span></label>
                            <input type="text" name = "zoom_secret_key" id = "zoom_secret_key" class="form-control" value="<?php echo get_settings('zoom_secret_key');  ?>" required>
                        </div>
                        
                        <button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo get_phrase('save'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
