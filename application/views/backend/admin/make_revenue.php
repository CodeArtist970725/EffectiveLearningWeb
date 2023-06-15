<style>
    .student-select, .course-select {
        display: flex;
        align-items: baseline;
        justify-content: center;
        padding: 2rem 1rem 2rem 0rem;
    }
    .course-select > label, .student-select > label {
        padding-right: 1rem;
    }
    #course, #student {
        width: 70%;
    }
    .payment-type {
        display: flex;
        align-items: baseline;
        justify-content: center;
        padding: 1.5rem 1rem 2rem 0rem;
    }
    .payment-type > label {
        padding-right: 1rem;
    }
    .admin-revenue, .instructor-revenue, .amount {
        display: flex;
        flex-direction: column;
        margin: 1rem 3rem 3rem 3rem;
        align-items: center;
        align-content: center;
    }
    .payment-amount {
        justify-content: center;
    }
    #payment_type, #amount, #admin_revenue, #instructor_revenue {
        padding: 0.3rem;
        border-radius: 0.8rem;
        outline: none;
    }
    #upload-file {
        float: right;
    }
    .submit {
        display: flex;
        padding: 3rem;
        justify-content: flex-end;
    }
    #submit {
        width: 10rem;
        margin-right: 10rem;
    }
	#attachment-file {
		margin-left: 0.3rem;
	}
</style>

<div class="container">
    <form action="" method="POST" id="make-revenue" class="form">
        <div class="row course-select">
            <label for="course">Course :</label>
            <select name="course_id" id="course" class="custom-select">
                <option value="-1" selected>Select Course</option>
                <?php foreach($courses as $course) { ?>
                <option value="<?=$course->id;?>"><?=$course->title;?></option>
                <?php } ?>
			</select>
		</div>
        <div class="row student-select">
            <label for="student">Student :</label>
            <select name="user_id" id="student" class="custom-select">
                <option value="-1" selected>Select Student</option>
            </select>
        </div>
        <div class="row payment-type">
            <label for="payment_type">Payment Type :</label>
            <input type="text" id="payment_type" name="payment_type" />
        </div>
        <div class="row payment-amount">
            <div class="amount">
                <label for="amount">Total Amount :</label>
                <input type="number" id="amount" name="amount" step="0.001" />
            </div>
            <div class="admin-revenue">
                <label for="admin_revenue">Admin Revenue :</label>
                <input type="number" id="admin_revenue" name="admin_revenue" step="0.001" />
            </div>
            <div class="instructor-revenue">
                <label for="instructor_revenue">Instructor Revenue :</label>
                <input type="number" id="instructor_revenue" name="instructor_revenue" step="0.001" />
            </div>
        </div>
        <div class="row pt-3">
            <div class="col-md-3"></div>
            <div class="attachment col-md-6">
                <div class="row align-items-center" style="width: 100%;">
                    <div class="col-md-10">
                        <input type="file" id="attachment-file" class="form-control" accept="application/pdf" />
                        <input type="text" style="display: none;" name="attachment_name" id="attachment_name" />
                    </div>
                    <div class="col-md-2" style="padding-right: 0px;">
                        <button type="button" class="btn btn-primary" id="upload-file">
                            <i class="fa fa-upload" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col mt-2 mb-3">
                        <div class="progress"><div id="file-progress-bar" class="progress-bar"></div></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="row submit">
            <button class="btn btn-primary" id="submit">Submit</button>
        </div>
    </form>
</div>

<script>
	$(document).ready(function() {
	});
	$('#upload-file').click(function() {
		attachment_file_upload();
	});
	$('#make-revenue').submit(function(event) {
		event.preventDefault();
		if(!form_valid()) return;
		
		var data = {
			course_id: $('#course').val(),
			user_id: $('#student').val(),
			payment_type: $('#payment_type').val(),
			amount: $('#amount').val(),
			admin_revenue: $('#admin_revenue').val(),
			instructor_revenue: $('#instructor_revenue').val(),
			attachment_name: $('#attachment_name').val()
		};
		$.post('<?=site_url('ajax/make_payment_revenue');?>', data, function(res) {
			res = JSON.parse(res);
			if(res.success == true) {
				window.location.href='<?=site_url('admin/instructor_revenue');?>';
			}
		});
	});
	$('#course').change(function() {
		draw_student($(this).val());
	});

	function draw_student(course_id) {
		var student_select = $('#student');
		student_select.html('<option value="-1" selected>Select Student</option>');
		student_select.val(-1);
		if(course_id == -1) return;

		$.get('<?=site_url('ajax/get_students');?>/'+course_id, function(res) {
			res = JSON.parse(res);
			res.forEach(function(item) {
				student_select.append('<option value="'+item.user_id+'">'+item.first_name + ' ' + item.last_name + ' ('+item.user_email+')' + '</option>');
			});
		});
	}

	function attachment_file_upload() {
		if($('#attachment-file')[0].files.length == 0) return;
		var at_name = $('#attachment_name'), progress_bar = $('#file-progress-bar');
        var formdata = new FormData();

		at_name.val('');
        formdata.append('upload_file', $('#attachment-file')[0].files[0]);
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
            url: '<?=site_url('ajax/ajax_upload_file/pdf');?>',
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
					at_name.val(res.filename);
                    alert(res.content);
                } else {
                    alert(res.content);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
				at_name.val('');
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }

	function form_valid() {
		if($('#course').val() == -1) {
			alert('Please select Course');
			return false;
		}
		if($('#student').val() == -1) {
			alert('Please select Student');
			return false;
		}
		var inputs = ['payment_type', 'amount', 'admin_revenue', 'instructor_revenue'];
		var flg = true;
		inputs.forEach(function(x) {
			if(!flg) return;
			if(!$('#'+x).val() || $('#'+x).val() == '') {
				alert('Please input '+x);
				flg = false;
			}
		});

		var total = parseFloat($('#amount').val()), admin_rev = parseFloat($('#admin_revenue').val()), ins_rev = parseFloat($('#instructor_revenue').val());
		if(total < admin_rev + ins_rev) {
			alert('Total amount should not be smaller than the sum of Admin Revenue and Instructor Revenue');
			return false;
		}

		return flg;
	}
</script>