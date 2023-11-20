<!--{$subnav}-->
<style>

    iframe#selectorFrame {
        display: flex;
        align-content: stretch;
        align-items: stretch;
        flex-direction: row;
        flex-wrap: nowrap;
        width:100%;
        justify-content: center;
        height: calc(100vh - 15.2em);
    }
</style>
<script>
    $(() => {
        $('#selectorFrame')[0].src = "<!--{$appsPath}-->/app_selector/sl-viewer/<!--{$mapId}-->";
        $('.contentarea').toggleClass('padless');
        $('.contentarea').toggleClass('flex-content');
    });
</script>
<iframe id="selectorFrame"></iframe>