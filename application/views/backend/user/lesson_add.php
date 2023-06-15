<style>
    .video-element > .card-header,
    .video-element > .card-body,
    .video-element > .card-footer {
        padding: 0.5rem;
    }
    .video-element {
        width: 49%;
        display: inline-block;
        vertical-align: top;
        margin-bottom: 0.2rem;
    }
    .selected-video {
        font-weight: bolder;
        color: rebeccapurple;
        border-style: inherit;
    }
    .hide {
        display: none !important;
    }
</style>

<?php
// $param2 = course id
$course_details = $this->crud_model->get_course_by_id($param2)->row_array();
$sections = $this->crud_model->get_section('course', $param2)->result_array();
?>
<form action="<?php echo site_url('user/lessons/'.$param2.'/add'); ?>" method="post" enctype="multipart/form-data">

    <div class="form-group">
        <label><?php echo get_phrase('title'); ?></label>
        <input type="text" name = "title" class="form-control" required>
    </div>

    <input type="hidden" name="course_id" value="<?php echo $param2; ?>">

    <div class="form-group">
        <label for="section_id"><?php echo get_phrase('section'); ?></label>
        <select class="form-control select2" data-toggle="select2" name="section_id" id="section_id" required>
            <?php foreach ($sections as $section): ?>
                <option value="<?php echo $section['id']; ?>"><?php echo $section['title']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="section_id"><?php echo get_phrase('lesson_type'); ?></label>
        <select class="form-control select2" data-toggle="select2" name="lesson_type" id="lesson_type" required onchange="show_lesson_type_form(this.value)">
            <option value=""><?php echo get_phrase('select_type_of_lesson'); ?></option>
            <option value="video-url"><?php echo get_phrase('video'); ?></option>
            <?php if (addon_status('amazon-s3')): ?>
                <option value="s3-video"><?php echo get_phrase('video_file'); ?></option>
            <?php endif;?>
            <option value="other-txt"><?php echo get_phrase('text_file'); ?></option>
            <option value="other-pdf"><?php echo get_phrase('pdf_file'); ?></option>
            <option value="other-doc"><?php echo get_phrase('document_file'); ?></option>
            <option value="other-img"><?php echo get_phrase('image_file'); ?></option>
        </select>
    </div>

    <div class="mb-3" id="video" style="display: none;">
        <div id="video-upload" class="upload-form">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <input type="file" id="video-file-lesson" class="form-control" accept="video/mp4"/>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary" id="upload-file">
                        <i class="fa fa-upload" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col mt-2 mb-3">
                    <div class="progress"><div id="file-progress-bar-lesson" class="progress-bar"></div></div>
                </div>
            </div>
        </div>
        <input type="text" id="video-file-name" name="video_file_name" class="hide" />
    </div>

    <div class="" id = "other" style="display: none;">
        <div class="form-group">
            <label> <?php echo get_phrase('attachment'); ?></label>
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="attachment" name="attachment" onchange="changeTitleOfImageUploader(this)">
                    <label class="custom-file-label" for="attachment"><?php echo get_phrase('attachment'); ?></label>
                </div>
            </div>
        </div>
    </div>

    <div class="" id = "amazon-s3" style="display: none;">
        <div class="form-group">
            <label> <?php echo get_phrase('upload_video_to').' Amazon S3'; ?>( <?php echo get_phrase('for_web_and_mobile_application'); ?> )</label>
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="video_file_for_amazon_s3" name="video_file_for_amazon_s3" onchange="changeTitleOfImageUploader(this)">
                    <label class="custom-file-label" for="video_file_for_amazon_s3"><?php echo get_phrase('select_video_file'); ?></label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label><?php echo get_phrase('duration'); ?>( <?php echo get_phrase('for_web_and_mobile_application'); ?> )</label>
            <input type="text" class="form-control" data-toggle='timepicker' data-minute-step="5" name="amazon_s3_duration" id = "amazon_s3_duration" data-show-meridian="false" value="00:00:00">
        </div>
    </div>

    <div class="form-group">
        <label><?php echo get_phrase('summary'); ?></label>
        <textarea name="summary" class="form-control"></textarea>
        </div>

        <div class="text-center">
            <button class = "btn btn-success" type="submit" name="button"><?php echo get_phrase('add_lesson'); ?></button>
        </div>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        initSelect2(['#section_id','#lesson_type', '#lesson_provider', '#lesson_provider_for_mobile_application']);
        initTimepicker();
    });

    $('#scrollable-modal').on('hidden', function() {
        $('#library_video').html('');
    });
    $(document).on('click', '#upload-file', function() {
        video_ajax_upload_lesson();
    });
    $('#add-lesson-form').submit(function(event) {
        if($('#lesson_type').val() == 'video-url') {
            var video = $('#video-file-name').val();
            if(!video || video == '') {
                event.preventDefault();
                alert('Please upload correct video file');
            }
        }
    });
    
    function show_lesson_type_form(param) {
        var checker = param.split('-');
        var lesson_type = checker[0];
        if (lesson_type === "video") {
            $('#other').hide();
            $('#video').show();
            $('#amazon-s3').hide();
        }else if (lesson_type === "other") {
            $('#video').hide();
            $('#other').show();
            $('#amazon-s3').hide();
        }
        else if (lesson_type === "s3") {
            $('#video').hide();
            $('#other').hide();
            $('#amazon-s3').show();
        }else {
            $('#video').hide();
            $('#other').hide();
            $('#amazon-s3').hide();
        }
    }

    function video_ajax_upload_lesson() {
        var file = $('#video-file-lesson')[0].files[0];
        if(!file) return;

        $.get('<?=site_url('ajax/aws_upload/course');?>', function(data) {
            data = JSON.parse(data);
            AWS.config.update(data.a);
            AWS.config.region = data.b;
            var bucket = new AWS.S3(data.c);

            var params = {
                Key: data.prefix+file.name, ContentType: file.type, Body: file, ACL: 'public-read'
            };

            $("#file-progress-bar-lesson").width('0%');
            $('#video-file-name').val('');

            bucket.upload(params).on('httpUploadProgress', function(evt) {
				var percentComplete = (evt.loaded * 100) / evt.total;
                $('#file-progress-bar-lesson').width(percentComplete+'%');
                $('#file-progress-bar-lesson').html(parseInt(percentComplete)+'%');
			}).send(function(err, data) {
                if(err) {
                    console.log(err);
                    $("#file-progress-bar-lesson").width('0%');
                    $('#video-file-name').val('');
                } else {
                    $('#video-file-name').val(data.Location);
                }
			});
        });
    }
</script>
