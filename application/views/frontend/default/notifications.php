<style>
    .hide {
        display: none !important;
    }
</style>

<div class="notifications-box menu-icon-box">
    <div class="icon">
        <a href=""><i class="far fa-bell"></i></a>
        <span class="noti_number number hide">2</span>
    </div>
    <div class="dropdown notifications-list-dropdown corner-triangle top-right">
        <div class="notifications-head">
            Notifications
        </div>
        <div class="list-wrapper">
            <div class="notification-list">
                <ul>
                    <li>
                        <a href="">
                            <div class="notification clearfix">
                                <div class="notification-image">
                                    <img src="<?php echo base_url().'assets/frontend/img/author.jpg';?>" alt="" class="img-fluid">
                                </div>
                                <div class="notification-details">
                                    <p class="notification-text">
                                        Daragh Walsh made an announcement: <b>Please tick all the subjects you would like to learn about in the form below. Thanks in advance! https://goo.gl/forms/6r3tZjtXlpD31jju1</b>
                                    </p>
                                    <p class="notification-time">14 days ago</p>
                                </div>
                                <div class="mark-as-read" data-toggle="tooltip" data-placement="bottom" title="Mark as Read"></div>
                            </div>
                        </a>
                    </li>

                    <li>
                        <a href="">
                            <div class="notification clearfix">
                                <div class="notification-image">
                                    <img src="<?php echo base_url().'assets/frontend/img/author.jpg';?>" alt="" class="img-fluid">
                                </div>
                                <div class="notification-details">
                                    <p class="notification-text">
                                        Daragh Walsh made an announcement: <b>Please tick all the subjects you would like to learn about in the form below. Thanks in advance! https://goo.gl/forms/6r3tZjtXlpD31jju1</b>
                                    </p>
                                    <p class="notification-time">14 days ago</p>
                                </div>
                                <div class="mark-as-read marked"></div>
                            </div>
                        </a>
                    </li>

                </ul>
            </div>
            <div class="notifications-footer clearfix">
                <button type="button" class="mark-all-read">Mark All as Read</button>
            </div>
        </div>
        <div class="empty-box text-center d-none">
            <p>No notifications.</p>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/5.1/pusher.min.js"></script>
<script>
    Pusher.logToConsole = false;
    var pusher = new Pusher('e5402ab131348277484a', {
        cluster: 'us2',
        forceTLS: true
    });
    var channel = pusher.subscribe('notifications');
    channel.bind('<?=$this->session->user_id;?>', function(data) {
        read_notifications();
    });

    var noti_ids = [];

    $(document).ready(function() {
        read_notifications();
    });
    $('.notification-list').delegate('.mark-as-read', 'click', function(event) {
        event.preventDefault();
        var id = $(this).attr('data-id');
        mark_notification(id, function() {
            read_notifications();
        });
    });
    $('.mark-all-read').click(function() {
        for(var i = 0; i < noti_ids.length; i++) {
            if(i == noti_ids.length - 1) {
                mark_notification(noti_ids[i], function() {
                    read_notifications();
                });
            } else {
                mark_notification(noti_ids[i]);
            }
        }
    });

    function read_notifications() {
        $.get('<?=site_url('forum/get_all_notifications');?>', function(data) {
            data = JSON.parse(data);
            noti_ids = [];

            if(data.length > 0) {
                $('.noti_number').removeClass('hide');
                $('.noti_number').text(data.length);
            }
            else {
                $('.noti_number').addClass('hide');
            }

            var board = $('.notification-list > ul');
            board.html('');
            data.forEach(function(row) {
                noti_ids.push(row.id);
                var str = calc_noti_element(row);
                board.append(str);
            });
        });
    }

    function calc_noti_element(data) {
        var ans = '<li data-id="'+data.id+'"><a href="'+data.link+'">';
            ans += '<div class="notification clearfix"><div class="notification-image">';
            ans += '<img src="<?php echo base_url().'assets/frontend/img/author.jpg';?>" alt="" class="img-fluid"></div>';
            ans += '<div class="notification-details">';
            ans += '<p class="notification-text">';
            ans += '<b>'+data.title+'</b><br>'+data.body;
            ans += '</p>';
            ans += '<p class="notification-time">'+data.created_at+'</p>';
            ans += '</div>';
            ans += '<div data-id="'+data.id+'" class="mark-as-read" data-toggle="tooltip" data-placement="bottom" title="Mark as Read"></div>'
            ans += '</div></a></li>';
        return ans;
    }

    function mark_notification(id, callback = function() {}) {
        $.get('<?=site_url('forum/remove_notification');?>/'+id, function() {
            callback();
        });
    }
</script>