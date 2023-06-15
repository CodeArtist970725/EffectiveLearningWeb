<div class="row ">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-body">
        <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('instructor_revenue'); ?></h4>
      </div> <!-- end card body-->
    </div> <!-- end card -->
  </div><!-- end col-->
</div>

<div class="row" style="width: 100%;overflow: auto;">
  <div class="col-xl-12">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-3 header-title"><?php echo get_phrase('instructor_revenue'); ?></h4>
        <div class="table-responsive-sm mt-4">
          <table id="basic-datatable" class="table table-striped table-centered mb-0">
            <thead>
              <tr>
                <th><?php echo get_phrase('enrolled_course'); ?></th>
                <th><?php echo get_phrase('instructor'); ?></th>
                <th><?php echo get_phrase('instructor_revenue'); ?></th>
                <th><?php echo get_phrase('status'); ?></th>
                <th class="text-center"><?php echo get_phrase('actions'); ?></th>
                <th>Attachment File</th>
                <th>Make Payment</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payment_history as $payment):
                $course_data = $this->db->get_where('course', array('id' => $payment['course_id']))->row_array();
                $user_data = $this->db->get_where('users', array('id' => $course_data['user_id']))->row_array();?>
                <?php
                $paypal_keys          = json_decode($user_data['paypal_keys'], true);
                $stripe_keys          = json_decode($user_data['stripe_keys'], true);
                ?>
                <tr class="gradeU">
					<td>
						<strong><a href="<?php echo site_url('home/course/'.slugify($course_data['title']).'/'.$course_data['id']); ?>" target="_blank"><?php echo $course_data['title']; ?></a></strong><br>
						<small class="text-muted"><?php echo get_phrase('enrolment_date').': '.date('D, d-M-Y', $payment['date_added']); ?></small>
					</td>
					<td><?php echo $user_data['first_name'].' '.$user_data['last_name']; ?></td>
					<td>
						<?php echo currency($payment['instructor_revenue']); ?><br>
						<small class="text-muted"><?php echo get_phrase('total_amount').': '.currency($payment['amount']); ?></small>
					</td>
					<td style="text-align: center;">
						<?php if ($payment['instructor_payment_status'] == 0) { ?>
							<div class="label label-secondary"><?php echo get_phrase('pending'); ?></div>
						<?php } else if($payment['instructor_payment_status'] == 1) { ?>
							<div class="label label-success"><?php echo get_phrase('paid'); ?></div>
						<?php } else { ?>
							<div class="label label-success">Under Review</div>
						<?php } ?>
					</td>
                  	<td style="text-align: center;">
                    <?php if ($payment['instructor_payment_status'] == 0): ?>
						<div class="row align-items-center" style="width: 100%;">
							<div class="col-md-11">
								<input type="file" class="file-attachment" class="form-control" accept=".pdf,.jpg,.png,.jpeg" />
							</div>
							<div class="col-md-1" style="padding: 0px;">
								<button type="button" class="btn btn-primary file-upload-button" p-id="<?=$payment['id'];?>">
									<i class="fa fa-upload" aria-hidden="true"></i>
								</button>
							</div>
						</div>
						<div class="row align-items-center">
							<div class="col mt-2 mb-3">
								<div class="progress"><div class="progress-bar"></div></div>
							</div>
						</div>
                    <?php else: ?>
						<a href="<?php echo site_url('admin/invoice/'.$payment['id']); ?>" class="btn btn-outline-primary btn-rounded btn-sm"><i class="mdi mdi-printer-settings"></i></a>
                    <?php endif; ?>
					</td>
					<td class="file-url">
						<?=($payment['file_url'] ? ('<a href="'.$payment['file_url'].'" download><i class="fa fa-download" aria-hidden="true"></i>'.$payment['attachment_name'].'</a>') : '');?>
					</td>
					<td class="buttons">
						<?php if ($payment['instructor_payment_status'] == 0 && $payment['file_url']) { ?>
							<button class="btn btn-success send-button" p-id="<?=$payment['id'];?>">Send</button>
						<?php } else if ($payment['instructor_payment_status'] != 0) { ?>
							<span>Sent</span>
						<?php } else { ?>
							<span>Waiting</span>
						<?php } ?>
					</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
	$('.send-button').click(function() {
		var id = $(this).attr('p-id');
		var data = {
			instructor_payment_status: 2
		};
		$.post('<?=site_url('ajax/update_payment/');?>/'+id, data, function(res) {
			res = JSON.parse(res);
			if(res.success) window.location.reload();
		});
	});

	$('.file-upload-button').click(function() {
		var payment_id = $(this).attr('p-id');
		if(payment_id == '') return;
		var parent = $(this).parentsUntil('tr').parent();
		var fileinput = parent.find('.file-attachment');
		if(fileinput[0].files.length == 0) return;
		var progress_bar = parent.find('.progress-bar');
        var formdata = new FormData();

        formdata.append('upload_file', fileinput[0].files[0]);
		var ext = fileinput[0].files[0].name.substring(fileinput[0].files[0].name.lastIndexOf(".")+1);
		ext = ext.toLowerCase();

        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(element) {
                    if(element.lengthComputable) {
                        var percentComplete = ((element.loaded / element.total) * 100);
                        progress_bar.width(percentComplete+'%');
                        progress_bar.html(percentComplete.toFixed(2)+'%');
                    }
                }, false);
                return xhr;
            },
            type: 'POST',
            url: '<?=site_url('ajax/ajax_upload_file');?>/'+ext,
            data: formdata,
            contentType: false,
            cache: false,
            processData: false,
            dataType: 'json',

            beforeSend: function() {
                progress_bar.width('0%');
            },
            success: function(res) {
                if(res.success == true) {
					update_payment_file(payment_id, res.filename, function(ans) {
						ans = JSON.parse(ans);
						if(ans.success) window.location.reload();
					});
                } else {
                    alert(res.content);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    });

	function update_payment_file(id, filename, callback = function(para) {}) {
		$.post('<?=site_url('ajax/update_payment/');?>/'+id, {attachment_name: filename}, function(res) {
			callback(res);
		});
	}
</script>