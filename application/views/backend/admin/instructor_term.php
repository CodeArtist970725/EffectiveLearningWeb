<style>
    .items {
        display: flex;
        width: 100%;
        padding: 1rem 3rem 3rem 3rem;
        justify-content: space-around;
        flex-wrap: wrap;
    }
    .item {
        display: flex;
        flex-direction: column;
        width: 50%;
        padding: 1rem;
    }
    .item .title {
        font-size: smaller;
        padding-bottom: 0.2rem;
        color: burlywood;
    }
    .item .body {
        font-size: medium;
        padding-bottom: 1rem;
        color: black;
    }
    .pre {
        padding: 2rem 3rem 0rem 3rem;
    }
    .pre .title {
        font-size: smaller;
        padding-bottom: 0.2rem;
        color: burlywood;
    }
    .pre .body {
        font-size: medium;
        padding-bottom: 1rem;
        color: black;
    }
    .term-title {
        text-align: center;
        margin-top: 2rem;
        color: darkgreen;
    }
</style>

<div class="container">
    <h4 class="term-title">Instructor Bank Information</h4>
    <div class="items">
        <div class="item">
            <div class="title">Name</div>
            <div class="body"><?=$instructor->nick_name?></div>
        </div>
        <div class="item">
            <div class="title">NRIC Number</div>
            <div class="body"><?=$instructor->nric_number?></div>
        </div>
        <div class="item">
            <div class="title">Phone Number 1</div>
            <div class="body"><?=$instructor->phone_one?></div>
        </div>
        <div class="item">
            <div class="title">Phone Number 2</div>
            <div class="body"><?=$instructor->phone_two?></div>
        </div>
        <div class="item">
            <div class="title">KBZ Bank</div>
            <div class="body"><?=$instructor->kbz_bank_number?></div>
        </div>
        <div class="item">
            <div class="title">AYA Bank</div>
            <div class="body"><?=$instructor->aya_bank_number?></div>
        </div>
        <div class="item">
            <div class="title">CB Bank</div>
            <div class="body"><?=$instructor->cb_bank_number?></div>
        </div>
        <div class="item">
            <div class="title">MAB Bank</div>
            <div class="body"><?=$instructor->mab_bank_number?></div>
        </div>
    </div>
</div>