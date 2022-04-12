<script type='text/javascript'>
    function forceThumbnail() {
        var url = document.getElementById('thumbnail').src;

        if (url.indexOf('force=') < 0) {
	    document.getElementById('thumbnail').src = url + '/?&force=' + new Date().getTime();
        } else {
            var segs = url.split('&');
            segs.pop();
            segs.push('force=' + new Date().getTime());
            document.getElementById('thumbnail').src = segs.join('&');
        }
    }
</script>
<style>
    .layer-stats {
        background: rgb(255,255,255); /* Old browsers */
        background: -moz-linear-gradient(top, rgba(255,255,255,1) 0%, rgba(229,229,229,1) 100%); /* FF3.6-15 */
        background: -webkit-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(229,229,229,1) 100%); /* Chrome10-25,Safari5.1-6 */
        background: linear-gradient(to bottom, rgba(255,255,255,1) 0%,rgba(229,229,229,1) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e5e5e5',GradientType=0 );    
    }
    .layer-stats .stat-header {
        background:#000;
        color:#fff;
        padding-left: .5em;
        padding-right: .5em;
        
    }
    .layer-stats .stat-header > .form-group {
        height: 1.75em;
    }
    .layer-stats .stat-header span {
        margin-right: .5em;
    }
    .stat-header .form-group:first-child span {
        /* background: #f00; */
        /* padding-left: .25em; */
        margin-right: .7em;
        padding-left: .125em;
    }
    form {
        width: 100%;
    }
    #description {
        line-height: 1.4em;
    }

    .flex-row>.flex-column:first-child.grow {
        align-items: flex-start;
    }
    .listed:not(.show-list).stateful.req-not-showing,
    .listed.show-list.stateful.req-showing {
        display:initial !important;
    }
    .stateful {
        display: none !important;
    }
    .stateful-ability {
        pointer-events:none;
        opacity: .5;
    }
    .enabled.stateful-ability {
        pointer-events: initial;
        opacity: 1;
    }
    .table-container.listed.show-list.stateful.req-showing {
        display: block !important;
    }
    .table-container {
        height: calc(100vh - 52.5em);
        min-height: 10em;
        overflow: auto;
        border: 1px inset #000;
    }
    .table-container table {
        border:0px;
        border-collapse: collapse;
    }
    .table-container table thead {
        position:sticky !important;
        top: -1px;
    }
    #thumbnail {
        border: inset;
        border-color: #ddd;
        margin: .5em;
    }
    .extent-group table td {
        padding-right: .5em;
    }
    .contact-sel .form-check {
        margin-right:.25em;
    }
    .contact-sel select {
        margin-left: .25em;
        margin-right:.25em;
    }
    .contact-sel select:last-child {
        margin-right:0px;
    }

</style>
<script src="contexts/dmi/templates/layer/vector/editvector1.tpl.js" type="text/javascript"></script>

