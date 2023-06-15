<style>
    .phone {
        padding: 0.5rem;
        width: 100%;
        display: flex;
        flex-direction: column;
    }
    .phone-body {
        display: flex;
        justify-content: space-between;
        padding: 0.1rem 1rem 1rem 1rem;
    }
    .phone-number {
        width: 50%;
        border-style: groove;
        border-radius: 0.2rem;
        border-color: floralwhite;
        outline: none;
        padding: 0.2rem;
    }
    .banks {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        padding: 1.5rem 1rem 1rem 1rem;
    }
    .bank {
        width: 50%;
        padding: 2rem 0.5rem 2rem 0.5rem;
        align-items: center;
        align-content: center;
    }
    .name-number {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        padding: 1.5rem 1rem 1rem 1rem;
    }
    .bank-number {
        width: 100%;
        border-style: groove;
        border-radius: 0.2rem;
        border-color: floralwhite;
        outline: none;
        padding: 0.2rem;
    }
    #submit-button {
        float: right;
    }
    #phone_one {
        margin-right: 0.5rem;
    }
    #phone_two {
        margin-left: 0.5rem;
    }
</style>

<div class="container" style="padding-top:2rem;">
    <div class="name-number">
        <div class="bank">
            <div class="bank-title">
                <label class="col-md-12">Name</label>
            </div>
            <div class="bank-body">
                <input type="text" class="bank-number" id="nick_name" value="<?=($instructor->nick_name ?? '');?>" />
            </div>
        </div>
        <div class="bank">
            <div class="bank-title">
                <label class="col-md-12">NRIC Number</label>
            </div>
            <div class="bank-body">
                <input type="text" class="bank-number" id="nric_number" value="<?=($instructor->nric_number ?? '');?>" />
            </div>
        </div>
    </div>
    <div class="phone">
        <div class="phone-title">
            <label class="col-md-12">Instructor Phone Numbers</label>
        </div>
        <div class="phone-body">
            <input type="text" class="phone-number" id="phone_one" value="<?=($instructor->phone_one ?? '');?>" />
            <input type="text" class="phone-number" id="phone_two" value="<?=($instructor->phone_two ?? '');?>" />
        </div>
    </div>
    <div class="banks">
        <div class="bank">
            <div class="bank-title">
                <label class="col-md-12">KBZ Bank</label>
            </div>
            <div class="bank-body">
                <input type="text" class="bank-number" id="kbz_bank_number" value="<?=($instructor->kbz_bank_number ?? '');?>" />
            </div>
        </div>
        <div class="bank">
            <div class="bank-title">
                <label class="col-md-12">AYA Bank</label>
            </div>
            <div class="bank-body">
                <input type="text" class="bank-number" id="aya_bank_number" value="<?=($instructor->aya_bank_number ?? '');?>" />
            </div>
        </div>
        <div class="bank">
            <div class="bank-title">
                <label class="col-md-12">CB Bank</label>
            </div>
            <div class="bank-body">
                <input type="text" class="bank-number" id="cb_bank_number" value="<?=($instructor->cb_bank_number ?? '');?>" />
            </div>
        </div>
        <div class="bank">
            <div class="bank-title">
                <label class="col-md-12">MAB Bank</label>
            </div>
            <div class="bank-body">
                <input type="text" class="bank-number" id="mab_bank_number" value="<?=($instructor->mab_bank_number ?? '');?>" />
            </div>
        </div>
        <div class="col-md-11 mt-3">
            <button class="btn btn-primary" id="submit-button">Update</button>
        </div>
    </div>
</div>

<script>
    $('#submit-button').click(function() {
        var data = {
            nick_name: $('#nick_name').val(),
            nric_number: $('#nric_number').val(),
            phone_one: $('#phone_one').val(),
            phone_two: $('#phone_two').val(),
            kbz_bank_number: $('#kbz_bank_number').val(),
            aya_bank_number: $('#aya_bank_number').val(),
            cb_bank_number: $('#cb_bank_number').val(),
            mab_bank_number: $('#mab_bank_number').val(),
        }
        $.post('<?=site_url('ajax/update_instructor_term')?>', data, function(res) {
            res = JSON.parse(res);
            alert(res.content);
        });
    });
</script>