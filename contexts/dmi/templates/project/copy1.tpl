<div class="flex-content">
    <form id="form" action="." method="post" onSubmit="return check_form(this);">
        <input type="hidden" name="do" value="project.copy2"/>
        <input type="hidden" name="id" id="id" value="<!--{$project->id}-->"/>
        <!-- at the top: the simple attributes -->
        <div class="form-group">
            <label class="form-label"> Name for new Map</label  >
            <input class="form-control" type="text" name="name" value="Copy of <!--{$project->name}-->" maxlength="50" style="width:4in;" />
        </div>
        <div class="form-group flex-row">
            <button class="btn btn-primary" type="submit" name="submit" >Copy Map</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    function check_form(formdata) {
        if (!formdata.elements['name'].value) {
            return false;
        }
        return save();
    }
</script>
