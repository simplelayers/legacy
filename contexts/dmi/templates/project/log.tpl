<!--{if isset($subnav)}--><!--{$subnav}--><!--{/if}-->
<script>
    $(() => {
        $('.contentarea').toggleClass('flex-content');

    });
</script>
<style>
    .table-container {
        position: relative;
        padding: 0px;
        width: 100%;
        height: calc(100vh - 22.5em);
        overflow:auto;
        border: 1px inset black;

    }

    .table-container table thead tr th {
        border-top: 0px solid #fff;
        top:-1px;
        position: -webkit-sticky;
        position: -moz-sticky;
        position: -ms-sticky;
        position: -o-sticky;
        position: sticky;
    }
    .table-container table {
        border: 0px;
        border-collapse:collapse;
    }
    .log-table {
        width: 100%;

    }
    .input-group label {
        margin-left: 1em !important;
        margin-right: 1em;
    }
    .input-group label:first-child {
        margin-left:0em !important;
    }
</style>
<form action="." method="post">
    <input type="hidden" name="do" value="project.log"/>
    <div class="flex-inline">
        <div class="form-group input-group flex-row">
            <label class="form-label">Map to show:</label>
            <!--{html_options class="form-select fitted" values=$mapOptions.keys output=$mapOptions.names selected=$selectedMap name=id}-->

        </div>
        <div class="form-group input-group flex-row">
            <label class="form-label">Show the last </label>
            <!--{html_options class="form-select fitted" values=$howmany_choices output=$howmany_choices selected=$howmany name=howmany}-->
            <label class="form-label">entries.</label>
        </div>
        <div class="form-group flex-row">
            <button class="btn btn-primary" type="submit" value="go">Update List</button>
        </div>
    </div>
    <div class="data-display-area flex-column">
        <label class='form-label'>Activity Log</label>
        <div class="table-container">
            <table class="log-table bordered">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Who accessed</th>
                        <th>Map name</th>
                        <th>Source / Comment</th>
                    </tr>
                </thead>
                <!--{section name=i loop=$entries}-->
                <!--{cycle values="color,altcolor" assign=class}-->
                <tr>
                    <td style="width:1in;" class="<!--{$class}-->"><!--{$entries[i].datetime|escape:'html'}--></td>
                    <td style="width:2in;" class="<!--{$class}-->"><!--{if !is_null($entries[i].account_id)}--><a href="./?do=contact.info&id=<!--{$entries[i].account_id}-->"><!--{$entries[i].account|escape:'html'}--></a><!--{else}--><!--{$entries[i].account|escape:'html'}--><!--{/if}--></td>
                    <td style="width:5in;" class="<!--{$class}-->"><!--{if !is_null($entries[i].project_id)}--><a href="./?do=project.edit1&id=<!--{$entries[i].project_id}-->"><!--{$entries[i].project|escape:'html'}--></a><!--{else}--><!--{$entries[i].project|escape:'html'}--><!--{/if}--></td>
                    <td style="width:5in;" class="<!--{$class}-->"><!--{$entries[i].comment|escape:'html'}--></td>
                </tr>
                <!--{/section}-->
            </table>
        </div>
    </div>
</form>
