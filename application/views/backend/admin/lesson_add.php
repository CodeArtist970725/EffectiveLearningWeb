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
<form id="add-lesson-form" action="<?php echo site_url('admin/lessons/'.$param2.'/add'); ?>" method="post" enctype="multipart/form-data">

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

    <div class="mb-1" id="video" style="display: none;">
        <ul class="nav nav-pills nav-justified mb-1">
            <li class="nav-item">
                <a href="#upload_video" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2 active"><?=get_phrase('upload_video')?></a>
            </li>
            <li class="nav-item">
                <a href="#library_video" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"><?=get_phrase('library_video')?></a>
            </li>
        </ul>
        <div class="tab-content b-0 mb-0 p-1">
            <div class="tab-pane active" id="upload_video">
                <div id="video-upload" class="upload-form">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <input type="file" id="video-file" class="form-control" accept="video/mp4"/>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" id="upload-file">
                                <i class="fa fa-upload" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col mt-2 mb-3">
                        <div class="progress"><div id="file-progress-bar" class="progress-bar"></div></div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="library_video"></div>
            <input type="text" id="selected-video-file-name" name="video_file_name" class="hide" />
        </div>
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

        load_media_library();
    });

    $('#library_video').delegate('.card-header a', 'click', function(event) {
        event.preventDefault();
        $('#library_video .card-header a').removeClass('selected-video');
        $(this).addClass('selected-video');
    });
    $('#scrollable-modal').on('hidden', function() {
        $('#library_video').html('');
    });
    $('#upload-file').click(function() {
        video_ajax_upload();
    });
    $('#add-lesson-form').submit(function(event) {
        if($('#lesson_type').val() == 'video-url') {
            var selected_video = $(this).find('.selected-video');
            if(selected_video == null || selected_video.attr('filename') == null) {
                alert('Please selected video');
                event.preventDefault();
                return;
            }
            $('#selected-video-file-name').val(selected_video.attr('filename'));
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

    function load_media_library() {
        $.get('<?=site_url('ajax/load_video_files');?>', function(res) {
            res = JSON.parse(res);
            $('#library_video').html('');
            res.forEach(function(item) {
                var video = calc_video_element(item);
                $('#library_video').append(video);
            });
        });
    }

    function calc_video_element(item) {
        var ans = '<div class="video-element card">';
        ans += '<div class="card-header"><a href="#" filename="'+item.filename+'">'+item.filename+'</a></div>';
        ans += '<div class="card-body">';
        ans += '<video width="195" height="140" controls>';
        ans += '<source src="'+item.url+'" type="video/mp4">';
        ans += 'Your browser does not support the video tag.';
        ans += '</video></div></div>';
        return ans;
    }

    function video_ajax_upload() {
        var formdata = new FormData();
        formdata.append('upload_file', $('#video-file')[0].files[0]);
        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(element) {
                    if(element.lengthComputable) {
                        var percentComplete = ((element.loaded / element.total) * 100);
                        $('#file-progress-bar').width(percentComplete+'%');
                        $('#file-progress-bar').html(percentComplete.toFixed(2)+'%');
                    }
                }, false);
                return xhr;
            },
            type: 'POST',
            url: '<?=site_url('ajax/ajax_upload_file/video');?>',
            data: formdata,
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',

            beforeSend: function() {
                $("#file-progress-bar").width('0%');
            },
            success: function(res) {
                if(res.success == true) {
                    load_media_library();
                    $('a[href="#library_video"]').click();
                } else {
                    alert(res.content);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }
</script>
