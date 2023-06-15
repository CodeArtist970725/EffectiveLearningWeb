<style>
    .header-questions input, .header-questions button {
        border-color: antiquewhite;
    }
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
    .forever-hide {
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
        align-items: baseline;
        padding-top: 2px;
        padding-bottom: 2px;
    }
    .reply-likes {
        display: flex;
        justify-content: space-evenly;
        width: 10%;
        align-items: baseline;
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
    .footer-questions {
        display: flex;
        width: 100%;
        justify-content: flex-end;
    }
    .see-more {
        height: 2rem;
    }
    .replies, .reply {
        padding: 5px;
        margin: 5px;
    }
    #reply_body {
        margin: 5px;
        padding: 0.5rem;
        width: 94.5%;
        font-size: 0.75rem;
    }
    .reply .card-header {
        display: flex;
        justify-content: space-between;
    }
    .reply .card-body {
        padding: 10px 20px;
    }
    .reply pre {
        margin: 0px;
        max-height: 100px;
    }
    .reply .card-footer {
        justify-content: space-between;
        display: flex;
    }
    #note_qa li {
        cursor: pointer;
    }
    .header-r-side {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        width: 20%;
    }
    .edit-delete {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        width: 27%;
    }
</style>

<div class="forum container hide">
    <div class="row header-questions" style="justify-content: space-around;">
        <div class="input-group search-box mobile-search" style="width: 50%">
            <input type="text" name="query" id="query_word" class="form-control" placeholder="Search">
            <div class="input-group-append"><button class="btn"><i class="fas fa-search"></i></button></div>
        </div>
        <button class="btn btn-success post-question"><?php echo get_phrase('post a question')?></button>
    </div>

    <div class="row back-row hide" style="padding-left: 2rem;">
        <a href="" class="back-to-question">Back to Questions</a>
    </div>

    <div class="questions"></div>

    <div class="replies hide"></div>

    <div class="make-reply form-group hide">
        <div class="row" style="justify-content: center">
            <textarea name="reply_body" id="reply_body" rows="7" class="form-control" style="font-size: 0.75rem" require></textarea>
        </div>
        <div class="row mt-2" style="justify-content: flex-end; margin-right: 1rem;">
            <button class="btn btn-success submit-button submit-reply"><?php echo get_phrase('Reply'); ?></button>
        </div>
    </div>

    <div class="row footer-questions">
        <button class="btn btn-success see-more">See More</button>
    </div>

    <div class="make-question-form hide">
        <div class="title-div">
            <input name="title" id="new-question-title" class="title form-control" placeholder="Please input title" style="font-size: small;" require />
            <em class="has-error for-title ml-2"><?php echo get_phrase('*please input title'); ?></em>
        </div>

        <div class="body-div">
            <label for="comment" class="ml-2 mt-3">Question Content:</label>
            <textarea name="body" class="body form-control" rows="5" id="comment" require></textarea>
            <em class="has-error for-body mb-3 ml-2"><?php echo get_phrase('*please input description'); ?></em>
        </div>

        <div class="mt-3 row" style="width: 100%; justify-content: space-evenly;">
            <button class="btn btn-success submit-button" type="submit"><?php echo get_phrase('Submit');?></button>
            <button type="button" class="btn cancel-button"><?php echo get_phrase('Cancel'); ?></button>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/5.1/pusher.min.js"></script>