<div class="flex-row flex-split">
    <div class="flex-column grow">
        <form action="." method="post" onSubmit="return check_form(this);">
            <input type="hidden" name="do" value="layer.editvector2"/>
            <input type="hidden" name="id" value="<!--{$layer->id}-->"/>

            <div class="flex-row col-12">
                <div class="flex-column col-6">
                    <div class="form-group col-12">
                        <label class="form-label">Name:</label>
                        <input id="name" class="form-control" type="text" name="name" value="<!--{$layer->name}-->" maxlength="50"  >
                    </div>
                    <div class="form-group col-12">
                        <label class="form-label">Tags:</label>
                        <textarea name="tags" class="form-control tagsinput" ><!--{$layer->tags}--></textarea>
                    </div>
                </div>

                <div class="flex-column col-6">
                    <div class="form-group col-12">
                        <label class="form-label">Description:</label>
                        <textarea id="description" class="form-control" name="description" rows="5"><!--{$layer->description}--></textarea>
                    </div>

                </div>
            </div>
            <div class="flex-row col-12">
                <div class="flex-column col-6">
                    <div class="form-group col-12">
                        <label class="form-label">Tooltip:</label>
                        <textarea name='tooltip' class="form-control" rows="5"><!--{$layer->tooltip}--></textarea>
                    </div>
                </div>
                <div class="flex-row col-6">
                    <div class="flex-column col-12">
                        <label class="form-label">Rich Tooltip:</label>
                        <textarea name='rich_tooltip' class="form-control" rows="5" ><!--{$layer->rich_tooltip}--></textarea>
                    </div>
                </div>
            </div>
            <div class="flex-row col-12">
                <div class="flex-column col-6">
                    <div class="form-group col-12">
                        <label class="form-label">Search Tip: <img style="margin-left:5px;" src="media/icons/information.png" title="Search Tip appears in the viewer when doing searches, use this to identify search recommendations or special search tricks for getting useful results for this layer."></label>
                        <textarea name="searchtip"class="form-control" rows="5"><!--{$layer->searchtip}--></textarea>
                    </div>
                    <!--{if $hasEditPrivilege}-->
                        <div class="form-group col-12">
                            <button class="btn btn-primary" type="submit" name="submit" >Save Changes</button>
                        </div>
                    <!--{/if}-->

                </div>
                <div class="flex-column col-6">
                    <div class="form-group col-12 flex-column">
                        <label class="form-label">Default Search:</label>
                        <div class="flex-row col-12 contact-sel">
                            <input onclick="window.controller.ToggleCriteria()" class="form-check mr1" id="defaultsearch" type="checkbox" name="defaultsearch" <!--{if !is_null($defaultCriteria)}-->checked="checked"<!--{/if}-->/>
                            <select class="criterial stateful-ability req-enabled form-select custom-select" id="search_field" name="search_field">

                            </select>
                            <select class="criterial stateful-ability req-enabled form-select custom-select" id="comparison" name="comparison">
                            </select>
                            <input class="criterial stateful-ability req-enabled form-control" id="searchVal" name="searchVal" type="text" value="<!--{if !is_null($defaultCriteria)}--><!--{$defaultCriteria[2]}--><!--{/if}-->"/>
                        </div>
                    </div>  
                    <!--{if $hasGivePrivilege}-->
                        <div class="form-group flex-row col-12">
                            <label class="form-label">Change Owner</label>
                            <div class="form-group">
                                <button class="btn btn-primary mr1" type="button" onClick="window.controller.ToggleList()">
                                    <span class="listed stateful req-not-showing" id="show">Show List</span>
                                    <span class="listed stateful req-showing" class="toggle">Hide List</span>
                                </button>

                                <button type="button" class="btn btn-primary listed stateful req-showing" 
                                        onClick="$('input[name=\'contact\']').removeAttr('checked');">Clear Selection</button>
                            </div>

                        </div>
                        <div class="mb1 form-group flex-row col-12 listed stateful req-showing">
                            <span id='contact_selector' class="flex-inline mb-2" ></span>
                            <span id="selector" class="flex-inline"></span>
                        </div>

                        <div class="toggle form-group flex-row col-12 table-container listed stateful req-showing">
                            <!--{include file='list/contact.tpl'}-->
                        </div>

                    </div>


                </div>
            <!--{/if}-->
        </form>
    </div>

        
    <div class="layer-stats flex-column no-grow">
        <div class="stat-header form-group">
            <div class="form-group flex-row">
                <label class="form-label"><span class="fas fa-map-marker"></span>Layer Features:</label>
                <!--{$recordcount}-->
            </div>
            <div class="form-group flex-row">
                <label class="form-label"><span class="fas fa-hdd"></span>Disk Space Used:</label> 
                <!--{Units::bytesToString($layer->diskusage,2)}-->
            </div>
        </div>
        <div class="form-group flex-column">
            <!--{if $layer->backup}-->
                <label class="form-label">Last Backup:</label>
                <!--{$layer->backuptime}-->
            <!--{/if}-->
        </div>
        
        <div class="form-group">
            <img class="thumbnail"  id="thumbnail" src="<!--{$svcsURL}-->download/thumbnail/layer:<!--{$layer->id}-->/token:<!--{$token}-->" onClick="forceThumbnail()" />
        </div>
        <div class="form-group extent-group">
            <!--{$layer->getExtentPretty()}-->
        </div>
        
        <div class="form-group flex-column mb-2">
            <label class="form-label">Display no higher than scale:</label>
            <select class="form-select custom-select" id="minscale" name="minscale">
                <option value="200000000">global</option>
                <option value="25000000">1:25,000,000</option>
                <option value="10000000">1:10,000,000</option>
                <option value="5000000">1:5,000,000</option>
                <option value="3000000">1:3,000,000</option>
                <option value="2000000">1:2.000,000</option>
                <option value="1000000">1:1,000,000</option>
                <option value="500000">1:500,000</option>
                <option value="250000">1:250,000</option>
                <option value="126720">1:126,720</option>
                <option value="125000">1:125,000</option>
                <option value="100000">1:100,000</option>
                <option value="80000">1:80,000</option>
                <option value="63360">1:63,360</option>
                <option value="62500">1:62,500</option>
                <option value="50000">1:50,000</option>
                <option value="31680">1:31,680</option>
                <option value="25000">1:25,000</option>
                <option value="24000">1:24,000</option>
                <option value="20000">1:20,000</option>
                <option value="15840">1:15,840</option>
                <option value="12000">1:12,000</option>
                <option value="10000">1:10,000</option>
                <option value="9600">1:9,600</option>
                <option value="9000">1:9,000</option>
                <option value="6000">1:6,000</option>
                <option value="5000">1:5,000</option>
                <option value="4800">1:4,800</option>
                <option value="2500">1:2,500</option>
                <option value="2400">1:2,400</option>
                <option value="100">1:100</option>
                <option value="2000">1:2,000</option>
                <option value="1200">1:1,200</option>
                <option value="1000">1:1,000</option>
            </select>
        </div>
    </div>
</div>
