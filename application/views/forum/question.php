<style>
    .question {
        padding: 20px 10px 8px 10px;
        margin: 5px;
        background-color: beige;
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
    .submit {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }
    .question .card-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        padding: 12px 20px 1px 20px;
    }
    .question .card-body {
        padding: 10px 20px;
    }
    .question pre {
        margin: 0px;
        max-height: 100px;
    }
    .question .card-header h4 {
        font-size: 1rem;
    }
    .question .card-footer {
        justify-content: space-between;
        display: flex;
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
    .edit-delete {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        width: 4.5%;
    }
</style>

<div class="container">
    <div class="row mt-3">
        <a href="<?php echo site_url('forum');?>" class="ml-4">Back To Questions</a>
    </div>
    <div class="question">
        <div class="card">
            <div class="card-header">
                <h4><?php echo $question->title; ?></h4>
                <a href="#"><?php echo($question->user_name); ?></a>
            </div>
            <div class="card-body">
                <pre><?php echo $question->body; ?></pre>
            </div>
            <div class="card-footer">
                <div class="created-time"><?php echo $question->created_at; ?></div>
                <div class="likes">
                    <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                    <span><?php echo $question->likes; ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="replies">
        <?php foreach($question->replies as $reply) { ?>
            <div class="reply card" reply-id="<?=$reply->id;?>">
                <div class="card-header">
                    <a href="#">
                        <?php echo(get_phrase('Replied by ').$reply->user_name.($reply->is_instructor ? '(instructor)' : '')); ?>
                    </a>
                    <?php if($this->session->userdata('admin_login')) { ?>
                        <a href="" reply-id="<?=$reply->id;?>" class="delete-reply"><i class="fa fa-trash" aria-hidden="true"></i></a>
                    <?php } else if($this->session->user_id == $reply->user_id) { ?>
                        <div class="edit-delete">
                        <a href="" reply-id="<?=$reply->id;?>" class="edit-reply"><i class="fa fa-paint-brush" aria-hidden="true"></i></a>
                        <a href="" reply-id="<?=$reply->id;?>" class="delete-reply"><i class="fa fa-trash" aria-hidden="true"></i></a>
                        </div>
                    <?php } ?>
                </div>
                <div class="card-body">
                    <pre class="reply-body"><?php echo $reply->body; ?></pre>
                </div>
                <div class="card-footer">
                    <div class="created-time"><?php echo $reply->created_at; ?></div>
                    <a class="likes" href="#" id="reply-<?php echo $reply->id;?>">
                        <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                        <span><?php echo $reply->likes; ?></span>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php if ($this->session->userdata('user_login')) { ?>
    <form class="make-reply form-group" action="<?php echo site_url('forum/make_reply/'.$question->id);?>" method="POST">
        <div class="row" style="justify-content: center">
            <textarea name="reply_body" id="reply_body" rows="7" class="form-control" style="font-size: 0.75rem" require></textarea>
        </div>
        <div class="row mt-2" style="justify-content: flex-end; margin-right: 1rem;">
            <button class="btn btn-success submit" type="submit"><?php echo get_phrase('Reply'); ?></button>
        </div>
    </form>
    <?php } ?>
</div>

<script src="https://js.pusher.com/5.1/pusher.min.js"></script>
<script>
    Pusher.logToConsole = false;
    var pusher = new Pusher('e5402ab131348277484a', {
        cluster: 'us2',
        forceTLS: true
    });
    var channel = pusher.subscribe('make_reply');
    channel.bind('<?php echo $question->id;?>', function(data) {
        handle_new_reply(data);
    });
    channel = pusher.subscribe('likes');
    channel.bind('reply', function(qa_id) {
        var obj = $('#reply-'+qa_id).find('span');
        obj.text(parseInt(obj.text()) + 1);
    });
    channel = pusher.subscribe('edit-delete');
    channel.bind('delete-reply', function(data) {
        $('.replies > .reply[reply-id='+data+']').remove();
    });
    channel.bind('edit-reply', function(data) {
        window.location.reload();
    });

    var selected_reply_id = false;

    $('.replies').delegate('.likes', 'click', function(event) {
        event.preventDefault();
        var reply_id = $(this).attr('id');
        reply_id = reply_id.substr(reply_id.lastIndexOf('-')+1);
        $.post('<?php echo site_url('forum/make_like/1');?>', {'question_id': reply_id}, function(res) {
            if(res == 0) alert('Please login first!');
            else if(res == 2) alert('You recommended this question already!');
        });
    });
    $('.make-reply').submit(function(event) {
        if(!$('#reply_body').val() || $('#reply_body').val()=="") {
            alert('Please input content');
            event.preventDefault();
        }
        if(selected_reply_id) {
            event.preventDefault();
            edit_reply(selected_reply_id, $('#reply_body').val());
        }
    });
    $('.replies').delegate('.delete-reply', 'click', function(event) {
        event.preventDefault();
        delete_reply($(this).attr('reply-id'));
    });
    $('.replies').delegate('.edit-reply', 'click', function(event) {
        event.preventDefault();
        selected_reply_id = $(this).attr('reply-id');
        var parent = $(this).parentsUntil('.reply').parent();
        init_reply_form(true, parent.find('.reply-body').text());
    });

    function handle_new_reply(data, just_return = false) {
        var is_admin = <?php echo $this->session->userdata('admin_login') ? 'true' : 'false';?>;
        var current_user = <?=$this->session->user_id;?>;

        var str = '<div class="reply card" reply-id="'+data.id+'">';
            str += '<div class="card-header">';
            str += '<a href="#"><?php echo(get_phrase('Replied by ')); ?>'+data.user_name+(data.is_instructor?'(instructor)':'')+'</a>';
            if(is_admin)  {
                str += '<a href="" reply-id="'+data.id+'" class="delete-reply"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            } else if(current_user == data.user_id) {
                str += '<div class="edit-delete">';
                str += '<a href="" reply-id="'+data.id+'" class="edit-reply"><i class=fa fa-paint-brush" aria-hidden="true"></i></a>';
                str += '<a href="" reply-id="'+data.id+'" class="delete-reply"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                str += '</div>';
            }
            str += '</div>';
            str+= '<div class="card-body">'+
                    '<pre class="reply-body">'+data.body+'</pre>'+
                '</div><div class="card-footer">'+
                    '<div class="created-time">'+data.created_at+'</div>'+
                    '<a class="likes" href="#" id="reply-'+data.id+'"><i class="fa fa-thumbs-up" aria-hidden="true"></i>'+
                    '<span>'+data.likes+'</span></a>'
                '</div></div>';
        if(just_return) return str;
        $('.replies').append(str);
    }

    function delete_reply(reply_id, callback = function() {}) {
        $.post('<?=site_url('forum/delete_reply');?>/'+reply_id, function(res) {
            callback();
        });
    }

    function edit_reply(reply_id, body, callback = function() {}) {
        $.post('<?=site_url('forum/edit_reply');?>/'+reply_id, {'body': body}, function(res) {
            callback();
        });
    }

    function init_reply_form(only_form = false, body = '') {
        if(only_form) $('.replies').addClass('hide');
        else $('.replies').removeClass('hide');
        $('#reply_body').val(body);
    }
</script>