<script>
    var cur_quesion_cnt = <?= $question_count;?>;
    var selected_question_id = null;
    var update_clicked = false, update_reply = false;

    Pusher.logToConsole = false;
    var pusher = new Pusher('e5402ab131348277484a', {
        cluster: 'us2',
        forceTLS: true
    });
    var channel = pusher.subscribe('make_question');
    channel.bind('new-<?=$course_id;?>', function(data) {
        var str = calc_question(data);
        $('.questions').prepend(str);
    });
    channel = pusher.subscribe('likes');
    channel.bind('question', function(q_id) {
        var obj = $('.likes[q-id='+q_id+'] span');
        obj.text(parseInt(obj.text())+1);
    });
    channel = pusher.subscribe('make_reply');
    channel.bind('for_question', function(data) {
        if(data.question_id != selected_question_id) return;
        var question_user_id = $('.replies .question-user-info').attr('user-id');
        $('.replies').append(calc_reply(data, question_user_id));
    });
    channel = pusher.subscribe('edit-delete');
    channel.bind('edit-question', function(data) {
        get_questions();
        swith_quetion_reply('questions');
    });
    channel.bind('delete-question', function(data) {
        $('.questions .question[q-id='+data+']').remove();
        swith_quetion_reply('question');
    });
    channel.bind('edit-reply', function(data) {
        get_reply(selected_question_id);
    });
    channel.bind('delete-reply', function(data) {
        $('.replies > .reply[reply-id='+data+']').remove();
    });

    $(document).ready(function() {
        var question_id = <?=(empty($question_id) ? 'null' : $question_id);?>;
        var callback = function() {}
        if(question_id != null) callback = function() {
            $('#qa_nav').click();
            $('a.reply[q-id='+question_id+']').click();
        }
        get_questions(callback);

        $('.forum').appendTo($('#lesson-summary'));
    });
    $('.forum').delegate('a', 'click', function(event) {
        event.preventDefault();
    });
    $('#comment').keyup(function() {
        form_valid();
    });
    $('#new-question-title').keyup(function() {
        form_valid();
    });
    $('.cancel-button').click(function() {
        $('#new-question-title').val('');
        $('#comment').val('');
        init_form();
        swith_quetion_reply('question');
    });
    $('.submit-button').click(function(event) {
        if(!form_valid()) return;
        if(update_clicked === false) {
            post_question($('#new-question-title').val(), $('#comment').val(), function() {
                swith_quetion_reply('question');
                init_form();
            });
        } else {
            edit_question(update_clicked, $('#new-question-title').val(), $('#comment').val(), function() {
                update_clicked = false;
            });
        }
    });
    $('.post-question').click(function() {
        update_clicked = false;
        init_form();
        swith_quetion_reply('post');
    });
    $('.see-more').click(function() {
        cur_quesion_cnt += parseInt(cur_quesion_cnt / 2);
        get_questions();
    });
    $('.questions').delegate('.card-header>a', 'click', function() {
        selected_question_id = $(this).attr('q-id');
        get_reply(selected_question_id, function() {
            swith_quetion_reply('reply');
        });
    });
    $('.questions').delegate('.reply-likes .reply', 'click', function() {
        selected_question_id = $(this).attr('q-id');
        get_reply(selected_question_id, function() {
            swith_quetion_reply('reply');
        });
    });
    $('.back-to-question').click(function() {
        swith_quetion_reply('questions');
        update_clicked = false;
        update_reply = false;
    });
    $('.questions').delegate('.reply-likes .likes', 'click', function() {
        var param = {
            'question_id': $(this).attr('q-id')
        };
        $.post('<?=site_url('forum/make_like/0')?>', param, function(res) {
            if(res == 0) alert('You should login');
            else if(res == 2) alert('You already recommeded it!');
        });
    });
    $('.submit-reply').click(function() {
        if($('#reply_body').val().trim() == '') {
            $('.replies').removeClass('hide');
            return;
        }
        if(update_reply == false) {
            post_reply($('#reply_body').val(), function() {
                $('#reply_body').val('');
            });
        } else {
            edit_reply(update_reply, $('#reply_body').val(), function() {
                $('.replies').removeClass('hide');
            });
        }
        update_reply = false;
        init_form();
    });
    $('#query_word').keyup(function() {
        get_questions();
    });
    $('#qa_nav').click(function() {
        $(this).addClass('active');
        $('#note_nav').removeClass('active');
        $('.forum.container').removeClass('hide');
        $('#lesson-summary>.card>.card-body').addClass('hide');
    });
    $('#note_nav').click(function() {
        $(this).addClass('active');
        $('#qa_nav').removeClass('active');
        $('.forum.container').addClass('hide');
        $('#lesson-summary>.card>.card-body').removeClass('hide');
    });
    $('.questions').delegate('.edit-question', 'click', function(event) {
        event.preventDefault();
        update_clicked = $(this).attr('q-id');
        var parent = $(this).parentsUntil('.question.card').parent();
        init_form(parent.find('.question-header').html(), parent.find('.question-body').text());
        swith_quetion_reply('post');
    });
    $('.questions').delegate('.delete-question', 'click', function(event) {
        event.preventDefault();
        delete_question($(this).attr('q-id'));
    });
    $('.replies').delegate('.edit-reply', 'click', function(event) {
        event.preventDefault();
        update_reply = $(this).attr('reply-id');
        var parent = $(this).parentsUntil('.reply.card').parent();
        init_form('', '', parent.find('.reply-body').text());
        $('.replies').addClass('hide');
    });
    $('.replies').delegate('.delete-reply', 'click', function(event) {
        event.preventDefault();
        delete_reply($(this).attr('reply-id'));
    });

    function get_questions(callback = function(){}) {
        var param = {
            'course_id': <?= $course_id;?>,
            'search': $('#query_word').val(),
            'question_count': cur_quesion_cnt
        };
        $.post('<?=site_url('forum/get_questions');?>', param, function(questions) {
            questions = JSON.parse(questions);
            $('.questions').html('');
            if(!questions || questions.length == 0) {
                $('.footer-questions').addClass('forever-hide');
            }
            else {
                $('.footer-questions').removeClass('forever-hide');
            }
            questions.forEach(function(qs) {
                var str = calc_question(qs);
                $('.questions').append(str);
            });

            callback();
        });
    }

    function calc_question(item, flag = false) {
        var user_id = <?=$this->session->user_id;?>;
        var str = '<div class="question card" q-id="'+item.id+'"><div class="card-header">';
            str += flag ? ('<h4 class="question-header">'+item.title+'</h4>') : ('<a class="question-header" href="" q-id='+item.id+'>'+item.title+'</a>');
            if(!flag && user_id == item.user_id) {
                str += '<div class="header-r-side"><div class="question-user-info" user-id="'+item.user_id+'">'+item.user_name+'</div>';
                str += '<div class="edit-delete">';
                str += '<a class="edit-question" href="" q-id='+item.id+'><i class="fa fa-paint-brush" aria-hidden="true"></i></a>';
                str += '<a class="delete-question" href="" q-id='+item.id+'><i class="fa fa-trash" aria-hidden="true"></i></a>';
                str += '</div></div>';
            } else {
                str += '<div class="question-user-info" user-id="'+item.user_id+'">'+item.user_name+'</div>'
            }
            str += '</div>';
            str += '<div class="card-body"><pre class="question-body">'+item.body+'</pre>';
            str += '</div><div class="card-footer">'+
                '<div class="created-time">'+item.created_at+'</div>';
            str += flag ? ('') : ('<div class="reply-likes"><a class="reply" href="" q-id='+item.id+'><i class="fa fa-reply" aria-hidden="true"></i><span>'+item.reply_count+'</span></a>');
            str += '<a class="likes" href="" q-id="'+item.id+'"><i class="fa fa-thumbs-up" aria-hidden="true"></i>'+
                '<span>'+item.likes+'</span></a>'+
            '</div></div></div>';
        return str;
    }

    function swith_quetion_reply(type) {
        if(type && type[0] == 'q') {
            $('.questions').removeClass('hide');
            $('.footer-questions').removeClass('hide');
            $('.replies').addClass('hide');
            $('.back-row').addClass('hide');
            $('.make-question-form').addClass('hide');
            $('.make-reply').addClass('hide');
        }
        else if(type && type[0] == 'r') {
            $('.questions').addClass('hide');
            $('.footer-questions').addClass('hide');
            $('.replies').removeClass('hide');
            $('.back-row').removeClass('hide');
            $('.make-reply').removeClass('hide');
            $('.make-question-form').addClass('hide');
        }
        else if(type && type[0] == 'p') {
            $('.questions').addClass('hide');
            $('.footer-questions').addClass('hide');
            $('.replies').addClass('hide');
            $('.back-row').addClass('hide');
            $('.make-question-form').removeClass('hide');
            $('.make-reply').addClass('hide');
        }
    }

    function get_reply(question_id, callback = function() {}) {
        $.post('<?=site_url('forum/get_question_replies');?>/'+question_id, {}, function(res) {
            res = JSON.parse(res);
            $('.replies').html('');
            var str = calc_question(res, true);
            $('.replies').append(str);

            res.replies.forEach(function(reply) {
                str = calc_reply(reply, res.user_id);
                $('.replies').append(str);
            });
            callback();
        });
    }

    function form_valid() {
        var res = true;
        if($('#new-question-title').val().trim() == '') {
            $('.for-title').removeClass('hide');
            res = false;
        }
        else {
            $('.for-title').addClass('hide');
        }
        if($('#comment').val().trim() == '') {
            $('.for-body').removeClass('hide');
            res = false;
        }
        else {
            $('.for-body').addClass('hide');
        }
        return res;
    }

    function calc_reply(item, question_user_id = null) {
        var user_id = <?=$this->session->user_id;?>;

        var str = '<div class="reply card" reply-id="'+item.id+'"><div class="card-header">';
            str += '<a href="">Replied by '+item.user_name+(item.is_instructor ? ' (instructor)' : '')+'</a>';
            if(user_id && user_id == item.user_id) {
                str += '<div class="edit-delete" style="width: 6%;">';
                str += '<a href="" reply-id="'+item.id+'" class="edit-reply"><i class="fa fa-paint-brush" aria-hidden="true"></i></a>';
                str += '<a href="" reply-id="'+item.id+'" class="delete-reply"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                str += '</div>';
            } else if((user_id && user_id == question_user_id) && !item.is_instructor) {
                str += '<div class="edit-delete" style="width: 5%;">';
                str += '<a href="" reply-id="'+item.id+'" class="delete-reply"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                str += '</div>';
            }
            str += '</div><div class="card-body"><pre class="reply-body">'+item.body+'</pre></div>';
            str += '<div class="card-footer"><div class="created-time">'+item.created_at+'</div>';
            str += '<a class="likes" href="" id="reply-'+item.id+'">';
            str += '<i class="fa fa-thumbs-up" aria-hidden="true"></i>'
            str += '<span>'+item.likes+'</span></a></div></div>';
        return str;
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

    function edit_reply(reply_id, body, callback = function() {}) {
        $.post('<?=site_url('forum/edit_reply');?>/'+reply_id, {'body': body}, function(res) {
            callback();
        });
    }

    function delete_reply(reply_id, callback = function() {}) {
        $.post('<?=site_url('forum/delete_reply');?>/'+reply_id, function(res) {
            callback();
        });
    }

    function init_form(q_title = '', q_body = '', r_body = '') {
        $('#new-question-title').val(q_title);
        $('#comment').val(q_body);
        $('#reply_body').val(r_body);
        form_valid();
    }

    function post_question(title, body, callback = function() {}) {
        var param = {
            'course_id': '<?=$course_id;?>',
            'title': title,
            'body': body
        };
        $.post("<?=site_url('forum/make_question/1')?>", param, function(data) {
            callback();
        });
    }

    function post_reply(body, callback = function() {}) {
        if(!selected_question_id) return;
        $.post('<?=site_url('forum/ajax_make_reply/');?>'+selected_question_id, {'reply_body': body}, function() {
            callback();
        });
    }
</script>