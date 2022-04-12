<!--{$subnav}-->

<h3 class="section_head">WMS: Web Mapping Service</h3>
<p>
    Your map's layers are available via OGC Web Mapping Service (WMS).<br/>
    To use this service, use this URL in your programs:<br/>
    <code><!--{$baseURL}-->wapi/map/ogcwms/application:wms/project:<!--{$project->id}-->/&</code>
</p>
<p>
    To view the Capabilities document, <a target="_blank" href="<!--{$baseURL}-->wapi/map/ogcwms/application:wms/project:<!--{$project->id}-->/?&SERVICE=WMS&&REQUEST=GetCapabilities">click here</a>.
</p>

<!--{if $pageArgsInfo['isMapEditor']=='true'}-->
<h3 class="section_head">WFS: Web feature Service</h3>
<p>
    Your map's layers are available via OGC Web feature Service (WFS).<br/>
    To use this service, use this URL in your programs:<br/>
    <code><!--{$baseURL}-->wapi/map/ogcwfs/application:wfs/project:<!--{$project->id}-->/?&SERVICE=WFS&<?code>
    
</p>
<p>
    To view the Capabilities document, <a target="_blank" href="<!--{$baseURL}-->wapi/map/ogcwfs/application:wfs/project:<!--{$project->id}-->/?&SERVICE=WFS&REQUEST=GetCapabilities">click here</a>.
</p>

<h3 class="section_head">WCS: Web Coverage Service</h3>
<p>
    Your map's layers are available via OGC Web Coverage Service (WCS).<br/>
    To use this service, use this URL in your programs:<br/>
    <code><!--{$baseURL}-->wapi/map/ogcwcs/application:wcs/project:<!--{$project->id}-->/?&SERVICE=WCS&</code>
    
</p>
<p>
    To view the Capabilities document, <a target="_blank" href="<!--{$baseURL}-->wapi/map/ogcwcs/application:wcs/project:<!--{$project->id}-->/?&SERVICE=WCS&REQUEST=GetCapabilities">click here</a>.
</p>
<!--{/if}-->