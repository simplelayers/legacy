<style>
    .layer-stats {
        max-width: 20em;
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
    .layer-stats .stat-footer {
        padding:.5em;
    }
    .stat-header .form-group:first-child span {
        /* background: #f00; */
        /* padding-left: .25em; */
        margin-right: .7em;
        padding-left: .125em;
    }
    #description {
        line-height: 1.4em;
    }
    .stat-footer .flex-column .form-group {
        margin-top:.5em;
        margin-bottom:.5em;
    }
    .stat-footer .flex-column .form-group:last-child {
        margin-bottom: 0em;
    }
    .stat-footer .flex-column .form-group:first-child {
        margin-top: inherit;
    }
    
</style>

<form action="." method="post" onSubmit="return check_form(this);">
    <input type="hidden" name="do" value="layer.editrelational2"/>
    <input type="hidden" name="id" value="<!--{$layer->id}-->"/>
    <div class="flex-row flex-split">
        <div class="flex-column grow">
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
            <div class="form-group col-12">
                <button class="btn btn-primary" type="submit" name="submit">Save Changes</button>
            </div>
        </div>

        <div class="flex-column no-grow">
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
                <div class="flex-column form-group  stat-footer">
                    <label class="form-label">Spatialize Data</label>
                    <p class="instruction">Tabular data layers can be made into spatial layers. To do so, select the intended geometry type and click the Spatialize button below</p>
                    <div class=" form-group">
                        <select id="geomType_sel" class="form-select custom-select">
                            <option>Select a geometry type</option>
                            <option value="1">Point</option>
                            <option value="3">Line</option>
                            <option value="2">Polygon</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button onClick="Spatialize()" type="button" class="btn btn-primary">Spatialize</button>
                    </div>
                </div>


            </div>
        </div>
</form>
<script type="text/javascript">
    function Spatialize() {
        const layerId = <!--{$layer->id}-->;
        const geomType = $('#geomType_sel')[0].selectedIndex;
        if (geomType === 0) {
            alert('A geometry type must be specified to spatialize this layer');
            return;
        }
        window.location.href = '<!--{$baseURL}-->?do=layer.tabular.spatialize&layer=' + layerId + '&geom=' + geomType;
    }
    function check_form(formdata) {
        if (!formdata.elements['id'].value)
            return false;
        if (!formdata.elements['name'].value)
            return false;
        return true;
    }
    $(() => {
        $('.contentarea').toggleClass('flex-content', true);
    });
</script>



<script type="text/javascript">
    function reminder() {
        alert('It will take a moment to prepare your download.\nPlease be patient.');
    }
    $(function () {
        $('textarea[name="tags"]').tagsInput({
            width: '6in'
        });
    });
</script>
