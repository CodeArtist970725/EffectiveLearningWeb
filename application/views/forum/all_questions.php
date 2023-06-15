<style>
    .post-question, .post-question:hover {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .hide {
        display: none !important;
    }
    textarea.body {
        width: 100%;
    }
    textarea.title {
        width: 100%;
    }
    .has-error {
        color: red;
    }
    .forum {
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .question {
        margin: 10px;
        border-color: darkseagreen;
    }
    .question > .card-header {
        display: flex;
        justify-content: space-between;
    }
    .question > .card-body {
        padding: 10px 20px;
    }
    .question pre {
        margin: 0px;
        max-height: 100px;
    }
    .question > .card-footer {
        display: flex;
        justify-content: space-between;
        padding-left: 20px;
        padding-right: 20px;
    }
    .reply-likes {
        display: flex;
        justify-content: space-evenly;
        width: 10%;
    }
    .header-questions input, .header-questions button {
        border-color: antiquewhite;
    }
    .footer-questions {
        display: flex;
        width: 100%;
        justify-content: flex-end;
    }
    .submit-button {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }
    .submit-button:hover {
        color: #fff;
        background-color: #2e9b47;
        border-color: #25893c;
    }
    .submit-button:left {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }
    .make-question-form {
        margin-top: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .course-dropdown {
        width: 100%;
        display: flex;
        flex-direction: column;
        padding: 0.3rem;
        margin-bottom: 0.5rem;
    }
    .course {
        width: 30%;
        border-radius: 0.2rem;
        height: 1.5rem;
        padding-left: 0.3rem;
    }
    .course option {
        width: 30%;
        border-radius: 0.2rem;
        height: 1.5rem;
        padding-left: 0.3rem;
    }
    .search-course {
        margin: 0.5rem;
        border-radius: 0.2rem;
        width: 8rem;
    }
    .question-header {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        width: 10%;
    }
</style>

<div class="forum container">
    <div class="row header-questions" style="justify-content: space-around;">
        <div class="input-group search-box mobile-search" style="width: 50%">
            <input type="text" name="query" id="query_word" class="form-control" placeholder="Search">
            <div class="input-group-append"><button class="btn"><i class="fas fa-search"></i></button></div>
        </div>

        <select class="search-course" name="course_id">
            <option value="-1" selected>All of your course</option>
            <?php foreach($courses as $course) { ?>
                <option value="<?php echo $course->id;?>"><?php echo $course->title;?></option>
            <?php } ?>
        </select>

        <button class="btn btn-success post-question <?=($this->session->userdata('admin_login') ? 'hide' : '');?>"><?php echo get_phrase('post a question')?></button>
    </div>
    <div class="questions">
    </div>
    <div class="row footer-questions">
        <button class="btn btn-success see-more">See More</button>
    </div>

    <form action="<?php echo site_url('forum/make_question'); ?>" method="POST" class="make-question-form">
        <div class="dropdown course-dropdown">
            <select class="course" name="course_id">
                <option value="-1" selected>Select Your Course</option>
                <?php foreach($courses as $course) { ?>
                    <option value="<?php echo $course->id;?>"><?php echo $course->title;?></option>
                <?php } ?>
            </select>
            <em class="has-error for-course ml-2">*Please Select Course</em>
        </div>

        <input name="title" class="title form-control" rows="1" placeholder="Please input title" style="font-size: small;" require />
        <em class="has-error for-title ml-2"><?php echo get_phrase('*please input title'); ?></em>

        <label for="comment" class="ml-2 mt-3">Question Content:</label>
        <textarea name="body" class="body form-control" rows="5" id="comment" require></textarea>
        <em class="has-error for-body mb-3 ml-2"><?php echo get_phrase('*please input description'); ?></em>

        <div class="mt-3 row" style="width: 100%; justify-content: space-evenly;">
            <button class="btn btn-success submit-button" type="submit"><?php echo get_phrase('Submit');?></button>
            <button type="button" class="btn cancel-button"><?php echo get_phrase('Cancel'); ?></button>
        </div>
    </form>
</div>

<?php
$course_ids = [];
foreach($courses as $course) array_push($course_ids, $course->id);
?>

<script src="https://js.pusher.com/5.1/pusher.min.js"></script>
<script>
    var allow_course = <?php echo('['.implode(",", $course_ids).']'); ?>;
    var selected_question = null;

    Pusher.logToConsole = false;
    var pusher = new Pusher('e5402ab131348277484a', {
        cluster: 'us2',
        forceTLS: true
    });
    var channel = pusher.subscribe('make_question');
    channel.bind('new', function(data) {
        if(allow_course.indexOf(parseFloat(data.course_id)) == -1) return;
        handle_new_question(data);
    });
    channel = pusher.subscribe('likes');
    channel.bind('question', function(q_id) {
        var obj = $('#question_for-'+q_id+' span');
        obj.text(parseInt(obj.text())+1);
    });
    channel = pusher.subscribe('edit-delete');
    channel.bind('edit-question', function(data) {
        get_questions();
        swith_question_form('question');
    });
    channel.bind('delete-question', function(data) {
        $('.questions .question[q-id='+data+']').remove();
        swith_question_form('question');
    });

    var current_cnt = 10;

    $(document).ready(function() {
        $('.make-question-form').addClass('hide');
        get_questions();
    });
    $('.post-question').click(function() {
        swith_question_form('post');
    });
    $('.cancel-button').click(function() {
        swith_question_form('question');
        selected_question = null;
        init_form();
    });
    $('.make-question-form').submit(function(event) {
        if(!post_form_valid()) {
            event.preventDefault();
            return;
        }
        if(selected_question != null) {
            event.preventDefault();
            edit_question(selected_question, $('input.title[name=title]').val(), $('#comment').val());
        }
    });
    $('.make-question-form .title').keyup(function() {
        post_form_valid();
    });
    $('.make-question-form .body').keyup(function() {
        post_form_valid();
    });
    $('.make-question-form .course').change(function() {
        post_form_valid();
    });
    $('.see-more').click(function() {
        current_cnt += parseInt(current_cnt / 2);
        get_questions();
    });
    $('#query_word').keyup(function() {
        get_questions();
    });
    $('.search-course').change(function() {
        get_questions();
    });
    $('.questions').delegate('.reply-likes > .likes', 'click', function(event) {
        event.preventDefault();
        var question_id = $(this).attr('id');
        question_id = question_id.substr(question_id.lastIndexOf("-")+1);
        $.post('<?php echo site_url('forum/make_like/0');?>', {'question_id': question_id}, function(res) {
            if(res == 0) alert('Please login first!');
            else if(res == 2) alert('You recommended this question already!');
        });
    });
    $('.questions').delegate('.edit-question', 'click', function(event) {
        event.preventDefault();
        selected_question = $(this).attr('q-id');
        var parent = $(this).parentsUntil('.question').parent();
        var course_id = parent.attr('course-id');
        init_form(course_id, parent.find('.question-title').html(), parent.find('.question-body').text());
        swith_question_form('post');
    });
    $('.questions').delegate('.delete-question', 'click', function(event) {
        event.preventDefault();
        delete_question($(this).attr('q-id'));
    });

    function post_form_valid() {
        var flg = true, valid = ['title', 'body'];
        valid.forEach(function(t) {
            if($('.make-question-form .'+t).val().length == 0) {
                flg = false;
                $('.make-question-form .for-'+t).removeClass('hide');
            }
            else {
                $('.make-question-form .for-'+t).addClass('hide');
            }
        });
        if($('.make-question-form .course').val() == -1) {
            $('.make-question-form .for-course').removeClass('hide');
            flg = false;
        }
        else {
            $('.make-question-form .for-course').addClass('hide');
        }
        return flg;
    }
    function swith_question_form(para='q') {
        if(para[0] == 'q') {
            $('.questions').removeClass('hide');
            $('.footer-questions').removeClass('hide');
            $('.make-question-form').addClass('hide');
        } else if(para[0] == 'p') {
            $('.questions').addClass('hide');
            $('.footer-questions').addClass('hide');
            $('.make-question-form').removeClass('hide');
        }
    }

    function init_form(selected_course = -1, title = '', body = '') {
        if(selected_course != -1) {
            $('select.course[name=course_id]').attr('disabled', 'true');
        } else {
            $('select.course[name=course_id]').removeAttr('disabled');
        }
        $('select.course[name=course_id]').val(selected_course);
        $('input.title[name=title]').val(title);
        $('#comment').val(body);
        post_form_valid();
    }

    function handle_new_question(data, just_return = false) {
        var is_admin = <?php echo $this->session->userdata('admin_login') ? 'true' : 'false';?>;
        var current_user = <?=$this->session->user_id;?>;

        var str = '<div class="question card" q-id="'+data.id+'" course-id="'+data.course_id+'"><div class="card-header">';
            str += '<a class="question-title" href="<?php echo site_url('forum/question/'); ?>'+data.id+'">'+data.title+'</a>';
            if(is_admin) {
                str += '<div class="question-header">';
                str += '<div class="question-user-name">'+data.user_name+'</div>';
                str += '<a href="" class="delete-question" q-id="'+data.id+'"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                str += '</div>';
            } else if(current_user == data.user_id) {
                str += '<div class="question-header">';
                str += '<div class="question-user-name">'+data.user_name+'</div>';
                str += '<a href="" class="edit-question" q-id="'+data.id+'"><i class="fa fa-paint-brush" aria-hidden="true"></i></a>';
                str += '<a href="" class="delete-question" q-id="'+data.id+'"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                str += '</div>';
            } else {
                str += '<span>'+data.user_name+'</span>';
            }
            str += '</div>';
            str += '<div class="card-body">'+
                    '<pre class="question-body">'+data.body+'</pre>'+
                '</div><div class="card-footer">'+
                    '<div class="created-time">'+data.created_at+'</div><div class="reply-likes">'+
                    '<a class="reply" href="<?php echo site_url('forum/question/');?>'+data.id+'"><i class="fa fa-reply" aria-hidden="true"></i>'+
                    '<span>'+data.reply_count+'</span></a>'+
                    '<a class="likes" href="#" id="question_for-"'+data.id+'><i class="fa fa-thumbs-up" aria-hidden="true"></i>'+
                        '<span>'+data.likes+'</span></a>'+
                '</div></div>';
        if(just_return) return str;
        $('.questions').prepend(str);
    }

    function get_questions() {
        var para = {
            'count': current_cnt,
            'search_word': $('#query_word').val(),
            'course_id': $('.search-course').val()
        };
        $.post('<?php echo site_url('forum/search');?>', para, function(res) {
            res = JSON.parse(res);
            if(!res || res.length == 0) $('.see-more').addClass('hide');
            var question_body = $('.questions');
            question_body.html('');
            res.forEach(function(q) {
                var str = handle_new_question(q, true);
                question_body.append(str);
            });
        });
    }

    function edit_question(question_id, title, body, callback = function() {}) {
        var data = {
            "title": title,
            "body": body
        };
        $.post('<?=site_url('forum/edit_question');?>/'+question_id, data, function(res) {
            callback();
        });
    }

    function delete_question(question_id, callback = function() {}) {
        $.get('<?=site_url('forum/delete_question');?>/'+question_id, function(res) {
            callback();
        });
    }
</script>