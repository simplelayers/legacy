<!--{assign var="community" value=$user->community}-->
<!--{assign var="cantMake" value=($user->community && count($user->listLayers()) >= 3)}-->
<div class="toolbar">
<ul class="menulist" id="toolbarMenuContent">
 <li>
  <a><!--{$user->organization->name}--></a>
  <ul>
	<li><a href="<!--{$baseURL}-->?do=organization.info">Details</a></li>
	<li><a href="<!--{$baseURL}-->/organization/resources">Resources</a></li>
	<li><a href="<!--{$baseURL}-->/organization/license">License</a></li>
	<li><a href="<!--{$baseURL}-->/organization/employees/">Employees</a></li>
	<li><a href="<!--{$baseURL}-->?do=group.info">Group</a></li>
	<li><a href="<!--{$baseURL}-->?do=organization.report">Usage Report</a></li>
  </ul>
 </li>
 <li>
  <a href="<!--{$baseURL}-->?do=project.list">Maps</a>
 </li>
 <li>
  <a>Layers</a>
  <ul>
    <li><a href="<!--{$baseURL}-->?do=layer.list">Layers List</a></li>
    <li>
      <a>Import data</a>
      <ul>
        <!--{if $formatOptions['shp']['viewable']}-->
        	<li><a <!--{if $formatOptions['shp']['cantMake']}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=layer.io&amp;mode=import&amp;stage=1&amp;format=shp"<!--{/if}-->>Import shapefiles</a></li>
        <!--{/if}-->
        <!--{if $formatOptions['raster']['viewable']}-->
        <li><a <!--{if $formatOptions['raster']['cantMake']}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=layer.io&amp;mode=import&amp;stage=1&amp;format=raster"<!--{/if}--> >Import raster file</a></li>
        <!--{/if}-->
        <!--{if $formatOptions['wms']['viewable']}-->
        <li><a <!--{if $formatOptions['wms']['cantMake']}-->href="#" style="color:#999;"<!--{else}--> href="<!--{$baseURL}-->?do=layer.io&amp;mode=import&amp;stage=1&amp;format=wms" <!--{/if}-->>Import WMS server</a></li>
        <!--{/if}-->
        <!--{if $formatOptions['gps']['viewable']}-->
        <li><a <!--{if $formatOptions['gps']['viewable']}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=layer.io&amp;mode=import&amp;stage=1&amp;format=gps"<!--{/if}-->>Import GPS data</a></li>
        <!--{/if}-->
        <!--{if $formatOptions['kmlz']['viewable']}-->
        <li><a <!--{if $formatOptions['kmlz']['viewable']}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=import.kml1"<!--{/if}-->>Import KML/KMZ data</a></li>
        <!--{/if}-->
        <!--{if $formatOptions['gen']['viewable']}-->
        <li><a <!--{if $formatOptions['gen']['viewable']}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=import.gen1"<!--{/if}-->>Import GEN file</a></li>
        <!--{/if}-->
        <!--{if $formatOptions['delim']['viewable']}-->
        <li><a <!--{if $formatOptions['delim']['viewable']}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=import.csv1"<!--{/if}-->>Import delimited text file</a></li>
        <!--{/if}-->
        <!--{if $formatOptions['odbc']['viewable']}-->
        <li><a <!--{if $formatOptions['odbc']['viewable']}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=import.odbc1"<!--{/if}-->>Remote database via ODBC</a></li>
        <!--{/if}-->
      </ul>
    </li>
    <li>
      <a>Create a new layer</a>
      <ul>
        <li><a <!--{if $cantMake}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=vector.create1&amp;type=<!--{$smarty.const.GEOMTYPE_POINT}-->"<!--{/if}-->>Feature Layer</a></li>
        <li><a <!--{if $cantMake}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=layer.createrelational1"<!--{/if}-->>Relational layer</a></li>
		<li><a <!--{if $cantMake}-->href="#" style="color:#999;"<!--{else}-->href="<!--{$baseURL}-->?do=layer.collection.edit1"<!--{/if}-->>Layer collection</a></li>
      </ul>
    </li>
  </ul>
 </li>
 <li>
  <a href="<!--{$baseURL}-->?do=contact.list">Contacts</a>
 </li>
 <li>
  <a href="<!--{$baseURL}-->?do=group.list">Groups</a>
 </li>
 <!--{if $adminOptions['view']}-->
  <li>
  <a>Administration</a>
  <ul>
  	<!--{if $adminOptions['user_accounts']['view']}-->  	
    <li>
    <a>User accounts</a>
      <ul>
      	
        <li><a href="<!--{$baseURL}-->?do=admin.userlist">Overview</a></li>
        <li><a href="<!--{$baseURL}-->?do=admin.spoof">Spoof user account</a></li>
        <li><a href="<!--{$baseURL}-->?do=admin.usersetupbookmarks1">Defaults: Bookmarks</a></li>
        <li><a href="<!--{$baseURL}-->?do=admin.usersetupfriends1">Defaults: Friends</a></li>
        <li><a href="<!--{$baseURL}-->?do=admin.usersetuplayers1">Defaults: Data layers</a></li>
      </ul>
    </li>
    <!--{/if}-->
    <!--{if $adminOptions['logs']['view']}-->
    <li>    
    <a>Activity Logs</a>
      <ul>
      <!--{if $adminOptions['logs']['maps']}-->
        <li><a href="<!--{$baseURL}-->?do=admin.projectlog">Map usage</a></li>
       <!--{/if}-->
       <!--{if $adminOptions['logs']['logins']}-->
        <li><a href="<!--{$baseURL}-->?do=admin.loginlog">Account logins</a></li>
        <!--{/if}-->
        <!--{if $adminOptions['logs']['accounts']}-->
        <li><a href="<!--{$baseURL}-->?do=admin.accountlog">Account changes</a></li>
        <!--{/if}-->
        <!--{if $adminOptions['logs']['layers']}-->
        <li><a href="<!--{$baseURL}-->?do=admin.report.transactions">Layer Transactions</a></li>
        <!--{/if}-->
      </ul>
    </li>
    <!--{/if}-->
    <!--{if $adminOptions['configuration']['view']}-->
    <li>
    <a>Configuration</a>
      <ul>
      	<!--{if $adminOptions['configuration']['permissions']}-->
      	<li><a href="<!--{$baseURL}-->admin/master_list/">Permissions Masterlist</a></li>
      	<!--{/if}-->
      	<!--{if $adminOptions['configuration']['plans']}-->
      	<li><a href="<!--{$baseURL}-->admin/plan_manager/">Plans Manager</a></li>
      	<!--{/if}-->
      	<!--{if $adminOptions['configuration']['roles']}-->
      	<li><a href="<!--{$baseURL}-->admin/roles_manager/">Roles Manager</a></li>
      	<!--{/if}-->
      	<!--{if $adminOptions['configuration']['seats']}-->
      	<li><a href="<!--{$baseURL}-->admin/seat_manager/">Seats Manager</a></li>
      	<!--{/if}-->
      	<!--{if null eq true}-->
        <li><a href="<!--{$baseURL}-->?do=admin.configidentification1">System identification</a></li>
        <li><a href="<!--{$baseURL}-->?do=admin.configquotas1">Quotas and pricing</a></li>
        <!--{/if}-->
        
      </ul>
    </li>
    <!--{/if}-->
	<li>
	<!--{if $adminOptions['organizations']['list']}-->
    <a>Organizations</a>
      <ul>
        <li><a href="<!--{$baseURL}-->admin/organization/list/">List</a></li>
        <li><a href="<!--{$baseURL}-->admin/organization/list/cmd:new_org/">New</a></li>
        <li><a href="<!--{$baseURL}-->admin/organization/invites">Invites</a></li>
      </ul>
    </li>
    <!--{/if}-->
  </ul>
 
 </li>
 <!--{/if}-->
  <li><a href="<!--{$baseURL}-->/organization/invites/cmd:add">Invites</a></li>
</ul>


<script type="text/javascript">
//<![CDATA[
var listMenu = new FSMenu('listMenu', true, 'display', 'block', 'none');
listMenu.animations[listMenu.animations.length] = FSMenu.animFade;
listMenu.animations[listMenu.animations.length] = FSMenu.animSwipeDown;
var arrow = null;
if (document.createElement && document.documentElement) {
  arrow = document.createElement('span');
  arrow.appendChild(document.createTextNode('>'));
  arrow.className = 'subind';
}
$('#toolbarMenuContent').disableSelection(); 
$( document ).bind("click", function( e ) {
	listMenu.hideAll();
});
addEvent(window, 'load', new Function('listMenu.activateMenu("toolbarMenuContent", arrow)'));
//]]>
</script>
<br/>
</div>