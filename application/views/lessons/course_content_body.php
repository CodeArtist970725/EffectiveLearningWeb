<div class="col-lg-9  order-md-1 course_col" id = "video_player_area">
    <!-- <div class="" style="background-color: #333;"> -->
    <div class="" style="text-align: center;">
        <?php
            $lesson_details = $this->crud_model->get_lessons('lesson', $lesson_id)->row_array();
            $lesson_thumbnail_url = $this->crud_model->get_lesson_thumbnail_url($lesson_id);
            $opened_section_id = $lesson_details['section_id'];
            // If the lesson type is video
            // i am checking the null and empty values because of the existing users does not have video in all video lesson as type
            if($lesson_details['lesson_type'] == 'video' || $lesson_details['lesson_type'] == '' || $lesson_details['lesson_type'] == NULL):
        ?>
            <!------------- PLYR.IO ------------>
            <link rel="stylesheet" href="<?php echo base_url();?>assets/global/plyr/plyr.css">
            <video poster="<?php echo $lesson_thumbnail_url;?>" id="player" playsinline controls>
                <source src="<?php echo $lesson_details['video_url']; ?>" type="video/mp4">
                <h4><?php get_phrase('video_url_is_not_supported'); ?></h4>
            </video>

            <script src="<?php echo base_url();?>assets/global/plyr/plyr.js"></script>
            <script>const player = new Plyr('#player');</script>
            <!------------- PLYR.IO ------------>

        <?php elseif ($lesson_details['lesson_type'] == 'quiz'): ?>
            <div class="mt-5">
                <?php include 'quiz_view.php'; ?>
            </div>
        <?php else: ?>
            <div class="mt-5">
                <a href="<?php echo base_url().'uploads/lesson_files/'.$lesson_details['attachment']; ?>" class="btn btn-sign-up" download style="color: #fff;">
                    <i class="fa fa-download" style="font-size: 20px;"></i> <?php echo get_phrase('download').' '.$lesson_details['title']; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="" style="margin: 20px 0;" id = "lesson-summary">
        <div class="card">
            <?php if(!$is_free_course) { ?>
            <div class="card-header">
                <ul class="nav nav-tabs" id="note_qa" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="note_nav" role="tab">Note</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="qa_nav" role="tab">Q & A</a>
                    </li>
                </ul>
            </div>
            <?php } ?>
            <div class="card-body">
                <h5 class="card-title"><?php echo $lesson_details['lesson_type'] == 'quiz' ? get_phrase('instruction') : get_phrase("note"); ?>:</h5>
                <?php if ($lesson_details['summary'] == ""): ?>
                    <p class="card-text"><?php echo $lesson_details['lesson_type'] == 'quiz' ? get_phrase('no_instruction_found') : get_phrase("no_summary_found"); ?></p>
                <?php else: ?>
                    <p class="card-text"><?php echo $lesson_details['summary']; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
