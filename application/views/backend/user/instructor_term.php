<style>
    #term {
        width: 100%;
        padding: 0.4rem;
        height: 17rem;
        border-radius: 1rem;
        margin: 0.1rem 1rem 2rem 1rem;
        border-color: blueviolet;
        outline: none;
    }
    #update-button {
        float: right;
    }
    .term-header {
        margin-top: 2rem;
        margin-left: 1.3rem;
    }
</style>

<div class="container">
    <div class="row form-group">
        <label for="term" class="term-header">Instructor Term : </label>
        <textarea name="term" id="term" disabled><?=($instructor->term_body ?? '');?></textarea>
    </div>
</div>

<script>
    $('#update-button').click(function() {
        $.post('<?=site_url('ajax/update_instructor_term')?>', {term_body: $('#term').val()}, function(res) {
            res = JSON.parse(res);
            alert(res.content);
        });
    });
</script>